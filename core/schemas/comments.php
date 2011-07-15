<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['comments'] = array(
    "columns" => array(
        "comment_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "序号" ),
            "editable" => FALSE
        ),
        "for_comment_id" => array(
            "type" => "number",
            "label" => __( "对comments的回复" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "goods_id" => array(
            "type" => "object:goods/products",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "咨询商品" ),
            "editable" => FALSE
        ),
        "object_type" => array(
            "type" => array( "ask" => "ask", "discuss" => "discuss", "buy" => "buy" ),
            "default" => "ask",
            "required" => TRUE,
            "label" => __( "评论类型" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "author_id" => array(
            "type" => "number",
            "label" => __( "会员(后台管理员)id" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "author" => array(
            "type" => "varchar(100)",
            "label" => __( "咨询人" ),
            "editable" => FALSE,
            "searchtype" => "tequal",
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "levelname" => array(
            "type" => "varchar(50)",
            "label" => __( "会员等级" ),
            "editable" => FALSE,
            "filtertype" => "bool"
        ),
        "contact" => array(
            "type" => "varchar(255)",
            "label" => __( "联系方式" ),
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE,
            "escape_html" => TRUE
        ),
        "mem_read_status" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "label" => __( "会员阅读标识" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "adm_read_status" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "label" => __( "已阅" ),
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "time" => array(
            "type" => "time",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "咨询时间" ),
            "editable" => FALSE,
            "filtertype" => "time",
            "filterdefalut" => TRUE
        ),
        "lastreply" => array(
            "type" => "time",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "回复时间" ),
            "editable" => FALSE,
            "filtertype" => "time"
        ),
        "reply_name" => array(
            "type" => "varchar(100)",
            "label" => __( "最后回复人" ),
            "editable" => FALSE
        ),
        "title" => array(
            "type" => "varchar(255)",
            "label" => __( "标题" ),
            "editable" => FALSE,
            "searchtype" => "has",
            "filtertype" => "normal",
            "filterdefalut" => TRUE,
            "escape_html" => TRUE
        ),
        "comment" => array(
            "type" => "longtext",
            "label" => __( "内容" ),
            "editable" => FALSE,
            "searchtype" => "has",
            "filtertype" => "normal",
            "filterdefalut" => TRUE,
            "escape_html" => TRUE
        ),
        "ip" => array(
            "type" => "varchar(15)",
            "label" => __( "咨询人IP" ),
            "editable" => FALSE
        ),
        "display" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "label" => __( "前台显示" ),
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "p_index" => array(
            "type" => array(
                1 => __( "已置顶" ),
                0 => __( "无" )
            ),
            "default" => 0,
            "label" => __( "置顶" ),
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "label" => __( "失效" ),
            "editable" => FALSE,
            "hidden" => TRUE
        )
    ),
    "comment" => "商品评论表",
    "index" => array(
        "ind_goods" => array(
            "columns" => array( 0 => "goods_id" )
        ),
        "ind_member" => array(
            "columns" => array( 0 => "author_id" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_pindex" => array(
            "columns" => array( 0 => "p_index" )
        )
    )
);
?>
