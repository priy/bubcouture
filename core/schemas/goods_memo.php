<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_memo'] = array(
    "columns" => array(
        "goods_id" => array( "type" => "object:goods/products", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "p_key" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "p_value" => array( "type" => "longtext", "editable" => FALSE )
    ),
    "comment" => "商品扩展信息"
);
?>
