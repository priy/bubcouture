<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['gimages'] = array(
    "columns" => array(
        "gimage_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "goods_id" => array( "type" => "object:goods/products", "editable" => FALSE ),
        "is_remote" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "source" => array( "type" => "varchar(255)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "orderby" => array( "type" => "tinyint unsigned", "default" => 0, "required" => TRUE, "editable" => TRUE ),
        "src_size_width" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "src_size_height" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "small" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "big" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "thumbnail" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "up_time" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "supplier_id" => array( "type" => "int unsigned" ),
        "supplier_gimage_id" => array( "type" => "number" ),
        "sync_time" => array( "type" => "int unsigned", "default" => 0 )
    ),
    "index" => array(
        "ind_up_time" => array(
            "columns" => array( 0 => "up_time" )
        )
    )
);
?>
