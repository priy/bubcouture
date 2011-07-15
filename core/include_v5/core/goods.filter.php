<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_filter( &$filter, &$object )
{
    $where = array( );
    if ( isset( $filter['marketable'] ) )
    {
        if ( $filter['marketable'] === true )
        {
            $filter['marketable'] = "true";
        }
        if ( $filter['marketable'] === false )
        {
            $filter['marketable'] = "false";
        }
    }
    if ( $filter['list_type'] == "lack" && !$filter['goods_id'] )
    {
        $oProduct = $object->system->loadModel( "goods/finderPdt" );
        $filter_p['store_alarm'] = $object->system->getConf( "system.product.alert.num" );
        foreach ( $oProduct->getList( "goods_id", $filter_p, 0, -1 ) as $row )
        {
            $filter['goods_id'][] = $row['goods_id'];
        }
    }
    if ( $filter['supplier_id'] )
    {
        if ( !is_array( $filter['supplier_id'] ) )
        {
            $where[] = " supplier_id = ".$filter['supplier_id']." ";
            if ( isset( $filter['costsync'] ) && $filter['costsync'] )
            {
                $oCostSync = $object->system->loadModel( "distribution/costsync" );
                $version_id = $oCostSync->getMaxVersionId( $filter['supplier_id'], true );
                $where[] = " goods_id IN(SELECT goods_id FROM sdb_cost_sync WHERE supplier_id = '".$filter['supplier_id']."' AND version_id= '".$version_id."') ";
            }
        }
        else
        {
            foreach ( $filter['supplier_id'] as $supplier_id )
            {
                if ( $supplier_id != "_ANY_" )
                {
                    $aSupplier[] = intval( $supplier_id );
                }
            }
            if ( 0 < count( $aSupplier ) )
            {
                $where[] = "supplier_id IN(".implode( ",", $aSupplier ).")";
            }
        }
    }
    if ( isset( $filter['storeless'] ) && !$filter['goods_id'] )
    {
        foreach ( $object->db->select( "SELECT distinct(goods_id) as goods_id FROM sdb_products where store<=".intval( $filter['storeless'] ) ) as $row )
        {
            $filter['goods_id'][] = $row['goods_id'];
        }
        unset( $filter['storeless'] );
    }
    if ( $filter['cat_id'] || $filter['cat_id'] === 0 )
    {
        if ( !is_array( $filter['cat_id'] ) )
        {
            $filter['cat_id'] = array(
                $filter['cat_id']
            );
        }
        foreach ( $filter['cat_id'] as $vCat_id )
        {
            if ( $vCat_id !== "_ANY_" && $vCat_id !== "" )
            {
                $aCat_id[] = intval( $vCat_id );
            }
        }
        $filter['cat_id'] = $aCat_id;
        if ( !isset( $object->__show_goods ) )
        {
            $object->__show_goods = $object->system->getConf( "system.category.showgoods" );
        }
        if ( $object->__show_goods )
        {
            if ( 0 < count( $filter['cat_id'] ) )
            {
                $where[] = "cat_id in (".implode( $filter['cat_id'], " , " ).")";
            }
        }
        else
        {
            $oCat = $object->system->loadModel( "goods/productCat" );
            $aCat = $oCat->getFieldById( $filter['cat_id'], array( "cat_path", "cat_id" ) );
            $pathplus = "";
            if ( count( $aCat ) )
            {
                foreach ( $aCat as $v )
                {
                    $pathplus .= " cat_path LIKE '".( $v['cat_path'] == "," ? "" : $v['cat_path'] ).$v['cat_id'].",%' OR";
                }
            }
            if ( $aCat )
            {
                foreach ( $object->db->select( "SELECT cat_id FROM sdb_goods_cat WHERE ".$pathplus." cat_id in (".implode( $filter['cat_id'], " , " ).")" ) as $rows )
                {
                    $aCatid[] = $rows['cat_id'];
                }
            }
            else
            {
                unset( $aCatid );
            }
            if ( in_array( "0", $filter['cat_id'] ) )
            {
                $aCatid[] = 0;
            }
            if ( !is_null( $aCatid ) )
            {
                $where[] = "cat_id IN (".implode( ",", $aCatid ).")";
            }
            else if ( $filter['cat_id'] && $filter['cat_id'][0] )
            {
                $where[] = "cat_id IN (".implode( ",", $filter['cat_id'] ).")";
            }
        }
        $filter['cat_id'] = null;
    }
    if ( isset( $filter['area'] ) && $filter['area'] )
    {
        $where[] = "goods_id < ".$filter['area'][0]." and goods_id >".$filter['area'][1];
        unset( $filter['area'] );
    }
    if ( $filter['type_id'] == "_ANY_" || empty( $filter['type_id'][0] ) )
    {
        unset( $filter['type_id'] );
    }
    if ( isset( $filter['brand_id'] ) && $filter['brand_id'] )
    {
        if ( is_array( $filter['brand_id'] ) )
        {
            foreach ( $filter['brand_id'] as $brand_id )
            {
                if ( $brand_id != "_ANY_" )
                {
                    $aBrand[] = intval( $brand_id );
                }
            }
            if ( 0 < count( $aBrand ) )
            {
                $where[] = "brand_id IN(".implode( ",", $aBrand ).")";
            }
        }
        else if ( 0 < $filter['brand_id'] )
        {
            $where[] = "brand_id = ".$filter['brand_id'];
        }
        unset( $filter['brand_id'] );
    }
    if ( isset( $filter['goods_id'] ) && $filter['goods_id'] )
    {
        if ( is_array( $filter['goods_id'] ) )
        {
            if ( $filter['goods_id'][0] != "_ALL_" )
            {
                foreach ( $filter['goods_id'] as $goods_id )
                {
                    if ( $goods_id != "_ANY_" )
                    {
                        $goods[] = intval( $goods_id );
                    }
                }
            }
        }
        else
        {
            $goods[] = intval( $filter['goods_id'] );
        }
    }
    unset( $filter['goods_id'] );
    if ( isset( $filter['tag'] ) && is_array( $filter['tag'] ) )
    {
        foreach ( $filter['tag'] as $tag )
        {
            if ( $tag != "_ANY_" )
            {
                $aTag[] = intval( $tag );
            }
        }
        if ( 0 < count( $aTag ) )
        {
            $tagId[] = -1;
            foreach ( $object->db->select( "SELECT rel_id FROM sdb_tag_rel r\n                LEFT JOIN sdb_tags t ON r.tag_id=t.tag_id\n                WHERE t.tag_type = 'goods' AND r.tag_id IN(".implode( ",", $aTag ).")" ) as $rows )
            {
                $tagId[] = $rows['rel_id'];
            }
            if ( $goods )
            {
                $goods = array_intersect( $goods, $tagId );
            }
            else
            {
                $goods = $tagId;
            }
        }
        $filter['tag'] = null;
    }
    if ( isset( $filter['props'] ) && is_array( $filter['props'] ) )
    {
        foreach ( $filter['props'] as $k => $v )
        {
            $ac = array( );
            if ( is_array( $v ) )
            {
                foreach ( $v as $m )
                {
                    if ( $m !== "_ANY_" && $m !== "" )
                    {
                        $ac[] = "p_".$k."='".$m."'";
                    }
                }
                if ( 0 < count( $ac ) )
                {
                    $where[] = "(".implode( $ac, " or " ).")";
                }
            }
            else if ( isset( $v ) && $v != "" && $v != "_ANY_" )
            {
                $where[] = "p_".$k."='".$v."'";
            }
        }
    }
    unset( $filter['props'] );
    if ( isset( $filter['keyword'] ) && $filter['keyword'] )
    {
        $filter['keywords'] = array(
            $filter['keyword']
        );
    }
    unset( $filter['keyword'] );
    if ( isset( $filter['keywords'] ) && $filter['keywords'] && !in_array( "_ANY_", $filter['keywords'] ) )
    {
        $oGoods = $object->system->loadModel( "trading/goods" );
        $keywordsList = $oGoods->getGoodsIdByKeyword( $filter['keywords'], $filter['_keyword_search'] );
        $keywordsGoods = array( );
        foreach ( $keywordsList as $keyword )
        {
            $keywordsGoods[] = intval( $keyword['goods_id'] );
        }
        if ( !empty( $keywordsGoods ) && !empty( $goods ) )
        {
            $keywordsGoods = array_intersect( $keywordsGoods, $goods );
            if ( empty( $keywordsGoods ) )
            {
                $goods = array( "-1" );
            }
            else
            {
                $goods = $keywordsGoods;
            }
        }
        else if ( !empty( $keywordsGoods ) )
        {
            $goods = $keywordsGoods;
        }
        else
        {
            $goods = array( "-1" );
        }
    }
    unset( $filter['keywords'] );
    if ( isset( $filter['bn'] ) && $filter['bn'] )
    {
        $sBn = "";
        if ( is_array( $filter['bn'] ) )
        {
            $sBn = addslashes( trim( $filter['bn'][0] ) );
        }
        else
        {
            $sBn = addslashes( trim( $filter['bn'] ) );
        }
        $bnGoodsId = $object->getGoodsIdByBn( $sBn, $filter['_bn_search'] );
        if ( !empty( $bnGoodsId ) && !empty( $goods ) )
        {
            $bnGoodsId = array_intersect( $bnGoodsId, $goods );
            if ( empty( $bnGoodsId ) )
            {
                $goods = array( "-1" );
            }
            else
            {
                $goods = $bnGoodsId;
            }
        }
        else if ( !empty( $bnGoodsId ) )
        {
            $goods = $bnGoodsId;
        }
        else
        {
            $goods = array( "-1" );
        }
        unset( $filter['bn'] );
    }
    foreach ( $filter as $k => $v )
    {
        if ( substr( $k, 0, 2 ) == "p_" )
        {
            $ac = array( );
            if ( is_array( $v ) )
            {
                foreach ( $v as $m )
                {
                    if ( $m !== "_ANY_" && $m !== "" )
                    {
                        $ac[] = $tPre.$k."='".$m."'";
                    }
                }
                if ( 0 < count( $ac ) )
                {
                    $where[] = "(".implode( $ac, " or " ).")";
                }
            }
            else if ( isset( $v ) && $v != "" && $v != "_ANY_" )
            {
                $where[] = $tPre.$k."='".$v."'";
            }
            unset( $filter[$k] );
        }
        else if ( substr( $k, 0, 2 ) == "s_" )
        {
            $sSpecId = array( );
            if ( is_array( $v ) )
            {
                foreach ( $v as $n )
                {
                    if ( $n !== "_ANY_" && $n != false )
                    {
                        $sSpecId[] = $n;
                    }
                }
                unset( $filter[$k] );
            }
            if ( 0 < count( $sSpecId ) )
            {
                $sGoodsId = $object->db->select( "SELECT goods_id FROM sdb_goods_spec_index WHERE spec_value_id IN ( ".implode( ",", $sSpecId )." )" );
                $sgid = array( );
                foreach ( $sGoodsId as $si )
                {
                    $sgid[] = $si['goods_id'];
                }
                if ( !empty( $goods ) )
                {
                    $sgid = array_intersect( $sgid, $goods );
                }
                if ( !empty( $sgid ) )
                {
                    $goods = $sgid;
                }
                else
                {
                    $goods = array( -1 );
                }
            }
        }
    }
    if ( isset( $goods ) && 0 < count( $goods ) )
    {
        $where[] = "goods_id IN (".implode( ",", $goods ).")";
    }
    if ( isset( $filter['price'] ) && is_array( $filter['price'] ) )
    {
        if ( $filter['price'][0] == 0 || $filter['price'][0] )
        {
            $where[] = "price >= ".intval( $filter['price'][0] );
        }
        if ( $filter['price'][1] == "0" || $filter['price'][1] )
        {
            $where[] = "price <= ".intval( $filter['price'][1] );
        }
        if ( !is_numeric( $filter['price'][0] ) || !is_numeric( $filter['price'][1] ) )
        {
            unset( $filter['price'] );
        }
        if ( $filter['price'][0] && $filter['price'][1] )
        {
            $where[] = "price >= ".min( $filter['price'] )." AND price <= ".max( $filter['price'] );
        }
        unset( $filter['price'] );
    }
    else if ( ( $filter['pricefrom'] == 0 || $filter['pricefrom'] ) && ( $filter['priceto'] || $filter['priceto'] ) )
    {
        $where[] = "price >= ".$filter['pricefrom']." AND price <= ".$filter['priceto'];
        unset( $filter['pricefrom'] );
        unset( $filter['priceto'] );
    }
    if ( isset( $filter['cost'] ) && !is_numeric( $filter['cost'] ) )
    {
        unset( $filter['cost'] );
    }
    if ( isset( $filter['mktprice'] ) && !is_numeric( $filter['mktprice'] ) )
    {
        unset( $filter['mktprice'] );
    }
    if ( isset( $filter['store'] ) && !is_numeric( $filter['store'] ) )
    {
        unset( $filter['store'] );
    }
    if ( isset( $filter['gkey'] ) && trim( $filter['gkey'] ) )
    {
        $filter['name'] = trim( $filter['gkey'] );
    }
    if ( $filter['searchname'] )
    {
        $filter['name'][] = $filter['searchname'];
    }
    if ( isset( $filter['name'] ) && $filter['name'] )
    {
        if ( is_array( $filter['name'] ) )
        {
            $filter['name'] = implode( "+", $filter['name'] );
            if ( $filter['name'] )
            {
                $filter['name'] = str_replace( "%xia%", "_", $filter['name'] );
                $filter['name'] = preg_replace( "/['|\\\"]/", "+", $filter['name'] );
                $GLOBALS['GLOBALS']['search'] = $filter['name'];
                $where[] = $object->wFilter( $filter['name'] );
            }
        }
        else
        {
            $GLOBALS['GLOBALS']['search'] = $filter['name'];
            $where[] = "name LIKE '%".addslashes( trim( $filter['name'] ) )."%'";
        }
        $filter['name'] = null;
    }
    if ( isset( $filter['keyword'] ) && trim( $filter['keyword'] ) )
    {
        $key_data = $o->db->select( "select goods_id from sdb_goods_keywords where keyword = '".trim( $filter['keyword'] )."'" );
        foreach ( $key_data as $tmp_key )
        {
            $now_key[] = $tmp_key['goods_id'];
        }
        $where[] = "goods_id IN ('".implode( "','", $now_key )."')";
        unset( $filter['keyword'] );
    }
    if ( isset( $filter['spec_desc'] ) )
    {
        if ( $filter['spec_desc'] === "true" )
        {
            $where[] = "(spec_desc IS NOT NULL && spec_desc != '' && spec_desc != 'a:0:{}')";
        }
        if ( $filter['spec_desc'] === "false" )
        {
            $where[] = "(spec_desc IS NULL || spec_desc = '' || spec_desc = 'a:0:{}')";
        }
        if ( $filter['spec_desc'] == 1 )
        {
            $where[] = "(spec_desc IS NOT NULL && spec_desc != '' && spec_desc != 'a:0:{}' && spec_desc != 'b:0;')";
        }
        if ( $filter['spec_desc'] == "" )
        {
            $where[] = "(spec_desc IS NULL || spec_desc = '' || spec_desc = 'a:0:{}' || spec_desc = 'b:0;')";
        }
        unset( $filter['spec_desc'] );
    }
    $filter['goods_type'] = "normal";
    return $where;
}

?>
