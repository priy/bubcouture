<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['tpl_source'] = array(
    "columns" => array(
        "tpl_source_id" => array( "type" => "int", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "hidden" => 1, "editable" => FALSE ),
        "tpl_type" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "width" => 300, "editable" => FALSE ),
        "tpl_name" => array( "type" => "varchar(100)", "required" => TRUE, "default" => "", "width" => 300, "editable" => FALSE ),
        "tpl_file" => array( "type" => "varchar(100)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "tpl_theme" => array( "type" => "varchar(100)", "required" => TRUE, "editable" => FALSE )
    )
);
?>
