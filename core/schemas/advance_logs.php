<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['advance_logs'] = array(
    "columns" => array(
        "log_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "日志id" ),
            "width" => 110,
            "comment" => __( "日志id" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "member_id" => array(
            "type" => "object:member/member",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "用户名" ),
            "width" => 110,
            "comment" => __( "用户id" ),
            "editable" => FALSE
        ),
        "money" => array(
            "type" => "money",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "出入金额" ),
            "width" => 110,
            "comment" => __( "出入金额" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "message" => array(
            "type" => "varchar(255)",
            "label" => __( "管理备注" ),
            "width" => 110,
            "comment" => __( "管理备注" ),
            "editable" => TRUE
        ),
        "mtime" => array(
            "type" => "time",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "交易时间" ),
            "width" => 75,
            "comment" => __( "交易时间" ),
            "editable" => FALSE
        ),
        "payment_id" => array(
            "type" => "varchar(20)",
            "label" => __( "支付单号" ),
            "width" => 110,
            "comment" => __( "支付单号" ),
            "editable" => FALSE
        ),
        "order_id" => array(
            "type" => "object:trading/order",
            "label" => __( "订单号" ),
            "width" => 110,
            "comment" => __( "订单号" ),
            "editable" => FALSE
        ),
        "paymethod" => array(
            "type" => "varchar(100)",
            "label" => __( "支付方式" ),
            "width" => 110,
            "comment" => __( "支付方式" ),
            "editable" => FALSE
        ),
        "memo" => array(
            "type" => "varchar(100)",
            "label" => __( "业务摘要" ),
            "width" => 110,
            "comment" => __( "业务摘要" ),
            "editable" => FALSE
        ),
        "import_money" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "存入金额" ),
            "width" => 110,
            "comment" => __( "存入金额" ),
            "editable" => FALSE
        ),
        "explode_money" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "支出金额" ),
            "width" => 110,
            "comment" => __( "支出金额" ),
            "editable" => FALSE
        ),
        "member_advance" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "当前余额" ),
            "width" => 110,
            "comment" => __( "当前余额" ),
            "editable" => FALSE
        ),
        "shop_advance" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "商店余额" ),
            "width" => 110,
            "comment" => __( "商店余额" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "comment" => __( "失效" ),
            "editable" => FALSE
        )
    ),
    "comment" => __( "预存款历史记录" ),
    "index" => array(
        "ind_mtime" => array(
            "columns" => array( 0 => "mtime" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
