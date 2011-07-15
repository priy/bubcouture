<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_goodsnotify extends shopobject
{

    var $defaultCols = "goods_id,member_id,email,send_time,creat_time,status,product_id";
    var $idColumn = "gnotify_id";
    var $adminCtl = "goods/gnotify";
    var $textColumn = "goods_id";
    var $defaultOrder = array
    (
        0 => "gnotify_id",
        1 => "DESC"
    );
    var $tableName = "sdb_gnotify";
    var $typeName = "goods";

    function modifier_product_id( &$rows )
    {
        foreach ( $rows as $k => $v )
        {
            $date = $this->db->selectrow( "SELECT store FROM sdb_products  WHERE product_id = ".$v."" );
            if ( $date['store'] === "0" )
            {
                $rows[$k] = __( "无货" );
            }
            else
            {
                $rows[$k] = __( "有货" );
            }
        }
    }

    function searchoptions( )
    {
        return array(
            "gname" => __( "商品名称" )
        );
    }

    function getnotifybygid( $nGid )
    {
        return $this->db->select( "SELECT gn.*,m.uname,m.name FROM sdb_gnotify gn LEFT JOIN sdb_members m ON gn.member_id=m.member_id WHERE gn.goods_id=".$nGid );
    }

    function _filter( $filter )
    {
        if ( $filter['gname'] || $filter['gbn'] )
        {
            if ( $filter['gname'] )
            {
                $gfilter['name'] = $filter['gname'];
            }
            if ( $filter['gbn'] )
            {
                $gfilter['bn'] = $filter['gbn'];
            }
            $oGoods =& $this->system->loadmodel( "goods/products" );
            $filter['goods_id'][] = -1;
            foreach ( $oGoods->getlist( "goods_id", $gfilter, 0, 1000 ) as $rows )
            {
                $filter['goods_id'][] = $rows['goods_id'];
            }
        }
        unset( $filter->'gname' );
        if ( $filter['notifytime'] )
        {
            $where = " and creat_time > ".$filter['notifytime'];
        }
        return shopobject::_filter( $filter ).$where;
    }

    function getfieldbyid( $nId )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_gnotify WHERE gnotify_id=".$nId );
    }

    function getinfobyid( $nId )
    {
        return $this->db->selectrow( "SELECT gn.*, g.name AS goods_name, m.uname AS username FROM sdb_gnotify gn\n                    LEFT JOIN sdb_members m ON gn.member_id = m.member_id\n                    LEFT JOIN sdb_goods g ON g.goods_id = gn.goods_id\n                    WHERE gnotify_id=".$nId );
    }

    function createnotify( $aData )
    {
        $aData['disabled'] = "false";
        if ( $aData['member_id'] )
        {
            if ( $this->db->select( "SELECT * FROM sdb_gnotify WHERE goods_id=".$aData['goods_id']." AND product_id=".$aData['product_id']." AND member_id=".$aData['member_id']." AND status='ready'" ) )
            {
                $aRs = $this->db->exec( "SELECT * FROM sdb_gnotify WHERE goods_id=".$aData['goods_id']." AND product_id=".$aData['product_id']." AND member_id=".$aData['member_id']." AND status='ready'" );
                $sSql = $this->db->getupdatesql( $aRs, $aData, true );
            }
            else
            {
                $aRs = $this->db->exec( "SELECT * FROM sdb_gnotify WHERE goods_id=".$aData['goods_id']." AND product_id=".$aData['product_id']." AND member_id=".$aData['member_id'] );
                $sSql = $this->db->getinsertsql( $aRs, $aData );
            }
        }
        else
        {
            $aData['member_id'] = NULL;
            $aRs = $this->db->exec( "SELECT * FROM sdb_gnotify WHERE 0=1" );
            $sSql = $this->db->getinsertsql( $aRs, $aData );
        }
        if ( !$sSql && $this->db->exec( $sSql ) )
        {
            $this->updategoodsnum( $aData['goods_id'] );
            $status =& $this->system->loadmodel( "system/status" );
            $status->count_gnotify( );
            return true;
        }
        return false;
    }

    function updategoodsnum( $gid )
    {
        $nGNotify = $this->db->selectrow( "SELECT COUNT(gnotify_id) as notify_num FROM sdb_gnotify WHERE goods_id=".$gid );
        $num = intval( $nGNotify['notify_num'] );
        $aRs = $this->db->query( "SELECT notify_num FROM sdb_goods WHERE goods_id=".$gid );
        $sSql = $this->db->getupdatesql( $aRs, array(
            "notify_num" => $nGNotify['notify_num']
        ) );
        return !$sSql || $this->db->exec( $sSql );
    }

    function tonofity( $aData )
    {
        foreach ( $aData as $id )
        {
            $aTmp = $this->getinfobyid( $id );
            $objMember =& $this->system->loadmodel( "member/member" );
            $trust_uname = $objMember->trust_check( $aTmp['username'] );
            if ( $trust_uname )
            {
                $aTmp['username'] = $trust_uname;
            }
            $aTmp['product_url'] = $this->system->realurl( "product", "index", array(
                $aTmp['goods_id']
            ) );
            if ( $this->fireevent( "notify", $aTmp, $aTmp['member_id'] ) )
            {
                $this->setnotifystatus( $aTmp['gnotify_id'] );
                ++$sNum;
            }
            else
            {
                ++$fNum;
            }
        }
        $status =& $this->system->loadmodel( "system/status" );
        $status->count_gnotify( );
        return array(
            "success" => $sNum,
            "failed" => $fNum
        );
    }

    function setnotifystatus( $id )
    {
        $this->db->exec( "UPDATE sdb_gnotify SET status = 'send', send_time=".time( )." WHERE gnotify_id=".intval( $id ) );
        $status =& $this->system->loadmodel( "system/status" );
        $status->count_gnotify( );
        return true;
    }

}

?>
