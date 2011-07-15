<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['gift'] = array(
    "columns" => array(
        "gift_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "giftcat_id" => array(
            "type" => "object:trading/giftcat",
            "label" => __( "分类" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "insert_time" => array(
            "type" => "time",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "插入时间" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "update_time" => array(
            "type" => "time",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "更新时间" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "name" => array(
            "type" => "varchar(255)",
            "label" => __( "赠品名称" ),
            "searchtype" => "has",
            "width" => 230,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "thumbnail_pic" => array(
            "type" => "varchar(255)",
            "label" => __( "列表页缩略图" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "small_pic" => array(
            "type" => "varchar(255)",
            "label" => __( "缩略图" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "big_pic" => array(
            "type" => "varchar(255)",
            "label" => __( "详细图" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "image_file" => array( "type" => "longtext", "editable" => FALSE ),
        "intro" => array(
            "type" => "varchar(255)",
            "label" => __( "简介" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "gift_describe" => array(
            "type" => "longtext",
            "label" => __( "详细描述" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "weight" => array(
            "type" => "int",
            "label" => __( "重量" ),
            "width" => 110,
            "required" => TRUE,
            "default" => 0,
            "editable" => FALSE
        ),
        "storage" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "库存" ),
            "width" => 30,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "price" => array(
            "type" => "varchar(255)",
            "label" => __( "价格" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "orderlist" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "排序" ),
            "width" => 30,
            "editable" => TRUE
        ),
        "shop_iffb" => array(
            "type" => "intbool",
            "default" => "1",
            "required" => TRUE,
            "label" => __( "发布" ),
            "width" => 30,
            "editable" => FALSE
        ),
        "limit_num" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "限购数量" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "limit_start_time" => array(
            "type" => "time",
            "label" => __( "开始时间" ),
            "width" => 75,
            "inputType" => "date",
            "required" => TRUE,
            "default" => 0,
            "editable" => FALSE
        ),
        "limit_end_time" => array(
            "type" => "time",
            "label" => __( "结束时间" ),
            "width" => 75,
            "inputType" => "date",
            "required" => TRUE,
            "default" => 0,
            "editable" => FALSE
        ),
        "limit_level" => array(
            "type" => "varchar(255)",
            "label" => __( "允许兑换等级" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "ifrecommend" => array(
            "type" => "intbool",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "推荐" ),
            "width" => 30,
            "bool" => "number",
            "editable" => TRUE
        ),
        "point" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "兑换所需积分" ),
            "width" => 30,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "freez" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "冻结库存" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    ),
    "comment" => "赠品关系表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
