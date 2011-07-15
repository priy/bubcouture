<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_cart extends shopPage
{

    public $customer_template_type = "cart";
    public $noCache = TRUE;

    public function ctl_cart( &$system )
    {
        parent::shoppage( $system );
        $this->_verifyMember( FALSE );
        if ( !$this->system->getConf( "system.use_cart", TRUE ) )
        {
            $system->responseCode( 404 );
            echo "<h1>cart has been disabled</h1>";
            exit( );
        }
        $this->objCart =& $this->system->loadModel( "trading/cart" );
        $this->objCart->checkMember( $this->member );
        if ( $_POST['isfastbuy'] )
        {
            if ( $_POST['goods'] )
            {
                $aParams = $this->objCart->getParams( $_POST['goods'] );
                $this->cart = $this->objCart->setFastBuy( "g", $aParams );
                setcookie( "S[Cart_Fastbuy]", $this->objCart->_save( $this->cart ) );
            }
            else
            {
                $this->cart = $this->objCart->getCart( "all", $_COOKIE['Cart_Fastbuy'] );
            }
        }
        else
        {
            $this->cart = $this->objCart->getCart( "all" );
        }
        $this->products = $this->cart['g'];
        $this->pkggoods = $this->cart['p'];
        $this->gifts = $this->cart['f'];
    }

    public function addPkgToCart( $pkgId, $num = 1 )
    {
        $this->begin( $this->system->mkUrl( "package" ) );
        $aPkg['pkgid'] = $pkgId;
        $status = $this->objCart->addToCart( "p", $aPkg, $num );
        if ( $_POST['mini_cart'] )
        {
            if ( $status )
            {
                $this->view( 1 );
                exit( );
            }
            else
            {
                $this->end( fasle );
            }
        }
        else
        {
            $this->end( $status, __( "添加成功" ), $this->system->mkUrl( "cart" ) );
        }
    }

    public function addGiftToCart( $giftId, $num = 1 )
    {
        if ( !intval( $num ) )
        {
            $num = 1;
        }
        $aParams = $this->_addGift( $giftId, $num );
        switch ( $aParams )
        {
        case "less_point" :
            $this->begin( $url = $this->system->mkUrl( "gift", "showList" ) );
            $message = __( "用户积分不足" );
            break;
        case "less_store" :
            $this->begin( $url = $this->system->mkUrl( "gift", "showList" ) );
            $message = __( "库存不足/购买数量超过限定数量/过期/超过最大购买限额" );
            break;
        case "no_login" :
            $this->begin( $url = $this->system->mkUrl( "passport", "login" ) );
            $message = __( "您还未登陆" );
        default :
            $this->begin( $this->system->mkUrl( "gift", "showList" ) );
            $this->objCart->addToCart( "f", $aParams, $num );
            break;
        }
        if ( $message )
        {
            $this->end( FALSE, $message, $url );
        }
        else
        {
            $this->end( TRUE, __( "添加成功" ), $this->system->mkurl( "cart" ) );
        }
    }

    public function _addGift( $giftId, $num = 1 )
    {
        $aParams['gift_id'] = $giftId;
        $oGift =& $this->system->loadModel( "trading/gift" );
        $aGiftInfo = $oGift->getGiftById( $giftId );
        $aCart = $this->objCart->getCart( "f" );
        if ( $aCart[$giftId] )
        {
            $nums = $aCart[$giftId]['num'] + $num;
        }
        else
        {
            $nums = $num;
        }
        if ( $GLOBALS['runtime']['member_lv'] <= 0 )
        {
            return "no_login";
            exit( );
        }
        if ( $this->member['point'] < $this->objCart->getCartCPoint( ) + $aGiftInfo['point'] * $num )
        {
            return "less_point";
            exit( );
        }
        if ( !$oGift->isOnSale( $aGiftInfo, $GLOBALS['runtime']['member_lv'], $nums ) )
        {
            return "less_store";
            exit( );
        }
        return $aParams;
    }

    public function addGoodsToCart( $gid = 0, $pid = 0, $stradj = "", $pmtid = 0, $num = 1 )
    {
        $aParams = $this->objCart->getParams( $_POST['goods'], $gid, $pid, $stradj, $pmtid );
        if ( $aParams['pid'] == -1 )
        {
            $this->begin( $_SERVER['HTTP_REFERER'] );
            trigger_error( __( "加入购物车失败：无此货品" ), E_USER_ERROR );
            $this->end( );
        }
        $_num = intval( $aParams['num'] );
        if ( $_num )
        {
            $num = $_num;
        }
        else
        {
            $num = intval( $num );
        }
        if ( !$num )
        {
            $num = 1;
        }
        $status = $this->objCart->addToCart( "g", $aParams, $num );
        if ( $status === "notify" )
        {
            $this->begin( $this->system->mkUrl( "product", "gnotify", array(
                $gid,
                $pid
            ) ) );
            $this->setError( 10001 );
            if ( $_POST['mini_cart'] )
            {
                header( "HTTP/1.0 404 Not Found" );
            }
            trigger_error( __( "加入购物车失败：商品缺货，转入缺货登记" ), E_USER_ERROR );
            $this->end( );
        }
        else if ( !$status )
        {
            $this->begin( $_SERVER['HTTP_REFERER'] );
            $this->setError( 10002 );
            if ( $_POST['mini_cart'] )
            {
                header( "HTTP/1.0 404 Not Found" );
            }
            trigger_error( __( "加入购物车失败: 商品库存不足或者提交参数错误！" ), E_USER_ERROR );
            $this->end( );
        }
        else
        {
            if ( $_POST['fastbuy'] )
            {
                $this->checkout( );
            }
            else
            {
                if ( $_POST['mini_cart'] )
                {
                    $this->view( 1 );
                    exit( );
                }
                $this->redirect( "cart" );
            }
        }
    }

    public function ajaxAdd( )
    {
        switch ( $_POST['type'] )
        {
        case "g" :
            $aParams = $this->objCart->getParams( "", $_POST['gid'], $_POST['pid'], "", 0 );
            break;
        case "p" :
            $aParams['pkgid'] = $_POST['gid'];
            break;
        case "f" :
            if ( !intval( $num ) )
            {
                $num = 1;
            }
            $aParams = $this->_addGift( $giftId, $num );
            if ( !is_array( $aParams ) )
            {
                $this->system->_succ = FALSE;
                exit( );
            }
            break;
        }
        if ( !intval( $_POST['num'] ) )
        {
            $GLOBALS['_POST']['num'] = 1;
        }
        $this->objCart->addToCart( $_POST['type'], $aParams, intval( $_POST['num'] ) );
        $this->system->_succ = TRUE;
        exit( );
    }

    public function removeCart( $objType = "g" )
    {
        $this->objCart->removeCart( $objType, $_POST['cartNum'][$objType] );
        $this->cartTotal( );
    }

    public function updateCart( $objType = "g", $key = "" )
    {
        $key = str_replace( "@", "-", $key );
        $nQuantity = $_POST['cartNum'][$objType][$key];
        switch ( $objType )
        {
        case "f" :
            $oCart->member['member_lv_id'] = $GLOBALS['runtime']['member_lv'];
            $oCart->member['point'] = $this->member['point'];
            break;
        case "g" :
            break;
        case "p" :
            break;
        default :
            break;
        }
        if ( !$this->objCart->updateCart( $objType, $key, $nQuantity, $aError ) )
        {
            echo implode( "", $aError );
        }
        else
        {
            $this->cartTotal( );
        }
    }

    public function cartTotal( )
    {
        $this->ctl_cart( );
        $sale =& $this->system->loadModel( "trading/sale" );
        $trading = $sale->getCartObject( $this->cart, $GLOBALS['runtime']['member_lv'], TRUE );
        $this->pagedata['trading'] =& $trading;
        $this->__tmpl = "cart/cart_total.html";
        $this->output( );
    }

    public function index( )
    {
        $this->title = __( "查看购物车" );
        $sale =& $this->system->loadModel( "trading/sale" );
        $trading = $sale->getCartObject( $this->cart, $GLOBALS['runtime']['member_lv'], TRUE );
        $number = count( $trading['products'] ) + count( $trading['gift_e'] ) + count( $trading['package'] );
        if ( $number != $_COOKIE['CART_COUNT'] )
        {
            $this->system->setCookie( "CART_COUNT", $number );
        }
        $this->pagedata['alert_num'] = $this->system->getConf( "system.product.alert.num" );
        $this->pagedata['trading'] =& $trading;
        $cur =& $this->system->loadModel( "system/cur" );
        $aCur = $cur->getFormat( $this->system->request['cur'] );
        $this->pagedata['currency'] = json_encode( $aCur );
        header( "Expires: -1" );
        header( "Pragma: no-cache" );
        header( "Cache-Control: no-cache, no-store" );
        $this->output( );
    }

    public function view( $mini = 0 )
    {
        $sale =& $this->system->loadModel( "trading/sale" );
        $this->cart = $this->objCart->getCart( "all" );
        $this->pagedata['trading'] = $sale->getCartObject( $this->cart, $GLOBALS['runtime']['member_lv'], TRUE );
        $this->pagedata['cartCount'] = $_COOKIE['CART_COUNT'];
        $this->pagedata['cartNumber'] = $_COOKIE['CART_NUMBER'];
        $this->__tmpl = $mini ? "cart/mini_cart.html" : "cart/view.html";
        $this->output( );
    }

    public function merge( $sType = 0 )
    {
        switch ( $sType )
        {
        case 0 :
            $this->objCart->removeCart( );
            $this->objCart->memberLogin = FALSE;
            $aCart = $this->objCart->getCart( );
            $this->objCart->memberLogin = TRUE;
            $this->objCart->save( "all", $aCart );
            $this->system->setcookie( $oCart->cookiesName, "" );
            break;
        case 1 :
            $cartDb = $this->objCart->getCart( );
            $this->objCart->memberLogin = FALSE;
            $cartCookie = $this->objCart->getCart( );
            $aCart = $this->objCart->mergeCart( $cartCookie, $cartDb );
            $this->objCart->memberLogin = TRUE;
            $this->objCart->save( "all", $aCart );
            $this->system->setcookie( $oCart->cookiesName, "" );
            break;
        case 2 :
            $aCart = $this->objCart->getCart( );
            $this->system->setcookie( $oCart->cookiesName, "" );
            break;
        }
        $this->objCart->setCartNum( $aCart );
        header( "Location: ".( $_GET['forward'] ? $_GET['forward'] : $this->system->base_url( ) ) );
        $this->system->_succ = TRUE;
        exit( );
    }

    public function checkout( $isfastbuy = 0 )
    {
        if ( $isfastbuy )
        {
            $this->cart = $this->objCart->getCart( "all", $_COOKIE['Cart_Fastbuy'] );
            $this->products = $this->cart['g'];
            $GLOBALS['_POST']['isfastbuy'] = 1;
        }
        $this->title = __( "填写购物信息" );
        if ( count( $this->products['cart'] ) + count( $this->pkggoods ) + count( $this->gifts ) == 0 )
        {
            $this->redirect( "cart" );
            exit( );
        }
        if ( !$this->member['member_id'] && !$_COOKIE['ST_ShopEx-Anonymity-Buy'] )
        {
            $this->redirect( "cart", "loginBuy", array(
                $_POST['isfastbuy']
            ) );
            exit( );
        }
        $aOut = $this->objCart->getCheckout( $this->cart, $this->member, $this->system->request['cur'] );
        $this->pagedata['has_physical'] = $aOut['has_physical'];
        $this->pagedata['minfo'] = $aOut['minfo'];
        $this->pagedata['areas'] = $aOut['areas'];
        $this->pagedata['dlytime'] = date( "Y-m-d", time( ) + floatval( $this->system->getConf( "site.delivery_time" ) ) * 3600 * 24 );
        $this->pagedata['currencys'] = $aOut['currencys'];
        $this->pagedata['currency'] = $aOut['currency'];
        $payment = $this->system->loadModel( "trading/payment" );
        $payment->showPayExtendCon( $aOut['payments'] );
        $this->pagedata['payments'] = $aOut['payments'];
        if ( $aOut['payments'] )
        {
            foreach ( $aOut['payments'] as $key => $val )
            {
                if ( !$this->member['member_id'] && $val['pay_type'] == "deposit" )
                {
                    unset( $this->payments[$key] );
                    continue;
                }
                $this->pagedata['payments'][$key]['config'] = unserialize( $val['config'] );
            }
        }
        $this->pagedata['config'] = unserialize( $aOut['payments']['config'] );
        $aOut['trading']['history_GainScore'] = $aOut['trading']['totalScore'] + $aOut['trading']['receiver']['point'];
        $this->pagedata['trading'] = $aOut['trading'];
        if ( $this->member['member_id'] )
        {
            $member =& $this->system->loadModel( "member/member" );
            $addrlist = $member->getMemberAddr( $this->member['member_id'] );
            foreach ( $addrlist as $rows )
            {
                if ( empty( $rows['tel'] ) )
                {
                    $str_tel = __( "手机：" ).$rows['mobile'];
                }
                else
                {
                    $str_tel = __( "电话：" ).$rows['tel'];
                }
                $addr[] = array(
                    "addr_id" => $rows['addr_id'],
                    "def_addr" => $rows['def_addr'],
                    "addr_region" => $rows['area'],
                    "addr_label" => $rows['addr'].__( " (收货人：" ).$rows['name']." ".$str_tel.__( " 邮编：" ).$rows['zip'].")"
                );
            }
            $this->pagedata['trading']['receiver']['addrlist'] = $addr;
            $this->pagedata['is_allow'] = count( $addr ) < 5 ? 1 : 0;
        }
        else
        {
            setcookie( "S[ST_ShopEx-Anonymity-Buy]", "", time( ) - 1000 );
        }
        $this->output( );
    }

    public function loginBuy( $isfastbuy = 0 )
    {
        $this->title = __( "用户登陆或注册" );
        if ( 0 < $_COOKIE['CART_COUNT'] && $_COOKIE['UNAME'] )
        {
            $this->system->location( $this->system->mkUrl( "cart", "checkout" ) );
        }
        if ( $this->system->getConf( "site.login_valide" ) == "true" || $this->system->getConf( "site.login_valide" ) == TRUE )
        {
            $this->pagedata['valideCode'] = TRUE;
        }
        $this->pagedata['options']['url'] = $this->system->mkUrl( "cart", "checkout" );
        if ( $this->system->getConf( "site.register_valide" ) == TRUE || $this->system->getConf( "site.register_valide" ) == "true" )
        {
            $this->pagedata['SignUpvalideCode'] = TRUE;
        }
        if ( $this->system->getConf( "site.login_valide" ) == TRUE || $this->system->getConf( "site.login_valide" ) == "true" )
        {
            $this->pagedata['LogInvalideCode'] = TRUE;
        }
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $login_plugin = $appmgr->getloginplug( );
        $this->pagedata['mustMember'] = !$this->system->getConf( "security.guest.enabled" );
        if ( $isfastbuy )
        {
            $this->pagedata['isfastbuy'] = $isfastbuy;
        }
        $GLOBALS['_POST']['isfastbuy'] = $isfastbuy;
        $this->pagedata['to_buy'] = TRUE;
        if ( $_GET['mini_passport'] )
        {
            $this->__tmpl = "cart/loginbuy_fast.html";
            foreach ( $login_plugin as $key => $value )
            {
                $object = $appmgr->instance_loginplug( $value );
                if ( method_exists( $object, "getMiniHtml" ) )
                {
                    $this->pagedata['mini_login_content'][] = $object->getMiniHtml( );
                }
            }
        }
        else
        {
            foreach ( $login_plugin as $key => $value )
            {
                $object = $appmgr->instance_loginplug( $value );
                if ( method_exists( $object, "getCartHtml" ) )
                {
                    $this->pagedata['cart_login_content'][] = $object->getCartHtml( );
                }
            }
        }
        $this->pagedata['ref_url'] = $this->system->mkUrl( "cart", "checkout", array(
            $isfastbuy
        ) );
        $this->output( );
    }

    public function shipping( )
    {
        $sale =& $this->system->loadModel( "trading/sale" );
        $trading = $sale->getCartObject( $this->cart, $GLOBALS['runtime']['member_lv'], TRUE );
        $shipping =& $this->system->loadModel( "trading/delivery" );
        $aShippings = $shipping->getDlTypeByArea( $_POST['area'] );
        foreach ( $aShippings as $k => $s )
        {
            $aShippings[$k]['price'] = cal_fee( $s['expressions'], $trading['weight'], $trading['pmt_b']['totalPrice'], $s['price'] );
            $s['pad'] == 0 ? $aShippings[$k]['has_cod'] = 0 : $aShippings[$k]['has_cod'] = 1;
            if ( $s['protect'] == 1 )
            {
                $aShippings[$k]['protect'] = TRUE;
            }
            else
            {
                $aShippings[$k]['protect'] = FALSE;
            }
        }
        $this->pagedata['shippings'] = $aShippings;
        $this->display( "cart/checkout_shipping.html" );
    }

    public function payment( $type = "" )
    {
        $payment =& $this->system->loadModel( "trading/payment" );
        $oCur =& $this->system->loadModel( "system/cur" );
        $this->pagedata['payments'] = $payment->getByCur( $_POST['cur'], $type );
        $payment->showPayExtendCon( $this->pagedata['payments'] );
        $this->pagedata['delivery']['has_cod'] = $_POST['d_pay'];
        $this->pagedata['order']['payment'] = $_POST['payment'];
        $this->__tmpl = "common/paymethod.html";
        $this->output( );
    }

    public function total( )
    {
        $tarea = explode( ":", $_POST['area'] );
        $GLOBALS['_POST']['area'] = $tarea[count( $tarea ) - 1];
        $trading = $this->objCart->checkoutInfo( $this->cart, $this->member, $_POST );
        $trading['history_GainScore'] = $trading['totalScore'] + $trading['receiver']['point'];
        $this->pagedata['trading'] = $trading;
        $this->__tmpl = "cart/checkout_total.html";
        $this->output( );
    }

    public function removeCoupon( )
    {
        $this->objCart->removeCart( "c" );
        echo "<html><header><meta http-equiv=\"refresh\" content=\"0; url=".$this->system->mkUrl( "cart", "index" )."\"></header></html>";
    }

    public function applycoupon( )
    {
        $this->begin( $this->system->mkUrl( "cart", "index" ), NULL, E_ERROR | E_USER_ERROR | E_USER_WARNING );
        $oCoupon =& $this->system->loadModel( "trading/coupon" );
        if ( !empty( $_POST['coupon'] ) )
        {
            $oSale =& $this->system->loadModel( "trading/sale" );
            $oPromotion =& $this->system->loadModel( "trading/promotion" );
            $trading = $oSale->getCartObject( $this->cart, $GLOBALS['runtime']['member_lv'], TRUE );
            if ( $trading['ifCoupon'] )
            {
                if ( !$oPromotion->apply_coupon_pmt( $trading, $_POST['coupon'], $GLOBALS['runtime']['member_lv'] ) )
                {
                    $this->end( FALSE, __( "无效优惠券" ), $this->system->mkUrl( "cart", "index" ) );
                }
            }
            else
            {
                trigger_error( __( "有促销活动期间是不否允许使用优惠券" ), E_USER_ERROR );
                $this->end( FALSE, __( "有促销活动期间是不否允许使用优惠" ), $this->system->mkUrl( "cart", "index" ) );
            }
        }
        else
        {
            trigger_error( __( "请输入优惠券" ), E_USER_ERROR );
            $this->end( FALSE, __( "请输入优惠券" ), $this->system->mkUrl( "cart", "index" ) );
        }
        $this->end( TRUE, __( "成功加入购物车" ), $this->system->mkUrl( "cart", "index" ) );
    }

    public function getReceiverList( )
    {
        $oMem =& $this->system->loadModel( "member/member" );
        $this->pagedata['receiver'] = $oMem->getMemberAddr( $this->member['member_id'] );
        $this->__tmpl = "common/dialog_receiver.html";
        $this->output( );
    }

    public function getAddr( )
    {
        if ( $_GET['addr_id'] )
        {
            $oMem =& $this->system->loadModel( "member/member" );
            $data = $oMem->getAddrById( $_GET['addr_id'] );
            if ( $this->member['member_id'] == $data['member_id'] )
            {
                $this->pagedata['trading']['receiver'] = $data;
            }
        }
        $areaId = explode( ":", $this->pagedata['trading']['receiver']['area'] );
        $areaId = $areaId[count( $areaId ) - 1];
        $this->pagedata['trading']['member_id'] = $this->member['member_id'];
        $this->__tmpl = "common/rec_addr.html";
        $this->output( );
    }

}

?>
