<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['order_pmt'] = array(
    "columns" => array(
        "pmt_id" => array( "type" => "bigint(20) unsigned", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "order_id" => array( "type" => "object:trading/order", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "pmt_amount" => array( "type" => "money", "editable" => FALSE ),
        "pmt_memo" => array( "type" => "longtext", "editable" => FALSE ),
        "pmt_describe" => array( "type" => "longtext", "editable" => FALSE )
    ),
    "comment" => "订单优惠方案"
);
?>
