<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_2_0_advance extends shop_api_object
{

    public $max_number = 100;
    public $app_error = array
    (
        "predeposits_is_not_enough" => array
        (
            "no" => "b_advance_001",
            "debug" => "",
            "level" => "error",
            "desc" => "预存款帐户余额不足",
            "info" => ""
        ),
        "fail_to_update_predeposits" => array
        (
            "no" => "b_advance_002",
            "debug" => "",
            "level" => "error",
            "desc" => "更新预存款帐户失败",
            "info" => ""
        ),
        "payment_is_not_predeposits" => array
        (
            "no" => "b_advance_003",
            "debug" => "",
            "level" => "error",
            "desc" => "支付方式不是预存款",
            "info" => ""
        ),
        "advance_is_not_exist" => array
        (
            "no" => "b_advance_004",
            "debug" => "",
            "level" => "error",
            "desc" => "预存款帐户不存在",
            "info" => ""
        ),
        "fail_to_select_advance" => array
        (
            "no" => "b_advance_005",
            "debug" => "",
            "level" => "error",
            "desc" => "查询预存款帐户失败",
            "info" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array( );
        return $columns;
    }

    public function add( $member_id, $money, $message, $payment_id = "", $order_id = "", $paymethod = "", $memo = "" )
    {
        $error_msg = "";
        $this->checkAccount( $member_id, 0, $rows );
        $data = array(
            "advance" => $rows[0]['advance'] + $money
        );
        $member_advance = $data['advance'];
        $rs = $this->db->exec( "SELECT * FROM sdb_members WHERE member_id=".intval( $member_id ) );
        $sql = $this->db->getUpdateSQL( $rs, $data );
        if ( $this->db->exec( $sql ) )
        {
            $this->log( $member_id, $money, $message, $payment_id, $order_id, $paymethod, $memo, $member_advance );
        }
        else
        {
            $error_msg = "fail_to_update_predeposits";
        }
        if ( !empty( $error_msg ) )
        {
            $this->add_application_error( $error_msg );
        }
        else
        {
            return TRUE;
        }
    }

    public function checkAccount( $member_id, $money = 0, &$rows )
    {
        $error_msg = "";
        if ( $rs = $this->db->exec( "SELECT advance,member_id FROM sdb_members WHERE member_id=".intval( $member_id ) ) )
        {
            $rows = $this->db->getRows( $rs, 1 );
            if ( 0 < count( $rows ) )
            {
                if ( $rows[0]['advance'] < $money )
                {
                    $error_msg = "predeposits_is_not_enough";
                }
            }
            else
            {
                $error_msg = "advance_is_not_exist";
            }
        }
        else
        {
            $error_msg = "fail_to_select_advance";
            return FALSE;
        }
        if ( !empty( $error_msg ) )
        {
            $this->add_application_error( $error_msg );
        }
        else
        {
            return TRUE;
        }
    }

    public function deduct_dealer_advance( $data )
    {
        $dealer_id = $data['dealer_id'];
        $order_id = $data['order_id'];
        $pay_id = $data['pay_id'];
        $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
        $obj_member->verify_member_valid( $dealer_id, $member );
        $obj_order = $this->load_api_instance( "set_dead_order", "2.0" );
        $obj_order->verify_order_valid( $order_id, $order, "*" );
        $obj_order->checkOrderStatus( "pay", $order );
        $obj_order->verify_order_item_valid( $order_id, $local_order_item_list );
        $obj_payment_cfg = $this->load_api_instance( "search_payment_cfg_list", "2.0" );
        $obj_payment_cfg->verify_paymentcfg_advance_valid( $pay_id, $local_payment_cfg );
        $obj_payment = $this->load_api_instance( "search_payments_by_order", "2.0" );
        if ( $local_payment_cfg['pay_type'] != "deposit" )
        {
            $this->add_application_error( "payment_is_not_predeposits" );
        }
        $last_cost_payment = empty( $order['cost_payment'] ) ? 0 : $order['cost_payment'];
        $money = $order['total_amount'] - $order['payed'];
        $cost_payment = $local_payment_cfg['fee'] * $money;
        $cost_payment = $obj_payment->formatNumber( $cost_payment );
        $order['total_amount'] = $order['total_amount'] + $cost_payment;
        $order['total_amount'] = $obj_payment->getOrderDecimal( $order['total_amount'] );
        $money = $order['total_amount'] - $order['payed'];
        $this->advance_is_enough( $member['advance'], $money );
        $obj_order->is_payed( $money );
        $obj_order->verify_payed_valid( $order, $money );
        $order_payment = array(
            "order_id" => $order['order_id'],
            "money" => $money,
            "paycost" => $cost_payment,
            "member_id" => $order['member_id'],
            "currency" => $order['currency'],
            "payment" => $order['payment'] ? $order['payment'] : $pay_id
        );
        $oOrder = $this->system->loadModel( "trading/order" );
        if ( method_exists( $oOrder, "getFreezeStorageStatus" ) && !$obj_order->is_freeze_store( $order_id ) )
        {
            if ( $aTemp = $oOrder->isNotEnoughStore( $order_id ) )
            {
                $this->api_response( "fail", "data fail", $result, "订单货品(".$aResult['product']['name'].")没有可下单库存" );
            }
            $oOrder->freezeStorage( $order_id );
            $obj_order->update_order_freeze( $order_id );
        }
        $payment_id = $obj_payment->create_payment( $pay_id, $order_payment, "deposit" );
        $obj_payment->verify_payment_valid( $payment_id, $payment );
        $objPlatform =& $this->system->loadModel( "system/platform" );
        if ( $objPlatform->tell_platform( "payments", array(
            "pay_id" => $payment_id
        ) ) === FALSE )
        {
            $obj_payment->deletePayment( $payment_id );
            $this->api_response( "fail", "data fail", $result, $objPlatform->getErrorInfo( ) );
        }
        $obj_product = $this->load_api_instance( "search_product_by_bn", "2.0" );
        $this->deduct_member_advance( $member, $money, $payment_id, $order_id );
        $curr_cost_payment = $last_cost_payment + $cost_payment;
        $obj_order->set_order_payment( $order_id, $curr_cost_payment );
        $obj_order->changeOrderPayment( $order_id, $pay_id );
        $obj_order->payed( $order, $money );
        $result['data_info'] = $payment;
        $this->api_response( "true", FALSE, $result );
    }

    public function deduct_member_advance( $member, $money, $payment_id, $order_id )
    {
        $member_id = $member['member_id'];
        $data = array(
            "advance" => $member['advance'] - $money
        );
        $message = "扣费成功";
        $memo = "扣费成功";
        $paymethod = "预存款支付";
        $member_advance = $data['advance'];
        $rs = $this->db->exec( "SELECT * FROM sdb_members WHERE member_id=".intval( $member_id ) );
        $sql = $this->db->getUpdateSQL( $rs, $data );
        if ( !$sql || $this->db->exec( $sql ) )
        {
            $this->log( $member_id, 0 - $money, $message, $payment_id, $order_id, $paymethod, $memo, $member_advance );
        }
        else
        {
            $this->add_application_error( "fail_to_update_predeposits" );
        }
    }

    public function log( $member_id, $money, $message, $payment_id = "", $order_id = "", $paymethod = "", $memo = "", $member_advance = "" )
    {
        $shop_advance = $this->getShopAdvance( );
        $rs = $this->db->exec( "select * from sdb_advance_logs where 0=1" );
        $sql = $this->db->getInsertSQL( $rs, array(
            "member_id" => $member_id,
            "money" => $money,
            "mtime" => time( ),
            "message" => $message,
            "payment_id" => $payment_id,
            "order_id" => $order_id,
            "paymethod" => $paymethod,
            "memo" => $memo,
            "import_money" => 0 < $money ? $money : 0,
            "explode_money" => $money < 0 ? 0 - $money : 0,
            "member_advance" => $member_advance,
            "shop_advance" => $shop_advance
        ) );
        return $this->db->exec( $sql );
    }

    public function advance_is_enough( $member_advance, $money )
    {
        if ( $member_advance < $money )
        {
            $this->add_application_error( "predeposits_is_not_enough" );
        }
        return TRUE;
    }

    public function getShopAdvance( )
    {
        $row = $this->db->selectrow( "SELECT SUM(advance) as sum_advance FROM sdb_members" );
        return $row['sum_advance'];
    }

}

?>
