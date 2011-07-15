<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['dly_area'] = array(
    "columns" => array(
        "area_id" => array( "type" => "mediumint(6) unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "label" => "ID", "width" => 110, "editable" => FALSE ),
        "name" => array(
            "type" => "varchar(80)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "配送地区" ),
            "width" => 180,
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "ordernum" => array(
            "type" => "smallint(4) unsigned",
            "label" => __( "排序" ),
            "width" => 180,
            "editable" => TRUE
        )
    ),
    "comment" => "配送地区表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
