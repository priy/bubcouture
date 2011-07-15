<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['template_relation'] = array(
    "columns" => array(
        "template_relation_id" => array(
            "type" => "int",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "网店机器人id" ),
            "hidden" => 1,
            "editable" => FALSE
        ),
        "source_type" => array(
            "type" => "varchar(20)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "条件" ),
            "width" => 300,
            "editable" => FALSE
        ),
        "source_id" => array(
            "type" => "int",
            "required" => TRUE,
            "label" => __( "动作" ),
            "width" => 300,
            "default" => 0,
            "editable" => FALSE
        ),
        "template_name" => array( "type" => "varchar(100)", "required" => TRUE, "default" => "", "width" => 80, "editable" => FALSE ),
        "template_type" => array( "type" => "varchar(100)", "width" => 200, "editable" => FALSE )
    )
);
?>
