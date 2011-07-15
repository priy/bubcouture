<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['promotion_activity'] = array(
    "columns" => array(
        "pmta_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "序号" ),
            "width" => 30,
            "editable" => FALSE
        ),
        "pmta_name" => array(
            "type" => "varchar(200)",
            "label" => __( "活动名称" ),
            "width" => 230,
            "searchable" => TRUE,
            "editable" => TRUE
        ),
        "pmta_enabled" => array(
            "type" => "bool",
            "label" => __( "发布" ),
            "width" => 30,
            "editable" => TRUE
        ),
        "pmta_time_begin" => array(
            "type" => "time",
            "label" => __( "开始时间" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "pmta_time_end" => array(
            "type" => "time",
            "label" => __( "结束时间" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "pmta_describe" => array(
            "type" => "longtext",
            "label" => __( "详细描述" ),
            "width" => 180,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
