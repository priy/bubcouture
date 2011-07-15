<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_finderpdt extends shopobject
{

    var $idColumn = "product_id";
    var $adminCtl = "goods/items";
    var $textColumn = "name";
    var $appendCols = "pdt_desc";
    var $defaultCols = "bn,name,price,store,pdt_desc";
    var $defaultOrder = array
    (
        0 => "uptime",
        1 => "DESC"
    );
    var $tableName = "sdb_products";

    function getcolumns( $filter, $from = null )
    {
        $ret = shopobject::getcolumns( );
        if ( $from == "from" )
        {
            $ret['price']['default'] = "";
        }
        return $ret;
    }

    function searchoptions( )
    {
        return array(
            "bn" => __( "货号" ),
            "name" => __( "商品名称" )
        );
    }

    function getlist( $cols, $filter = "", $start = 0, $limit = 20, $orderType = null )
    {
        $rows = shopobject::getlist( $cols, $filter, $start, $limit, $orderType );
        foreach ( $rows as $k => $v )
        {
            $rows[$k]['name'] .= " ".stripslashes( $v['pdt_desc'] );
        }
        return $rows;
    }

    function getbindlist( $start = 0, $limit = 20, &$count, $filter = "" )
    {
        $ident = md5( var_export( func_get_args( ), 1 ) );
        if ( $filter )
        {
            if ( !function_exists( "object_filter_parser" ) )
            {
                require( CORE_INCLUDE_DIR."/core/object.filter_parser.php" );
            }
            foreach ( $filter as $k => $v )
            {
                if ( $k[0] != "_" )
                {
                    $where[] = "p.".$k.getfiltertype( $filter["_".$k."_search"], $v );
                }
            }
            $condition = "";
            if ( $where )
            {
                $condition = " AND ".implode( $where, " AND " );
            }
        }
        if ( !$this->_dbstorage[$ident] )
        {
            $sql = "SELECT p.product_id, p.name, p.pdt_desc FROM sdb_products p\n                    LEFT JOIN sdb_goods g ON g.goods_id = p.goods_id\n                    WHERE g.supplier_id IS NULL\n                    AND p.disabled = 'false'\n                    AND p.goods_id IS NOT NULL\n                    ".$condition."\n                    AND 1\n                    ORDER BY p.uptime DESC";
            $count = $this->db->count( $sql );
            $this->_dbstorage[$ident] = $this->db->selectlimit( $sql, $limit, $start );
        }
        foreach ( $this->_dbstorage[$ident] as $k => $v )
        {
            $this->_dbstorage[$ident][$k]['name'] .= " ".stripslashes( $v['pdt_desc'] );
        }
        return $this->_dbstorage[$ident];
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        $aId = array( 0 );
        $aBrand = array( );
        $serach_goodsid = false;
        if ( isset( $filter['brand_id'] ) && is_array( $filter['brand_id'] ) )
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
                $serach_goodsid = true;
                foreach ( $this->db->select( "SELECT goods_id FROM sdb_goods WHERE marketable = 'true' AND brand_id IN(".implode( ",", $aBrand ).")" ) as $rows )
                {
                    $aId[] = $rows['goods_id'];
                }
            }
            unset( $filter->'brand_id' );
        }
        $aTag = array( );
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
                $tagId = array( 0 );
                foreach ( $this->db->select( "SELECT rel_id FROM sdb_tag_rel r LEFT JOIN sdb_tags t ON r.tag_id=t.tag_id WHERE t.tag_type = 'goods' AND r.tag_id IN(".implode( ",", $aTag ).")" ) as $rows )
                {
                    $tagId[] = $rows['rel_id'];
                }
                if ( $serach_goodsid )
                {
                    $aId = array_intersect( $aId, $tagId );
                }
                else
                {
                    $aId = $tagId;
                }
                $serach_goodsid = true;
            }
            unset( $filter->'tag' );
        }
        $aGoods = array( );
        if ( isset( $filter['cat_id'] ) && is_array( $filter['cat_id'] ) )
        {
            foreach ( $filter['cat_id'] as $cat_id )
            {
                if ( $cat_id != "_ANY_" )
                {
                    $aGoods[] = intval( $cat_id );
                }
            }
            if ( 0 < count( $aGoods ) )
            {
                $catId = array( 0 );
                $tmp_array = $this->db->select( "SELECT cat_id FROM sdb_goods_cat WHERE cat_path IN (".implode( ",", $aGoods ).")" );
                $cId = array( );
                array_push( $cId, implode( $aGoods ) );
                foreach ( $tmp_array as $k => $v )
                {
                    array_push( $cId, $v['cat_id'] );
                }
                foreach ( $this->db->select( "SELECT goods_id FROM sdb_goods WHERE marketable = 'true' AND cat_id IN(".implode( ",", $cId ).")" ) as $rows )
                {
                    $catId[] = $rows['goods_id'];
                }
                if ( $serach_goodsid )
                {
                    $aId = array_intersect( $aId, $catId );
                }
                else
                {
                    $aId = $catId;
                }
                $serach_goodsid = true;
            }
            unset( $filter->'cat_id' );
        }
        $aType = array( );
        if ( isset( $filter['type_id'] ) && $filter['type_id'] )
        {
            $filter['type_id'] = intval( $filter['type_id'] );
            $typeId = array( 0 );
            foreach ( $this->db->select( "SELECT goods_id FROM sdb_goods WHERE marketable = 'true' AND type_id =".$filter['type_id'] ) as $rows )
            {
                $typeId[] = $rows['goods_id'];
            }
            if ( $serach_goodsid )
            {
                $aId = array_intersect( $aId, $typeId );
            }
            else
            {
                $aId = $typeId;
            }
            $serach_goodsid = true;
            unset( $filter->'type_id' );
        }
        $aProps = array( );
        if ( isset( $filter['props'] ) && is_array( $filter['props'] ) )
        {
            foreach ( $filter['props'] as $cols => $rows )
            {
                foreach ( $rows as $propid )
                {
                    if ( $propid != "_ANY_" )
                    {
                        $aProps["p_".$cols][] = intval( $propid );
                    }
                }
            }
            if ( 0 < count( $aProps ) )
            {
                $catId = array( 0 );
                $p_where = array( 1 );
                foreach ( $aProps as $cols => $rows )
                {
                    $p_where[] = $cols." IN(".implode( ",", $rows ).")";
                }
                foreach ( $this->db->select( "SELECT goods_id FROM sdb_goods WHERE marketable = 'true' AND ".implode( " AND ", $p_where ) ) as $rows )
                {
                    $catId[] = $rows['goods_id'];
                }
                if ( $serach_goodsid )
                {
                    $aId = array_intersect( $aId, $catId );
                }
                else
                {
                    $aId = $catId;
                }
                $serach_goodsid = true;
            }
            unset( $filter->'props' );
        }
        if ( $serach_goodsid )
        {
            $where[] = "goods_id IN (".implode( ",", $aId ).")";
        }
        if ( isset( $filter['product_id'] ) && is_array( $filter['product_id'] ) )
        {
            foreach ( $filter['product_id'] as $goods_id )
            {
                if ( $goods_id != "_ANY_" )
                {
                    $goods[] = intval( $goods_id );
                }
            }
            if ( 0 < count( $goods ) )
            {
                $where[] = "product_id IN (".implode( ",", $goods ).")";
            }
        }
        if ( isset( $filter['notifytime'] ) && is_array( $filter['notifytime'] ) )
        {
            $where[] = "creat_time > ".$filter['notifytime'];
        }
        if ( isset( $filter['price'] ) && is_array( $filter['price'] ) )
        {
            foreach ( $filter['price'] as $price )
            {
                if ( $price != "_ANY_" )
                {
                    $aPrice = explode( "-", $price );
                    $aWhere[] = "(price >= ".$aPrice[0]." AND price <= ".$aPrice[1].")";
                }
            }
            if ( !empty( $aWhere ) )
            {
                $where[] = "(".implode( " OR ", $aWhere ).")";
            }
            unset( $filter->'price' );
        }
        else if ( trim( $filter['pricefrom'] ) !== "" || trim( $filter['priceto'] ) !== "" )
        {
            if ( $filter['pricefrom'] !== "" )
            {
                $where[] = "price >= ".$filter['pricefrom'];
                unset( $filter->'pricefrom' );
            }
            if ( $filter['priceto'] !== "" )
            {
                $where[] = "price <= ".$filter['priceto'];
                unset( $filter->'priceto' );
            }
        }
        if ( isset( $filter['is_local'] ) && $filter['is_local'] )
        {
            $sSql = " AND goods_id IN ( SELECT goods_id FROM sdb_goods WHERE supplier_goods_id = 0 OR supplier_goods_id IS NULL ) ";
        }
        else
        {
            $sSql = "";
        }
        if ( trim( $filter['searchname'] ) )
        {
            $where[] = "name like '%".trim( $filter['searchname'] )."%'";
            unset( $filter->'searchname' );
        }
        return shopobject::_filter( $filter )." AND goods_id IS NOT NULL AND ".implode( " AND ", $where ).( isset( $filter['store_alarm'] ) ? " AND store <=".intval( $filter['store_alarm'] ) : "" );
    }

    function getfilter( $p )
    {
        $row = $this->db->selectrow( "SELECT max(price) as max,min(price) as min FROM sdb_products " );
        $brand =& $this->system->loadmodel( "goods/brand" );
        $return['brands'] = $brand->getall( );
        $modTag =& $this->system->loadmodel( "system/tag" );
        $return['tags'] = $modTag->taglist( "goods" );
        $return['prices'] = steprange( $row['min'], $row['max'], 5 );
        return $return;
    }

}

?>
