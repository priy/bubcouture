<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['autosync_task'] = array(
    "columns" => array(
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "default" => "0" ),
        "command_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "default" => "0" ),
        "local_op_id" => array( "type" => "number", "default" => "0" )
    )
);
?>
