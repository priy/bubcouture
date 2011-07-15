<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['magicvars'] = array(
    "columns" => array(
        "var_name" => array(
            "type" => "varchar(20)",
            "required" => TRUE,
            "pkey" => TRUE,
            "label" => __( "变量名" ),
            "class" => "span-3",
            "editable" => FALSE
        ),
        "var_title" => array(
            "type" => "varchar(100)",
            "label" => __( "名称" ),
            "class" => "span-3",
            "editable" => FALSE
        ),
        "var_remark" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "label" => __( "备注" ),
            "class" => "span-3",
            "editable" => FALSE
        ),
        "var_value" => array(
            "type" => "text",
            "hidden" => TRUE,
            "label" => __( "变量值" ),
            "class" => "span-4",
            "editable" => FALSE
        ),
        "var_type" => array(
            "type" => array(
                "system" => __( "系统" ),
                "custom" => __( "自定义" )
            ),
            "default" => "custom",
            "required" => TRUE,
            "label" => __( "变量类型" ),
            "class" => "span-2",
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE )
    )
);
?>
