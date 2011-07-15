<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['trust_login'] = array(
    "columns" => array(
        "login_id" => array( "type" => "number", "pkey" => TRUE, "required" => TRUE, "extra" => "auto_increment" ),
        "member_id" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "uname" => array(
            "type" => "varchar(50)",
            "label" => __( "信任登陆用户名" ),
            "required" => TRUE,
            "editable" => FALSE,
            "default" => ""
        ),
        "member_refer" => array(
            "type" => "varchar(50)",
            "label" => __( "信任登陆来源" ),
            "required" => TRUE,
            "editable" => FALSE,
            "default" => ""
        ),
        "show_uname" => array( "type" => "varchar(50)", "default" => "" )
    ),
    "index" => array(
        "ind_id" => array(
            "columns" => array( 0 => "member_id" )
        )
    )
);
?>
