<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['job_apilist'] = array(
    "columns" => array(
        "job_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment" ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "api_name" => array( "type" => "varchar(100)", "required" => TRUE ),
        "api_params" => array( "type" => "longtext" ),
        "api_version" => array( "type" => "varchar(10)", "required" => TRUE ),
        "api_action" => array( "type" => "varchar(100)", "required" => TRUE ),
        "page" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "limit" => array( "type" => "number", "required" => TRUE, "default" => 0 )
    )
);
?>
