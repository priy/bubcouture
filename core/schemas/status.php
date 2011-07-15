<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['status'] = array(
    "columns" => array(
        "status_key" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "date_affect" => array( "type" => "date", "default" => "0000-00-00", "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "status_value" => array( "type" => "varchar(100)", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "last_update" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE )
    )
);
?>
