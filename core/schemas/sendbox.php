<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['sendbox'] = array(
    "columns" => array(
        "out_id" => array( "type" => "int", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "tmpl_name" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "sender" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "creattime" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "target" => array( "type" => "longtext", "editable" => FALSE ),
        "sendcount" => array( "type" => "number", "editable" => FALSE ),
        "content" => array( "type" => "varchar(200)", "editable" => FALSE ),
        "subject" => array( "type" => "varchar(100)", "editable" => FALSE )
    ),
    "index" => array(
        "ind_sender" => array(
            "columns" => array( 0 => "sender" )
        )
    )
);
?>
