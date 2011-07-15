<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['job_goods_download'] = array(
    "columns" => array(
        "job_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment" ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "supplier_goods_id" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "supplier_goods_count" => array( "type" => "int unsigned", "required" => TRUE, "default" => "1" ),
        "command_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "failed" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "default" => "false",
            "required" => TRUE
        ),
        "to_cat_id" => array( "type" => "number" )
    )
);
?>
