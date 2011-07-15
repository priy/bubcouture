<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['package_product'] = array(
    "columns" => array(
        "product_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "goods_id" => array( "type" => "object:goods/products", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "discount" => array( "type" => "decimal(5,3)", "editable" => FALSE ),
        "pkgnum" => array( "type" => "number", "default" => 1, "required" => TRUE, "editable" => FALSE )
    )
);
?>
