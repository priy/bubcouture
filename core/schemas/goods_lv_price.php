<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_lv_price'] = array(
    "columns" => array(
        "product_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "level_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "goods_id" => array( "type" => "object:goods/products", "default" => 0, "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "price" => array( "type" => "money", "editable" => FALSE )
    ),
    "comment" => "商品会员等级价格"
);
?>
