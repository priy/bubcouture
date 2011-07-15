<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['member_coupon'] = array(
    "columns" => array(
        "memc_code" => array( "type" => "varchar(255)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "cpns_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "member_id" => array( "type" => "object:member/member", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "memc_gen_orderid" => array( "type" => "varchar(15)", "editable" => FALSE ),
        "memc_source" => array(
            "type" => array(
                "a" => __( "全体优惠券" ),
                "b" => __( "会员优惠券" ),
                "c" => __( "ShopEx优惠券" )
            ),
            "default" => "a",
            "required" => TRUE,
            "editable" => FALSE
        ),
        "memc_enabled" => array( "type" => "bool", "default" => "true", "required" => TRUE, "editable" => FALSE ),
        "memc_used_times" => array( "type" => "mediumint", "default" => 0, "editable" => FALSE ),
        "memc_gen_time" => array( "type" => "time", "editable" => FALSE )
    )
);
?>
