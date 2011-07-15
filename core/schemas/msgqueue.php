<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['msgqueue'] = array(
    "columns" => array(
        "queue_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "title" => array( "type" => "varchar(250)", "editable" => FALSE ),
        "target" => array( "type" => "varchar(250)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "event_name" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "data" => array( "type" => "longtext", "editable" => FALSE ),
        "tmpl_name" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "level" => array( "type" => "tinyint unsigned", "default" => 5, "required" => TRUE, "editable" => FALSE ),
        "sender" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "sender_order" => array( "type" => "tinyint unsigned", "default" => 5, "required" => TRUE, "editable" => FALSE )
    ),
    "index" => array(
        "ind_level" => array(
            "columns" => array( 0 => "level" )
        )
    )
);
?>
