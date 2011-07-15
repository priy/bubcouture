<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['print_tmpl'] = array(
    "columns" => array(
        "prt_tmpl_id" => array(
            "type" => "int unsigned",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "prt_tmpl_title" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "单据名称" ),
            "width" => 390,
            "unique" => TRUE,
            "editable" => TRUE
        ),
        "shortcut" => array(
            "type" => "bool",
            "default" => "false",
            "label" => __( "是否启用" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "prt_tmpl_width" => array( "type" => "tinyint unsigned", "default" => 100, "required" => TRUE, "editable" => FALSE ),
        "prt_tmpl_height" => array( "type" => "tinyint unsigned", "default" => 100, "required" => TRUE, "editable" => FALSE ),
        "prt_tmpl_data" => array( "type" => "longtext", "editable" => FALSE )
    )
);
?>
