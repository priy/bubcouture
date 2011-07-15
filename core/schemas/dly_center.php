<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['dly_center'] = array(
    "columns" => array(
        "dly_center_id" => array(
            "type" => "int unsigned",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 30,
            "editable" => FALSE
        ),
        "name" => array(
            "type" => "varchar(50)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "发货点名称" ),
            "width" => 150,
            "editable" => TRUE
        ),
        "address" => array(
            "type" => "varchar(200)",
            "label" => __( "地址" ),
            "width" => 180,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "region" => array(
            "type" => "region",
            "label" => __( "地区" ),
            "width" => 180,
            "editable" => FALSE
        ),
        "zip" => array(
            "type" => "varchar(20)",
            "label" => __( "邮编" ),
            "width" => 75,
            "editable" => TRUE
        ),
        "phone" => array(
            "type" => "varchar(100)",
            "label" => __( "电话" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "uname" => array(
            "type" => "varchar(100)",
            "label" => __( "发货人" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "cellphone" => array(
            "type" => "varchar(100)",
            "label" => __( "手机" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "sex" => array(
            "type" => array(
                "female" => __( "女士" ),
                "male" => __( "先生" )
            ),
            "label" => __( "性别" ),
            "width" => 30,
            "editable" => TRUE
        ),
        "memo" => array( "type" => "longtext", "editable" => FALSE ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE )
    ),
    "comment" => "发货点表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
