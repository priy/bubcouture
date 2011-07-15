<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_2_0_order extends shop_api_object
{

    public $app_error = array
    (
        "order_not_belong_to_dealer" => array
        (
            "no" => "b_order_001",
            "debug" => "",
            "level" => "error",
            "desc" => "此订单不属于经销商",
            "info" => ""
        ),
        "order_has_orderitem" => array
        (
            "no" => "b_order_002",
            "debug" => "",
            "level" => "error",
            "desc" => "订单没有对应的商品项",
            "info" => ""
        ),
        "order_id_not_null" => array
        (
            "no" => "b_order_003",
            "debug" => "",
            "level" => "error",
            "desc" => "订单ID不能为空",
            "info" => ""
        ),
        "dealer_id_not_null" => array
        (
            "no" => "b_order_004",
            "debug" => "",
            "level" => "error",
            "desc" => "经销商ID不能为空",
            "info" => ""
        ),
        "dealer_order_id_not_null" => array
        (
            "no" => "b_order_005",
            "debug" => "",
            "level" => "error",
            "desc" => "经销商订单ID不能为空",
            "info" => ""
        ),
        "tax_company_must_tax" => array
        (
            "no" => "b_order_006",
            "debug" => "",
            "level" => "error",
            "desc" => "如果要开票,必须要交税",
            "info" => ""
        ),
        "order_not_valid" => array
        (
            "no" => "b_order_007",
            "debug" => "",
            "level" => "error",
            "desc" => "订单无效",
            "info" => ""
        ),
        "order_not_dealer" => array
        (
            "no" => "b_order_008",
            "debug" => "",
            "level" => "error",
            "desc" => "此订单不是经销商订单",
            "info" => ""
        ),
        "not_active" => array
        (
            "no" => "b_order_009",
            "debug" => "",
            "level" => "error",
            "desc" => "订单状态未激活",
            "info" => ""
        ),
        "not_pay" => array
        (
            "no" => "b_order_010",
            "debug" => "",
            "level" => "error",
            "desc" => "订单未支付",
            "info" => ""
        ),
        "already_full_refund" => array
        (
            "no" => "b_order_011",
            "debug" => "",
            "level" => "error",
            "desc" => "订单已全额退款",
            "info" => ""
        ),
        "already_pay" => array
        (
            "no" => "b_order_012",
            "debug" => "",
            "level" => "error",
            "desc" => "订单已支付",
            "info" => ""
        ),
        "go_process" => array
        (
            "no" => "b_order_013",
            "debug" => "",
            "level" => "error",
            "desc" => "订单支付中",
            "info" => ""
        ),
        "already_part_refund" => array
        (
            "no" => "b_order_014",
            "debug" => "",
            "level" => "error",
            "desc" => "订单已部分退款",
            "info" => ""
        ),
        "no_full_pay" => array
        (
            "no" => "b_order_015",
            "debug" => "",
            "level" => "error",
            "desc" => "订单未完成支付",
            "info" => ""
        ),
        "already_shipping" => array
        (
            "no" => "b_order_016",
            "debug" => "",
            "level" => "error",
            "desc" => "订单已配送",
            "info" => ""
        ),
        "not_shipping" => array
        (
            "no" => "b_order_017",
            "debug" => "",
            "level" => "error",
            "desc" => "订单未配送",
            "info" => ""
        ),
        "must_not_shipping" => array
        (
            "no" => "b_order_018",
            "debug" => "",
            "level" => "error",
            "desc" => "订单必须未配送",
            "info" => ""
        ),
        "must_not_pay" => array
        (
            "no" => "b_order_019",
            "debug" => "",
            "level" => "error",
            "desc" => "订单必须未支付",
            "info" => ""
        ),
        "is_dead" => array
        (
            "no" => "b_order_020",
            "debug" => "",
            "level" => "error",
            "desc" => "此订单是死单",
            "info" => ""
        ),
        "must_not_pending" => array
        (
            "no" => "b_order_021",
            "debug" => "",
            "level" => "error",
            "desc" => "订单必须是暂停发货",
            "info" => ""
        ),
        "order_exist" => array
        (
            "no" => "b_order_022",
            "debug" => "",
            "level" => "error",
            "desc" => "订单已经存在",
            "info" => ""
        ),
        "payment_is_out_of_order_amount" => array
        (
            "no" => "b_order_023",
            "debug" => "",
            "level" => "error",
            "desc" => "支付金额已经超过订单总金额",
            "info" => ""
        ),
        "order_not_payed" => array
        (
            "no" => "b_order_024",
            "debug" => "",
            "level" => "error",
            "desc" => "此订单不需要支付",
            "info" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array( );
        return $columns;
    }

    public function set_dead_order( $data )
    {
        $order_id = $data['order_id'];
        $this->verify_order_valid( $order_id, $local_new_version_order, "*" );
        $this->checkOrderStatus( "cancel", $local_new_version_order );
        $this->verify_order_item_valid( $order_id, $local_order_item_list );
        $obj_product = $this->load_api_instance( "search_product_by_bn", "2.0" );
        if ( $this->is_freeze_store( $order_id ) )
        {
            foreach ( $local_order_item_list as $k => $local_order_item )
            {
                $obj_product->update_product_store( $local_order_item['bn'], $local_order_item['nums'], "unfreeze" );
            }
        }
        $objPlatform = $this->system->loadModel( "system/platform" );
        if ( $objPlatform->tell_platform( "invalid_order", array(
            "id" => $order_id
        ) ) === FALSE )
        {
            $this->api_response( "fail", "data fail", $result, $objPlatform->getErrorInfo( ) );
        }
        $this->db->exec( "update sdb_orders set status=\"dead\" where version_id=0 and order_id=".$order_id );
        $this->api_response( "true", FALSE, $result );
    }

    public function set_cancel_stop_shipping( $data )
    {
        $order_id = $data['order_id'];
        $this->verify_order_valid( $order_id, $order, "*" );
        $this->checkOrderStatus( "cancel_stop_shipping", $order );
        $objPlatform = $this->system->loadModel( "system/platform" );
        if ( $objPlatform->tell_platform( "cancel_stop_shipping", array(
            "id" => $order_id
        ) ) === FALSE )
        {
            $this->api_response( "fail", "data fail", $result, $objPlatform->getErrorInfo( ) );
        }
        $this->db->exec( "update sdb_orders set status=\"active\" where  version_id=0 and order_id=".$order_id );
        $this->api_response( "true", FALSE, $result );
    }

    public function set_stop_shipping( $data )
    {
        $order_id = $data['order_id'];
        $this->verify_order_valid( $order_id, $order, "*" );
        $this->checkOrderStatus( "stop_shipping", $order );
        $objPlatform = $this->system->loadModel( "system/platform" );
        if ( $objPlatform->tell_platform( "stop_shipping", array(
            "id" => $order_id
        ) ) === FALSE )
        {
            $this->api_response( "fail", "data fail", $result, $objPlatform->getErrorInfo( ) );
        }
        $this->db->exec( "update sdb_orders set status=\"pending\" where version_id=0 and  order_id=".$order_id );
        $this->api_response( "true", FALSE, $result );
    }

    public function set_disable( $order_id, $disable )
    {
        $this->db->exec( "update sdb_orders set disabled=\"".$disable."\" where version_id=0 and  order_id=".$order_id );
    }

    public function change_order_info( $data )
    {
        $data = stripslashes_array( $data );
        $data_order = json_decode( $data['order'], TRUE );
        $arr_order_item = $data_order['items'];
        unset( $data_order['items'] );
        $order_id = $data_order['order_id'];
        $dealer_id = $data_order['dealer_id'];
        $this->verify_order_valid( $order_id, $local_new_version_order, "*" );
        $this->verify_is_dealerorder( $dealer_id, $local_new_version_order );
        $this->checkOrderStatus( "change_order", $local_new_version_order );
        $this->verify_order_item_valid( $order_id, $local_order_item_list );
        $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
        $obj_member->verify_member_valid( $dealer_id, $member );
        $obj_product = $this->load_api_instance( "search_product_by_bn", "2.0" );
        $objGoodsStatus = $this->system->loadModel( "trading/goodsstatus" );
        $op_goods_id = $objGoodsStatus->getGoodsIdByOrders( $order_id );
        $objGoodsStatus->batchEditStart( $op_goods_id, array( "updateAct" => "store" ) );
        if ( $this->is_freeze_store( $order_id ) )
        {
            foreach ( $local_order_item_list as $k => $local_order_item )
            {
                $obj_product->update_product_store( $local_order_item['bn'], $local_order_item['nums'], "unfreeze" );
            }
        }
        if ( count( $arr_order_item ) <= 0 )
        {
            $this->set_disable( $order_id, "true" );
            $this->api_response( "true", FALSE, $result );
        }
        $obj_product->filter_product_invalid( $member, $arr_order_item, $filter_order_item );
        $arr_order_item = $filter_order_item;
        $old_pay = $local_new_version_order['cost_payment'];
        if ( isset( $data_order['is_tax'] ) )
        {
            $local_new_version_order['is_tax'] = $data_order['is_tax'];
        }
        if ( isset( $data_order['is_protect'] ) )
        {
            $local_new_version_order['is_protect'] = $data_order['is_protect'];
        }
        if ( isset( $data_order['tax_company'] ) )
        {
            $local_new_version_order['tax_company'] = $data_order['tax_company'];
        }
        $local_new_version_order = $this->organize_order_data( "change", $member, $local_new_version_order, $arr_order_item );
        $local_new_version_order['total_amount'] = $local_new_version_order['total_amount'] + $old_pay;
        $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
        $local_new_version_order['final_amount'] = $local_new_version_order['total_amount'] * $local_new_version_order['cur_rate'];
        $local_new_version_order['final_amount'] = $obj_payments->getOrderDecimal( $local_new_version_order['final_amount'] );
        $new_paymoney = $local_new_version_order['total_amount'];
        $local_paymoney = $local_new_version_order['payed'];
        if ( $local_new_version_order['pay_status'] != 0 )
        {
            $a_update_order = array( );
            if ( $new_paymoney < $local_paymoney )
            {
                $refund_money = $local_paymoney - $new_paymoney;
                $local_new_version_order['refund_money'] = $refund_money;
                $obj_refund = $this->load_api_instance( "refund", "2.0" );
                $a_update_order = $obj_refund->refund( $local_new_version_order, $local_paymoney, $this );
                $a_update_order['process_money'] = $refund_money;
                unset( $local_new_version_order['refund_money'] );
                $a_update_order['pay_status'] = 1;
            }
            else if ( $local_paymoney < $new_paymoney )
            {
                $local_new_version_order['full_money'] = $new_paymoney - $local_paymoney;
                $a_update_order = $this->fill_section( $local_new_version_order );
                $a_update_order['process_money'] = $local_paymoney - $new_paymoney;
                unset( $local_new_version_order['full_money'] );
            }
            else
            {
                $a_update_order['pay_status'] = 1;
            }
            if ( 0 < count( $a_update_order ) )
            {
                $local_new_version_order = array_merge( $local_new_version_order, $a_update_order );
            }
        }
        $data_order = $local_new_version_order;
        $data_order_item = $arr_order_item;
        $this->create_order( $data_order, $insert_order );
        $this->create_order_item( $order_id, $data_order_item, $insert_order_item );
        $objGoodsStatus->batchEditEnd( );
        $this->addLog( $order_id, "远程订单修改", NULL, NULL, "订单修改" );
        $insert_order['items'] = $insert_order_item;
        $insert_order['effect_time'] = $insert_order['effect_time'] - $insert_order['createtime'];
        $insert_order['process_money'] = isset( $insert_order['process_money'] ) ? $insert_order['process_money'] : 0;
        $insert_order = $this->filter_order_output( $insert_order );
        $result['data_info'] = $insert_order;
        $this->api_response( "true", FALSE, $result );
    }

    public function generate_order_record( $data )
    {
        $data = stripslashes_array( $data );
        $order = json_decode( $data['order'], TRUE );
        $order_id = $order['order_id'];
        $dealer_id = $order['dealer_id'];
        $dealer_order_id = $order['dealer_order_id'];
        if ( empty( $order_id ) )
        {
            $this->add_application_error( "order_id_not_null" );
        }
        if ( empty( $dealer_id ) )
        {
            $this->add_application_error( "dealer_id_not_null" );
        }
        if ( empty( $dealer_order_id ) )
        {
            $this->add_application_error( "dealer_order_id_not_null" );
        }
        $this->verify_order_exist( $order_id );
        $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
        $obj_member->verify_member_valid( $dealer_id, $member );
        $arr_order_item = $order['items'];
        unset( $order['items'] );
        $obj_product = $this->load_api_instance( "search_product_by_bn", "2.0" );
        $obj_product->filter_product_invalid( $member, $arr_order_item, $filter_order_item );
        $arr_order_item = $filter_order_item;
        $order = $this->organize_order_data( "new", $member, $order, $arr_order_item );
        $order['use_registerinfo'] = "true";
        $order['member_id'] = $member['member_id'];
        $objGoodsStatus = $this->system->loadModel( "trading/goodsstatus" );
        $op_goods_id = array( );
        foreach ( $arr_order_item as $order_item )
        {
            $op_goods_id[] = $order_item['goods_id'];
        }
        $objGoodsStatus->batchEditStart( $op_goods_id, array( "updateAct" => "store" ) );
        $obj_tools = $this->load_api_instance( "get_http", "1.0" );
        $order = $obj_tools->addslashes_array( $order );
        $this->create_order( $order, $insert_order );
        $this->create_order_item( $order['order_id'], $arr_order_item, $insert_order_item );
        $objGoodsStatus->batchEditEnd( );
        $this->addLog( $order_id, "远程订单创建", NULL, NULL, "订单创建" );
        $insert_order['items'] = $insert_order_item;
        $insert_order['effect_time'] = $insert_order['effect_time'] - $insert_order['createtime'];
        $insert_order = $obj_tools->stripslashes_array( $insert_order );
        $insert_order = $this->filter_order_output( $insert_order );
        $result['data_info'] = $insert_order;
        $this->api_response( "true", FALSE, $result );
    }

    public function organize_order_data( $type, $member, $aOrder, &$aOrderItem )
    {
        if ( !empty( $aOrder['tax_company'] ) && "true" != $aOrder['is_tax'] )
        {
            $this->add_application_error( "tax_company_must_tax" );
        }
        $effect_time = $this->system->getConf( "system.order_invalid_time" ) * 60 * 60;
        $aOrder['effect_time'] = $effect_time;
        $weight = 0;
        $tostr = "";
        $itemnum = 0;
        $cost_item = 0;
        $total_goods_score = 0;
        foreach ( $aOrderItem as $k => $order_item )
        {
            $weight += $order_item['amount_weight'];
            $tostr += $order_item['name']."(".$order_item['nums']."),";
            $itemnum += $order_item['nums'];
            $cost_item += $order_item['amount'];
            $total_goods_score += $order_item['score'];
            unset( $order_item['score'] );
            $aOrderItem[$k] = $order_item;
        }
        if ( !empty( $tostr ) )
        {
            $tostr = substr( $tostr, 0, strlen( $tostr ) - 1 );
        }
        $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
        $cost_item = $obj_payments->formatNumber( $cost_item );
        $aOrder['weight'] = $weight;
        $aOrder['tostr'] = $tostr;
        $aOrder['itemnum'] = $itemnum;
        $aOrder['cost_item'] = $cost_item;
        $aOrder['total_amount'] = $cost_item;
        $score_u = 0;
        $aOrder['score_u'] = $score_u;
        if ( $type == "new" )
        {
            $score_g = $this->get_order_point( $member['member_lv_id'], $cost_item, $total_goods_score );
            $aOrder['score_g'] = intval( $score_g - $score_u );
        }
        if ( $aOrder['is_tax'] == "true" && $this->system->getConf( "site.trigger_tax" ) )
        {
            $aOrder['cost_tax'] = $cost_item * $this->system->getConf( "site.tax_ratio" );
            $aOrder['cost_tax'] = $obj_payments->formatNumber( $aOrder['cost_tax'] );
            $aOrder['total_amount'] += $aOrder['cost_tax'];
        }
        $shipping_area = explode( ":", $aOrder['ship_area'] );
        $aOrder['area'] = $shipping_area[2];
        $obj_delivery = $this->load_api_instance( "getDlTypeByArea", "1.0" );
        $rows = $obj_delivery->getDlTypeByArea( $aOrder['area'], $weight, $aOrder['shipping_id'] );
        $aOrder['cost_freight'] = $obj_payments->formatNumber( $obj_delivery->cal_fee( $rows[0]['expressions'], $weight, $cost_item, $rows[0]['price'] ) );
        $aOrder['shipping'] = $rows[0]['dt_name'];
        $aOrder['cost_freight'] = is_null( $aOrder['cost_freight'] ) ? 0 : $aOrder['cost_freight'];
        $aOrder['total_amount'] += $aOrder['cost_freight'];
        if ( $aOrder['is_protect'] == "true" )
        {
            $aOrder['cost_protect'] = $obj_payments->formatNumber( max( $cost_item * $rows[0]['protect_rate'], $rows[0]['minprice'] ) );
        }
        else
        {
            $aOrder['cost_protect'] = 0;
        }
        $aOrder['total_amount'] += $aOrder['cost_protect'];
        $currency = $this->getDefault( );
        $aOrder['currency'] = $currency['cur_code'];
        $aOrder['cur_rate'] = 0 < $currency['cur_rate'] ? $currency['cur_rate'] : 1;
        $aOrder['total_amount'] = $obj_payments->getOrderDecimal( $aOrder['total_amount'] );
        $aOrder['final_amount'] = $aOrder['total_amount'] * $aOrder['cur_rate'];
        $aOrder['final_amount'] = $obj_payments->getOrderDecimal( $aOrder['final_amount'] );
        $aOrder['is_remote'] = "true";
        return $aOrder;
    }

    public function getDefault( )
    {
        if ( $cur = $this->db->selectrow( "select * from sdb_currency where def_cur=1" ) )
        {
            return $cur;
        }
        else
        {
            return $this->db->selectrow( "select * FROM sdb_currency" );
        }
    }

    public function amountExceptPay( $order )
    {
        return $order['cost_protect'] + $order['cost_freight'] + $order['cost_tax'] + $order['cost_item'] - $order['pmt_amount'] - $order['discount'];
    }

    public function payed( $order, $money )
    {
        $pay_money = $order['payed'] + $money;
        if ( $order['total_amount'] <= $pay_money )
        {
            $pay_status = $order['pay_status'] == 2 ? 2 : 1;
        }
        else
        {
            $pay_status = 3;
        }
        $a_update_order['pay_status'] = $pay_status;
        $a_update_order['payed'] = $pay_money;
        $a_update_order['total_amount'] = $order['total_amount'];
        $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
        $a_update_order['final_amount'] = $order['total_amount'] * $order['cur_rate'];
        $a_update_order['final_amount'] = $obj_payments->getOrderDecimal( $a_update_order['final_amount'] );
        if ( 0 < $order['score_g'] && $order['pay_status'] == 0 && ( $pay_status == 1 || $pay_status == 2 ) )
        {
            $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
            $obj_member->chgPoint( $order['member_id'], $order['score_g'], "order_pay_get", 0 );
        }
        $this->update_order( $order['order_id'], $a_update_order );
        $this->addLog( $order['order_id'], "订单".$order['order_id']."付款".$money, NULL, NULL, "付款" );
    }

    public function changeOrderPayment( $order_id, $payment )
    {
        $a_update_order['payment'] = $payment;
        $this->update_order( $order_id, $a_update_order );
    }

    public function filter_order_output( $aOrder )
    {
        if ( isset( $aOrder['items']['member_id'] ) )
        {
            unset( $this->items['member_id'] );
        }
        if ( isset( $aOrder['order_source'] ) )
        {
            unset( $aOrder['order_source'] );
        }
        return $aOrder;
    }

    public function fill_section( $aOrder )
    {
        $aUpdate['pay_status'] = 3;
        return $aUpdate;
    }

    public function checkOrderStatus( $act, $aOrder )
    {
        $error_msg = "";
        switch ( $act )
        {
        case "pay" :
            if ( $aOrder['status'] != "active" && $aOrder['status'] != "pending" )
            {
                $error_msg = "not_active";
            }
            else if ( $aOrder['pay_status'] == "1" )
            {
                $error_msg = "already_pay";
            }
            else if ( $aOrder['pay_status'] == "2" )
            {
                $error_msg = "go_process";
            }
            else if ( $aOrder['pay_status'] == "4" )
            {
                $error_msg = "already_part_refund";
            }
            else if ( $aOrder['pay_status'] == "5" )
            {
                $error_msg = "already_full_refund";
            }
            break;
        case "refund" :
            if ( $aOrder['status'] != "active" && $aOrder['status'] != "pending" )
            {
                $error_msg = "not_active";
            }
            else if ( $aOrder['pay_status'] == "0" )
            {
                $error_msg = "not_pay";
            }
            else if ( $aOrder['pay_status'] == "5" )
            {
                $error_msg = "already_full_refund";
            }
            break;
        case "stop_shipping" :
            if ( $aOrder['status'] == "dead" )
            {
                $error_msg = "is_dead";
            }
            else if ( $aOrder['pay_status'] == "1" )
            {
                if ( $aOrder['ship_status'] != "0" )
                {
                    $error_msg = "must_not_shipping";
                }
            }
            else
            {
                $error_msg = "no_full_pay";
            }
            break;
        case "cancel_stop_shipping" :
            if ( $aOrder['status'] == "dead" )
            {
                $error_msg = "is_dead";
            }
            else if ( $aOrder['status'] != "pending" )
            {
                $error_msg = "must_not_pending";
            }
            break;
        case "change_order" :
            if ( $aOrder['status'] == "dead" )
            {
                $error_msg = "is_dead";
            }
            else if ( $aOrder['ship_status'] != "0" )
            {
                $error_msg = "must_not_shipping";
            }
            break;
        case "cancel" :
            if ( $aOrder['status'] != "active" )
            {
                $error_msg = "not_active";
            }
            else if ( 0 < $aOrder['pay_status'] )
            {
                $error_msg = "must_not_shipping";
            }
            else if ( 0 < $aOrder['ship_status'] )
            {
                $error_msg = "must_not_pay";
            }
            break;
        case "archive" :
            if ( $aOrder['status'] != "active" )
            {
                $error_msg = "not_active";
            }
            break;
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

    public function get_error( $key )
    {
        return $this->error[$key];
    }

    public function create_order( $data, &$insert_order )
    {
        $this->update_order_version( $data['order_id'] );
        $this->update_order_item_version( $data['order_id'] );
        $this->update_delivery_version( $data['order_id'] );
        $this->update_payments_version( $data['order_id'] );
        $this->update_refunds_version( $data['order_id'] );
        $curr_time = time( );
        $data['acttime'] = $curr_time;
        $data['createtime'] = $curr_time;
        $data['last_change_time'] = $curr_time;
        $data['effect_time'] += $curr_time;
        $data['version_id'] = 0;
        $aRs = $this->db->query( "SELECT * FROM sdb_orders WHERE 0=1" );
        $sSql = $this->db->getInsertSql( $aRs, $data );
        $this->db->exec( $sSql );
        $insert_order = $data;
    }

    public function create_order_item( $order_id, $arr_data, &$insert_order_item )
    {
        $obj_product = $this->load_api_instance( "search_product_by_bn", "2.0" );
        $oOrder = $this->system->loadModel( "trading/order" );
        if ( method_exists( $oOrder, "getFreezeStorageStatus" ) )
        {
            $flag = $oOrder->getFreezeStorageStatus( );
        }
        foreach ( $arr_data as $k => $data )
        {
            $data['version_id'] = 0;
            $data['order_id'] = $order_id;
            $aRs = $this->db->query( "SELECT * FROM sdb_order_items WHERE 0=1" );
            $sSql = $this->db->getInsertSql( $aRs, $data );
            if ( isset( $flag ) && $flag == "order" )
            {
                $obj_product->update_product_store( $data['bn'], $data['nums'] );
            }
            else if ( !isset( $flag ) )
            {
                $obj_product->update_product_store( $data['bn'], $data['nums'] );
            }
            $this->db->exec( $sSql );
            $arr_data[$k] = $data;
        }
        if ( isset( $flag ) && $flag == "order" )
        {
            $this->update_order_freeze( $order_id );
        }
        $insert_order_item = $arr_data;
    }

    public function set_order_payment( $order_id, $cost_payment )
    {
        $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
        $data['cost_payment'] = $obj_payments->formatNumber( $cost_payment );
        $this->update_order( $order_id, $data );
    }

    public function update_order_version( $order_id )
    {
        $this->db->exec( "update sdb_orders set version_id=version_id+1 where order_id=".$order_id." order by version_id desc" );
    }

    public function update_delivery_version( $order_id )
    {
        $this->db->exec( "update sdb_delivery set version_id=version_id+1 where order_id=".$order_id." order by version_id desc" );
    }

    public function update_refunds_version( $order_id )
    {
        $this->db->exec( "update sdb_refunds set version_id=version_id+1 where order_id=".$order_id." order by version_id desc" );
    }

    public function update_payments_version( $order_id )
    {
        $this->db->exec( "update sdb_payments set version_id=version_id+1 where order_id=".$order_id." order by version_id desc" );
    }

    public function update_order_freeze( $order_id )
    {
        $this->db->exec( "UPDATE sdb_orders SET is_freeze_store=\"true\" WHERE order_id=".$order_id );
    }

    public function is_freeze_store( $order_id )
    {
        $aResult = $this->db->selectrow( "SELECT is_freeze_store FROM sdb_orders WHERE version_id=0 AND order_id=".$order_id );
        return $aResult['is_freeze_store'] == "true";
    }

    public function update_order_item_version( $order_id )
    {
        $this->db->exec( "update sdb_order_items set version_id=version_id+1 where order_id=".$order_id." order by version_id desc" );
    }

    public function update_order( $order_id, $data )
    {
        $rs = $this->db->exec( "SELECT * FROM sdb_orders WHERE  version_id=0 and  order_id=".$order_id );
        $sql = $this->db->getUpdateSQL( $rs, $data );
        $this->db->exec( $sql );
    }

    public function update_order_item( $item_id, $data )
    {
        $rs = $this->db->exec( "SELECT * FROM sdb_order_items WHERE item_id=".$item_id );
        $sql = $this->db->getUpdateSQL( $rs, $data );
        $this->db->exec( $sql );
    }

    public function verify_order_valid( $order_id, &$order, $colums = "*" )
    {
        $_order = $this->db->selectrow( "select ".$colums." from sdb_orders where order_id=".$order_id." and version_id=0" );
        if ( !$_order )
        {
            $this->add_application_error( "order_not_valid" );
        }
        if ( empty( $_order['dealer_id'] ) )
        {
            $this->add_application_error( "order_not_dealer" );
        }
        $order = $_order;
    }

    public function verify_is_dealerorder( $dealer_id, $order )
    {
        if ( $dealer_id != $order['dealer_id'] )
        {
            $this->add_application_error( "order_not_belong_to_dealer" );
        }
    }

    public function verify_order_exist( $order_id )
    {
        $_order = $this->db->selectrow( "select order_id from sdb_orders where version_id=0 and  order_id=".$order_id );
        if ( $_order )
        {
            $this->add_application_error( "order_exist" );
        }
        $order = $_order;
    }

    public function verify_order_dead( $order )
    {
        if ( $order == "dead" )
        {
            $this->add_application_error( "is_dead" );
        }
    }

    public function verify_order_item_valid( $order_id, &$order_item_list, $colums = "*" )
    {
        $_order_item_list = $this->db->select( "select ".$colums." from sdb_order_items where version_id=0 and order_id=".$order_id );
        if ( !$_order_item_list )
        {
            $this->add_application_error( "order_has_orderitem" );
        }
        $order_item_list = $_order_item_list;
    }

    public function verify_payed_valid( $order, $payed )
    {
        $total_payed = $order['payed'] + $payed;
        $total_payed .= "";
        $total_amount = $order['total_amount']."";
        if ( $total_payed != $total_amount && $order['total_amount'] < $total_payed )
        {
            $this->add_application_error( "payment_is_out_of_order_amount" );
        }
    }

    public function addLog( $order_id, $message, $op_id = NULL, $op_name = NULL, $behavior = "", $result = "success" )
    {
        if ( $message )
        {
            $op_name = "";
            $rs = $this->db->query( "select * from sdb_order_log where 0=1" );
            $sql = $this->db->getInsertSQL( $rs, array(
                "order_id" => $order_id,
                "op_id" => $op_id,
                "op_name" => $op_name,
                "behavior" => $behavior,
                "result" => $result,
                "log_text" => addslashes( $message ),
                "acttime" => time( )
            ) );
            return $this->db->exec( $sql );
        }
        else
        {
            return FALSE;
        }
    }

    public function is_payed( $money )
    {
        if ( $money <= 0 )
        {
            $this->add_application_error( "order_not_payed" );
        }
    }

    public function get_order_point( $member_lv_id, $total_price, $total_goods_score )
    {
        $_totalGainScore = 0;
        if ( $this->system->getConf( "point.get_policy" ) == 0 )
        {
            $_totalGainScore = 0;
        }
        else if ( $this->system->getConf( "point.get_policy" ) == 1 )
        {
            $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
            $obj_member->verify_member_lv_valid( $member_lv_id, $member_lv, "more_point" );
            if ( ( $_point_rate = $member_lv['more_point'] ) <= 0 )
            {
                $_point_rate = 1;
            }
            $_totalGainScore = $total_price * $this->system->getConf( "point.get_rate" ) * $_point_rate;
        }
        else if ( $this->system->getConf( "point.get_policy" ) == 2 )
        {
            $_totalGainScore = intval( $total_goods_score );
        }
        return $_totalGainScore;
    }

}

?>
