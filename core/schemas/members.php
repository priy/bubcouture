<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['members'] = array(
    "columns" => array(
        "member_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "member_lv_id" => array(
            "required" => TRUE,
            "default" => 0,
            "label" => __( "会员等级" ),
            "width" => 75,
            "type" => "object:member/level",
            "editable" => TRUE,
            "filtertype" => "bool",
            "filterdefalut" => "true"
        ),
        "uname" => array(
            "type" => "varchar(50)",
            "label" => __( "用户名" ),
            "width" => 75,
            "required" => 1,
            "searchtype" => "head",
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => "true"
        ),
        "name" => array(
            "type" => "varchar(50)",
            "label" => __( "姓名" ),
            "width" => 75,
            "searchtype" => "has",
            "editable" => TRUE,
            "filtertype" => "normal",
            "filterdefalut" => "true"
        ),
        "lastname" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "firstname" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "password" => array( "type" => "varchar(32)", "editable" => FALSE ),
        "area" => array(
            "label" => __( "地区" ),
            "width" => 110,
            "type" => "region",
            "editable" => FALSE,
            "filtertype" => "yes",
            "filterdefalut" => "true"
        ),
        "mobile" => array(
            "type" => "varchar(30)",
            "label" => __( "手机" ),
            "width" => 75,
            "fuzzySearch" => 1,
            "searchtype" => "head",
            "editable" => TRUE,
            "filtertype" => "normal",
            "filterdefalut" => "true",
            "escape_html" => TRUE
        ),
        "tel" => array(
            "type" => "varchar(30)",
            "label" => __( "固定电话" ),
            "width" => 110,
            "fuzzySearch" => 1,
            "searchtype" => "head",
            "editable" => TRUE,
            "filtertype" => "normal",
            "filterdefalut" => "true",
            "escape_html" => TRUE
        ),
        "email" => array(
            "type" => "varchar(200)",
            "label" => __( "EMAIL" ),
            "width" => 110,
            "required" => 1,
            "fuzzySearch" => 1,
            "searchtype" => "has",
            "editable" => TRUE,
            "filtertype" => "normal",
            "filterdefalut" => "true",
            "escape_html" => TRUE
        ),
        "zip" => array(
            "type" => "varchar(20)",
            "label" => __( "邮编" ),
            "width" => 110,
            "editable" => TRUE,
            "filtertype" => "normal",
            "escape_html" => TRUE
        ),
        "addr" => array(
            "type" => "varchar(255)",
            "label" => __( "地址" ),
            "width" => 110,
            "editable" => TRUE,
            "filtertype" => "normal",
            "escape_html" => TRUE
        ),
        "province" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "city" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "order_num" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "订单数" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "refer_id" => array(
            "type" => "varchar(50)",
            "label" => __( "首次来源ID" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "refer_url" => array(
            "type" => "varchar(200)",
            "label" => __( "首次来源URL" ),
            "width" => 150,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "refer_time" => array(
            "type" => "time",
            "label" => __( "首次来源时间" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "time"
        ),
        "c_refer_id" => array(
            "type" => "varchar(50)",
            "label" => __( "本次来源ID" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "c_refer_url" => array(
            "type" => "varchar(200)",
            "label" => __( "本次来源URL" ),
            "width" => 150,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "c_refer_time" => array(
            "type" => "time",
            "label" => __( "本次来源时间" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "time"
        ),
        "b_year" => array( "type" => "smallint unsigned", "width" => 30, "editable" => FALSE ),
        "b_month" => array( "label" => "生月", "type" => "tinyint unsigned", "width" => 30, "editable" => FALSE, "hidden" => TRUE ),
        "b_day" => array( "label" => "生日", "type" => "tinyint unsigned", "width" => 30, "editable" => FALSE, "hidden" => TRUE ),
        "sex" => array(
            "type" => array(
                0 => __( "女" ),
                1 => __( "男" )
            ),
            "default" => 1,
            "required" => TRUE,
            "label" => __( "性别" ),
            "width" => 30,
            "editable" => TRUE,
            "filtertype" => "yes"
        ),
        "addon" => array( "type" => "longtext", "editable" => FALSE ),
        "wedlock" => array( "type" => "intbool", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "education" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "vocation" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "interest" => array( "type" => "longtext", "editable" => FALSE ),
        "advance" => array(
            "type" => "money",
            "default" => "0.00",
            "required" => TRUE,
            "label" => __( "预存款" ),
            "width" => 110,
            "searchable" => TRUE,
            "editable" => FALSE,
            "filtertype" => "number"
        ),
        "advance_freeze" => array( "type" => "money", "default" => "0.00", "required" => TRUE, "editable" => FALSE ),
        "point_freeze" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "point_history" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "point" => array(
            "type" => "number",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "积分" ),
            "width" => 110,
            "searchable" => TRUE,
            "editable" => FALSE,
            "filtertype" => "number"
        ),
        "score_rate" => array( "type" => "decimal(5,3)", "editable" => FALSE ),
        "reg_ip" => array(
            "type" => "varchar(16)",
            "label" => __( "注册IP" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "regtime" => array(
            "label" => __( "注册时间" ),
            "width" => 75,
            "type" => "time",
            "editable" => FALSE,
            "searchable" => TRUE,
            "filtertype" => "number",
            "filterdefalut" => "true"
        ),
        "state" => array(
            "type" => "tinyint(1)",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "验证状态" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "pay_time" => array( "type" => "number", "editable" => FALSE ),
        "biz_money" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "pw_answer" => array( "type" => "varchar(250)", "editable" => FALSE ),
        "pw_question" => array( "type" => "varchar(250)", "editable" => FALSE ),
        "fav_tags" => array( "type" => "longtext", "editable" => FALSE ),
        "custom" => array( "type" => "longtext", "editable" => FALSE ),
        "cur" => array(
            "type" => "varchar(20)",
            "label" => __( "货币" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "lang" => array(
            "type" => "varchar(20)",
            "label" => __( "语言" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "unreadmsg" => array(
            "type" => "smallint unsigned",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "未读信息" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "number"
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "remark" => array(
            "label" => __( "备注" ),
            "type" => "text",
            "width" => 75,
            "modifier" => "row"
        ),
        "remark_type" => array( "type" => "varchar(2)", "default" => "b1", "required" => TRUE, "editable" => FALSE ),
        "login_count" => array( "type" => "int(11)", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "experience" => array(
            "label" => __( "经验值" ),
            "type" => "int(10)",
            "default" => 0,
            "editable" => FALSE
        ),
        "foreign_id" => array( "type" => "varchar(255)" ),
        "member_refer" => array( "type" => "varchar(50)", "hidden" => TRUE, "default" => "local" )
    ),
    "comment" => "商店会员表",
    "index" => array(
        "ind_email" => array(
            "columns" => array( 0 => "email" )
        ),
        "uni_user" => array(
            "columns" => array( 0 => "uname" )
        ),
        "ind_regtime" => array(
            "columns" => array( 0 => "regtime" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
