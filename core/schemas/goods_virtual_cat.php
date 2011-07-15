<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_virtual_cat'] = array(
    "columns" => array(
        "virtual_cat_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "虚拟分类ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "virtual_cat_name" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "虚拟分类名称" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "filter" => array( "type" => "longtext", "editable" => FALSE ),
        "addon" => array( "type" => "longtext", "editable" => FALSE ),
        "type_id" => array(
            "type" => "int(10)",
            "label" => __( "类型" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "parent_id" => array(
            "type" => "number",
            "default" => 0,
            "label" => __( "虚拟分类父ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "cat_id" => array( "type" => "int(10)", "editable" => FALSE ),
        "p_order" => array(
            "type" => "number",
            "label" => __( "排序" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "cat_path" => array( "type" => "varchar(100)", "default" => ",", "editable" => FALSE ),
        "child_count" => array( "type" => "number", "default" => 0, "editable" => FALSE )
    ),
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_p_order" => array(
            "columns" => array( 0 => "p_order" )
        ),
        "ind_cat_path" => array(
            "columns" => array( 0 => "cat_path" )
        )
    )
);
?>
