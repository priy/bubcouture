<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_cart extends modelfactory
{

    var $cookiesName = "CART";
    var $memberLogin = false;

    function checkmember( $aMember )
    {
        $this->memInfo = $aMember;
        if ( $aMember['member_id'] && $aMember['uname'] )
        {
            $this->memberLogin = true;
        }
    }

    function addtocart( $objType = "g", &$aParams, $quantity = 1 )
    {
        switch ( $objType )
        {
        case "g" :
            if ( 0 < $aParams['gid'] && 0 < $aParams['pid'] && 0 < $quantity )
            {
                $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj'];
                $aCart = $this->getcart( "g" );
                if ( isset( $aCart['cart'][$cartKey] ) )
                {
                    $aCart['cart'][$cartKey] += $quantity;
                    $buyStatus = 1;
                }
                else
                {
                    $aCart['cart'][$cartKey] = $quantity;
                    $buyStatus = 0;
                }
                if ( 0 < $aParams['pmtid'] )
                {
                    $aCart['pmt'][$aParams['gid']] = $aParams['pmtid'];
                }
                $objGoods = $this->system->loadmodel( "trading/goods" );
                $aGoods = $objGoods->getmarketablebyid( $aParams['gid'] );
                if ( $aGoods['marketable'] == "false" )
                {
                    $this->seterror( 10001 );
                    trigger_error( __( "该货品已经下架" ), E_USER_NOTICE );
                    return false;
                }
                if ( !$this->_checkstore( $aParams['pid'], $aCart['cart'][$cartKey] ) )
                {
                    if ( $buyStatus == 0 )
                    {
                        $this->seterror( 10001 );
                        trigger_error( __( "库存不足" ), E_USER_NOTICE );
                        return false;
                    }
                    return "notify";
                }
                if ( $stradj != "na" )
                {
                    $aAdj = explode( "|", $aParams['adj'] );
                    foreach ( $aAdj as $val )
                    {
                        $adjItem = explode( "_", $val );
                        if ( !( 0 < $adjItem[0] ) && !( 0 < $adjItem[2] ) && $this->_checkstore( $adjItem[0], $adjItem[2] * $aCart['cart'][$cartKey] ) )
                        {
                            continue;
                        }
                        $this->seterror( 10001 );
                        trigger_error( __( "配件库存不足" ), E_USER_NOTICE );
                        return false;
                    }
                }
                return $this->save( "g", $aCart );
            }
            $this->seterror( 10001 );
            trigger_error( __( "参数错误!" ), E_USER_NOTICE );
            return false;
        case "p" :
            if ( $aParams['pkgid'] )
            {
                $aCart = $this->getcart( "p" );
                $aCart[$aParams['pkgid']]['num'] += $quantity;
                if ( !$this->_checkgoodsstore( $aParams['pkgid'], $aCart[$aParams['pkgid']]['num'] ) )
                {
                    $this->seterror( "10000" );
                    trigger_error( __( "捆绑商品数量不足" ), E_USER_ERROR );
                    return false;
                }
                return $this->save( "p", $aCart );
            }
            $this->seterror( 10001 );
            trigger_error( __( "参数错误!" ), E_USER_NOTICE );
            return false;
        case "f" :
            if ( $aParams['gift_id'] )
            {
                $aCart = $this->getcart( "f" );
                $aCart[$aParams['gift_id']]['num'] += $quantity;
                return $this->save( "f", $aCart );
            }
            $this->seterror( 10001 );
            trigger_error( __( "参数错误!" ), E_USER_NOTICE );
            return false;
        case "c" :
            if ( is_array( $aParams ) && count( $aParams == 1 ) )
            {
                foreach ( $aParams as $k => $c )
                {
                    $cart_c[$k] = array(
                        "type" => $c['type'],
                        "pmt_id" => $c['pmt_id']
                    );
                }
                return $this->save( "c", $cart_c );
            }
            $this->seterror( 10001 );
            trigger_error( __( "参数错误!" ), E_USER_NOTICE );
            return false;
        }
    }

    function updatecart( $objType = "g", $cartKey, $quantity, &$aMsg )
    {
        $quantity = intval( $quantity );
        if ( $quantity < 1 )
        {
            $aMsg[] = __( "输入更新数量不合法" );
            return false;
        }
        switch ( $objType )
        {
        case "g" :
            list( $goodsid, $productid, $stradj ) = explode( "-", $cartKey );
            $o_goods = $this->system->loadmodel( "goods/finderPdt" );
            $goods_info = $o_goods->instance( $productid );
            if ( 0 < $goodsid && 0 < $productid && 0 < $quantity )
            {
                $aCart = $this->getcart( $objType );
                $aCart['cart'][$cartKey] = $quantity;
                if ( !$this->_checkstore( $productid, $aCart['cart'][$cartKey] ) )
                {
                    $aMsg[] = __( $goods_info['name']."<br>商品库存不足" );
                    return false;
                }
                if ( $stradj != "na" )
                {
                    $aAdj = explode( "|", $stradj );
                    foreach ( $aAdj as $val )
                    {
                        $adjItem = explode( "_", $val );
                        if ( !( 0 < $adjItem[0] ) && !( 0 < $adjItem[2] ) && $this->_checkstore( $adjItem[0], $adjItem[2] * $aCart['cart'][$cartKey] ) )
                        {
                            continue;
                        }
                        $aMsg[] = __( $goods_info['name']."<br>配件库存不足" );
                        return false;
                    }
                }
                return $this->save( $objType, $aCart );
            }
            $aMsg[] = __( "参数错误" );
            return false;
        case "p" :
            if ( 0 < $quantity )
            {
                $aCart = $this->getcart( "p" );
                $aCart[$cartKey]['num'] = $quantity;
                if ( !$this->_checkgoodsstore( $cartKey, $aCart[$cartKey]['num'] ) )
                {
                    $aMsg[] = __( "捆绑商品库存不足" );
                    return false;
                }
                return $this->save( "p", $aCart );
            }
            $aMsg[] = __( "参数错误" );
            return false;
        case "f" :
            $oGift =& $this->system->loadmodel( "trading/gift" );
            $aGiftInfo = $oGift->getgiftbyid( $cartKey );
            if ( !( 0 < intval( $cartKey ) ) )
            {
                break;
            }
            if ( $oGift->isonsale( $aGiftInfo, $this->memInfo['member_lv_id'], $quantity ) )
            {
                if ( $aGiftInfo['point'] * $quantity <= $this->memInfo['point'] )
                {
                    $aCart = $this->getcart( $objType );
                    $aCart[$cartKey]['num'] = $quantity;
                    return $this->save( $objType, $aCart );
                }
                $aMsg[] = __( "用户积分不足" );
                return false;
            }
            $aMsg[] = __( "库存不足/购买数量超过限定数量/过期/超过最大购买限额" );
            return false;
        }
    }

    function removecart( $objType = "all", $aGoods = array( ) )
    {
        switch ( $objType )
        {
        case "g" :
            if ( is_array( $aGoods ) && !empty( $aGoods ) )
            {
                $aCart = $this->getcart( $objType );
                foreach ( $aCart['cart'] as $strKey => $v )
                {
                    if ( !$aGoods[$strKey] )
                    {
                        unset( $this->cart->$strKey );
                    }
                }
            }
            else
            {
                $aCart = array( );
            }
            return $this->save( "g", $aCart );
        case "p" :
            if ( is_array( $aGoods ) && !empty( $aGoods ) )
            {
                $aCart = $this->getcart( "p" );
                foreach ( $aCart as $goodsId => $v )
                {
                    if ( !$aGoods[$goodsId] )
                    {
                        unset( $aCart->$goodsId );
                    }
                }
            }
            else
            {
                $aCart = array( );
            }
            return $this->save( "p", $aCart );
        case "f" :
            if ( is_array( $aGoods ) && !empty( $aGoods ) )
            {
                $aCart = $this->getcart( "f" );
                foreach ( $aCart as $giftId => $v )
                {
                    if ( !$aGoods[$giftId] )
                    {
                        unset( $aCart->$giftId );
                    }
                }
            }
            else
            {
                $aCart = array( );
            }
            return $this->save( "f", $aCart );
        case "c" :
            $aCart = $this->getcart( );
            unset( $aCart->'c' );
            $this->save( "all", $aCart );
            break;
        case "all" :
            return $this->save( "all", array( ) );
        }
    }

    function getcartcpoint( )
    {
        $aCart = $this->getcart( "f" );
        $giftIds = array_keys( $aCart );
        $nums = array_item( $aCart, "num" );
        $count = 0;
        if ( $giftIds )
        {
            $oGift =& $this->system->loadmodel( "trading/gift" );
            $aGift = $oGift->getgiftbyids( $giftIds );
            foreach ( $aGift as $k => $item )
            {
                $count += $item['point'] * $nums[$k];
            }
        }
        return $count;
    }

    function setcartnum( &$aCart )
    {
        $sale =& $this->system->loadmodel( "trading/sale" );
        $trading = $sale->getcartobject( $aCart, $runtime['member_lv'], true );
        $count = count( $trading['products'] ) + count( $trading['gift_e'] ) + count( $trading['package'] );
        if ( $trading['products'] )
        {
            foreach ( $trading['products'] as $rows )
            {
                $number += $rows['nums'];
            }
        }
        if ( $trading['gift_e'] )
        {
            foreach ( $trading['gift_e'] as $rows )
            {
                $number += $rows['nums'];
            }
        }
        if ( $trading['package'] )
        {
            foreach ( $trading['package'] as $rows )
            {
                $number += $rows['nums'];
            }
        }
        if ( $count != $_COOKIE['CART_COUNT'] )
        {
            $this->system->setcookie( "CART_COUNT", $count );
        }
        if ( $number != $_COOKIE['CART_NUMBER'] )
        {
            $this->system->setcookie( "CART_NUMBER", $number );
        }
    }

    function getcart( $objType = "all", $sCookie = "" )
    {
        $aCart = $this->_getcart( $sCookie );
        if ( $objType == "all" )
        {
            return $aCart;
        }
        if ( $this->_verifyobjtype( $objType ) && is_array( $aCart[$objType] ) )
        {
            return $aCart[$objType];
        }
    }

    function _getcart( $sCookie = "" )
    {
        $aCart = array( );
        if ( !$sCookie )
        {
            if ( $this->memberLogin )
            {
                $oMember =& $this->system->loadmodel( "member/member" );
                $sCookie = $oMember->getcart( $this->memInfo['member_id'] );
            }
            else
            {
                $sCookie = $_COOKIE[$this->cookiesName];
            }
        }
        $aType = explode( "@", $sCookie );
        unset( $aType->0 );
    default :
        switch ( $sCurObj )
        {
            foreach ( $aType as $sType )
            {
                if ( !empty( $sType ) )
                {
                    $aItems = explode( ".", $sType );
                    $sCurObj = $aItems[0];
                    $sItem = $aItems[1];
                case "g" :
                    $aTmp = null;
                    $aSplit = explode( ";", $sItem );
                    $sCart = $aSplit[0];
                    $sPmt = $aSplit[1];
                    if ( !empty( $sCart ) )
                    {
                        $aRow = explode( ",", $sCart );
                        foreach ( $aRow as $sRow )
                        {
                            $aTmp = explode( "-", $sRow );
                            $aCart['g']['cart'][$aTmp[0]."-".$aTmp[1]."-".$aTmp[2]] = $aTmp[3];
                        }
                        $aRow = explode( ",", $sPmt );
                        foreach ( $aRow as $sRow )
                        {
                            $aTmp = explode( "-", $sRow );
                            if ( $aTmp[0] )
                            {
                                $aCart['g']['pmt'][$aTmp[0]] = $aTmp[1];
                            }
                        }
                    }
                    else
                    {
                        $aCart['g']['cart'] = array( );
                    }
                    continue;
                case "p" :
                    $aTmp = null;
                    $aRow = explode( ",", $sItem );
                    foreach ( $aRow as $sRow )
                    {
                        $aTmp = explode( "-", $sRow );
                        $aCart['p'][$aTmp[0]]['num'] = $aTmp[1];
                    }
                    continue;
                case "f" :
                    $aTmp = null;
                    $aRow = explode( ",", $sItem );
                    foreach ( $aRow as $sRow )
                    {
                        $aTmp = explode( "-", $sRow );
                        $aCart['f'][$aTmp[0]]['num'] = $aTmp[1];
                    }
                }
            }
            continue;
        case "c" :
            $aTmp = null;
            $aRow = explode( ",", $sItem );
            foreach ( $aRow as $sRow )
            {
                $aTmp = explode( "-", $sItem );
                $aCart['c'][$aTmp[0]]['pmt_id'] = $aTmp[1];
                switch ( $aTmp[2] )
                {
                case "o" :
                    $aTmp[2] = "order";
                    break;
                case "g" :
                    $aTmp[2] = "goods";
                }
                $aCart['c'][$aTmp[0]]['type'] = $aTmp[2];
            }
        }
        return $aCart;
    }

    function save( $objType, $aPara )
    {
        if ( $objType == "all" )
        {
            $aRet = $aPara;
        }
        else if ( $this->_verifyobjtype( $objType ) )
        {
            $aRet = $this->getcart( );
            $aRet[$objType] = $aPara;
        }
        else
        {
            return false;
        }
        $this->setcartnum( $aRet );
        if ( $aRet )
        {
            $sRet = $this->_save( $aRet );
        }
        else
        {
            $sRet = "";
        }
        if ( $this->memberLogin )
        {
            $oMember =& $this->system->loadmodel( "member/member" );
            $oMember->savecart( $this->memInfo['member_id'], $sRet );
        }
        else
        {
            $this->system->setcookie( $this->cookiesName, $sRet );
        }
        return true;
    }

    function _save( &$aRet )
    {
        $sRet = "";
    default :
        switch ( $sObj )
        {
            foreach ( $aRet as $sObj => $aObj )
            {
                if ( !is_array( $aObj ) && empty( $aObj ) )
                {
                    $sRet .= "@";
                case "g" :
                    $sRet .= $sObj.".";
                    $iLoop = 0;
                    foreach ( $aObj['cart'] as $item => $num )
                    {
                        if ( !$item && !$num )
                        {
                            if ( 0 < $iLoop )
                            {
                                $sRet .= ",";
                            }
                            $sRet .= $item."-".$num;
                            ++$iLoop;
                        }
                    }
                    $iLoop = 0;
                    $sRet .= ";";
                    foreach ( $aObj['pmt'] as $gid => $pmtid )
                    {
                        if ( !$gid && !$pmtid )
                        {
                            if ( 0 < $iLoop )
                            {
                                $sRet .= ",";
                            }
                            $sRet .= $gid."-".$pmtid;
                            ++$iLoop;
                        }
                    }
                    continue;
                case "p" :
                    $sRet .= $sObj.".";
                    $aComponents = array( );
                    foreach ( $aObj as $gid => $aProduct )
                    {
                        $aComponents[] = $gid."-".$aProduct['num'];
                    }
                    $sRet .= implode( ",", $aComponents );
                    continue;
                case "f" :
                    $sRet .= $sObj.".";
                    $aComponents = array( );
                    foreach ( $aObj as $gid => $aProduct )
                    {
                        $aComponents[] = $gid."-".$aProduct['num'];
                    }
                    $sRet .= implode( ",", $aComponents );
                }
            }
            continue;
        case "c" :
            $sRet .= $sObj.".";
            $aComponents = array( );
            foreach ( $aObj as $code => $aCoupon )
            {
                switch ( $aCoupon['type'] )
                {
                case "order" :
                    $aCoupon['type'] = "o";
                    break;
                case "goods" :
                    $aCoupon['type'] = "g";
                }
                $aComponents[] = $code."-".$aCoupon['pmt_id']."-".$aCoupon['type'];
            }
            $sRet .= implode( ",", $aComponents );
        }
        return $sRet;
    }

    function _checkstore( $pid, $num = 1, $orderid = "" )
    {
        $objProduct =& $this->system->loadmodel( "goods/products" );
        $aStore = $objProduct->getfieldbyid( $pid );
        $aOrderItem = $this->db->selectrow( "SELECT nums FROM sdb_order_items WHERE product_id='".$pid."' AND order_id='".$orderid."'" );
        if ( !is_null( $aStore['store'] ) )
        {
            $gStore = intval( $aStore['store'] ) - intval( $aStore['freez'] ) + intval( $aOrderItem['nums'] );
            if ( $gStore < $num )
            {
                return false;
            }
        }
        return true;
    }

    function _checkgoodsstore( $pid, $num = 1 )
    {
        $objGoods =& $this->system->loadmodel( "trading/goods" );
        $aStore = $objGoods->getfieldbyid( $pid );
        if ( !is_null( $aStore['store'] ) )
        {
            $gStore = intval( $aStore['store'] ) - intval( $aStore['freez'] );
            if ( $gStore < $num )
            {
                return false;
            }
        }
        return true;
    }

    function _verifyobjtype( $objType )
    {
        $_allObjType = array( "g", "p", "f", "c" );
        return in_array( $objType, $_allObjType );
    }

    function getcheckout( &$aCart, $aMember, $currency )
    {
        $trading = $this->checkoutinfo( $aCart, $aMember );
        $gtype =& $this->system->loadmodel( "goods/gtype" );
        foreach ( $trading['products'] as $p )
        {
            if ( 0 < $p['goods_id'] )
            {
                $aP[] = $p['goods_id'];
            }
        }
        if ( !empty( $aP ) && $trading['package'] || $trading['gift_e'] )
        {
            if ( !empty( $aP ) )
            {
                $deliverInfo = $gtype->deliveryinfo( $aP );
                $aOut['has_physical'] = $deliverInfo['physical'];
            }
            else
            {
                $aOut['has_physical'] = 1;
            }
            foreach ( $trading['products'] as $product )
            {
                if ( $deliverInfo['custom'][$product['type_id']] )
                {
                    $aOut['minfo'][$product['product_id']] = array(
                        "goods_id" => $product['goods_id'],
                        "nums" => $product['nums'],
                        "name" => $product['name'],
                        "minfo" => $deliverInfo['custom'][$product['type_id']]
                    );
                }
            }
            $oDly =& $this->system->loadmodel( "trading/delivery" );
            if ( $area = $oDly->getdlarealist( ) )
            {
                foreach ( $area as $a )
                {
                    $aOut['areas'][$a['area_id']] = $a['name'];
                }
            }
        }
        $payment =& $this->system->loadmodel( "trading/payment" );
        $oCur =& $this->system->loadmodel( "system/cur" );
        $currency = $oCur->getcur( $currency, true );
        $oMem =& $this->system->loadmodel( "member/member" );
        $trading['receiver'] = $oMem->getdefaultaddr( $aMember['member_id'] );
        $trading['receiver']['email'] = $aMember['email'];
        $trading['receiver']['point'] = $aMember['point'];
        $aOut['currencys'] = $oCur->curall( );
        $aOut['currency'] = $currency['cur_code'];
        $aOut['payments'] = $payment->getbycur( $currency['cur_code'] );
        $aOut['trading'] = $trading;
        return $aOut;
    }

    function checkoutinfo( &$aCart, &$aMember, $aParam = null )
    {
        $sale =& $this->system->loadmodel( "trading/sale" );
        $trading = $sale->getcartobject( $aCart, $aMember['member_lv_id'], true );
        $trading['total_amount'] = $trading['totalPrice'];
        if ( $aParam['shipping_id'] )
        {
            $shipping =& $this->system->loadmodel( "trading/delivery" );
            $aShip = $shipping->getdltypebyarea( $aParam['area'], 0, $aParam['shipping_id'] );
            if ( $trading['exemptFreight'] == 1 )
            {
                $trading['cost_freight'] = 0;
            }
            else
            {
                $trading['cost_freight'] = cal_fee( $aShip[0]['expressions'], $trading['weight'], $trading['pmt_b']['totalPrice'], $aShip[0]['price'] );
            }
            $trading['shipping_id'] = $aParam['shipping_id'];
            if ( $aParam['is_protect'] == "true" && $aShip[0]['protect'] )
            {
                $trading['is_protect'] = 1;
                $trading['cost_protect'] = max( $aShip[0]['protect_rate'] * $trading['totalPrice'], $aShip[0]['minprice'] );
            }
            $trading['total_amount'] += $trading['cost_freight'] + $trading['cost_protect'];
        }
        if ( $this->system->getconf( "site.trigger_tax" ) )
        {
            $trading['is_tax'] = 1;
            if ( isset( $aParam['is_tax'] ) && $aParam['is_tax'] == "true" )
            {
                $trading['tax_checked'] = "checked";
                $trading['cost_tax'] = $trading['totalPrice'] * $this->system->getconf( "site.tax_ratio" );
                $trading['total_amount'] += $trading['cost_tax'];
            }
            $trading['tax_rate'] = $this->system->getconf( "site.tax_ratio" );
        }
        $shipping_del =& $this->system->loadmodel( "trading/delivery" );
        $aShip_del = $shipping_del->gethascod( $aParam['shipping_id'] );
        if ( $aShip_del['has_cod'] != 1 && $aParam['payment'] )
        {
            $payment =& $this->system->loadmodel( "trading/payment" );
            $aPay = $payment->getpaymentbyid( $aParam['payment'] );
            $config = unserialize( $aPay['config'] );
            if ( $config['method'] != 2 )
            {
                $trading['cost_payment'] = $aPay['fee'] * $trading['total_amount'];
            }
            else
            {
                $trading['cost_payment'] = $config['fee'];
            }
            $trading['total_amount'] += $trading['cost_payment'];
        }
        $oMem =& $this->system->loadmodel( "member/member" );
        $trading['receiver'] = $oMem->getdefaultaddr( $aMember['member_id'] );
        $trading['history_GainScore'] = $trading['totalScore'] + $trading['receiver']['point'];
        $trading['score_g'] = $trading['pmt_b']['totalGainScore'];
        $trading['pmt_amount'] = $trading['pmt_b']['totalPrice'] - $trading['totalPrice'];
        $trading['member_id'] = $aMember['member_id'];
        $order =& $this->system->loadmodel( "trading/order" );
        $newNum = $order->getorderdecimal( $trading['total_amount'] );
        $trading['discount'] = $trading['total_amount'] - $newNum;
        $trading['total_amount'] = $newNum;
        $oCur =& $this->system->loadmodel( "system/cur" );
        $currency = $oCur->getcur( $aParam['cur'] );
        if ( $currency['cur_code'] )
        {
            $trading['cur_rate'] = $currency['cur_rate'];
        }
        else
        {
            $trading['cur_rate'] = 1;
        }
        $trading['final_amount'] = $newNum * $trading['cur_rate'];
        $trading['cur_sign'] = $currency['cur_sign'];
        $trading['cur_display'] = $this->system->request['cur'];
        $trading['cur_code'] = $currency['cur_code'];
        return $trading;
    }

    function setfastbuy( $objType = "g", $aParams )
    {
        if ( !$this->_checkstore( $aParams['pid'], 1 ) )
        {
            $this->seterror( 10001 );
            trigger_error( __( "库存不足" ), E_USER_NOTICE );
            return false;
        }
        $aAdj = explode( "|", $aParams['adj'] );
        foreach ( $aAdj as $val )
        {
            $adjItem = explode( "_", $val );
            if ( !( 0 < $adjItem[0] ) && !( 0 < $adjItem[2] ) && $this->_checkstore( $adjItem[0], $adjItem[2] ) )
            {
                continue;
            }
            $this->seterror( 10001 );
            trigger_error( __( "配件库存不足" ), E_USER_NOTICE );
            return false;
        }
        if ( $objType == "g" )
        {
            $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj'];
            $aCart['g']['cart'][$cartKey] = $aParams['num'];
            if ( 0 < $aParams['pmtid'] )
            {
                $aCart['pmt'][$aParams['gid']] = $aParams['pmtid'];
            }
        }
        return $aCart;
    }

    function getparams( $aIn, &$gid, &$pid, $stradj = "", $pmtid = 0 )
    {
        if ( is_array( $aIn ) )
        {
            $gid = intval( $aIn['goods_id'] );
            $pid = intval( $aIn['product_id'] );
            $gnum = intval( $aIn['num'] );
            foreach ( ( array )$aIn['adjunct'] as $key => $aAdj )
            {
                foreach ( ( array )$aAdj as $adjid => $num )
                {
                    $stradj .= $adjid."_".$key."_".$num."|";
                }
                $pmtid = $aIn['pmt_id'];
            }
        }
        if ( intval( $pmtid ) == 0 )
        {
            $oPromotion =& $this->system->loadmodel( "trading/promotion" );
            $mlvid = intval( $runtime['member_lv'] );
            $pmtid = $oPromotion->getgoodspromotionid( $gid, $mlvid );
        }
        if ( intval( $pid ) == 0 )
        {
            $objGoods =& $this->system->loadmodel( "trading/goods" );
            $aP = $objGoods->getproducts( $gid );
            $pid = $aP[0]['product_id'];
        }
        if ( $stradj === "" || $stradj === 0 )
        {
            $stradj = "na";
        }
        $aParams['gid'] = $gid;
        $aParams['pid'] = $pid;
        $aParams['adj'] = $stradj;
        $aParams['pmtid'] = $pmtid;
        $aParams['num'] = $gnum;
        return $aParams;
    }

    function mergecart( $cartCookie, $cartDb )
    {
        $aCart = array( );
        if ( $cartCookie['g']['cart'] )
        {
            $aCart['g']['cart'] = $cartCookie['g']['cart'];
            if ( $cartDb['g']['cart'] )
            {
                foreach ( $cartDb['g']['cart'] as $k => $num )
                {
                    $aCart['g']['cart'][$k] = intval( $num ) + intval( $aCart['g']['cart'][$k] );
                }
            }
        }
        else if ( $cartDb['g']['cart'] )
        {
            $aCart['g']['cart'] = $cartDb['g']['cart'];
        }
        if ( $cartCookie['p'] )
        {
            $aCart['p'] = $cartCookie['p'];
            if ( $cartDb['p'] )
            {
                foreach ( $cartDb['p'] as $k => $item )
                {
                    $aCart['p'][$k]['num'] = intval( $item['num'] ) + intval( $aCart['p'][$k]['num'] );
                }
            }
        }
        else if ( $cartDb['p'] )
        {
            $aCart['p'] = $cartDb['p'];
        }
        if ( $cartCookie['f'] )
        {
            $aCart['f'] = $cartCookie['f'];
            if ( $cartDb['f'] )
            {
                foreach ( $cartDb['f'] as $k => $item )
                {
                    $aCart['f'][$k]['num'] = intval( $item['num'] ) + intval( $aCart['f'][$k]['num'] );
                }
                return $aCart;
            }
        }
        else if ( $cartDb['f'] )
        {
            $aCart['f'] = $cartDb['f'];
        }
        return $aCart;
    }

}

?>
