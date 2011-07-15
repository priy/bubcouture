<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_2_0_member extends shop_api_object
{

    public $max_number = 100;
    public $app_error = array
    (
        "dealer_member_not_exists" => array
        (
            "no" => "b_member_001",
            "debug" => "",
            "level" => "error",
            "desc" => "经销商所对应的会员记录无效",
            "info" => ""
        ),
        "dealer_member_lv_not_exists" => array
        (
            "no" => "b_member_005",
            "debug" => "",
            "level" => "error",
            "desc" => "经销商所对应的会员级别不存在",
            "info" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array( );
        return $columns;
    }

    public function verify_member_valid( $dealer_id, &$member, $colums = "*" )
    {
        $_member = $this->db->selectrow( "select ".$colums." from sdb_members where certificate_id=".$dealer_id );
        if ( !$_member )
        {
            $this->add_application_error( "dealer_member_not_exists" );
        }
        else
        {
            $member = $_member;
        }
    }

    public function verify_member_lv_valid( $member_lv_id, &$member_lv, $colums = "*" )
    {
        if ( empty( $member_lv_id ) )
        {
            $member_lv = array( "dis_count" => 1 );
            return TRUE;
        }
        $_member_lv = $this->db->selectrow( "select ".$colums." from sdb_member_lv where member_lv_id=".$member_lv_id );
        if ( !$_member_lv )
        {
            $this->add_application_error( "dealer_member_lv_not_exists" );
        }
        else
        {
            $member_lv = $_member_lv;
        }
    }

    public function getMemberPoint( $userId )
    {
        $sSql = "SELECT point FROM sdb_members WHERE member_id=".intval( $userId );
        $aUserPoint = $this->db->selectrow( $sSql );
        return intval( $aUserPoint['point'] );
    }

    public function chgPoint( $userId, $nPoint, $sReason, $relatedId = NULL )
    {
        $aUserPoint['point'] = $this->getMemberPoint( $userId );
        $aUserPoint['point'] += $nPoint;
        $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
        $sSql = $this->db->GetUpdateSQL( $rRs, $aUserPoint );
        if ( $sSql )
        {
            $this->db->exec( $sSql );
        }
        $aPointHistory = array(
            "member_id" => $userId,
            "point" => $nPoint,
            "reason" => $sReason,
            "related_id" => $relatedId
        );
        $this->addHistory( $aPointHistory );
    }

    public function addHistory( $aData )
    {
        $aHistoryReason = $this->getHistoryReason( );
        $aData['time'] = time( );
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $aData['describe'] = $aHistoryReason[$aData['reason']]['describe'];
        $aData['type'] = $aHistoryReason[$aData['reason']]['type'];
        $rRs = $this->db->query( "SELECT * FROM sdb_point_history WHERE 0=1" );
        $sSql = $this->db->GetInsertSQL( $rRs, $aData );
        $this->db->query( $sSql );
    }

    public function getHistoryReason( )
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
            )
        );
        return $aHistoryReason;
    }

}

?>
