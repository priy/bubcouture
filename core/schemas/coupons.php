<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['coupons'] = array(
    "columns" => array(
        "cpns_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "id" ),
            "width" => 110,
            "comment" => __( "优惠券方案id" ),
            "editable" => FALSE
        ),
        "cpns_name" => array(
            "type" => "varchar(255)",
            "label" => __( "优惠券名称" ),
            "searchable" => TRUE,
            "width" => 110,
            "comment" => __( "优惠券名称" ),
            "editable" => FALSE
        ),
        "pmt_id" => array(
            "type" => "number",
            "comment" => __( "*暂时废弃" ),
            "editable" => FALSE
        ),
        "cpns_prefix" => array(
            "type" => "varchar(50)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "优惠券号码" ),
            "width" => 110,
            "comment" => __( "生成优惠券前缀/号码(当全局时为号码)" ),
            "editable" => FALSE
        ),
        "cpns_gen_quantity" => array(
            "type" => "number",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "总数量" ),
            "width" => 110,
            "comment" => __( "总数量" ),
            "editable" => FALSE
        ),
        "cpns_key" => array(
            "type" => "varchar(20)",
            "required" => TRUE,
            "default" => "",
            "width" => 110,
            "comment" => __( "优惠券生成的key" ),
            "editable" => FALSE
        ),
        "cpns_status" => array(
            "type" => "intbool",
            "default" => 1,
            "required" => TRUE,
            "label" => __( "是否启用" ),
            "width" => 110,
            "comment" => __( "优惠券方案状态" ),
            "editable" => FALSE
        ),
        "cpns_type" => array(
            "type" => array(
                0 => __( "一张无限使用" ),
                1 => __( "多张使用一次" ),
                2 => __( "外部优惠券" )
            ),
            "default" => 1,
            "required" => TRUE,
            "label" => __( "优惠券类型" ),
            "width" => 110,
            "comment" => __( "优惠券类型" ),
            "editable" => FALSE
        ),
        "cpns_point" => array(
            "type" => "number",
            "default" => NULL,
            "label" => __( "兑换所需积分" ),
            "width" => 110,
            "comment" => __( "兑换优惠券积分" ),
            "editable" => FALSE
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "comment" => __( "失效" ),
            "editable" => FALSE
        )
    ),
    "comment" => __( "优惠券表" ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_cpns_prefix" => array(
            "columns" => array( 0 => "cpns_prefix" )
        )
    )
);
?>
