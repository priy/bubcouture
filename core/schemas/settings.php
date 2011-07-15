<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['settings'] = array(
    "columns" => array(
        "s_name" => array( "type" => "varchar(16)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "s_data" => array( "type" => "longtext", "editable" => FALSE ),
        "s_time" => array( "type" => "time", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    )
);
?>
