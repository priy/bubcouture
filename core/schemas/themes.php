<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['themes'] = array(
    "columns" => array(
        "theme" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "name" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "stime" => array( "type" => "int unsigned", "editable" => FALSE ),
        "author" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "site" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "version" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "info" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "config" => array( "type" => "longtext", "editable" => FALSE ),
        "update_url" => array( "type" => "varchar(100)", "editable" => FALSE ),
        "template" => array( "type" => "varchar(255)", "editable" => FALSE )
    )
);
?>
