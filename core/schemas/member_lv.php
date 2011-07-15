<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['member_lv'] = array(
    "columns" => array(
        "member_lv_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "name" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "等级名称" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "dis_count" => array(
            "type" => "decimal(5,2)",
            "default" => "1",
            "required" => TRUE,
            "label" => __( "优惠折扣率" ),
            "width" => 110,
            "vtype" => "positive",
            "editable" => TRUE
        ),
        "pre_id" => array( "type" => "mediumint", "editable" => FALSE ),
        "default_lv" => array(
            "type" => "intbool",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "是否默认" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "deposit_freeze_time" => array( "type" => "int", "default" => 0, "editable" => FALSE ),
        "deposit" => array( "type" => "int", "default" => 0, "editable" => FALSE ),
        "more_point" => array( "type" => "int", "default" => 1, "editable" => FALSE ),
        "point" => array(
            "type" => "mediumint(8)",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "所需积分" ),
            "width" => 110,
            "vtype" => "positive",
            "editable" => FALSE
        ),
        "lv_type" => array(
            "type" => array(
                "retail" => __( "零售" ),
                "wholesale" => __( "批发" ),
                "dealer" => __( "代理" )
            ),
            "default" => "retail",
            "required" => TRUE,
            "label" => __( "等级类型" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "show_other_price" => array( "type" => "bool", "default" => "true", "required" => TRUE, "editable" => FALSE ),
        "order_limit" => array( "type" => "tinyint(1)", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "order_limit_price" => array( "type" => "money", "default" => "0.000", "required" => TRUE, "editable" => FALSE ),
        "lv_remark" => array( "type" => "text", "editable" => FALSE ),
        "experience" => array(
            "label" => __( "经验值" ),
            "type" => "int(10)",
            "default" => 0,
            "required" => TRUE,
            "editable" => FALSE
        )
    ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
