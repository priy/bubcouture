<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['products'] = array(
    "columns" => array(
        "product_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "货品ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "goods_id" => array(
            "type" => "object:goods/products",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "商品ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "barcode" => array(
            "type" => "varchar(128)",
            "label" => __( "条码" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "title" => array( "type" => "varchar(255)", "label" => "", "width" => 110, "editable" => FALSE ),
        "bn" => array(
            "type" => "varchar(30)",
            "label" => __( "货号" ),
            "width" => 75,
            "fuzzySearch" => 1,
            "filtertype" => "normal",
            "filterdefalut" => TRUE,
            "editable" => FALSE
        ),
        "price" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "销售价格" ),
            "width" => 75,
            "filtertype" => "number",
            "filterdefalut" => TRUE,
            "editable" => FALSE
        ),
        "cost" => array(
            "type" => "money",
            "default" => "0",
            "label" => __( "成本价" ),
            "width" => 110,
            "filtertype" => "number",
            "editable" => FALSE
        ),
        "mktprice" => array(
            "type" => "money",
            "label" => __( "市场价" ),
            "width" => 75,
            "vtype" => "positive",
            "filtertype" => "number",
            "editable" => FALSE
        ),
        "name" => array(
            "type" => "varchar(200)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "货品名称" ),
            "width" => 180,
            "fuzzySearch" => 1,
            "filtertype" => "custom",
            "filtercustom" => array( "has" => "包含", "tequal" => "等于", "head" => "开头等于", "foot" => "结尾等于" ),
            "filterdefalut" => TRUE,
            "editable" => FALSE
        ),
        "weight" => array(
            "type" => "decimal(20,3)",
            "label" => __( "单位重量" ),
            "width" => 110,
            "filtertype" => "number",
            "filterdefalut" => TRUE,
            "editable" => FALSE
        ),
        "unit" => array(
            "type" => "varchar(20)",
            "label" => __( "单位" ),
            "width" => 110,
            "filtertype" => "normal",
            "editable" => FALSE
        ),
        "store" => array(
            "type" => "number",
            "label" => __( "库存" ),
            "width" => 30,
            "filtertype" => "number",
            "filterdefalut" => TRUE,
            "editable" => FALSE
        ),
        "store_place" => array(
            "type" => "varchar(255)",
            "label" => __( "货位" ),
            "width" => 255,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "freez" => array(
            "type" => "number",
            "label" => __( "冻结库存" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "pdt_desc" => array(
            "type" => "longtext",
            "label" => __( "物品描述" ),
            "width" => 110,
            "filtertype" => "normal",
            "editable" => FALSE
        ),
        "props" => array(
            "type" => "longtext",
            "label" => __( "规格值,序列化" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "uptime" => array(
            "type" => "time",
            "label" => __( "录入时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "last_modify" => array(
            "type" => "time",
            "label" => __( "最后修改时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "marketable" => array(
            "type" => "bool",
            "default" => "true",
            "required" => TRUE,
            "label" => __( "上架" ),
            "width" => 30,
            "filtertype" => "yes",
            "editable" => FALSE
        ),
        "is_local_stock" => array( "type" => "bool", "default" => "true", "required" => TRUE )
    ),
    "comment" => "货品表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_bn" => array(
            "columns" => array( 0 => "bn" )
        ),
        "ind_goods_id" => array(
            "columns" => array( 0 => "goods_id" )
        )
    )
);
?>
