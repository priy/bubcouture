<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_refund extends shop_api_object
{

    public $app_error = array
    (
        "can not create refund" => array
        (
            "no" => "b_refund_001",
            "debug" => "",
            "level" => "warning",
            "info" => "退款单不能正常生成",
            "desc" => ""
        ),
        "refund is out of order price" => array
        (
            "no" => "b_refund_001",
            "debug" => "",
            "level" => "warning",
            "info" => "退款金额不在订单已支付金额范围",
            "desc" => ""
        )
    );

    public function refund( $aOrder, $payMoney, &$obj_order )
    {
        $obj_order->checkOrderStatus( "refund", $aOrder );
        $aUpdate['pay_status'] = 5;
        $aUpdate['payed'] = $aOrder['cost_payment'];
        if ( isset( $aOrder['refund_money'] ) )
        {
            if ( $payMoney < $aOrder['refund_money'] || $aOrder['refund_money'] <= 0 )
            {
                $this->api_response( "fail", "data fail", $result, "退款金额不在订单已支付金额范围" );
                $this->add_application_error( "refund is out of order price", $insert, "asdfsdfsdaf_id" );
            }
            if ( $aOrder['refund_money'] < $payMoney )
            {
                $aUpdate['pay_status'] = 4;
                $aUpdate['payed'] = $aOrder['payed'] - $aOrder['refund_money'];
            }
            $paymentId = 1;
            $payMethod = "预存款支付";
            $payMoney = $aOrder['refund_money'];
        }
        else
        {
            $this->api_response( "fail", "data fail", $result, "没有退款金额" );
        }
        $obj_advance = $this->load_api_instance( "deduct_dealer_advance", "1.0" );
        $obj_advance->checkAccount( $aOrder['member_id'], 0 );
        $aRefund['money'] = $payMoney;
        $aRefund['order_id'] = $aOrder['order_id'];
        $aRefund['pay_type'] = "deposit";
        $aRefund['member_id'] = $aOrder['member_id'];
        $aRefund['account'] = $aOrder['account'];
        $aRefund['pay_account'] = $aOrder['pay_account'];
        $aRefund['bank'] = $aOrder['bank'];
        $aRefund['title'] = "title";
        $aRefund['currency'] = $aOrder['currency'];
        $aRefund['payment'] = $paymentId;
        $aRefund['paymethod'] = $payMethod;
        $aRefund['status'] = "sent";
        $aRefund['memo'] = "经销商修改订单退款产生";
        $aRefund['refund_id'] = $this->gen_id( );
        $aRefund['t_ready'] = time( );
        $aRefund['t_sent'] = time( );
        $objPlatform = $this->system->loadModel( "system/platform" );
        if ( $objPlatform->tell_platform( "refunds", array(
            "refund_id" => $aRefund['refund_id'],
            "data" => $aRefund
        ) ) === FALSE )
        {
            $this->api_response( "fail", "data fail", $result, $objPlatform->getErrorInfo( ) );
        }
        $rs = $this->db->query( "select * from sdb_refunds where 0=1" );
        $sql = $this->db->getInsertSQL( $rs, $aRefund );
        if ( !$this->db->exec( $sql ) )
        {
            $this->api_response( "fail", "data fail", $result, "退款单不能正常生成" );
        }
        $obj_order->addLog( $aOrder['order_id'], "订单退款".$payMoney, NULL, NULL, "退款" );
        $aUpdate['acttime'] = time( );
        $aUpdate['last_change_time'] = time( );
        $message .= "预存款退款：#O{".$aOrder['order_id']."}#";
        $obj_advance->add( $aOrder['member_id'], $payMoney, $message, "", $aOrder['order_id'], "", "预存款退款" );
        return $aUpdate;
    }

    public function gen_id( )
    {
        $i = rand( 0, 9999 );
        do
        {
            if ( 9999 == $i )
            {
                $i = 0;
            }
            ++$i;
            $refund_id = time( ).str_pad( $i, 4, "0", STR_PAD_LEFT );
            $row = $this->db->selectrow( "select refund_id from sdb_refunds where refund_id ='".$refund_id."'" );
        } while ( $row );
        return $refund_id;
    }

}

?>
