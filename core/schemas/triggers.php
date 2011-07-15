<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['triggers'] = array(
    "columns" => array(
        "trigger_id" => array(
            "type" => "int",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "网店机器人id" ),
            "hidden" => 1,
            "editable" => FALSE
        ),
        "filter_str" => array(
            "type" => "varchar(255)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "条件" ),
            "width" => 300,
            "editable" => FALSE
        ),
        "action_str" => array(
            "type" => "varchar(255)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "动作" ),
            "width" => 300,
            "editable" => FALSE
        ),
        "trigger_event" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "事件" ),
            "width" => 80,
            "editable" => FALSE
        ),
        "trigger_memo" => array(
            "type" => "varchar(100)",
            "label" => __( "备注" ),
            "width" => 200,
            "editable" => FALSE
        ),
        "trigger_define" => array( "type" => "text", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "trigger_order" => array( "type" => "tinyint", "default" => 5, "required" => TRUE, "editable" => FALSE ),
        "active" => array(
            "type" => array( "true" => "启用", "false" => "停用" ),
            "default" => "false",
            "label" => __( "状态" ),
            "width" => 100,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE )
    )
);
?>
