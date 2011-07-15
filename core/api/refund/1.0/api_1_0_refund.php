<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_1_0_refund extends shop_api_object
{

    public $select_limited = 100;

    public function getColumns( )
    {
        $columns = array(
            "refund_id" => array( "type" => "int" ),
            "order_id" => array( "type" => "int" ),
            "member_id" => array( "type" => "int" ),
            "account" => array( "type" => "string" ),
            "bank" => array( "type" => "string" ),
            "pay_account" => array( "type" => "string" ),
            "currency" => array( "type" => "string" ),
            "money" => array( "type" => "decimal" ),
            "pay_type" => array( "type" => "string" ),
            "payment" => array( "type" => "string" ),
            "paymethod" => array( "type" => "string" ),
            "ip" => array( "type" => "string" ),
            "t_ready" => array( "type" => "int" ),
            "t_sent" => array( "type" => "int" ),
            "t_received" => array( "type" => "int" ),
            "status" => array( "type" => "string" ),
            "memo" => array( "type" => "string" ),
            "title" => array( "type" => "string" ),
            "send_op_id" => array( "type" => "int" ),
            "disabled" => array( "type" => "string" )
        );
        return $columns;
    }

    public function insert_refunds( $data )
    {
        $aData = array(
            "refund_id" => $data['refund_id'],
            "order_id" => $data['order_id'],
            "member_id" => $data['member_id'],
            "account" => $data['account'],
            "bank" => $data['bank'],
            "pay_account" => $data['pay_account'],
            "currency" => $data['currency'],
            "money" => $data['money'],
            "pay_type" => $data['pay_type'],
            "payment" => $data['payment'],
            "paymethod" => $data['paymethod'],
            "ip" => $data['ip'],
            "t_ready" => $data['t_ready'],
            "t_sent" => $data['t_sent'],
            "t_received" => $data['t_received'],
            "status" => $data['status'],
            "memo" => $data['memo'],
            "title" => $data['title'],
            "send_op_id" => $data['send_op_id'],
            "disabled" => $data['disabled']
        );
        $rs = $this->db->query( "select * from sdb_refunds where 0=1" );
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

}

?>
