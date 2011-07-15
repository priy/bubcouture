<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['image_sync'] = array(
    "columns" => array(
        "img_sync_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment" ),
        "type" => array(
            "type" => array(
                "gimage" => __( "" ),
                "spec_value" => __( "" ),
                "udfimg" => __( "" ),
                "brand_logo" => __( "" )
            ),
            "required" => TRUE,
            "default" => "gimage"
        ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "supplier_object_id" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "add_time" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "command_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "failed" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "required" => TRUE,
            "default" => "false"
        )
    )
);
?>
