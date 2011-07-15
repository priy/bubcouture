<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_pointhistory extends modelfactory
{

    function gethistoryreason( )
    {
        $aHistoryReason = array(
            "order_pay_use" => array(
                "describe" => __( "订单消费积分" ),
                "type" => 1,
                "related_id" => "sdb_mall_orders"
            ),
            "order_pay_get" => array(
                "describe" => __( "订单获得积分." ),
                "type" => 2,
                "related_id" => "sdb_mall_orders"
            ),
            "order_refund_use" => array(
                "describe" => __( "退还订单消费积分" ),
                "type" => 1,
                "related_id" => "sdb_mall_orders"
            ),
            "order_refund_get" => array(
                "describe" => __( "扣掉订单所得积分" ),
                "type" => 2,
                "related_id" => "sdb_mall_orders"
            ),
            "order_cancel_refund_consume_gift" => array(
                "describe" => __( "Score deduction for gifts refunded for order cancelling." ),
                "type" => 1,
                "related_id" => "sdb_mall_orders"
            ),
            "exchange_coupon" => array(
                "describe" => __( "兑换优惠券" ),
                "type" => 3,
                "related_id" => ""
            ),
            "operator_adjust" => array(
                "describe" => __( "管理员改变积分." ),
                "type" => 3,
                "related_id" => ""
            ),
            "consume_gift" => array(
                "describe" => __( "积分换赠品." ),
                "type" => 3,
                "related_id" => "sdb_mall_orders"
            ),
            "fire_event" => array(
                "describe" => __( "网店机器人触发事件" ),
                "type" => 3,
                "related_id" => ""
            )
        );
        return $aHistoryReason;
    }

    function addhistory( $aData )
    {
        $aHistoryReason = $this->gethistoryreason( );
        $aData['time'] = time( );
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $aData['describe'] = $aHistoryReason[$aData['reason']]['describe'];
        $rRs = $this->db->query( "SELECT * FROM sdb_point_history WHERE 0=1" );
        $sSql = $this->db->getinsertsql( $rRs, $aData );
        $this->db->exec( $sSql );
    }

    function getgainedpoint( $userId )
    {
        $aPoint = $this->db->select( "SELECT SUM(point) AS point FROM sdb_point_history WHERE member_id=".$userId." AND point>0" );
        return intval( $aPoint[0]['point'] );
    }

    function getconsumepoint( $userId )
    {
        $aPoint = $this->db->select( "SELECT sum(point) AS point FROM sdb_point_history WHERE member_id=".$userId." AND point<0" );
        return intval( $aPoint[0]['point'] );
    }

    function getorderconsumepoint( $orderId )
    {
        $sSql = "select score_u from sdb_orders where order_id='".$orderId."'";
        $aData = $this->db->selectrow( $sSql );
        return intval( $aData['score_u'] );
    }

    function getorderconsumeexperience( $orderId )
    {
        return $this->getorderconsumepoint( $orderId );
    }

    function getorderhistorygetpoint( $orderId )
    {
        $sSql = "select sum(point) as point from sdb_point_history where related_id='".$orderId."' and type=1";
        $aData = $this->db->selectrow( $sSql );
        return intval( $aData['point'] );
    }

    function getpointhistorylist( $userId )
    {
        $aData = $this->db->select( "SELECT time, reason, point FROM sdb_point_history WHERE member_id=".$userId." ORDER BY time DESC" );
        $aHistoryReason = $this->gethistoryreason( );
        if ( $aData )
        {
            foreach ( $aData as $k => $aItem )
            {
                $aData[$k]['describe'] = $aHistoryReason[$aItem['reason']]['describe'];
            }
        }
        return $aData;
    }

    function getfrontpointhistorylist( $userId, $nPage )
    {
        $aData = $this->db->selectpager( "SELECT time, reason, point FROM sdb_point_history WHERE member_id=".$userId." ORDER BY time DESC", $nPage, PERPAGE );
        $aHistoryReason = $this->gethistoryreason( );
        if ( $aData['data'] )
        {
            foreach ( $aData['data'] as $k => $aItem )
            {
                $aData['data'][$k]['describe'] = $aHistoryReason[$aItem['reason']]['describe'];
            }
        }
        return $aData;
    }

}

?>
