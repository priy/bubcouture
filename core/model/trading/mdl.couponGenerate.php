<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_coupongenerate extends shopobject
{

    var $finder_action_tpl = "sale/coupon/generate/finder_action.html";
    var $idColumn = "cpns_id";
    var $textColumn = "pmt_name";
    var $defaultCols = "pmt_describe";
    var $defaultOrder = array
    (
        0 => "pmt_update_time",
        1 => "desc"
    );
    var $tableName = "sdb_pmt_gen_coupon";

    function getcolumns( )
    {
        return array(
            "cpns_id" => array(
                "label" => __( "促销ID" ),
                "width" => 110
            ),
            "pmt_describe" => array(
                "label" => __( "优惠券发放途径" )
            )
        );
    }

    function getlist( $cols, $filter, $start = 0, $limit = 20, $orderType = null )
    {
        $sql = "SELECT ".$cols." FROM sdb_pmt_gen_coupon\n                        LEFT JOIN sdb_promotion ON sdb_pmt_gen_coupon.pmt_id = sdb_promotion.pmt_id\n                        WHERE ".$this->_filter( $filter );
        if ( $orderType )
        {
            $sql .= " order by ".implode( $orderType, " " );
        }
        return $this->db->selectlimit( $sql, $limit, $start );
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['cpns_id'] )
        {
            $where[] = "sdb_pmt_gen_coupon.cpns_id=".intval( $filter['cpns_id'] );
        }
        return shopobject::_filter( $filter )." AND ".implode( $where, " AND " );
    }

}

?>
