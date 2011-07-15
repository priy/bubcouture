<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_payment extends shop_api_object
{

    public $max_number = 100;
    public $arr_pay_plugins = NULL;
    public $app_error = array
    (
        "valid payment" => array
        (
            "no" => "b_payment_001",
            "debug" => "",
            "level" => "warning",
            "info" => "支付单无效",
            "desc" => ""
        ),
        "fail to create payment" => array
        (
            "no" => "b_payment_002",
            "debug" => "",
            "level" => "warning",
            "info" => "支付单生成失败",
            "desc" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array(
            "payment_id" => array( "type" => "int" ),
            "order_id" => array( "type" => "string" ),
            "member_id" => array( "type" => "string" ),
            "account" => array( "type" => "string" ),
            "bank" => array( "type" => "decimal" ),
            "pay_account" => array( "type" => "string" ),
            "currency" => array( "type" => "int" ),
            "money" => array( "type" => "string" ),
            "paycost" => array( "type" => "int" ),
            "cur_money" => array( "type" => "int" ),
            "pay_type" => array( "type" => "string" ),
            "payment" => array( "type" => "string" ),
            "paymethod" => array( "type" => "string" ),
            "op_id" => array( "type" => "decimal" ),
            "ip" => array( "type" => "string" ),
            "t_begin" => array( "type" => "int" ),
            "t_end" => array( "type" => "string" ),
            "status" => array( "type" => "int" ),
            "memo" => array( "type" => "int" ),
            "disabled" => array( "type" => "string" ),
            "trade_no" => array( "type" => "int" )
        );
        return $columns;
    }

    public function search_payments_by_order( $data )
    {
        $data_info = $this->db->select( "select ".implode( ",", $data['columns'] )." from sdb_payments where order_id=".$data['order_id'] );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function get_payment_info( $data )
    {
        $data_info = $this->db->selectrow( "select ".implode( ",", $data['columns'] )." from sdb_payments where payment_id=".$data['payment_id'] );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function insert_payments( $data )
    {
        $aData = array(
            "payment_id" => $data['payment_id'],
            "order_id" => $data['order_id'],
            "member_id" => $data['member_id'],
            "account" => $data['account'],
            "bank" => $data['bank'],
            "pay_account" => $data['pay_account'],
            "currency" => $data['currency'],
            "money" => $data['money'],
            "paycost" => $data['paycost'],
            "cur_money" => $data['cur_money'],
            "pay_type" => $data['pay_type'],
            "payment" => $data['payment'],
            "paymethod" => $data['paymethod'],
            "op_id" => $data['op_id'],
            "ip" => $data['ip'],
            "t_begin" => $data['t_begin'],
            "t_end" => $data['t_end'],
            "status" => $data['status'],
            "memo" => $data['memo'],
            "disabled" => $data['disabled'],
            "trade_no" => $data['trade_no'],
            "parent_payment_id" => $data['parent_payment_id']
        );
        $rs = $this->db->query( "select * from sdb_payments where 0=1" );
        $sql = $this->db->getInsertSQL( $rs, $aData );
        if ( !$this->db->exec( $sql ) )
        {
            $this->api_response( "fail", "sql exec error", $sql );
        }
        else
        {
            $this->api_response( "true", FALSE, NULL );
        }
    }

    public function online_pay_center( $data )
    {
        $order_id = $data['order_id'];
        $pay_id = $data['pay_id'];
        $currency = $data['currency'];
        $obj_order = $this->load_api_instance( "set_dead_order", "1.0" );
        $obj_order->verify_order_valid( $order_id, $order, "*" );
        $dealer_id = $order['dealer_id'];
        $obj_order->checkOrderStatus( "pay", $order );
        $obj_order->verify_order_item_valid( $order_id, $local_order_item_list );
        $obj_member = $this->load_api_instance( "verify_member_valid", "1.0" );
        $obj_member->verify_member_valid( $dealer_id, $member );
        if ( $pay_id != -1 )
        {
            $obj_payment_cfg = $this->load_api_instance( "search_payment_cfg_list", "1.0" );
            $obj_payment_cfg->verify_paymentcfg_not_advance( $pay_id, $local_payment_cfg );
            $this->local_payment_cfg = $local_payment_cfg;
            $this->type = $local_payment_cfg['pay_type'];
        }
        else
        {
            $this->type = "offline";
        }
        if ( $this->type == "offline" )
        {
            $act_url = "index.php".$this->system->mkUrl( "passport", "payCenterOffline" );
            $html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n                \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n                <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">\n                <head>\n</header><body><div>Redirecting...</div>";
            $html .= "<form id=\"payment\" action=\"".$act_url."\" method=\"post\">";
            $html .= "\n                </form>\n                <script language=\"javascript\">\n                document.getElementById('payment').submit();\n                </script>\n                </html>";
            echo $html;
            exit( );
        }
        $last_cost_payment = empty( $order['cost_payment'] ) ? 0 : $order['cost_payment'];
        $money = $order['total_amount'] - $order['payed'];
        $cost_payment = $local_payment_cfg['fee'] * $money;
        $money += $cost_payment;
        $order['payment'] = $pay_id;
        $order_payment = array(
            "order_id" => $data['order_id'],
            "money" => $money,
            "paycost" => $cost_payment
        );
        $order_payment = array_merge( $order_payment, $order );
        $payment_id = $this->create_payment( $pay_id, $order_payment, "online" );
        $objPlatform =& $this->system->loadModel( "system/platform" );
        if ( $objPlatform->tell_platform( "payments", array(
            "pay_id" => $payment_id
        ) ) === FALSE )
        {
            $this->deletePayment( $payment_id );
            $this->api_response( "fail", "data fail", $result, $objPlatform->getErrorInfo( ) );
        }
        $obj_order->changeOrderPayment( $order_id, $pay_id );
        $this->dopay( $order, $member, $payment_id, $money, $currency );
    }

    public function dopay( $order, $member, $payment_id, $money, $currency )
    {
        $payObj = $this->loadMethod( $this->type );
        if ( $payObj->head_charset )
        {
            header( "Content-Type: text/html;charset=".$payObj->head_charset );
        }
        $html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n                \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n                <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">\n                <head>\n</header><body><div>Redirecting...</div>";
        $payObj->_payment = $this->payment;
        $toSubmit = $payObj->toSubmit( $this->getPaymentInfo( $order, $member, $payment_id, $money, $currency ) );
        if ( "utf8" != strtolower( $payObj->charset ) )
        {
            $charset =& $this->system->loadModel( "utility/charset" );
            foreach ( $toSubmit as $k => $v )
            {
                if ( !is_numeric( $v ) )
                {
                    $toSubmit[$k] = $charset->utf2local( $v, "zh" );
                }
            }
        }
        $html .= "<form id=\"payment\" action=\"".$payObj->submitUrl."\" method=\"".$payObj->method."\">";
        foreach ( $toSubmit as $k => $v )
        {
            if ( $k != "ikey" )
            {
                $html .= "<input name=\"".urldecode( $k )."\" type=\"hidden\" value=\"".htmlspecialchars( $v )."\" />";
                if ( $v )
                {
                    $buffer .= urldecode( $k )."=".$v."&";
                }
            }
        }
        if ( strtoupper( $this->type ) == "TENPAYTRAD" )
        {
            $buffer = substr( $buffer, 0, strlen( $buffer ) - 1 );
            $md5_sign = strtoupper( md5( $buffer."&key=".$toSubmit['ikey'] ) );
            $url = $payObj->submitUrl."?".$buffer."&sign=".$md5_sign;
            echo "<script language='javascript'>";
            echo "window.location.href='".$url."';";
            echo "</script>";
        }
        $html .= "\n            </form>\n            <script language=\"javascript\">\n            document.getElementById('payment').submit();\n            </script>\n            </html>";
        echo $html;
    }

    public function create_payment( $pay_id, $data, $pay_type = "deposit" )
    {
        $order_id = $data['order_id'];
        $money = $data['money'];
        $this->payment_id = $this->gen_id( );
        $this->order_id = $order_id;
        $this->member_id = $data['member_id'];
        $this->bank = $pay_type;
        $this->currency = !empty( $data['currency'] ) ? $data['currency'] : "CNY";
        $this->money = $money;
        $this->pay_type = $pay_type;
        $this->payment = $data['payment'];
        $this->t_begin = time( );
        $this->t_end = time( );
        $this->status = $pay_type == "deposit" ? "succ" : "ready";
        if ( $pay_type == "deposit" )
        {
            $this->cur_money = $this->money;
        }
        else if ( $this->currency != "CNY" )
        {
            $currency = $this->getcur( $this->currency );
            $cur_rate = 0 < $currency['cur_rate'] ? $currency['cur_rate'] : 1;
            $this->cur_money = $this->money * $cur_rate;
        }
        else
        {
            $this->cur_money = $this->money;
        }
        if ( $payCfg = $this->db->selectrow( "SELECT pay_type,fee,custom_name FROM sdb_payment_cfg WHERE id=".intval( $pay_id ) ) )
        {
            $this->paycost = $this->money * $payCfg['fee'] / ( 1 + $payCfg['fee'] );
            $this->paycost = $this->formatNumber( $this->paycost );
            $this->paymethod = addslashes( $payCfg['custom_name'] );
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_payments WHERE 0=1" );
        $sSql = $this->db->GetInsertSQL( $aRs, $this );
        if ( $this->db->exec( $sSql ) )
        {
            return $this->payment_id;
        }
        else
        {
            $this->api_response( "fail", "data fail", $result, "支付单生成失败" );
        }
    }

    public function loadMethod( $payPlugin )
    {
        if ( !isset( $this->arr_pay_plugins[$payPlugin] ) )
        {
            require_once( PLUGIN_DIR."/payment/pay.".$payPlugin.".php" );
            $className = "pay_".$payPlugin;
            ( $this->system );
            $method = new $className( );
            $this->arr_pay_plugins[$payPlugin] = $method;
        }
        else
        {
            $method = $this->arr_pay_plugins[$payPlugin];
        }
        return $method;
    }

    public function getPaymentInfo( $order, $member, $payment_id, $money, $currency )
    {
        $payment['M_OrderId'] = $payment_id;
        $payment['M_OrderNO'] = $order['order_id'];
        $payment['M_Amount'] = $money;
        $payment['M_Def_Amount'] = $money;
        $payment['M_Currency'] = $currency;
        $payment['M_Remark'] = $order['memo'];
        $payment['M_Time'] = $order['createtime'];
        $payment['M_Language'] = "zh_CN";
        $payment['R_Name'] = $order['ship_name'];
        $payment['R_Address'] = $order['ship_addr'];
        $payment['R_Postcode'] = $order['ship_zip'];
        $payment['R_Telephone'] = $order['ship_tel'];
        $payment['R_Mobile'] = $order['ship_mobile'];
        $payment['R_Email'] = $order['ship_email'];
        $payment['P_Name'] = $member['name'];
        $payment['P_Address'] = $member['addr'];
        $payment['P_PostCode'] = $member['zip'];
        $payment['P_Telephone'] = $member['tel'];
        $payment['P_Mobile'] = $member['mobile'];
        $payment['P_Email'] = $member['email'];
        $payment['K_key'] = $this->system->getConf( "certificate.token" );
        $configinfo = $this->local_payment_cfg;
        $pma = $this->getPaymentFileName( $configinfo['config'], $configinfo['pay_type'] );
        if ( is_array( $pma ) )
        {
            foreach ( $pma as $key => $val )
            {
                $payment[$key] = $val;
            }
        }
        return $payment;
    }

    public function getPaymentFileName( $config, $ptype )
    {
        if ( !empty( $config ) )
        {
            $pmt = $this->loadMethod( $ptype );
            $field = $pmt->getfields( );
            $config = unserialize( $config );
            if ( is_array( $config ) )
            {
                foreach ( $field as $k => $v )
                {
                    if ( strtoupper( $v['type'] ) == "FILE" || $k == "keyPass" )
                    {
                        $payment[$k] = $config[$k];
                    }
                }
            }
        }
        return $payment;
    }

    public function formatNumber( $number )
    {
        $this->_money_format = array(
            "decimals" => $this->system->getConf( "system.money.operation.decimals" ),
            "dec_point" => $this->system->getConf( "system.money.dec_point" )
        );
        return number_format( trim( $number ), $this->_money_format['decimals'], $this->_money_format['dec_point'], "" );
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
            $payment_id = time( ).str_pad( $i, 4, "0", STR_PAD_LEFT );
            $row = $this->db->selectrow( "select payment_id from sdb_payments where payment_id ='".$payment_id."'" );
        } while ( $row );
        return $payment_id;
    }

    public function getcur( $id, $getDef = FALSE )
    {
        $aCur = $this->db->selectrow( "select * FROM sdb_currency where cur_code=\"".$id."\"" );
        if ( $aCur['cur_code'] || !$getDef )
        {
            return $this->_in_cur = $aCur;
        }
        else
        {
            return $this->_in_cur = $this->getDefault( );
        }
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

    public function verify_payment_valid( $paymentId, &$payment )
    {
        $aTemp = $this->db->selectrow( "SELECT * FROM sdb_payments WHERE payment_id='".$paymentId."'" );
        if ( !$aTemp['payment_id'] )
        {
            $this->api_response( "fail", "data fail", $result, "支付单无效" );
        }
        $payment = $aTemp;
    }

    public function deletePayment( $sId = NULL )
    {
        if ( $sId )
        {
            $sSql = "DELETE FROM sdb_payments WHERE payment_id in (".$sId.")";
            return !$sSql || $this->db->exec( $sSql );
        }
        return FALSE;
    }

}

?>
