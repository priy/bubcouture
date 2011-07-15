<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['point_history'] = array(
    "columns" => array(
        "id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "member_id" => array( "type" => "object:member/member", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "point" => array( "type" => "int(10)", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "time" => array( "type" => "time", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "reason" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "related_id" => array( "type" => "bigint unsigned", "editable" => FALSE ),
        "type" => array( "type" => "tinyint(1)", "required" => TRUE, "default" => 1, "editable" => FALSE ),
        "operator" => array( "type" => "varchar(50)", "editable" => FALSE )
    ),
    "comment" => "积分历史"
);
?>
