<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_type_spec'] = array(
    "columns" => array(
        "spec_id" => array( "type" => "number", "default" => 0, "editable" => FALSE ),
        "type_id" => array( "type" => "int(10)", "default" => 0, "editable" => FALSE ),
        "spec_style" => array(
            "type" => array(
                "select" => __( "下拉" ),
                "flat" => __( "平面" ),
                "disabled" => __( "禁用" )
            ),
            "default" => "flat",
            "required" => TRUE,
            "editable" => FALSE
        )
    ),
    "comment" => "类型 规格索引表"
);
?>
