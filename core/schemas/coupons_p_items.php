<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['coupons_p_items'] = array(
    "columns" => array(
        "order_id" => array(
            "type" => "object:trading/order",
            "required" => TRUE,
            "default" => 0,
            "pkey" => TRUE,
            "comment" => __( "订单ID" ),
            "editable" => FALSE
        ),
        "cpns_id" => array(
            "type" => "number",
            "required" => TRUE,
            "default" => 0,
            "pkey" => TRUE,
            "comment" => __( "优惠券方案ID" ),
            "editable" => FALSE
        ),
        "cpns_name" => array(
            "type" => "varchar(255)",
            "comment" => __( "优惠券方案名称" ),
            "editable" => FALSE
        ),
        "nums" => array(
            "type" => "number",
            "comment" => __( "得到数量" ),
            "editable" => FALSE
        )
    ),
    "comment" => "优惠券生成记录"
);
?>
