<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['return_product'] = array(
    "columns" => array(
        "order_id" => array(
            "type" => "object:trading/order",
            "default" => 0,
            "required" => TRUE,
            "searchable" => TRUE,
            "label" => __( "订单号" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "member_id" => array(
            "type" => "object:member/member",
            "default" => 0,
            "required" => TRUE,
            "searchable" => TRUE,
            "label" => __( "申请人" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "return_id" => array( "type" => "bigint unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "label" => "ID", "width" => 150, "editable" => FALSE ),
        "title" => array(
            "type" => "varchar(200)",
            "default" => "",
            "required" => TRUE,
            "label" => __( "售后服务标题" ),
            "width" => 310,
            "fuzzySearch" => 1,
            "editable" => FALSE
        ),
        "content" => array( "type" => "longtext", "editable" => FALSE ),
        "status" => array(
            "type" => array(
                1 => __( "申请中" ),
                2 => __( "审核中" ),
                3 => __( "接受申请" ),
                4 => __( "完成" ),
                5 => __( "拒绝" )
            ),
            "default" => 1,
            "required" => TRUE,
            "label" => __( "处理状态" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "image_file" => array( "type" => "varchar(255)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "product_data" => array( "type" => "longtext", "editable" => FALSE ),
        "comment" => array( "type" => "longtext", "editable" => FALSE ),
        "add_time" => array(
            "type" => "time",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "售后处理时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE )
    ),
    "comment" => "退货记录表"
);
?>
