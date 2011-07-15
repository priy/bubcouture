<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_payment_cfg extends shop_api_object
{

    public $max_number = 100;
    public $app_error = array
    (
        "payment is not predeposits" => array
        (
            "no" => "b_payment_cfg_001",
            "debug" => "",
            "level" => "warning",
            "info" => "订单支付方式不是预存款",
            "desc" => ""
        ),
        "valid payment" => array
        (
            "no" => "b_payment_cfg_002",
            "debug" => "",
            "level" => "warning",
            "info" => "订单支付方式无效",
            "desc" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array(
            "id" => array( "type" => "int" ),
            "custom_name" => array( "type" => "string" ),
            "pay_type" => array( "type" => "string" ),
            "config" => array( "type" => "string" ),
            "fee" => array( "type" => "decimal" ),
            "des" => array( "type" => "string" ),
            "order_num" => array( "type" => "int" ),
            "disabled" => array( "type" => "string" ),
            "orderlist" => array( "type" => "int" )
        );
        return $columns;
    }

    public function get_payment_cfg_list( )
    {
        $result['data_info'] = $this->db->select( "select * from sdb_payment_cfg" );
        $this->api_response( "true", FALSE, $result );
    }

    public function search_payment_cfg_list( $data )
    {
        $data['disabled'] = "false";
        $data['orderby'] = "id";
        $data['last_modify_st_time'] = 0;
        $data['last_modify_en_time'] = time( );
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_payment_cfg where ".implode( " and ", $where ) );
        $where = $this->_filter( $data );
        $data_info = $this->db->select( "select ".implode( ",", $data['columns'] )." from sdb_payment_cfg".$where );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function verify_payment_cfg_valid( $payment_id, &$local_payment_cfg )
    {
        $payment_cfg = $this->db->selectrow( "select * from sdb_payment_cfg where id=".$payment_id );
        if ( !$payment_cfg )
        {
            $this->api_response( "fail", "data fail", $result, "订单支付方式无效" );
        }
        $local_payment_cfg = $payment_cfg;
    }

    public function verify_paymentcfg_advance_valid( $payment_id, &$local_payment_cfg )
    {
        $this->verify_payment_cfg_valid( $payment_id, $tmp_payment_cfg );
        if ( $tmp_payment_cfg['pay_type'] != "deposit" )
        {
            $this->api_response( "fail", "data fail", $result, "订单支付方式不是预存款" );
        }
        $local_payment_cfg = $tmp_payment_cfg;
    }

    public function verify_paymentcfg_not_advance( $payment_id, &$local_payment_cfg )
    {
        $this->verify_payment_cfg_valid( $payment_id, $tmp_payment_cfg );
        if ( $tmp_payment_cfg['pay_type'] == "deposit" )
        {
            $this->api_response( "fail", "data fail", $result, "当前在线支付方式不能是预存款" );
        }
        $local_payment_cfg = $tmp_payment_cfg;
    }

    public function before_filter( $filter )
    {
        $where = array( 1 );
        if ( isset( $filter['last_modify_st_time'] ) )
        {
            $where[] = "last_modify >=".intval( $filter['last_modify_st_time'] );
        }
        if ( isset( $filter['last_modify_en_time'] ) )
        {
            $where[] = "last_modify <".intval( $filter['last_modify_en_time'] );
        }
        if ( isset( $filter['disabled'] ) )
        {
            $where[] = "disabled=\"".$filter['disabled']."\"";
        }
        return $where;
    }

    public function _filter( $filter )
    {
        $where = $this->before_filter( $filter );
        return parent::_filter( $where, $filter );
    }

}

?>
