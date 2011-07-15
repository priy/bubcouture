<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_get_filter( $p, &$object )
{
    $cat =& $object->system->loadmodel( "goods/productCat" );
    if ( !$object->catMap )
    {
        $object->catMap = $cat->getmaptree( 0, "" );
    }
    $return['cats'] = $object->catMap;
    if ( $cat_id = $p['cat_id'] )
    {
        $p = $cat->get( $cat_id );
        $return['props'] = $p['props'];
        $brand = $object->system->loadmodel( "goods/brand" );
        $return['brands'] = $brand->getall( );
        $return['cat_id'] = $p['cat_id'];
        $row = $object->db->selectrow( "SELECT max(price) as max,min(price) as min FROM sdb_goods where cat_id=".intval( $cat_id ) );
    }
    else
    {
        $brand = $object->system->loadmodel( "goods/brand" );
        $return['brands'] = $brand->getall( );
        $row = $object->db->selectrow( "SELECT max(price) as max,min(price) as min FROM sdb_products " );
    }
    $modTag = $object->system->loadmodel( "system/tag" );
    $return['tags'] = $modTag->taglist( "goods" );
    $supplier = $this->system->loadmodel( "distribution/supplier" );
    $return['supplier'] = $supplier->getlist( "supplier_id,supplier_brief_name", "", 0, -1 );
    if ( $p['goods_id'] )
    {
        $oGoods = $object->system->loadmodel( "trading/goods" );
        $return['keywords'] = $oGoods->getkeywords( $p['goods_id'] );
    }
    $return['prices'] = steprange( $row['min'], $row['max'], 5 );
    return $return;
}

?>
