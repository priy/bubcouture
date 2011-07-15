<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['dly_h_area'] = array(
    "columns" => array(
        "dha_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "dt_id" => array( "type" => "number", "editable" => FALSE ),
        "area_id" => array( "type" => "mediumint(6) unsigned", "default" => 0, "editable" => FALSE ),
        "price" => array( "type" => "varchar(100)", "default" => 0, "editable" => FALSE ),
        "has_cod" => array( "type" => "tinyint(1) unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "areaname_group" => array( "type" => "longtext", "editable" => FALSE ),
        "areaid_group" => array( "type" => "longtext", "editable" => FALSE ),
        "config" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "expressions" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "ordernum" => array( "type" => "smallint(4) unsigned", "editable" => TRUE )
    ),
    "comment" => "配送地区运费配置表"
);
?>
