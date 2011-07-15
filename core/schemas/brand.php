<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['brand'] = array(
    "columns" => array(
        "brand_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "品牌id" ),
            "width" => 150,
            "comment" => __( "品牌id" ),
            "editable" => FALSE
        ),
        "supplier_id" => array(
            "type" => "int unsigned",
            "comment" => __( "供应商id" ),
            "editable" => FALSE
        ),
        "supplier_brand_id" => array(
            "type" => "number",
            "comment" => __( "供应商品牌id" ),
            "editable" => FALSE
        ),
        "brand_name" => array(
            "type" => "varchar(50)",
            "label" => __( "品牌名称" ),
            "width" => 180,
            "required" => TRUE,
            "comment" => __( "品牌名称" ),
            "editable" => TRUE,
            "searchtype" => "has"
        ),
        "brand_url" => array(
            "type" => "varchar(255)",
            "label" => __( "品牌网址" ),
            "width" => 350,
            "comment" => __( "品牌网址" ),
            "editable" => TRUE,
            "searchtype" => "has"
        ),
        "brand_desc" => array(
            "type" => "longtext",
            "comment" => __( "品牌介绍" ),
            "editable" => FALSE
        ),
        "brand_logo" => array(
            "type" => "varchar(255)",
            "comment" => __( "品牌图片标识" ),
            "editable" => FALSE
        ),
        "brand_keywords" => array(
            "type" => "longtext",
            "label" => __( "品牌别名" ),
            "width" => 150,
            "comment" => __( "品牌别名" ),
            "editable" => FALSE,
            "searchtype" => "has"
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "comment" => __( "失效" ),
            "editable" => FALSE
        ),
        "ordernum" => array(
            "type" => "number",
            "label" => __( "排序" ),
            "width" => 150,
            "comment" => __( "排序" ),
            "editable" => TRUE
        )
    ),
    "comment" => __( "品牌表" ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_ordernum" => array(
            "columns" => array( 0 => "ordernum" )
        ),
        "ind_supplier_brand" => array(
            "columns" => array( 0 => "supplier_id", 1 => "supplier_brand_id" )
        )
    )
);
?>
