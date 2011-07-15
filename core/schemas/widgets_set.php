<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['widgets_set'] = array(
    "columns" => array(
        "widgets_id" => array( "type" => "int", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "base_file" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "base_slot" => array( "type" => "tinyint unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "base_id" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "widgets_type" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "widgets_order" => array( "type" => "tinyint unsigned", "default" => 5, "required" => TRUE, "editable" => FALSE ),
        "title" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "domid" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "border" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "classname" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "tpl" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "params" => array( "type" => "longtext", "editable" => FALSE ),
        "modified" => array( "type" => "time", "editable" => FALSE ),
        "vary" => array( "type" => "varchar(250)", "editable" => FALSE )
    ),
    "index" => array(
        "ind_wgbase" => array(
            "columns" => array( 0 => "base_file", 1 => "base_id", 2 => "widgets_order" )
        ),
        "ind_wginfo" => array(
            "columns" => array( 0 => "base_file", 1 => "base_slot", 2 => "widgets_order" )
        )
    )
);
?>
