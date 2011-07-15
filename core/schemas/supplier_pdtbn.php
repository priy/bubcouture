<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['supplier_pdtbn'] = array(
    "columns" => array(
        "sp_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment" ),
        "local_bn" => array( "type" => "varchar(200)", "required" => TRUE, "pkey" => TRUE ),
        "source_bn" => array( "type" => "varchar(200)", "required" => TRUE ),
        "default" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "required" => TRUE,
            "default" => "true"
        ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 )
    ),
    "index" => array(
        "sp_srcbn" => array(
            "columns" => array( 0 => "source_bn", 1 => "supplier_id" )
        ),
        "sp_source_bn" => array(
            "columns" => array( 0 => "source_bn" )
        ),
        "sp_local_bn" => array(
            "columns" => array( 0 => "local_bn" )
        )
    )
);
?>
