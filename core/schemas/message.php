<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['message'] = array(
    "columns" => array(
        "msg_id" => array(
            "label" => __( "序号" ),
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "editable" => FALSE
        ),
        "for_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "msg_from" => array( "label" => "发送者", "type" => "varchar(30)", "default" => "", "required" => TRUE, "searchable" => TRUE, "editable" => FALSE, "filtertype" => "yes", "filterdefalut" => TRUE ),
        "from_id" => array( "type" => "number", "default" => 0, "editable" => FALSE ),
        "from_type" => array(
            "type" => "tinyint(1) unsigned",
            "map" => array(
                0 => __( "会员" ),
                1 => __( "管理员" ),
                2 => __( "非会员" )
            ),
            "default" => 0,
            "required" => TRUE,
            "editable" => FALSE
        ),
        "to_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "to_type" => array( "type" => "tinyint(1) unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "unread" => array( "type" => "intbool", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "folder" => array(
            "type" => array(
                "inbox" => __( "收件箱" ),
                "outbox" => __( "发件箱" )
            ),
            "default" => "inbox",
            "required" => TRUE,
            "editable" => FALSE
        ),
        "email" => array(
            "type" => "varchar(255)",
            "editable" => FALSE,
            "label" => __( "联系方式" ),
            "filtertype" => "normal",
            "hidden" => TRUE,
            "filterdefalut" => TRUE,
            "escape_html" => TRUE
        ),
        "tel" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "subject" => array(
            "label" => __( "消息标题" ),
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE,
            "escape_html" => TRUE
        ),
        "message" => array(
            "label" => __( "内容" ),
            "type" => "longtext",
            "required" => TRUE,
            "default" => "",
            "editable" => FALSE,
            "searchtype" => "has",
            "filtertype" => "normal",
            "filterdefalut" => TRUE,
            "escape_html" => TRUE
        ),
        "rel_order" => array( "type" => "bigint unsigned", "default" => 0, "editable" => FALSE ),
        "date_line" => array(
            "label" => __( "时间" ),
            "type" => "time",
            "default" => 0,
            "required" => TRUE,
            "editable" => FALSE,
            "filtertype" => "number",
            "filterdefalut" => TRUE
        ),
        "is_sec" => array(
            "type" => "bool",
            "default" => "true",
            "required" => TRUE,
            "editable" => FALSE,
            "label" => __( "公开" ),
            "filtertype" => "yes"
        ),
        "del_status" => array(
            "type" => array( 0 => 0, 1 => 1, 2 => 2 ),
            "default" => 0,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "msg_ip" => array( "type" => "varchar(20)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "msg_type" => array(
            "type" => array(
                "default" => __( "通常" ),
                "payment" => __( "支付" )
            ),
            "default" => "default",
            "required" => TRUE,
            "editable" => FALSE
        )
    ),
    "comment" => "留言和短信表",
    "index" => array(
        "ind_to_id" => array(
            "columns" => array( 0 => "to_id", 1 => "folder", 2 => "from_type", 3 => "unread" )
        ),
        "ind_from_id" => array(
            "columns" => array( 0 => "from_id", 1 => "folder", 2 => "to_type" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
