<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_product extends shopPage
{

    public $_call = "call";
    public $type = "goods";
    public $seoTag = array
    (
        0 => "shopname",
        1 => "brand",
        2 => "goods_name",
        3 => "goods_cat",
        4 => "goods_intro",
        5 => "goods_brief",
        6 => "brand_kw",
        7 => "goods_kw",
        8 => "goods_price",
        9 => "update_time",
        10 => "goods_bn"
    );

    public function ctl_product( )
    {
        parent::shoppage( );
        $this->title = $this->system->getConf( "site.goods_title" );
        $this->keywords = $this->system->getConf( "site.goods_meta_key_words" );
        $this->desc = $this->system->getConf( "site.goods_meta_desc" );
    }

    public function call( )
    {
        $args = func_get_args( );
        $action = array_shift( $args );
        if ( method_exists( $this, $action ) )
        {
            call_user_func_array( array(
                $this,
                $action
            ), $args );
        }
        else
        {
            $objSchema =& $this->system->loadModel( "goods/schema" );
            $gid = array_shift( $args );
            if ( !is_int( $gid ) )
            {
                echo "Invalid Schema calling";
                exit( );
            }
            $objSchema->runFunc( $gid, $action, $args );
        }
    }

    public function getVirtualCatById( $cat_id = 0 )
    {
        $vobjCat =& $this->system->loadModel( "goods/virtualcat" );
        $xml =& $this->system->loadModel( "utility/xml" );
        $result = $vobjCat->getVirtualCatById( intval( $cat_id ) );
        $searchtools =& $this->system->loadModel( "goods/search" );
        foreach ( $result as $k => $v )
        {
            $filter = $vobjCat->_mkFilter( $result[$k]['filter'] );
            $cat_id = $filter['cat_id'];
            $filter = $searchtools->encode( $filter );
            $result[$k]['url'] = $this->system->mkUrl( "gallery", $this->system->getConf( "gallery.default_view" ), array(
                implode( ",", $cat_id ),
                $filter,
                "0",
                "",
                "",
                $result[$k]['virtual_cat_id']
            ) );
        }
        echo json_encode( $result );
    }

    public function index( $gid, $specImg = "", $spec_id = "" )
    {
        $this->id = $gid;
        $this->customer_source_type = "product";
        $this->customer_template_type = "product";
        $this->customer_template_id = $gid;
        $oseo =& $this->system->loadModel( "system/seo" );
        $seo_info = $oseo->get_seo( "goods", $gid );
        $this->title = $seo_info['title'] ? $seo_info['title'] : $this->system->getConf( "site.goods_title" );
        $this->keywords = $seo_info['keywords'] ? $seo_info['keywords'] : $this->system->getConf( "site.goods_meta_key_words" );
        $this->desc = $seo_info['descript'] ? $seo_info['descript'] : $this->system->getConf( "site.goods_meta_desc" );
        $member_lv = intval( $this->system->request['member_lv'] );
        $objProduct =& $this->system->loadModel( "goods/products" );
        $objGoods =& $this->system->loadModel( "trading/goods" );
        if ( !( $aGoods = $objGoods->getGoods( $gid, $member_lv ) ) )
        {
            $this->system->responseCode( 404 );
        }
        if ( $aGoods['goods_type'] == "bind" )
        {
            $this->redirect( "package", "index" );
            exit( );
        }
        if ( !$aGoods || $aGoods['disabled'] == "true" || empty( $aGoods['products'] ) && empty( $aGoods['product_id'] ) )
        {
            $this->system->error( 404 );
            exit( );
        }
        $objCat =& $this->system->loadModel( "goods/productCat" );
        $aCat = $objCat->getFieldById( $aGoods['cat_id'], array( "cat_name", "addon" ) );
        $aCat['addon'] = unserialize( $aCat['addon'] );
        if ( $aGoods['seo']['meta_keywords'] )
        {
            if ( empty( $this->keyWords ) )
            {
                $this->keyWords = $aGoods['seo']['meta_keywords'];
            }
        }
        else if ( trim( $aCat['addon']['meta']['keywords'] ) )
        {
            $this->keyWords = trim( $aCat['addon']['meta']['keywords'] );
        }
        if ( $aGoods['seo']['meta_description'] )
        {
            $this->metaDesc = $aGoods['seo']['meta_description'];
        }
        else if ( trim( $aCat['addon']['meta']['description'] ) )
        {
            $this->metaDesc = trim( $aCat['addon']['meta']['description'] );
        }
        $tTitle = ( empty( $aGoods['seo']['seo_title'] ) ? $aGoods['name'] : $aGoods['seo']['seo_title'] ).( empty( $aCat['cat_name'] ) ? "" : " - ".$aCat['cat_name'] );
        if ( empty( $this->title ) )
        {
            $this->title = $tTitle;
        }
        $objPdtFinder =& $this->system->loadModel( "goods/finderPdt" );
        foreach ( $aGoods['adjunct'] as $key => $rows )
        {
            if ( $rows['set_price'] == "minus" )
            {
                $cols = "product_id,goods_id,name, pdt_desc, store, freez, price, price-".intval( $rows['price'] )." AS adjprice";
            }
            else
            {
                $cols = "product_id,goods_id,name, pdt_desc, store, freez, price, price*".( $rows['price'] ? $rows['price'] : 1 )." AS adjprice";
            }
            if ( $rows['type'] == "goods" )
            {
                if ( !$rows['items']['product_id'] )
                {
                    $rows['items']['product_id'] = array( -1 );
                }
                $arr = $rows['items'];
            }
            else
            {
                parse_str( $rows['items']."&dis_goods[]=".$gid, $arr );
            }
            if ( $aAdj = $objPdtFinder->getList( $cols, $arr, 0, -1 ) )
            {
                $aAdjGid = array( );
                foreach ( $aAdj as $item )
                {
                    $aAdjGid['goods_id'][] = $item['goods_id'];
                }
                if ( !empty( $aAdjGid ) )
                {
                    foreach ( $objProduct->getList( "marketable,disabled", $aAdjGid, 0, 1000 ) as $item )
                    {
                        $aAdjGid[$item['goods_id']] = $item;
                    }
                    foreach ( $aAdj as $k => $item )
                    {
                        $aAdj[$k]['marketable'] = $aAdjGid[$item['goods_id']]['marketable'];
                        $aAdj[$k]['disabled'] = $aAdjGid[$item['goods_id']]['disabled'];
                    }
                }
                $aGoods['adjunct'][$key]['items'] = $aAdj;
            }
            else
            {
                unset( $this->adjunct[$key] );
            }
        }
        $this->_plugins['function']['selector'] = array(
            $this,
            "_selector"
        );
        if ( !empty( $aGoods['products'] ) )
        {
            foreach ( $aGoods['products'] as $key => $products )
            {
                $a = array( );
                foreach ( $products['props']['spec'] as $k => $v )
                {
                    $a[] = trim( $k ).":".trim( $v );
                }
                $aGoods['products'][$key]['params_tr'] = implode( "-", $a );
                $aPdtIds[] = $products['product_id'];
                if ( $products['price'] < $aGoods['price'] )
                {
                    $aGoods['price'] = $products['price'];
                }
            }
        }
        else
        {
            $aPdtIds[] = $aGoods['product_id'];
        }
        if ( $this->system->getConf( "site.show_mark_price" ) )
        {
            $aGoods['setting']['mktprice'] = $this->system->getConf( "site.market_price" );
        }
        else
        {
            $aGoods['setting']['mktprice'] = 0;
        }
        $aGoods['setting']['saveprice'] = $this->system->getConf( "site.save_price" );
        $aGoods['setting']['buytarget'] = $this->system->getConf( "site.buy.target" );
        $aGoods['setting']['score'] = $this->system->getConf( "point.get_policy" );
        $aGoods['setting']['scorerate'] = $this->system->getConf( "point.get_rate" );
        if ( $aGoods['setting']['score'] == 1 )
        {
            $aGoods['score'] = intval( $aGoods['price'] * $aGoods['setting']['scorerate'] );
        }
        if ( !empty( $specImg ) )
        {
            $tmpImgAry = explode( "_", $specImg );
            if ( is_array( $tmpImgAry ) )
            {
                foreach ( $tmpImgAry as $key => $val )
                {
                    $tImgAry = explode( "@", $val );
                    if ( is_array( $tImgAry ) )
                    {
                        $spec[$tImgAry[0]] = $val;
                        $imageGroup[] = substr( $tImgAry[1], 0, strpos( $tImgAry[1], "|" ) );
                        $imageGstr .= substr( $tImgAry[1], 0, strpos( $tImgAry[1], "|" ) ).",";
                        $spec_value_id = substr( $tImgAry[1], strpos( $tImgAry[1], "|" ) + 1 );
                        if ( $aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'] )
                        {
                            $specValue[] = $aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'];
                        }
                        if ( $aGoods['FlatSpec'] && array_key_exists( $tImgAry[0], $aGoods['FlatSpec'] ) )
                        {
                            $aGoods['FlatSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected'] = TRUE;
                        }
                        if ( $aGoods['SelSpec'] && array_key_exists( $tImgAry[0], $aGoods['SelSpec'] ) )
                        {
                            $aGoods['SelSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected'] = TRUE;
                        }
                    }
                }
                if ( $imageGstr )
                {
                    $imageGstr = substr( $imageGstr, 0, -1 );
                }
            }
            if ( is_array( $aGoods['specVdesc'] ) )
            {
                foreach ( $aGoods['specVdesc'] as $gk => $gv )
                {
                    if ( is_array( $gv['value'] ) )
                    {
                        foreach ( $gv['value'] as $gkk => $gvv )
                        {
                            if ( is_array( $spec ) )
                            {
                                $specId = substr( $gvv['spec_goods_images'], 0, strpos( $gvv['spec_goods_images'], "@" ) );
                                foreach ( $spec as $sk => $sv )
                                {
                                    if ( $specId != $sk )
                                    {
                                        $aGoods['specVdesc'][$gk]['value'][$gkk]['spec_goods_images'] .= "_".$sv;
                                        if ( $aGoods['FlatSpec'] && array_key_exists( $gk, $aGoods['FlatSpec'] ) )
                                        {
                                            $aGoods['FlatSpec'][$gk]['value'][$gkk]['spec_goods_images'] .= "_".$sv;
                                        }
                                        if ( $aGoods['SelSpec'] && array_key_exists( $gk, $aGoods['SelSpec'] ) )
                                        {
                                            $aGoods['SelSpec'][$gk]['value'][$gkk]['spec_goods_images'] .= "_".$sv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $this->pagedata['SelectSpecValue'] = array(
                "value" => implode( "、", $specValue ),
                "selected" => 1
            );
        }
        else
        {
            if ( is_array( $aGoods['FlatSpec'] ) && 0 < count( $aGoods['FlatSpec'] ) )
            {
                foreach ( $aGoods['FlatSpec'] as $agk => $agv )
                {
                    $specValue[] = $agv['name'];
                }
            }
            if ( is_array( $aGoods['SelSpec'] ) && 0 < count( $aGoods['SelSpec'] ) )
            {
                foreach ( $aGoods['SelSpec'] as $agk => $agv )
                {
                    $specValue[] = $agv['name'];
                }
            }
            $this->pagedata['SelectSpecValue'] = array(
                "value" => implode( "、", $specValue ),
                "selected" => 0
            );
        }
        $this->pagedata['specShowItems'] = $specValue;
        if ( is_array( $gImages ) && 0 < count( $gImages ) )
        {
            $this->pagedata['images']['gimages'] = $gImages;
        }
        else
        {
            $gimage =& $this->system->loadModel( "goods/gimage" );
            $this->pagedata['images']['gimages'] = $gimage->get_by_goods_id( $gid );
        }
        $aGoods['product2spec'] = json_encode( $aGoods['product2spec'] );
        $aGoods['spec2product'] = json_encode( $aGoods['spec2product'] );
        $this->pagedata['goods'] = $aGoods;
        if ( $this->pagedata['goods']['products'] )
        {
            $priceArea = array( );
            $qujian_price = array( );
            foreach ( $this->pagedata['goods']['products'] as $p => $m )
            {
                $qujian_price[] = $m['price'] / $m['mktprice'] * 10;
                $qujian_youhui[] = $m['price'] / $m['mktprice'] * 100;
                $qujian_jisheng[] = $m['mktprice'] - $m['price'];
            }
            $minprice_qujian = min( $qujian_price );
            $maxprice_qujian = max( $qujian_price );
            $minyouhui_qujian = min( $qujian_youhui );
            $maxyouhui_qujian = max( $qujian_youhui );
            $minjiesheng_qujian = min( $qujian_jisheng );
            $maxjiesheng_qujian = max( $qujian_jisheng );
            $goods_price = $this->pagedata['goods']['price'];
            $goods_mktprice = $this->pagedata['goods']['mktprice'];
            $max_discount = round( $maxprice_qujian, 1 );
            $min_discount = round( $minprice_qujian, 1 );
            $max_jiesheng = round( $maxjiesheng_qujian, 1 );
            $min_jiesheng = round( $minjiesheng_qujian, 1 );
            $max_youhui = round( $maxyouhui_qujian, 1 );
            $min_youhui = round( $minyouhui_qujian, 1 );
            if ( 1 <= $minprice_qujian )
            {
                if ( $max_discount == $min_discount )
                {
                    $this->pagedata['goods']['price_qujian'] = $max_discount;
                }
                else
                {
                    $this->pagedata['goods']['price_qujian'] = $max_discount."-".$min_discount;
                }
            }
            else if ( $max_discount != $min_discount )
            {
                $this->pagedata['goods']['price_qujian'] = $min_discount."-".$max_discount;
            }
            else
            {
                $this->pagedata['goods']['price_qujian'] = round( $goods_price / $goods_mktprice * 10, 1 );
            }
            if ( $min_jiesheng && $max_jiesheng )
            {
                $this->pagedata['goods']['jiesheng_min'] = $min_jiesheng;
                $this->pagedata['goods']['jiesheng_max'] = $max_jiesheng;
            }
            if ( $min_youhui && $max_youhui )
            {
                $this->pagedata['goods']['youhui_max'] = $min_youhui;
                $this->pagedata['goods']['youhui_min'] = $max_youhui;
            }
            if ( $_COOKIE['MLV'] )
            {
                $MLV = $_COOKIE['MLV'];
            }
            else
            {
                $MLV = FALSE;
            }
            if ( $MLV )
            {
                foreach ( $this->pagedata['goods']['products'] as $gpk => $gpv )
                {
                    $priceArea[] = $gpv['mprice'][$MLV];
                    $mktpriceArea[] = $gpv['mktprice'];
                }
            }
            else
            {
                foreach ( $this->pagedata['goods']['products'] as $gpk => $gpv )
                {
                    $priceArea[] = $gpv['price'];
                    $mktpriceArea[] = $gpv['mktprice'];
                }
            }
            if ( 1 < count( $priceArea ) )
            {
                $minprice = min( $priceArea );
                $maxprice = max( $priceArea );
                $this->pagedata['goods']['minprice'] = $minprice;
                $this->pagedata['goods']['maxprice'] = $maxprice;
            }
            if ( 1 < count( $mktpriceArea ) )
            {
                $mktminprice = min( $mktpriceArea );
                $mktmaxprice = max( $mktpriceArea );
                $this->pagedata['goods']['minmktprice'] = $mktminprice;
                $this->pagedata['goods']['maxmktprice'] = $mktmaxprice;
            }
            foreach ( $this->pagedata['goods']['products'] as $key => $val )
            {
                $totalFreez += $val['freez'];
            }
        }
        else
        {
            $totalFreez = $this->pagedata['goods']['freez'];
            $this->pagedata['goods']['price_qujian'] = round( $aGoods['price'] / $aGoods['mktprice'] * 10, 1 );
        }
        $mLevelList = $objProduct->getProductLevel( $aPdtIds );
        $this->pagedata['mLevel'] = $mLevelList;
        if ( 0 < $this->pagedata['goods']['brand_id'] )
        {
            $brandObj =& $this->system->loadModel( "goods/brand" );
            $aBrand = $brandObj->getFieldById( $this->pagedata['goods']['brand_id'], array( "brand_name" ) );
        }
        $this->pagedata['goods']['brand_name'] = $aBrand['brand_name'];
        $aComment['switch']['ask'] = $this->system->getConf( "comment.switch.ask" );
        $aComment['switch']['discuss'] = $this->system->getConf( "comment.switch.discuss" );
        $aComment['power']['ask'] = $this->system->getConf( "comment.power.ask" );
        $aComment['power']['discuss'] = $this->system->getConf( "comment.power.discuss" );
        $this->pagedata['comment']['member_lv'] = $GLOBALS['runtime']['member_lv'];
        foreach ( $aComment['switch'] as $item => $switchStatus )
        {
            if ( $switchStatus == "on" )
            {
                $objComment =& $this->system->loadModel( "comment/comment" );
                $commentList = $objComment->getGoodsIndexComments( $gid, $item );
                $aComment['list'][$item] = $commentList['data'];
                $aComment[$item."Count"] = $commentList['total'];
                $aId = array( );
                if ( $commentList['total'] )
                {
                    foreach ( $aComment['list'][$item] as $rows )
                    {
                        $aId[] = $rows['comment_id'];
                    }
                    if ( count( $aId ) )
                    {
                        $aReply = $objComment->getCommentsReply( $aId, TRUE );
                    }
                    reset( $aComment['list'][$item] );
                    foreach ( $aComment['list'][$item] as $key => $rows )
                    {
                        foreach ( $aReply as $rkey => $rrows )
                        {
                            if ( $rows['comment_id'] == $rrows['for_comment_id'] )
                            {
                                $aComment['list'][$item][$key]['items'][] = $aReply[$rkey];
                            }
                        }
                        reset( $aReply );
                    }
                }
                else
                {
                    $aComment['null_notice'][$item] = $this->system->getConf( "comment.null_notice.".$item );
                }
            }
        }
        $aComment['member_lv'] = $GLOBALS['runtime']['member_lv'];
        $this->pagedata['comment'] = $aComment;
        $aLinkId['goods_id'] = array( );
        foreach ( $objGoods->getLinkList( $gid ) as $rows )
        {
            if ( $rows['goods_1'] == $gid )
            {
                $aLinkId['goods_id'][] = $rows['goods_2'];
            }
            else
            {
                $aLinkId['goods_id'][] = $rows['goods_1'];
            }
        }
        if ( 0 < count( $aLinkId['goods_id'] ) )
        {
            $aLinkId['marketable'] = "true";
            $objProduct =& $this->system->loadModel( "goods/products" );
            $this->pagedata['goods']['link'] = $objProduct->getList( "*", $aLinkId, 0, 500 );
            $this->pagedata['goods']['link_count'] = $objProduct->count( $aLinkId );
        }
        $PRICE = $this->pagedata['goods']['price'];
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        $aPmt = $oPromotion->getGoodsPromotion( $gid, $this->pagedata['goods']['cat_id'], $this->pagedata['goods']['brand_id'], $member_lv );
        if ( $aPmt )
        {
            $this->pagedata['goods']['pmt_id'] = $aPmt['pmt_id'];
            $arr = $oPromotion->getPromotionList( $aPmt['pmta_id'] );
            $MLV = $_COOKIE['MLV'];
            foreach ( $arr as $keys => $vals )
            {
                $arr[$keys]['pmt_solution'] = unserialize( $arr[$keys]['pmt_solution'] );
                if ( !in_array( $MLV, $arr[$keys]['pmt_solution']['condition'][0][1] ) )
                {
                    unset( $arr[$keys] );
                }
            }
            $this->pagedata['promotions'] = $arr;
            $aTrading = array(
                "price" => $this->pagedata['goods']['price'],
                "score" => $this->pagedata['goods']['score'],
                "gift" => array( ),
                "coupon" => array( )
            );
            $oPromotion->apply_single_pdt_pmt( $aTrading, unserialize( $aPmt['pmt_solution'] ), $member_lv );
            $oGift =& $this->system->loadModel( "trading/gift" );
            if ( !empty( $aTrading['gift'] ) )
            {
                $this->pagedata['gift'] = $oGift->getGiftByIds( $aTrading['gift'] );
            }
            $oCoupon =& $this->system->loadModel( "trading/coupon" );
            if ( !empty( $aTrading['coupon'] ) )
            {
                $this->pagedata['coupon'] = $oCoupon->getCouponByIds( $aTrading['coupon'] );
            }
            $this->pagedata['trading'] = $aTrading;
        }
        $oPackage =& $this->system->loadModel( "trading/package" );
        if ( !empty( $aPdtIds ) )
        {
            $aPkgList = $oPackage->findPmtPkg( $aPdtIds );
            foreach ( $aPkgList as $k => $row )
            {
                $aPkgList[$k]['items'] = $oPackage->getPackageProducts( $row['goods_id'] );
            }
            $this->pagedata['package'] = $aPkgList;
        }
        if ( $GLOBALS['runtime']['member_lv'] < 0 )
        {
            $this->pagedata['login'] = "nologin";
        }
        $cur =& $this->system->loadModel( "system/cur" );
        $this->pagedata['readingGlass'] = $this->system->getConf( "site.reading_glass" );
        $this->pagedata['readingGlassWidth'] = $this->system->getConf( "site.reading_glass_width" );
        $this->pagedata['readingGlassHeight'] = $this->system->getConf( "site.reading_glass_height" );
        $sellLogList = $objProduct->getGoodsSellLogList( $gid, 0, $this->system->getConf( "selllog.display.listnum" ) );
        $sellLogSetting['display'] = array(
            "switch" => $this->system->getConf( "selllog.display.switch" ),
            "limit" => $this->system->getConf( "selllog.display.limit" ),
            "listnum" => $this->system->getConf( "selllog.display.listnum" )
        );
        $this->pagedata['goods']['product_freez'] = $totalFreez;
        $this->pagedata['sellLog'] = $sellLogSetting;
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['money_format'] = json_encode( $cur->getFormat( $this->system->request['cur'] ) );
        $this->pagedata['askshow'] = $this->system->getConf( "comment.verifyCode.ask" );
        $this->pagedata['goodsBnShow'] = $this->system->getConf( "goodsbn.display.switch" );
        $this->pagedata['discussshow'] = $this->system->getConf( "comment.verifyCode.discuss" );
        $this->pagedata['showStorage'] = intval( $this->system->getConf( "site.show_storage" ) );
        $this->pagedata['specimagewidth'] = $this->system->getConf( "spec.image.width" );
        $this->pagedata['specimageheight'] = $this->system->getConf( "spec.image.height" );
        $this->pagedata['goodsproplink'] = $this->system->getConf( "goodsprop.display.switch" );
        $this->pagedata['goodspropposition'] = $this->system->getConf( "goodsprop.display.position" );
        $this->getGlobal( $this->seoTag, $this->pagedata );
        $GLOBALS['GLOBALS']['pageinfo']['goods'] =& $GLOBALS['GLOBALS']['runtime']['goods_name'];
        $GLOBALS['GLOBALS']['pageinfo']['brand'] =& $GLOBALS['GLOBALS']['runtime']['brand'];
        $GLOBALS['GLOBALS']['pageinfo']['gcat'] =& $GLOBALS['GLOBALS']['runtime']['goods_cat'];
        $this->output( );
    }

    public function picsJson( )
    {
        $gids = explode( ",", $_GET['gids'] );
        if ( !$gids )
        {
            return "";
        }
        $o = $this->system->loadModel( "goods/products" );
        $data = $o->getPicsByGoodsId( $gids );
        $storager = $this->system->loadModel( "system/storager" );
        $tmpd = $data;
        foreach ( $tmpd as $k => $d )
        {
            $data[$k]['thumbnail_pic'] = $storager->getUrl( $d['thumbnail_pic'] );
            $data[$k]['small_pic'] = $storager->getUrl( $d['small_pic'] );
            $data[$k]['big_pic'] = $storager->getUrl( $d['big_pic'] );
        }
        echo json_encode( $data );
    }

    public function viewpic( $goodsid, $selected = "def" )
    {
        $objGoods =& $this->system->loadModel( "trading/goods" );
        $gImg =& $this->system->loadModel( "goods/gimage" );
        $dImg = $gImg->get_by_goods_id( $goodsid );
        $aGoods = $objGoods->getFieldById( $goodsid, array( "name" ) );
        $this->pagedata['goods_name'] = urlencode( htmlspecialchars( $aGoods['name'], ENT_QUOTES ) );
        $this->pagedata['goods_name_show'] = $aGoods['name'];
        $this->pagedata['company_name'] = str_replace( array( "%", "'" ), array( "％", "‘" ), htmlspecialchars( $this->system->getConf( "system.shopname" ) ) );
        if ( !$dImg )
        {
            $selected = 0;
            $storager =& $this->system->loadModel( "system/storager" );
            $id = rand( 0, 10 );
            $dImg[$id] = array(
                "gimage_id" => $id,
                "goods_id" => $goodsid,
                "small" => $storager->getUrl( $this->system->getConf( "site.default_small_pic" ) ),
                "big" => $storager->getUrl( $this->system->getConf( "site.default_big_pic" ) ),
                "thumbnail" => $storager->getUrl( $this->system->getConf( "site.default_thumbnail_pic" ) )
            );
        }
        $this->pagedata['image_file'] = $dImg;
        if ( $selected == "def" )
        {
            $selected = current( $dImg );
            $selected = $selected['gimage_id'];
        }
        $this->pagedata['selected'] = $selected;
        $this->__tmpl = "product/viewpic.html";
        $this->output( );
    }

    public function diff( )
    {
        $comare = explode( "|", $_COOKIE['GCOMPARE'] );
        foreach ( $comare as $ci )
        {
            $ci = json_decode( $ci, TRUE );
            $gids[] = $ci['gid'];
        }
        $oGoods =& $this->system->loadModel( "trading/goods" );
        if ( $GLOBALS['runtime']['member_lv'] < 0 )
        {
            $this->pagedata['LOGIN'] = "nologin";
        }
        $this->pagedata['diff'] = $oGoods->diff( $gids );
        foreach ( $this->pagedata['diff']['goods'] as $row )
        {
            $goods_name[] = $row['name'];
        }
        $this->pagedata['goods']['name'] = implode( ",", $goods_name );
        $this->getGlobal( array( "shopname", "goods_name" ), $this->pagedata );
        $this->pagedata['setting']['buytarget'] = $this->system->getConf( "site.buy.target" );
        $this->output( );
    }

    public function photo( )
    {
    }

    public function pic( )
    {
    }

    public function gnotify( $goods_id = 0, $product_id = 0 )
    {
        if ( $_POST['goods']['goods_id'] )
        {
            $goods_id = $_POST['goods']['goods_id'];
            $product_id = $_POST['goods']['product_id'];
        }
        $this->id = $goods_id;
        $objGoods =& $this->system->loadModel( "trading/goods" );
        $aProduct = $objGoods->getProducts( $goods_id, $product_id );
        $this->pagedata['goods'] = $aProduct[0];
        if ( $this->member[member_id] )
        {
            $objMember =& $this->system->loadModel( "member/member" );
            $aMemInfo = $objMember->getFieldById( $this->member[member_id], array( "email" ) );
            $this->pagedata['member'] = $aMemInfo;
        }
        $this->output( );
    }

    public function toNotify( )
    {
        $this->begin( $this->system->mkUrl( "index" ) );
        $this->_verifyMember( FALSE );
        $aTemp = array( );
        $oNotify =& $this->system->loadModel( "goods/goodsNotify" );
        if ( !ereg( "^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+", $_POST['email'] ) )
        {
            $this->end( FALSE, __( "邮箱格式错误" ) );
        }
        else
        {
            foreach ( $_POST['item'] as $key => $val )
            {
                $aTemp['goods_id'] = $val['goods_id'];
                $aTemp['product_id'] = $val['product_id'];
                $aTemp['member_id'] = isset( $this->member['member_id'] ) && $this->member['member_id'] != "" ? $this->member['member_id'] : 0;
                $aTemp['email'] = $_POST['email'] != "" ? $_POST['email'] : $this->member['email'];
                $aTemp['creat_time'] = time( );
                $oNotify->createNotify( $aTemp );
            }
            $this->end( TRUE, __( "提交成功" ) );
        }
    }

    public function selllog( $gid, $nPage )
    {
        $nPage = $nPage ? $nPage : 1;
        $oPro =& $this->system->loadModel( "goods/products" );
        $sellLogList = $oPro->getGoodsSellLogList( $gid, $nPage - 1 );
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['pager'] = array(
            "current" => $nPage,
            "total" => $sellLogList['page'],
            "link" => $this->system->mkUrl( "product", "selllog", array(
                $gid,
                $tmp = time( )
            ) ),
            "token" => $tmp
        );
        $this->id = $gid;
        $member_lv = intval( $this->system->request['member_lv'] );
        $objGoods =& $this->system->loadModel( "trading/goods" );
        if ( !( $aGoods = $objGoods->getGoods( $gid, $member_lv ) ) )
        {
            $this->system->responseCode( 404 );
        }
        if ( !$aGoods || $aGoods['disabled'] == "true" || empty( $aGoods['products'] ) && empty( $aGoods['product_id'] ) )
        {
            $this->system->error( 404 );
            exit( );
        }
        $objCat =& $this->system->loadModel( "goods/productCat" );
        $aCat = $objCat->getFieldById( $aGoods['cat_id'], array( "cat_name", "addon" ) );
        $aCat['addon'] = unserialize( $aCat['addon'] );
        if ( $aGoods['seo']['meta_keywords'] )
        {
            $this->keyWords = $aGoods['seo']['meta_keywords'];
        }
        else if ( trim( $aCat['addon']['meta']['keywords'] ) )
        {
            $this->keyWords = trim( $aCat['addon']['meta']['keywords'] );
        }
        if ( $aGoods['seo']['meta_description'] )
        {
            $this->metaDesc = $aGoods['seo']['meta_description'];
        }
        else if ( trim( $aCat['addon']['meta']['description'] ) )
        {
            $this->metaDesc = trim( $aCat['addon']['meta']['description'] );
        }
        $this->title = ( empty( $aGoods['seo']['seo_title'] ) ? $aGoods['name'] : $aGoods['seo']['seo_title'] ).( empty( $aCat['cat_name'] ) ? "" : " - ".$aCat['cat_name'].__( " » 销售记录" ) );
        $this->output( );
    }

    public function goodspics( $goodsId, $images = array( ), $imgGstr = "" )
    {
        $Goods =& $this->system->loadModel( "goods/gimage" );
        $objGoods =& $this->system->loadModel( "trading/goods" );
        $gimg = $_POST['gimages'];
        $goodsId = $_POST['goodsId'];
        if ( $gimg )
        {
            $tmpGimg = explode( ",", $_POST['gimages'] );
            if ( $tmpGimg )
            {
                foreach ( $tmpGimg as $key => $val )
                {
                    if ( !$val )
                    {
                        unset( $tmpGimg[$key] );
                    }
                }
                $tmpImage = $Goods->get_by_gimage_id( $goodsId, $tmpGimg );
            }
        }
        else
        {
            $tmpImage = $Goods->get_by_goods_id( $goodsId );
        }
        $this->pagedata['imgtype'] = "spec";
        $this->pagedata['images']['gimages'] = $tmpImage;
        $this->pagedata['goods'] = $objGoods->getGoods( $goodsId );
        $this->__tmpl = "product/goodspics.html";
        $this->output( );
    }

    public function _selector( $params, &$smarty )
    {
        if ( $params['type'] != "b" )
        {
            $args[0] = "";
            $args[1] = "tp,".$params['type']."_".$params['key'].",".$params['value']."_p,0";
            $args[4] = 6;
            return $this->system->mkUrl( "gallery", $smarty->_vars['curView'], $args );
        }
        else
        {
            $args[0] = $params['key'];
            return $this->system->mkUrl( "brand", $smarty->_vars['curView'], $args );
        }
    }

    public function get_brand( $result )
    {
        return $result['goods']['brand'];
    }

    public function get_goods_name( $result )
    {
        return $result['goods']['name'];
    }

    public function get_goods_bn( $result )
    {
        return $result['goods']['bn'];
    }

    public function get_goods_cat( $result )
    {
        $pcat = $this->system->loadModel( "goods/productCat" );
        $row = $pcat->instance( $result['goods']['cat_id'], "cat_name" );
        return $row['cat_name'];
    }

    public function get_goods_intro( $result )
    {
        $intro = strip_tags( $result['goods']['intro'] );
        if ( 50 < $this->strlen( $intro ) )
        {
            $intro = $this->substr( $intro, 0, 50 );
        }
        return $intro;
    }

    public function get_goods_brief( $result )
    {
        $brief = strip_tags( $result['goods']['brief'] );
        if ( 50 < $this->strlen( $brief ) )
        {
            $brief = $this->substr( $brief, 0, 50 );
        }
        return $brief;
    }

    public function get_brand_kw( $result )
    {
        $brand = $this->system->loadModel( "goods/brand" );
        $row = $brand->instance( $result['goods']['brand_id'], "brand_keywords" );
        return $row['brand_keywords'];
    }

    public function get_goods_kw( $result )
    {
        $goods = $this->system->loadModel( "trading/goods" );
        $row = $goods->getKeywords( $result['goods']['goods_id'] );
        if ( $row )
        {
            foreach ( $row as $key => $val )
            {
                $tmpRow[] = $val['keyword'];
            }
            return implode( ",", $tmpRow );
        }
        return;
    }

    public function get_goods_price( $result )
    {
        return $result['goods']['price'];
    }

    public function get_update_time( $result )
    {
        return date( "c", $result['goods']['last_modify'] );
    }

    public function strlen( $str, $encode = "utf8" )
    {
        $enlen = 0;
        if ( $encode == "utf8" )
        {
            $minchar = 128;
        }
        else if ( $encode == "gbk" )
        {
            $minchar = 0;
        }
        $maxchar = 255;
        $i = 0;
        for ( ; $i < strlen( $str ); ++$i )
        {
            if ( $minchar <= ord( $str[$i] ) && ord( $str[$i] ) <= $maxchar )
            {
                $zh[] = ord( $str[$i] );
            }
            else
            {
                ++$enlen;
            }
        }
        if ( $encode == "utf8" )
        {
            $zhlen = count( $zh ) / 3;
        }
        else
        {
            $zhlen = count( $zh ) / 2;
        }
        return $enlen + $zhlen;
    }

    public function substr( $str, $start = 0, $offset = "" )
    {
        $j = 0;
        $cn = 0;
        if ( !$offset )
        {
            $offset = strlen( $str );
        }
        while ( $cn < $start )
        {
            if ( 128 <= ord( $str[$j] ) && ord( $str[$j] ) <= 255 )
            {
                $j += 3;
            }
            else
            {
                ++$j;
            }
            ++$cn;
        }
        $i = $j;
        $exp = 0;
        while ( $exp < $offset )
        {
            if ( 128 <= ord( $str[$i] ) && ord( $str[$i] ) < 255 )
            {
                $substr .= substr( $str, $i, 3 );
                $i += 3;
            }
            else
            {
                $substr .= $str[$i];
                ++$i;
            }
            ++$exp;
        }
        return $substr;
    }

}

?>
