<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['member_attr'] = array(
    "columns" => array(
        "attr_id" => array(
            "type" => "int unsigned",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "选项ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "attr_name" => array(
            "type" => "varchar(20)",
            "default" => "",
            "required" => TRUE,
            "label" => __( "选项名称" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "attr_type" => array( "type" => "varchar(20)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "attr_required" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "attr_search" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "label" => __( "搜索" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "attr_option" => array( "type" => "text", "editable" => FALSE ),
        "attr_valtype" => array( "type" => "varchar(20)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "attr_tyname" => array(
            "type" => "varchar(20)",
            "default" => "",
            "required" => TRUE,
            "label" => __( "选项类型" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "attr_group" => array(
            "type" => "varchar(20)",
            "default" => "",
            "required" => TRUE,
            "label" => __( "选项类别" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "attr_show" => array(
            "type" => "bool",
            "default" => "true",
            "required" => TRUE,
            "label" => __( "显示" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "attr_order" => array(
            "type" => "int unsigned",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "排序" ),
            "width" => 110,
            "editable" => FALSE
        )
    )
);
?>
