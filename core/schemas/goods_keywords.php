<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_keywords'] = array(
    "columns" => array(
        "goods_id" => array( "type" => "object:goods/products", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "keyword" => array( "type" => "varchar(40)", "default" => "", "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "refer" => array( "type" => "varchar(255)", "default" => "", "required" => FALSE, "editable" => FALSE ),
        "res_type" => array( "type" => "enum('goods','article')", "default" => "goods", "required" => TRUE, "pkey" => TRUE, "editable" => FALSE )
    ),
    "pkeys" => array( "keyword", "goods_id", "res_type" )
);
?>
