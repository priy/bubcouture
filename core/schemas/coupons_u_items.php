<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['coupons_u_items'] = array(
    "columns" => array(
        "order_id" => array(
            "type" => "object:trading/order",
            "required" => TRUE,
            "default" => 0,
            "pkey" => TRUE,
            "comment" => __( "应用订单号" ),
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
        "memc_code" => array(
            "type" => "varchar(255)",
            "comment" => __( "使用的优惠券号码" ),
            "editable" => FALSE
        ),
        "cpns_type" => array(
            "type" => array( 0 => 0, 1 => 1, 2 => 2 ),
            "comment" => __( "优惠券类型0全局 1用户 2外部优惠券" ),
            "editable" => FALSE
        )
    ),
    "comment" => "优惠券使用记录"
);
?>
