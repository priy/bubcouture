<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['systmpl'] = array(
    "columns" => array(
        "tmpl_name" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "content" => array( "type" => "longtext", "editable" => FALSE ),
        "edittime" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "active" => array( "type" => "bool", "default" => "true", "required" => TRUE, "editable" => FALSE )
    ),
    "comment" => "存储模板表"
);
?>
