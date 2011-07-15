<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['spec_values'] = array(
    "columns" => array(
        "spec_value_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "spec_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "spec_value" => array( "type" => "varchar(100)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "alias" => array(
            "type" => "varchar(255)",
            "default" => "",
            "label" => __( "规格别名" ),
            "width" => 180
        ),
        "spec_image" => array( "type" => "varchar(255)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "p_order" => array( "type" => "number", "default" => 50, "required" => TRUE, "editable" => FALSE ),
        "supplier_id" => array( "type" => "int unsigned" ),
        "supplier_spec_value_id" => array( "type" => "number" )
    ),
    "comment" => "商店中商品规格值"
);
?>
