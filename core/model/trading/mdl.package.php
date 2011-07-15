<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_package extends shopobject
{

    var $idColumn = "goods_id";
    var $textColumn = "name";
    var $defaultCols = "name,mktprice,price,store,marketable";
    var $adminCtl = "goods/package";
    var $defaultOrder = array
    (
        0 => "p_order",
        1 => "DESC"
    );
    var $tableName = "sdb_goods";

    function _filter( $filter )
    {
        $filter['goods_type'] = "bind";
        return shopobject::_filter( $filter );
    }

    function searchoptions( )
    {
        return array( );
    }

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "product/package/command.html"
            )
        );
        $now = shopobject::getcolumns( );
        $now['cat_id']['hidden'] = true;
        $now['type_id']['hidden'] = true;
        $now['goods_type']['hidden'] = true;
        $now['brand_id']['hidden'] = true;
        $now['brief']['hidden'] = true;
        $now['bn']['hidden'] = true;
        $now['cost']['hidden'] = true;
        $now['unit']['hidden'] = true;
        $now['score']['hidden'] = true;
        $now['uptime']['hidden'] = true;
        $now['downtime']['hidden'] = true;
        $now['last_modify']['hidden'] = true;
        $now['notify_num']['hidden'] = true;
        $now['name']['locked'] = 0;
        $now['name']['label'] = "捆绑商品名称";
        $now['goods_id']['label'] = "ID";
        $now['mktprice']['label'] = "原价格";
        $now['price']['label'] = "捆绑销售价";
        $now['brand']['hidden'] = true;
        unset( $this->brand_id->'filtertype' );
        unset( $this->brand->'filtertype' );
        unset( $this->cat_id->'filtertype' );
        unset( $this->bn->'filtertype' );
        unset( $this->cost->'filtertype' );
        unset( $this->unit->'filtertype' );
        unset( $this->brief->'filtertype' );
        unset( $this->intro->'filtertype' );
        unset( $this->type_id->'filtertype' );
        return array_merge( $ret, $now );
    }

    function findpmtpkg( $aPdtIds )
    {
        if ( $aPdtIds && 0 < count( $aPdtIds ) && 0 < $aPdtIds[0] )
        {
            $sSql = "SELECT * FROM sdb_goods g LEFT JOIN sdb_package_product p ON g.goods_id=p.goods_id\n                        WHERE goods_type='bind' AND marketable='true' AND g.disabled = 'false' AND product_id IN (".implode( ",", $aPdtIds ).")";
            $aPkg = $this->db->select( $sSql );
            return $aPkg;
        }
    }

    function getpackagebyids( $ids )
    {
        if ( is_array( $ids ) && !empty( $ids ) )
        {
            $sql = "SELECT * FROM sdb_goods WHERE goods_type='bind' and goods_id in(".implode( ",", $ids ).")";
        }
        return $this->db->select( $sql );
    }

    function getpackagebyid( $goodsId )
    {
        $sql = "SELECT * FROM sdb_goods WHERE goods_type='bind' AND goods_id=".$goodsId;
        return $this->db->selectrow( $sql );
    }

    function getpackagelist( $nPage )
    {
        $sSql = "SELECT * FROM sdb_goods where goods_type='bind' AND marketable ='true' AND disabled ='false' ORDER BY p_order DESC";
        $aRet = $this->db->selectpager( $sSql, $nPage, PAGELIMIT );
        foreach ( $aRet['data'] as $k => $row )
        {
            $aId[] = $row['goods_id'];
        }
        if ( $aId )
        {
            reset( $aRet['data'] );
            $this->getpackageitems( $aId, $aRet['data'] );
        }
        return $aRet;
    }

    function getpackageitems( &$aId, &$data )
    {
        $sSql = "SELECT p.goods_id,g.price,g.cost,g.name,pkgnum,g.product_id AS pkgid,g.goods_id AS p_goods_id,gd.image_default,gd.thumbnail_pic,gd.small_pic FROM sdb_package_product p\n                LEFT JOIN sdb_products g ON p.product_id = g.product_id\n                LEFT JOIN sdb_goods gd ON g.goods_id = gd.goods_id\n                WHERE p.goods_id IN (".implode( ",", $aId ).")";
        $aProduct = $this->db->select( $sSql );
        foreach ( $aProduct as $k => $row )
        {
            if ( $row['pkgid'] )
            {
                $aTmp[$row['goods_id']][] = $aProduct[$k];
            }
        }
        foreach ( $data as $k => $row )
        {
            $data[$k]['items'] = $aTmp[$row['goods_id']];
        }
        return true;
    }

    function getpackageproducts( $nGoodsId )
    {
        $sSql = "SELECT pkg.*,p.*,g.marketable,g.disabled,g.thumbnail_pic FROM sdb_package_product pkg\n                LEFT JOIN sdb_products p ON pkg.product_id = p.product_id\n                LEFT JOIN sdb_goods g ON p.goods_id = g.goods_id\n                WHERE pkg.goods_id = ".intval( $nGoodsId );
        return $this->db->select( $sSql );
    }

    function savepackage( $aData, $msg )
    {
        if ( empty( $aData['pkgnum'] ) )
        {
            trigger_error( __( "没有捆绑物品" ), E_USER_ERROR );
            return false;
        }
        $aData['weight'] = floatval( $aData['weight'] );
        $aData['bn'] = strtoupper( uniqid( "g" ) );
        if ( $aData['score'] === "" )
        {
            unset( $aData->'score' );
        }
        if ( !$aData['goods_id'] )
        {
            $aData['goods_type'] = "bind";
            $aData['cat_id'] = 0;
            $aRs = $this->db->query( "SELECT * FROM sdb_goods WHERE 0" );
            $sSql = $this->db->getinsertsql( $aRs, $aData );
            if ( $this->db->exec( $sSql ) )
            {
                $aData['goods_id'] = $this->db->lastinsertid( );
            }
            else
            {
                trigger_error( "", E_USER_ERROR );
                return false;
            }
        }
        $product =& $this->system->loadmodel( "goods/products" );
        $aPkg = $aData['pkgnum'];
        $aData['mktprice'] = 0;
        foreach ( $aPkg as $pid => $num )
        {
            $aData['product_id'] = intval( $pid );
            $aData['pkgnum'] = ceil( $num );
            $aRs = $this->db->query( "SELECT * FROM sdb_package_product WHERE goods_id = ".$aData['goods_id']." AND product_id = ".$aData['product_id'] );
            $sSql = $this->db->getupdatesql( $aRs, $aData, true );
            if ( $sSql && !$this->db->exec( $sSql ) )
            {
                trigger_error( "", E_USER_ERROR );
                return false;
            }
            $aProduct = $product->getfieldbyid( $pid, array( "name, store, price" ) );
            if ( $aProduct['store'] < $aData['pkgnum'] * $aData['store'] )
            {
                $aNotice[] = $aProduct['name'];
            }
            $aData['mktprice'] += $aProduct['price'] * $aData['pkgnum'];
            $aPdt[] = $aData['product_id'];
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".$aData['goods_id'] );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        if ( $sSql && !$this->db->exec( $sSql ) )
        {
            trigger_error( "", E_USER_ERROR );
            return false;
        }
        $this->db->exec( "DELETE FROM sdb_package_product WHERE goods_id = ".$aData['goods_id']." AND product_id NOT IN(".implode( ",", $aPdt ).")" );
        if ( $aNotice )
        {
            $msg = __( "注意，商品：" ).implode( ",", $aNotice ).__( "的库存不足。" );
        }
        return true;
    }

    function delpackage( $arrId )
    {
        if ( !empty( $arrId ) )
        {
            $sSql = "DELETE FROM sdb_goods WHERE goods_id IN (".implode( $arrId, "," ).") AND goods_type='bind'";
            if ( $this->db->exec( $sSql ) )
            {
                $sSql = "DELETE FROM sdb_package_product WHERE goods_id IN (".implode( $arrId, "," ).")";
                $this->db->exec( $sSql );
                return true;
            }
            return false;
        }
        return false;
    }

    function getinitorder( )
    {
        $aTemp = $this->db->selectrow( "select max(p_order) as p_order from sdb_goods where goods_type='bind'" );
        return $aTemp['p_order'] + 1;
    }

}

?>
