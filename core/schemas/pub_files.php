<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['pub_files'] = array(
    "columns" => array(
        "file_id" => array( "type" => "int", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "file_name" => array(
            "type" => "varchar(50)",
            "label" => __( "文件名" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "file_ident" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "文件" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "cdate" => array(
            "type" => "int unsigned",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "日期" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "memo" => array(
            "type" => "varchar(250)",
            "label" => __( "描述" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE )
    ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
