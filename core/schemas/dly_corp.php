<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['dly_corp'] = array(
    "columns" => array(
        "corp_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "物流公司ID" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "type" => array( "type" => "varchar(6)", "editable" => FALSE ),
        "name" => array(
            "type" => "varchar(200)",
            "label" => __( "物流公司" ),
            "width" => 180,
            "editable" => TRUE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "ordernum" => array(
            "type" => "smallint(4) unsigned",
            "label" => __( "排序" ),
            "width" => 180,
            "editable" => TRUE
        ),
        "website" => array(
            "type" => "varchar(200)",
            "label" => __( "网址" ),
            "width" => 180,
            "editable" => TRUE
        )
    ),
    "comment" => "物流公司表",
    "index" => array(
        "ind_type" => array(
            "columns" => array( 0 => "type" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_ordernum" => array(
            "columns" => array( 0 => "ordernum" )
        )
    )
);
?>
