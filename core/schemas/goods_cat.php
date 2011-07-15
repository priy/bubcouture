<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods_cat'] = array(
    "columns" => array(
        "cat_id" => array(
            "type" => "int(10)",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "分类ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "parent_id" => array(
            "type" => "int(10)",
            "label" => __( "分类ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "supplier_id" => array( "type" => "int unsigned", "editable" => FALSE ),
        "supplier_cat_id" => array( "type" => "number", "editable" => FALSE ),
        "cat_path" => array(
            "type" => "varchar(100)",
            "default" => ",",
            "label" => __( "分类路径(从根至本结点的路径,逗号分隔,首部有逗号)" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "is_leaf" => array(
            "type" => "bool",
            "required" => TRUE,
            "default" => "false",
            "label" => __( "是否叶子结点（true：是；false：否）" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "type_id" => array(
            "type" => "int(10)",
            "label" => __( "类型序号" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "cat_name" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "分类名称" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "label" => __( "是否屏蔽（true：是；false：否）" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "p_order" => array(
            "type" => "number",
            "label" => __( "排序" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "goods_count" => array(
            "type" => "number",
            "label" => __( "商品数" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "tabs" => array( "type" => "longtext", "editable" => FALSE ),
        "finder" => array(
            "type" => "longtext",
            "label" => __( "渐进式筛选容器" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "addon" => array( "type" => "longtext", "editable" => FALSE ),
        "child_count" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE )
    ),
    "comment" => "类别属性值有限表",
    "index" => array(
        "ind_cat_path" => array(
            "columns" => array( 0 => "cat_path" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
