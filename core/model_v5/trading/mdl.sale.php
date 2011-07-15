<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_sale extends modelFactory
{

    public $_mlvid = NULL;
    public $_isCheck = false;

    public function _load_c( &$trading, $cart_c )
    {
        $oCoupon =& $this->system->loadModel( "trading/coupon" );
        if ( 0 < count( $cart_c ) )
        {
            $oCoupon =& $this->system->loadModel( "trading/coupon" );
            $trading['coupon_u'] = array( );
            foreach ( $cart_c as $couponCode => $aCoupon )
            {
                if ( $aCoupon['type'] == "goods" )
                {
                    foreach ( $trading['products'] as $p )
                    {
                        $goods_ids[] = $p['goods_id'];
                        $brand_ids[] = $p['brand_id'];
                        $cat_ids[] = $p['cat_id'];
                    }
                    if ( $couponPmt = $oCoupon->useMemberCoupon( $couponCode, $this->_mlvid, $goods_ids, $brand_ids, $cat_ids ) )
                    {
                        list( , $firstCouponPmt ) = each( $couponPmt );
                        foreach ( $trading['products'] as $k => $p )
                        {
                            if ( in_array( $trading['products'][$k]['goods_id'], $firstCouponPmt['goods_ids'] ) )
                            {
                                $trading['products'][$k]['pmt_id'] = $aCoupon['pmt_id'];
                            }
                        }
                        $trading['coupon_u'] = array_merge( $trading['coupon_u'], $couponPmt );
                    }
                    else
                    {
                        unset( $this->coupon_u[$couponCode] );
                        return false;
                    }
                }
                else if ( $aCoupon['type'] == "order" )
                {
                    $trading['coupon_u'] = array_merge( $trading['coupon_u'], $cart_c );
                }
            }
        }
    }

    public function _check_g( &$cart_g )
    {
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        foreach ( $cart_g['cart'] as $key => $num )
        {
            $aTmp = explode( "-", $key );
            $goodsId = $aTmp[0];
            if ( !$aGid[$goodsId] )
            {
                $pmtid = $oPromotion->getGoodsPromotionId( $goodsId, $this->_mlvid );
                $cart_g['pmt'][$goodsId] = $pmtid;
            }
            $aGid[$goodsId] = 1;
        }
    }

    public function _load_g( &$trading, $cart_g )
    {
        $oMath =& $this->system->loadModel( "system/math" );
        $product_ids = array( );
        foreach ( $cart_g['cart'] as $strKey => $num )
        {
            $aRow = explode( "-", $strKey );
            if ( $aRow[1] != "" )
            {
                $product_ids[] = $aRow[1];
            }
        }
        reset( $cart_g );
        if ( 0 < count( $product_ids ) )
        {
            $aProduct = $this->db->select( "SELECT p.*,t.setting,g.score,g.brand_id,g.cat_id,g.type_id,g.image_default,g.thumbnail_pic\n                    FROM sdb_products AS p\n                    LEFT JOIN sdb_goods AS g ON p.goods_id=g.goods_id\n                    LEFT JOIN sdb_goods_type AS t ON g.type_id=t.type_id\n                    WHERE p.product_id IN (".implode( ",", $product_ids ).")" );
        }
        else
        {
            $aProduct = array( );
        }
        foreach ( $aProduct as $k => $p )
        {
            if ( $this->_mlvid )
            {
                $aMprice = $this->db->selectrow( "SELECT price, dis_count FROM sdb_member_lv\n                        LEFT JOIN sdb_goods_lv_price ON level_id = member_lv_id AND product_id=".intval( $p['product_id'] )."\n                        WHERE member_lv_id=".intval( $this->_mlvid ) );
                if ( floatval( $aMprice['dis_count'] ) <= 0 )
                {
                    $aMprice['dis_count'] = 1;
                }
            }
            else
            {
                $aMprice['dis_count'] = 1;
            }
            $items_g[$p['product_id']]['bn'] = $p['bn'] ? $p['bn'] : $this->system->getConf( "system.product.autobn.prefix" ).str_pad( $p['product_id'] + $this->system->getConf( "system.product.autobn.beginnum", 100 ), $this->system->getConf( "system.product.autobn.length" ), 0, STR_PAD_LEFT );
            $items_g[$p['product_id']]['name'] = $p['name'].( $p['pdt_desc'] ? " (".stripslashes( $p['pdt_desc'] ).")" : "" );
            $items_g[$p['product_id']]['sale_price'] = $p['price'];
            $items_g[$p['product_id']]['price'] = $aMprice['price'] ? $aMprice['price'] : $oMath->getOperationNumber( $oMath->getOperationNumber( $p['price'] ) * $aMprice['dis_count'] );
            $items_g[$p['product_id']]['type_id'] = $p['type_id'];
            $items_g[$p['product_id']]['weight'] = $p['weight'];
            $items_g[$p['product_id']]['store'] = $p['store'];
            $items_g[$p['product_id']]['cost'] = $p['cost'];
            $items_g[$p['product_id']]['addon'] = array_merge( unserialize( $p['props'] ), unserialize( $p['setting'] ) );
            $items_g[$p['product_id']]['pdt_desc'] = stripslashes( $p['pdt_desc'] );
            $items_g[$p['product_id']]['goods_id'] = $p['goods_id'];
            $items_g[$p['product_id']]['product_id'] = $p['product_id'];
            $items_g[$p['product_id']]['image_default'] = $p['image_default'];
            $items_g[$p['product_id']]['thumbnail_pic'] = $p['thumbnail_pic'];
            $items_g[$p['product_id']]['score'] = $p['score'];
        }
        unset( $aProduct );
        $oCur =& $this->system->loadModel( "system/cur" );
        $aItems = array( );
        foreach ( $cart_g['cart'] as $strKey => $num )
        {
            $aRow = explode( "-", $strKey );
            if ( $aRow[0] != "" && $items_g[$aRow[1]] )
            {
                $strName = "";
                $adjPrice = 0;
                $adjCost = 0;
                $adjWeight = 0;
                $adjList = array( );
                if ( $aRow[2] != "na" )
                {
                    $aAdj = explode( "|", $aRow[2] );
                    $tmpAdjList = array( );
                    $tmpAdjGrp = array( );
                    $tmpAdjId = array( );
                    $strAdj = "";
                    foreach ( $aAdj as $val )
                    {
                        $adjItem = explode( "_", $val );
                        if ( 0 < $adjItem[0] && 0 < $adjItem[2] )
                        {
                            $tmpAdjList[] = $adjItem[2];
                            $tmpAdjGrp[] = $adjItem[1];
                            $tmpAdjId['product_id'][] = $adjItem[0];
                        }
                    }
                    if ( count( $tmpAdjId ) )
                    {
                        $objGoods =& $this->system->loadModel( "trading/goods" );
                        $strAdjunct = $objGoods->getGoodsMemo( $aRow[0], "adjunct" );
                        $aAdj = unserialize( $strAdjunct );
                        $objProduct =& $this->system->loadModel( "goods/finderPdt" );
                        $adjName = $objProduct->getList( "product_id, name, price, cost, weight", $tmpAdjId, 0, -1 );
                        if ( $adjName )
                        {
                            foreach ( $adjName as $val )
                            {
                                $aAdjuncts[$val['product_id']] = $val;
                            }
                            foreach ( $tmpAdjId['product_id'] as $key => $pid )
                            {
                                if ( $aAdj[$tmpAdjGrp[$key]] && $aAdjuncts[$pid] )
                                {
                                    $strName .= "+".$aAdjuncts[$pid]['name']."(".$tmpAdjList[$key].")";
                                    if ( $aAdj[$tmpAdjGrp[$key]]['set_price'] == "minus" )
                                    {
                                        $adjPrice += $oMath->minus( array(
                                            $aAdjuncts[$pid]['price'],
                                            $aAdj[$tmpAdjGrp[$key]]['price']
                                        ) ) * $tmpAdjList[$key];
                                    }
                                    else
                                    {
                                        $adjDiscount = abs( $aAdj[$tmpAdjGrp[$key]]['price'] ) ? abs( $aAdj[$tmpAdjGrp[$key]]['price'] ) : 1;
                                        $adjPrice += $oMath->getOperationNumber( $oMath->getOperationNumber( $aAdjuncts[$pid]['price'] ) * $tmpAdjList[$key] * $adjDiscount );
                                    }
                                    $adjList[$pid] += $tmpAdjList[$key];
                                    $strAdj .= $pid."_".$tmpAdjGrp[$key]."_".$tmpAdjList[$key]."|";
                                    $adjCost += $oMath->getOperationNumber( $aAdjuncts[$pid]['cost'] ) * $tmpAdjList[$key];
                                    $adjWeight += $aAdjuncts[$pid]['weight'] * $tmpAdjList[$key];
                                }
                            }
                        }
                        else
                        {
                            $strAdj = "na";
                        }
                    }
                    else
                    {
                        $strAdj = "na";
                    }
                }
                else
                {
                    $strAdj = "na";
                }
                $strKey = $aRow[0]."-".$aRow[1]."-".$strAdj;
                $linkKey = $aRow[0]."@".$aRow[1]."@".$strAdj;
                $aGoods = $items_g[$aRow[1]];
                $aGoods['addon']['adjinfo'] = $strAdj;
                $aGoods['addon']['adjname'] = $strName;
                $aGoods['adjList'] = $adjList;
                $aGoods['price'] = $oMath->plus( array(
                    $aGoods['price'],
                    $adjPrice
                ) );
                $aGoods['cost'] = $oMath->plus( array(
                    $aGoods['cost'],
                    $adjCost
                ) );
                $aGoods['amount'] = $oMath->getOperationNumber( $aGoods['price'] ) * $num;
                $aGoods['price'] = $oCur->formatNumber( $aGoods['price'], false );
                $aGoods['key'] = $strKey;
                $aGoods['link_key'] = $linkKey;
                $aGoods['enkey'] = base64_encode( $strKey );
                $aGoods['nums'] = $num;
                $aGoods['pmt_id'] = intval( $cart_g['pmt'][$aRow[0]] );
                $aGoods['weight'] += $adjWeight;
                switch ( $this->system->getConf( "point.get_policy" ) )
                {
                case 0 :
                    $aGoods['score'] = 0;
                    break;
                case 1 :
                    $aGoods['score'] = $aGoods['price'] * $this->system->getConf( "point.get_rate" );
                    break;
                default :
                    break;
                }
                $aItems[] = $aGoods;
                $trading['totalPrice'] = $oMath->plus( array(
                    $trading['totalPrice'],
                    $aGoods['amount']
                ) );
                $trading['totalGainScore'] += $aGoods['score'] * $num;
                $trading['weight'] += $aGoods['weight'] * $num;
            }
        }
        unset( $items_g );
        $trading['products'] =& $aItems;
    }

    public function _load_p( &$trading, $cart_p )
    {
        $oMath =& $this->system->loadModel( "system/math" );
        foreach ( $cart_p as $pid => $aItems )
        {
            $pkg_ids[] = $pid;
            $pkg_num[] = $aItems['num'];
        }
        $oCur =& $this->system->loadModel( "system/cur" );
        $totalPrice = 0;
        $oPackage =& $this->system->loadModel( "trading/package" );
        $items_p = $oPackage->getPackageByIds( $pkg_ids );
        $oPackage->getPackageItems( $pkg_ids, $items_p );
        foreach ( $items_p as $k => $v )
        {
            $adjList = array( );
            $strAdj = "";
            $strName = "";
            $adjCost = 0;
            if ( is_array( $v['items'] ) && count( $v['items'] ) )
            {
                foreach ( $v['items'] as $val )
                {
                    $strAdj .= $val['pkgid']."_0_".$val['pkgnum']."|";
                    $strName .= "+".$val['name']."(".$val['pkgnum'].")";
                    $adjList[$val['pkgid']] = $val['pkgnum'];
                    $adjCost += $oMath->getOperationNumber( $val['cost'] ) * $val['pkgnum'];
                }
            }
            $items_p[$k]['bn'] = $v['bn'] ? $v['bn'] : $this->system->getConf( "system.product.autobn.prefix" ).str_pad( $v['goods_id'] + $this->system->getConf( "system.product.autobn.beginnum", 100 ), $this->system->getConf( "system.product.autobn.length" ), 0, STR_PAD_LEFT );
            $items_p[$k]['name'] = $v['name'];
            $items_p[$k]['price'] = $v['price'];
            $items_p[$k]['price'] = $oCur->formatNumber( $items_p[$k]['price'], false );
            $items_p[$k]['cost'] = $adjCost;
            $items_p[$k]['type_id'] = $v['type_id'];
            $items_p[$k]['weight'] = $v['weight'];
            $items_p[$k]['store'] = $v['store'];
            $items_p[$k]['pdt_desc'] = $v['pdt_desc'];
            $items_p[$k]['goods_id'] = $v['goods_id'];
            $items_p[$k]['product_id'] = 0;
            $items_p[$k]['nums'] = $pkg_num[$k];
            $items_p[$k]['amount'] = $oMath->multiple( array(
                $items_p[$k]['nums'],
                $v['price']
            ) );
            $items_p[$k]['addon']['adjinfo'] = $strAdj;
            $items_p[$k]['addon']['adjname'] = $strName;
            $items_p[$k]['adjList'] = $adjList;
            switch ( $this->system->getConf( "point.get_policy" ) )
            {
            case 0 :
                $items_p[$k]['score'] = 0;
                break;
            case 1 :
                $items_p[$k]['score'] = $v['price'] * $this->system->getConf( "point.get_rate" );
                break;
            case 2 :
                $items_p[$k]['score'] = $v['score'];
                break;
            default :
                break;
            }
            $trading['totalPkgScore'] += $oMath->multiple( array(
                $items_p[$k]['score'],
                $items_p[$k]['nums']
            ) );
            $totalPrice += $oMath->getOperationNumber( $items_p[$k]['amount'] );
            $trading['weight'] += $items_p[$k]['weight'] * $items_p[$k]['nums'];
        }
        $trading['totalPkgPrice'] += $totalPrice;
        $trading['package'] =& $items_p;
    }

    public function _load_f( &$trading, $cart_f )
    {
        $totalConsumeScore = 0;
        $oGift =& $this->system->loadModel( "trading/gift" );
        $items_f = $oGift->getGiftByIds( array_keys( $cart_f ) );
        foreach ( $items_f as $k => $v )
        {
            $items_f[$k]['nums'] = $cart_f[$v['gift_id']]['num'];
            $items_f[$k]['amount'] = $items_f[$k]['nums'] * $items_f[$k]['point'];
            $items_f[$k]['weight'] = $v['weight'];
            $totalConsumeScore += $items_f[$k]['amount'];
            $trading['weight'] += $items_f[$k]['weight'] * $items_f[$k]['nums'];
        }
        $trading['totalConsumeScore'] += $totalConsumeScore;
        $trading['gift_e'] =& $items_f;
    }

    public function checkAll( &$aCart )
    {
        $bReturn = true;
        $bReturn = $bReturn && $this->_check_g( $aCart['g'] );
        return $bReturn;
    }

    public function loadAll( &$trading, $cart )
    {
        if ( is_array( $cart ) && 0 < count( $cart ) )
        {
            $trading = array( "weight" => 0, "totalPrice" => 0, "totalGainScore" => 0 );
            $w_count = 0;
            if ( $this->_isCheck )
            {
                $this->checkAll( $cart );
            }
            krsort( $cart );
            foreach ( $cart as $code => $c )
            {
                $s_count = count( $c );
                $w_count += $s_count;
                if ( 0 < $s_count )
                {
                    call_user_func_array( array(
                        $this,
                        "_load_".$code
                    ), array(
                        $trading,
                        $c
                    ) );
                }
            }
            return $w_count;
        }
        else
        {
            return false;
        }
    }

    public function getCartObject( $aCart, $mlvid, $showPromotion = false, $isCheck = true )
    {
        $this->_isCheck = $isCheck;
        $this->_mlvid = intval( $mlvid );
        $trading = null;
        $w_count = $this->loadAll( $trading, $aCart );
        $trading['ifCoupon'] = 1;
        if ( 0 < $w_count )
        {
            $oMath =& $this->system->loadModel( "system/math" );
            $trading['totalPrice'] = $oMath->plus( array(
                $trading['totalPrice'],
                $trading['totalPkgPrice']
            ) );
            $trading['pmt_b'] = array(
                "totalPrice" => $trading['totalPrice'],
                "totalGainScore" => $oMath->plus( array(
                    $trading['totalGainScore'],
                    $trading['totalPkgScore']
                ) )
            );
            $oPromotion =& $this->system->loadModel( "trading/promotion" );
            if ( $showPromotion )
            {
                $oPromotion->apply_pdt_pmt( $trading, $mlvid );
                $oPromotion->apply_order_pmt( $trading, $mlvid );
            }
            $trading['totalGainScore'] = intval( $trading['totalGainScore'] );
            $trading['totalConsumeScore'] = intval( $trading['totalConsumeScore'] );
            $trading['totalScore'] = $trading['totalGainScore'] - $trading['totalConsumeScore'];
            $this->mount_gift_p( $trading );
            $this->mount_coupon( $trading );
            $this->mount_pmt_o( $trading );
            return $trading;
        }
        else
        {
            return false;
        }
    }

    public function mount_gift_p( &$trading )
    {
        $aTmp = array( );
        $gift =& $this->system->loadModel( "trading/gift" );
        foreach ( $trading['gift_p'] as $k => $v )
        {
            $aTmp[$k] = $this->db->selectRow( "select gift_id,name,weight,point,storage from sdb_gift where gift_id=".$k );
            $aTmp[$k]['nums'] = $v;
        }
        $trading['gift_p'] = $aTmp;
    }

    public function mount_coupon( &$trading )
    {
        $aTmp = array( );
        foreach ( $trading['coupon_p'] as $k => $v )
        {
            $aTmp[$k] = $this->db->selectRow( "select cpns_name,cpns_id from sdb_coupons where cpns_id=".$k );
            $aTmp[$k]['nums'] = $v;
        }
        $trading['coupon_p'] = $aTmp;
    }

    public function mount_pmt_o( &$trading )
    {
        if ( !empty( $trading['pmt_o']['pmt_ids'] ) )
        {
            $oPromotion =& $this->system->loadModel( "trading/promotion" );
            $trading['pmt_o']['list'] = $oPromotion->getPromotionByIds( $trading['pmt_o']['pmt_ids'] );
        }
    }

}

?>
