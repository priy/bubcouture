<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['gift_items'] = array(
    "columns" => array(
        "order_id" => array( "type" => "object:trading/order", "required" => TRUE, "default" => "0", "pkey" => TRUE, "editable" => FALSE ),
        "gift_id" => array( "type" => "number", "required" => TRUE, "default" => "0", "pkey" => TRUE, "editable" => FALSE ),
        "name" => array( "type" => "varchar(200)", "editable" => FALSE ),
        "point" => array( "type" => "int(8)", "editable" => FALSE ),
        "nums" => array( "type" => "number", "editable" => FALSE ),
        "amount" => array( "type" => "int unsigned", "editable" => FALSE ),
        "sendnum" => array( "type" => "number", "default" => 0, "editable" => FALSE ),
        "getmethod" => array(
            "type" => array(
                "present" => __( "赠送" ),
                "exchange" => __( "兑换" )
            ),
            "default" => "present",
            "required" => TRUE,
            "editable" => FALSE
        )
    ),
    "comment" => "赠品订单明细表"
);
?>
