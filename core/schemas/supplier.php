<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['supplier'] = array(
    "columns" => array(
        "sp_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "supplier_brief_name" => array( "type" => "varchar(30)" ),
        "status" => array( "type" => "tinyint(4)", "required" => TRUE, "default" => "1" ),
        "supplier_pline" => array( "type" => "longtext" ),
        "sync_time" => array( "type" => "int unsigned", "required" => TRUE, "default" => "0" ),
        "domain" => array( "type" => "varchar(255)", "required" => TRUE ),
        "has_new" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "required" => TRUE,
            "default" => "true"
        ),
        "has_cost_new" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "required" => TRUE,
            "default" => "false"
        ),
        "sync_time_for_plat" => array( "type" => "int unsigned", "required" => TRUE, "default" => "0" )
    ),
    "index" => array(
        "supplier_id" => array(
            "columns" => array( 0 => "supplier_id" )
        )
    )
);
?>
