<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_promotionactivity extends shopobject
{

    var $idColumn = "pmta_id";
    var $textColumn = "pmta_name";
    var $defaultCols = "pmta_id,pmta_name,pmta_time_begin,pmta_time_end,pmta_enabled,pmta_describe";
    var $adminCtl = "sale/activity";
    var $defaultOrder = array
    (
        0 => "pmta_id",
        1 => "desc"
    );
    var $tableName = "sdb_promotion_activity";

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['pmta_name'] )
        {
            $where[] = "pmta_name like'%".$filter['pmta_name']."%'";
        }
        return shopobject::_filter( $filter )." and ".implode( $where, " and " );
    }

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 150,
                "html" => "sale/activity/command.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function getactivitybyid( $nId )
    {
        return $this->db->selectrow( "select * from sdb_promotion_activity where pmta_id=".intval( $nId ) );
    }

    function saveactivity( $aData )
    {
        if ( $aData['pmta_id'] )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_promotion_activity WHERE pmta_id=".$aData['pmta_id'] );
            $sSql = $this->db->getupdatesql( $aRs, $aData );
            return !$sSql || $this->db->exec( $sSql );
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_promotion_activity WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            return $this->db->lastinsertid( );
        }
        return false;
    }

    function delete( $filter )
    {
        $pmtaId = $filter['pmta_id'];
        if ( $pmtaId[0] == "_ALL_" )
        {
            $sql = "select pmta_id from sdb_promotion_activity";
            $row = $this->db->select( $sql );
            if ( $row )
            {
                foreach ( $row as $key => $val )
                {
                    $tmpRow[] = $val['pmta_id'];
                }
            }
            if ( $this->db->exec( "delete from sdb_promotion_activity" ) )
            {
                return $this->db->exec( "delete from sdb_promotion where pmta_id IN(".implode( ",", $tmpRow ).")" );
            }
            return false;
        }
        $sSql = "delete from sdb_promotion_activity where pmta_id in (".implode( ",", $pmtaId ).")";
        if ( $this->db->exec( $sSql ) )
        {
            $sql = "delete from sdb_promotion where pmta_id IN(".implode( ",", $pmtaId ).")";
            return $this->db->exec( $sql );
        }
        return false;
    }

}

?>
