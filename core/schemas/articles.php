<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['articles'] = array(
    "columns" => array(
        "article_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "comment" => __( "文章ID" ),
            "editable" => FALSE
        ),
        "node_id" => array(
            "type" => "table:sitemaps:node_id",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "所属栏目" ),
            "width" => 110,
            "comment" => __( "所属栏目" ),
            "editable" => FALSE
        ),
        "title" => array(
            "type" => "varchar(200)",
            "label" => __( "文章标题" ),
            "width" => 310,
            "searchtype" => "has",
            "comment" => __( "文章标题" ),
            "editable" => TRUE
        ),
        "content" => array(
            "type" => "longtext",
            "label" => __( "文章内容" ),
            "comment" => __( "文章内容" ),
            "hidden" => TRUE,
            "searchtype" => "has",
            "editable" => FALSE
        ),
        "uptime" => array(
            "type" => "time",
            "label" => __( "更新时间" ),
            "width" => 110,
            "comment" => __( "更新时间" ),
            "editable" => FALSE
        ),
        "ifpub" => array(
            "type" => "intbool",
            "label" => __( "是否发布" ),
            "width" => 110,
            "comment" => __( "是否发布" ),
            "editable" => TRUE
        ),
        "align" => array(
            "type" => "varchar(12)",
            "comment" => __( "显示方式" ),
            "editable" => FALSE
        ),
        "filetype" => array(
            "type" => "varchar(15)",
            "comment" => __( "上传文件类型" ),
            "editable" => FALSE
        ),
        "filename" => array(
            "type" => "varchar(80)",
            "comment" => __( "文件名" ),
            "editable" => FALSE
        ),
        "orderlist" => array(
            "type" => "mediumint(6)",
            "comment" => __( "排序" ),
            "editable" => TRUE
        ),
        "pubtime" => array(
            "type" => "time",
            "label" => __( "创建时间" ),
            "editable" => FALSE
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "comment" => __( "失效" ),
            "editable" => FALSE
        ),
        "goodsinfo" => array( "type" => "text", "editable" => FALSE )
    ),
    "comment" => __( "文章表" ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_orderlist" => array(
            "columns" => array( 0 => "orderlist" )
        ),
        "ind_ifpub" => array(
            "columns" => array( 0 => "ifpub" )
        ),
        "ind_uptime" => array(
            "columns" => array( 0 => "uptime" )
        )
    )
);
?>
