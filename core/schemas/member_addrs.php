<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['member_addrs'] = array(
    "columns" => array(
        "addr_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "member_id" => array( "type" => "object:member/member", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "name" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "area" => array( "type" => "region", "editable" => FALSE ),
        "country" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "province" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "city" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "addr" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "zip" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "tel" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "mobile" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "def_addr" => array( "type" => "tinyint(1)", "default" => 0, "editable" => FALSE )
    )
);
?>
