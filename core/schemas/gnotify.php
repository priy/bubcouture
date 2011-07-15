<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['gnotify'] = array(
    "columns" => array(
        "gnotify_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "comment" => __( "会员id" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "goods_id" => array(
            "type" => "object:goods/products",
            "label" => __( "缺货商品名称" ),
            "width" => 270,
            "editable" => FALSE
        ),
        "member_id" => array(
            "type" => "object:member/member",
            "label" => __( "会员用户名" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "product_id" => array(
            "type" => "object:goods/products",
            "label" => __( "缺货状态" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "email" => array( "type" => "varchar(200)", "label" => "Email", "width" => 150, "editable" => FALSE ),
        "status" => array(
            "type" => array(
                "ready" => __( "准备发送" ),
                "send" => __( "已发送" ),
                "progress" => __( "发送中" )
            ),
            "default" => "ready",
            "required" => TRUE,
            "label" => __( "通知状态" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "send_time" => array(
            "type" => "time",
            "label" => __( "通知时间" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "creat_time" => array(
            "type" => "time",
            "label" => __( "登记时间" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "remark" => array( "type" => "longtext", "editable" => FALSE )
    ),
    "comment" => "商品缺货通知表",
    "index" => array(
        "ind_goods" => array(
            "columns" => array( 0 => "goods_id", 1 => "product_id", 2 => "member_id" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
