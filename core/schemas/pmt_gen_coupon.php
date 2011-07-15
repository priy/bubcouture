<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['pmt_gen_coupon'] = array(
    "columns" => array(
        "pmt_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "cpns_id" => array(
            "type" => "number",
            "required" => TRUE,
            "default" => 0,
            "pkey" => TRUE,
            "label" => __( "促销ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    ),
    "comment" => "通过促销所生成优惠券"
);
?>
