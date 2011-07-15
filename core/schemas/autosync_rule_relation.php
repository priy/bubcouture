<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['autosync_rule_relation'] = array(
    "columns" => array(
        "rule_id" => array( "type" => "number", "required" => TRUE, "default" => "0" ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => "0" ),
        "pline_id" => array( "type" => "tinyint(3) unsigned", "required" => TRUE, "default" => "0" ),
        "memo" => array( "type" => "text" )
    ),
    "index" => array(
        "rsp_index" => array(
            "columns" => array( 0 => "rule_id", 1 => "supplier_id", 2 => "pline_id" )
        ),
        "supplier_id" => array(
            "columns" => array( 0 => "supplier_id" )
        )
    )
);
?>
