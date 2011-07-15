<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['cost_sync'] = array(
    "columns" => array(
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "default" => "0" ),
        "bn" => array( "type" => "varchar(30)", "required" => TRUE, "pkey" => TRUE ),
        "cost" => array( "type" => "money", "required" => TRUE, "default" => "0" ),
        "version_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => "0" ),
        "product_id" => array( "type" => "number", "required" => TRUE, "default" => "0" ),
        "goods_id" => array( "type" => "number", "required" => TRUE, "default" => "0" )
    ),
    "index" => array(
        "spid_gid" => array(
            "columns" => array( 0 => "supplier_id", 1 => "goods_id" )
        )
    )
);
?>
