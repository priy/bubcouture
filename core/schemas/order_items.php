<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['order_items'] = array(
    "columns" => array(
        "item_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "order_id" => array( "type" => "object:trading/order", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "product_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "dly_status" => array(
            "type" => array(
                "storage" => __( "库存" ),
                "shipping" => __( "发送中" ),
                "return" => __( "退货中" ),
                "customer" => __( "客户" ),
                "returned" => __( "已退回" )
            ),
            "default" => "storage",
            "required" => TRUE,
            "editable" => FALSE
        ),
        "type_id" => array( "type" => "int(10)", "editable" => FALSE ),
        "bn" => array( "type" => "varchar(40)", "editable" => FALSE ),
        "name" => array( "type" => "varchar(200)", "editable" => FALSE ),
        "cost" => array( "type" => "money", "editable" => FALSE ),
        "price" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "amount" => array( "type" => "money", "editable" => FALSE ),
        "score" => array( "type" => "number", "editable" => FALSE ),
        "nums" => array( "type" => "number", "default" => 1, "required" => TRUE, "editable" => FALSE ),
        "minfo" => array( "type" => "longtext", "editable" => FALSE ),
        "sendnum" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "addon" => array( "type" => "longtext", "editable" => FALSE ),
        "is_type" => array(
            "type" => array(
                "goods" => __( "商品" ),
                "pkg" => __( "捆绑商品" )
            ),
            "default" => "goods",
            "required" => TRUE,
            "editable" => FALSE
        ),
        "point" => array( "type" => "mediumint", "editable" => FALSE ),
        "supplier_id" => array( "type" => "int unsigned" )
    )
);
?>
