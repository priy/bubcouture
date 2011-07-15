<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['sell_logs'] = array(
    "columns" => array(
        "log_id" => array( "type" => "mediumint(8)", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "member_id" => array( "type" => "object:member/member", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "name" => array( "type" => "varchar(50)", "default" => "", "editable" => FALSE ),
        "price" => array( "type" => "money", "default" => "0", "editable" => FALSE ),
        "product_id" => array( "type" => "mediumint(8)", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "goods_id" => array( "type" => "object:goods/products", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "product_name" => array( "type" => "varchar(200)", "default" => "", "editable" => FALSE ),
        "pdt_desc" => array( "type" => "varchar(200)", "default" => "", "editable" => FALSE ),
        "number" => array( "type" => "number", "default" => 0, "editable" => FALSE ),
        "createtime" => array( "type" => "time", "editable" => FALSE )
    ),
    "index" => array(
        "idx_goods_id" => array(
            "columns" => array( 0 => "member_id", 1 => "product_id", 2 => "goods_id" )
        )
    )
);
?>
