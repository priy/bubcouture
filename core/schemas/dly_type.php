<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['dly_type'] = array(
    "columns" => array(
        "dt_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "配送ID" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "dt_name" => array(
            "type" => "varchar(50)",
            "label" => __( "配送方式" ),
            "width" => 180,
            "editable" => TRUE
        ),
        "dt_config" => array( "type" => "longtext", "editable" => FALSE ),
        "dt_expressions" => array( "type" => "longtext", "editable" => FALSE ),
        "detail" => array( "type" => "longtext", "editable" => FALSE ),
        "price" => array( "type" => "longtext", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "type" => array( "type" => "intbool", "default" => 1, "required" => TRUE, "editable" => FALSE ),
        "gateway" => array( "type" => "number", "default" => 0, "editable" => FALSE ),
        "protect" => array(
            "type" => "intbool",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "物流保价" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "protect_rate" => array( "type" => "float(6,3)", "editable" => FALSE ),
        "ordernum" => array(
            "type" => "smallint(4)",
            "default" => 0,
            "label" => __( "排序" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "has_cod" => array(
            "type" => "intbool",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "货到付款" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "minprice" => array( "type" => "float(10,2)", "default" => "0.00", "required" => TRUE, "editable" => FALSE ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "corp_id" => array( "type" => "time", "editable" => FALSE ),
        "dt_status" => array(
            "type" => array( 0 => "关闭", 1 => "启用" ),
            "label" => __( "状态" ),
            "width" => 75,
            "editable" => FALSE,
            "default" => "1"
        )
    ),
    "comment" => "商店配送方式表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
