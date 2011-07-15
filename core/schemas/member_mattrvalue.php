<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['member_mattrvalue'] = array(
    "columns" => array(
        "attr_id" => array( "type" => "int unsigned", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "member_id" => array( "type" => "object:member/member", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "value" => array( "type" => "varchar(100)", "default" => "", "required" => TRUE, "editable" => FALSE ),
        "id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE )
    )
);
?>
