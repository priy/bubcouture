<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['regions'] = array(
    "columns" => array(
        "region_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "package" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "p_region_id" => array( "type" => "int unsigned", "editable" => FALSE ),
        "region_path" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "region_grade" => array( "type" => "number", "editable" => FALSE ),
        "local_name" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "en_name" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "p_1" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "p_2" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "ordernum" => array( "type" => "number", "editable" => TRUE ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE )
    )
);
?>
