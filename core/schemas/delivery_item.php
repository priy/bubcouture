<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['delivery_item'] = array(
    "columns" => array(
        "item_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "delivery_id" => array( "type" => "bigint unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "item_type" => array(
            "type" => array(
                "goods" => __( "商品" ),
                "gift" => __( "赠品" ),
                "pkg" => __( "捆绑商品" )
            ),
            "default" => "goods",
            "required" => TRUE,
            "editable" => FALSE
        ),
        "product_id" => array( "type" => "bigint unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "product_bn" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "product_name" => array( "type" => "varchar(200)", "editable" => FALSE ),
        "number" => array( "type" => "number", "required" => TRUE, "default" => 0, "editable" => FALSE )
    ),
    "comment" => "发货/退货单明细表"
);
?>
