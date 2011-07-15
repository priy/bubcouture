<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['autosync_rule'] = array(
    "columns" => array(
        "rule_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "ID" ),
            "editable" => FALSE
        ),
        "supplier_op_id" => array( "required" => TRUE, "default" => "0", "type" => "tinyint(3)" ),
        "local_op_id" => array( "required" => TRUE, "default" => "0", "type" => "tinyint(3)" ),
        "disabled" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "required" => TRUE,
            "default" => "false"
        ),
        "memo" => array( "type" => "varchar(255)" ),
        "rule_name" => array( "type" => "varchar(255)", "required" => TRUE )
    ),
    "index" => array(
        "index_1" => array(
            "columns" => array( 0 => "rule_id", 1 => "local_op_id", 2 => "disabled" )
        )
    )
);
?>
