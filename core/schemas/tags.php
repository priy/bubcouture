<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['tags'] = array(
    "columns" => array(
        "tag_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "tag_name" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "is_system" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "tag_type" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "rel_count" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE )
    ),
    "index" => array(
        "ind_type" => array(
            "columns" => array( 0 => "tag_type" )
        ),
        "ind_name" => array(
            "columns" => array( 0 => "tag_name" )
        )
    )
);
?>
