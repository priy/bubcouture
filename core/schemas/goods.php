<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( $this->system->getConf( "certificate.distribute" ) )
{
    $hidden = FALSE;
}
else
{
    $hidden = TRUE;
}
$db['goods'] = array(
    "columns" => array(
        "goods_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "cat_id" => array(
            "type" => "object:goods/productCat",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "分类" ),
            "width" => 75,
            "editable" => TRUE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "type_id" => array(
            "type" => "object:goods/gtype",
            "label" => __( "类型" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "goods_type" => array(
            "type" => array(
                "normal" => __( "普通商品" ),
                "bind" => __( "捆绑商品" )
            ),
            "default" => "normal",
            "required" => TRUE,
            "label" => __( "销售类型" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "brand_id" => array(
            "type" => "object:goods/brand",
            "label" => __( "品牌" ),
            "width" => 75,
            "editable" => TRUE,
            "hidden" => TRUE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "brand" => array(
            "type" => "varchar(100)",
            "label" => __( "品牌" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "supplier_id" => array(
            "label" => __( "供应商" ),
            "width" => 100,
            "type" => "int unsigned",
            "editable" => FALSE,
            "hidden" => $hidden
        ),
        "supplier_goods_id" => array( "type" => "number", "editable" => FALSE, "hidden" => TRUE ),
        "wss_params" => array( "type" => "longtext", "editable" => FALSE ),
        "image_default" => array(
            "type" => "longtext",
            "label" => __( "默认图片" ),
            "width" => 75,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "udfimg" => array(
            "type" => "bool",
            "default" => "false",
            "label" => __( "是否用户自定义图" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "thumbnail_pic" => array(
            "type" => "varchar(255)",
            "label" => __( "缩略图" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "small_pic" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "big_pic" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "image_file" => array(
            "type" => "longtext",
            "label" => __( "图片文件" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "brief" => array(
            "type" => "varchar(255)",
            "label" => __( "商品简介" ),
            "width" => 110,
            "hidden" => FALSE,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "intro" => array(
            "type" => "longtext",
            "label" => __( "详细介绍" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "mktprice" => array(
            "type" => "money",
            "label" => __( "市场价" ),
            "width" => 75,
            "vtype" => "positive",
            "editable" => FALSE,
            "filtertype" => "number"
        ),
        "cost" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "成本价" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "number"
        ),
        "price" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "销售价" ),
            "width" => 75,
            "vtype" => "bbsales",
            "editable" => FALSE,
            "filtertype" => "number",
            "filterdefalut" => TRUE
        ),
        "bn" => array(
            "type" => "varchar(200)",
            "label" => __( "商品编号" ),
            "width" => 110,
            "fuzzySearch" => 1,
            "primary" => TRUE,
            "searchtype" => "head",
            "editable" => TRUE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "name" => array(
            "type" => "varchar(200)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "商品名称" ),
            "width" => 310,
            "fuzzySearch" => 1,
            "primary" => TRUE,
            "locked" => 1,
            "searchtype" => "has",
            "editable" => TRUE,
            "filtertype" => "custom",
            "filterdefalut" => TRUE,
            "filtercustom" => array( "has" => "包含", "tequal" => "等于", "head" => "开头等于", "foot" => "结尾等于" )
        ),
        "marketable" => array(
            "type" => "bool",
            "default" => "true",
            "required" => TRUE,
            "label" => __( "上架" ),
            "width" => 30,
            "editable" => TRUE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "weight" => array(
            "type" => "decimal(20,3)",
            "label" => __( "重量" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "unit" => array(
            "type" => "varchar(20)",
            "label" => __( "单位" ),
            "width" => 30,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "store" => array(
            "type" => "number",
            "label" => __( "库存" ),
            "width" => 30,
            "editable" => FALSE,
            "filtertype" => "number",
            "filterdefalut" => TRUE
        ),
        "store_place" => array(
            "type" => "varchar(255)",
            "label" => __( "货位" ),
            "width" => 255,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "score_setting" => array(
            "type" => array(
                "percent" => __( "百分比" ),
                "number" => __( "实际值" )
            ),
            "default" => "number",
            "editable" => FALSE
        ),
        "score" => array(
            "type" => "number",
            "label" => __( "积分" ),
            "width" => 30,
            "editable" => FALSE
        ),
        "spec" => array(
            "type" => "longtext",
            "label" => __( "规格" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "pdt_desc" => array(
            "type" => "longtext",
            "label" => __( "物品" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "spec_desc" => array(
            "type" => "longtext",
            "label" => __( "物品" ),
            "width" => 110,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "params" => array( "type" => "longtext", "editable" => FALSE ),
        "uptime" => array(
            "type" => "time",
            "label" => __( "上架时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "downtime" => array(
            "type" => "time",
            "label" => __( "下架时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "last_modify" => array(
            "type" => "time",
            "label" => __( "更新时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "notify_num" => array(
            "type" => "number",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "缺货登记" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "rank" => array( "type" => "decimal(5,3)", "default" => "5", "editable" => FALSE ),
        "rank_count" => array( "type" => "int unsigned", "default" => 0, "editable" => FALSE ),
        "comments_count" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "view_w_count" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "view_count" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "buy_count" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "buy_w_count" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "count_stat" => array( "type" => "longtext", "editable" => FALSE ),
        "p_order" => array(
            "type" => "number",
            "default" => 30,
            "required" => TRUE,
            "label" => __( "排序" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "d_order" => array(
            "type" => "number",
            "default" => 30,
            "required" => TRUE,
            "label" => __( "排序" ),
            "width" => 30,
            "editable" => TRUE
        ),
        "p_1" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_2" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_3" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_4" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_5" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_6" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_7" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_8" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_9" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_10" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_11" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_12" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_13" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_14" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_15" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_16" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_17" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_18" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_19" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_20" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_21" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_22" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_23" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_24" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_25" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_26" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_27" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "p_28" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "goods_info_update_status" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "stock_update_status" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "marketable_update_status" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "img_update_status" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    ),
    "comment" => "商品表",
    "index" => array(
        "uni_bn" => array(
            "columns" => array( 0 => "bn" )
        ),
        "ind_p_1" => array(
            "columns" => array( 0 => "p_1" )
        ),
        "ind_p_2" => array(
            "columns" => array( 0 => "p_2" )
        ),
        "ind_p_3" => array(
            "columns" => array( 0 => "p_3" )
        ),
        "ind_p_4" => array(
            "columns" => array( 0 => "p_4" )
        ),
        "ind_p_23" => array(
            "columns" => array( 0 => "p_23" )
        ),
        "ind_p_22" => array(
            "columns" => array( 0 => "p_22" )
        ),
        "ind_p_21" => array(
            "columns" => array( 0 => "p_21" )
        ),
        "ind_frontend" => array(
            "columns" => array( 0 => "disabled", 1 => "goods_type", 2 => "marketable" )
        ),
        "supplier_goods" => array(
            "columns" => array( 0 => "supplier_id", 1 => "supplier_goods_id" )
        )
    )
);
?>
