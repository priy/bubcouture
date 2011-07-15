<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['gift_cat'] = array(
    "columns" => array(
        "giftcat_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "cat" => array(
            "type" => "varchar(255)",
            "label" => __( "分类名称" ),
            "width" => 180,
            "searchname" => TRUE,
            "editable" => TRUE
        ),
        "orderlist" => array(
            "type" => "mediumint(6) unsigned",
            "label" => __( "排序" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "shop_iffb" => array(
            "type" => "intbool",
            "default" => 1,
            "label" => __( "是否发布" ),
            "width" => 110,
            "editable" => TRUE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    ),
    "comment" => "赠品分类表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
