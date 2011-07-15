<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['admin_roles'] = array(
    "columns" => array(
        "role_id" => array(
            "type" => "int unsigned",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "角色id" ),
            "width" => 75,
            "comment" => __( "角色id" ),
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "role_name" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "角色名称" ),
            "width" => 150,
            "comment" => __( "角色名称" ),
            "editable" => TRUE
        ),
        "role_memo" => array(
            "type" => "text",
            "default" => "",
            "label" => __( "角色备注" ),
            "width" => 180,
            "comment" => __( "角色备注" ),
            "editable" => FALSE
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "comment" => __( "无效" ),
            "editable" => FALSE
        )
    ),
    "index" => array(
        "ind_disabled" => array(
            "name" => "ind_disabled",
            "columns" => array( 0 => "disabled" )
        )
    ),
    "comment" => __( "管理员角色表" )
);
?>
