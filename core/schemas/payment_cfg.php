<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['payment_cfg'] = array(
    "columns" => array(
        "id" => array(
            "type" => "mediumint",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "支付方式ID" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "custom_name" => array(
            "type" => "varchar(100)",
            "label" => __( "支付方式名称" ),
            "width" => 230,
            "editable" => TRUE
        ),
        "pay_type" => array( "type" => "varchar(30)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "config" => array( "type" => "longtext", "editable" => FALSE ),
        "fee" => array( "type" => "decimal(9,5)", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "des" => array( "type" => "longtext", "editable" => FALSE ),
        "order_num" => array( "type" => "smallint(3) unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "orderlist" => array(
            "type" => "number",
            "label" => __( "排序" ),
            "width" => 30,
            "editable" => TRUE
        )
    ),
    "comment" => "支付插件实例表"
);
?>
