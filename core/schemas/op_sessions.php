<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['op_sessions'] = array(
    "columns" => array(
        "sess_id" => array( "type" => "varchar(32)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "op_id" => array( "type" => "mediumint(6) unsigned", "editable" => FALSE ),
        "login_time" => array( "type" => "time", "editable" => FALSE ),
        "last_time" => array( "type" => "time", "editable" => FALSE ),
        "api_id" => array( "type" => "number", "editable" => FALSE ),
        "sess_data" => array( "type" => "longtext", "editable" => FALSE ),
        "status" => array( "type" => "tinyint(1)", "default" => 0, "editable" => FALSE ),
        "ip" => array( "type" => "varchar(17)", "editable" => FALSE )
    )
);
?>
