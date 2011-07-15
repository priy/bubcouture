<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['cachemgr'] = array(
    "columns" => array(
        "cname" => array(
            "type" => "varchar(30)",
            "required" => TRUE,
            "default" => "",
            "pkey" => TRUE,
            "comment" => __( "缓存名称" ),
            "editable" => FALSE
        ),
        "modified" => array(
            "type" => "int unsigned",
            "required" => TRUE,
            "default" => 0,
            "comment" => __( "最后更新时间" ),
            "editable" => FALSE
        )
    ),
    "engine" => "heap",
    "comment" => __( "缓存对象管理表" )
);
?>
