<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['link'] = array(
    "columns" => array(
        "link_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "友情链接id" ),
            "width" => 150,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "link_name" => array(
            "type" => "varchar(128)",
            "label" => __( "友情链接名称" ),
            "width" => 180,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "href" => array(
            "type" => "varchar(255)",
            "label" => __( "友情链接地址" ),
            "width" => 230,
            "required" => TRUE,
            "editable" => TRUE
        ),
        "image_url" => array( "type" => "varchar(255)", "editable" => TRUE ),
        "orderlist" => array(
            "type" => "number",
            "label" => __( "排序" ),
            "width" => 270,
            "required" => TRUE,
            "default" => 0,
            "editable" => TRUE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    )
);
?>
