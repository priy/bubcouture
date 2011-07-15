<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_rate'] = array(
    "columns" => array(
        "goods_1" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "goods_2" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "manual" => array(
            "type" => array(
                "left" => __( "单向" ),
                "both" => __( "关联" )
            ),
            "editable" => FALSE
        ),
        "rate" => array( "type" => "number", "default" => 1, "required" => TRUE, "editable" => FALSE )
    ),
    "comment" => "商品购买率统计表"
);
?>
