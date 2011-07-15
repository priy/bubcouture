<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['job_data_sync'] = array(
    "columns" => array(
        "job_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment" ),
        "from_time" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "to_time" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "page" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "limit" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "supplier_pline" => array( "type" => "longtext" ),
        "auto_download" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "default" => "false",
            "required" => TRUE
        ),
        "to_cat_id" => array( "type" => "number", "default" => NULL )
    )
);
?>
