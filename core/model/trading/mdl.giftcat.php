<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_giftcat extends shopobject
{

    var $idColumn = "giftcat_id";
    var $textColumn = "cat";
    var $defaultCols = "_cmd,cat,shop_iffb,orderlist";
    var $adminCtl = "sale/giftcat";
    var $defaultOrder = array
    (
        0 => "orderlist",
        1 => "desc"
    );
    var $tableName = "sdb_gift_cat";

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "sale/giftcat/command.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( is_array( $filter['giftcat_id'] ) )
        {
            foreach ( $filter['gcat'] as $giftcat_id )
            {
                if ( $giftcat_id != "_ANY_" )
                {
                    $cats[] = $giftcat_id;
                }
                if ( 0 < count( $cats ) )
                {
                    $where[] = "giftcat_id in (".implode( $cats, "," ).")";
                }
            }
        }
        if ( $filter['cat'] )
        {
            $where[] = "cat like'%".$filter['cat']."%'";
        }
        if ( isset( $filter['shop_iffb'] ) && $filter['shop_iffb'] === 1 )
        {
            $where[] = "shop_iffb='1'";
        }
        return shopobject::_filter( $filter )." and ".implode( $where, " and " );
    }

    function gettypebyid( $catid )
    {
        $sql = "SELECT * FROM sdb_gift_cat WHERE giftcat_id=".$catid;
        return $this->db->selectrow( $sql );
    }

    function addtype( $aData )
    {
        if ( isset( $aData['giftcat_id'] ) )
        {
            $aTemp = $this->db->select( "SELECT cat FROM sdb_gift_cat where giftcat_id != ".$aData['giftcat_id'] );
        }
        else
        {
            $aTemp = $this->db->select( "SELECT cat FROM sdb_gift_cat " );
        }
        foreach ( $aTemp as $val )
        {
            if ( !( $aData['cat'] == $val['cat'] ) )
            {
                continue;
            }
            trigger_error( __( "分类名称已经存在" ), E_USER_ERROR );
            return false;
        }
        if ( $aData['giftcat_id'] )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_gift_cat WHERE giftcat_id=".$aData['giftcat_id'] );
            $sSql = $this->db->getupdatesql( $aRs, $aData );
            return !$sSql || $this->db->exec( $sSql );
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_gift_cat WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            return $this->db->lastinsertid( );
        }
        return false;
    }

    function getinitorder( )
    {
        $aTemp = $this->db->selectrow( "select max(orderlist) as orderlist from sdb_gift_cat" );
        return $aTemp['orderlist'] + 1;
    }

    function gettypearr( )
    {
        $aTemp = $this->db->select( "SELECT giftcat_id,cat FROM sdb_gift_cat WHERE 1    ORDER BY orderlist desc" );
        return $aTemp;
    }

}

?>
