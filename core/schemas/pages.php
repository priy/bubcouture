<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['pages'] = array(
    "columns" => array(
        "page_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "page_name" => array( "type" => "varchar(90)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "page_title" => array( "type" => "varchar(90)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "page_content" => array( "type" => "longtext", "editable" => FALSE ),
        "page_time" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "editable" => FALSE )
    ),
    "index" => array(
        "uni_pagename" => array(
            "columns" => array( 0 => "page_name" )
        ),
        "uni_pagetitle" => array(
            "columns" => array( 0 => "page_title" )
        )
    )
);
?>
