<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['operators'] = array(
    "columns" => array(
        "op_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "label" => "ID", "width" => 30, "editable" => FALSE, "hidden" => TRUE ),
        "username" => array(
            "type" => "varchar(20)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "用户名" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "userpass" => array( "type" => "varchar(32)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "name" => array(
            "type" => "varchar(30)",
            "label" => __( "姓名" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "config" => array( "type" => "longtext", "editable" => FALSE ),
        "favorite" => array( "type" => "longtext", "editable" => FALSE ),
        "super" => array(
            "type" => "intbool",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "超级管理员" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "lastip" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "logincount" => array(
            "type" => "number",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "登陆次数" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "lastlogin" => array(
            "type" => "time",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "最后登陆时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "status" => array(
            "type" => "intbool",
            "default" => "1",
            "label" => __( "启用" ),
            "width" => 100,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "op_no" => array(
            "type" => "varchar(50)",
            "label" => __( "编号" ),
            "width" => 30,
            "editable" => TRUE
        ),
        "department" => array(
            "type" => "varchar(50)",
            "label" => __( "部门" ),
            "width" => 75,
            "editable" => TRUE
        ),
        "memo" => array(
            "type" => "text",
            "label" => __( "备注" ),
            "width" => 270,
            "editable" => FALSE
        )
    ),
    "comment" => "商店后台管理员表",
    "index" => array(
        "uni_username" => array(
            "columns" => array( 0 => "username" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
