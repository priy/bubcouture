<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_spec_index'] = array(
    "columns" => array(
        "type_id" => array( "type" => "int(10)", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "spec_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "spec_value_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "spec_value" => array( "type" => "varchar(100)", "default" => "", "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "goods_id" => array( "type" => "object:goods/products", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "product_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "pkey" => TRUE, "editable" => FALSE )
    ),
    "comment" => "商品规格索引表",
    "index" => array(
        "type_specvalue_index" => array(
            "columns" => array( 0 => "type_id", 1 => "spec_value_id", 2 => "goods_id" )
        )
    )
);
?>
