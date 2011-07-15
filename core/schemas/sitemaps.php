<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['sitemaps'] = array(
    "columns" => array(
        "node_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "p_node_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "node_type" => array( "type" => "varchar(30)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "depth" => array( "type" => "tinyint unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "path" => array( "type" => "varchar(200)", "editable" => FALSE ),
        "title" => array( "type" => "varchar(100)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "action" => array( "type" => "varchar(255)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "manual" => array( "type" => "intbool", "default" => 1, "required" => TRUE, "editable" => FALSE ),
        "item_id" => array( "type" => "number", "editable" => FALSE ),
        "p_order" => array( "type" => "number", "editable" => FALSE ),
        "hidden" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "child_count" => array( "type" => "mediumint(4)", "editable" => FALSE )
    ),
    "comment" => "站点结构",
    "index" => array(
        "ind_hidden" => array(
            "columns" => array( 0 => "hidden" )
        )
    )
);
?>
