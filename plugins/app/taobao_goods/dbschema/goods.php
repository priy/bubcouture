<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['goods'] = array(
    "columns" => array(
        "goods_id" => array( "type" => "mediumint(8)", "required" => TRUE, "default" => "0" ),
        "outer_id" => array( "type" => "varchar(50)", "required" => TRUE, "default" => "0" ),
        "outer_key" => array( "type" => "varchar(50)", "required" => TRUE ),
        "outer_content" => array( "type" => "text" ),
        "disabled" => array( "type" => "bool", "default" => "false" )
    ),
    "index" => array(
        "index_1" => array(
            "columns" => array( 0 => "goods_id", 1 => "outer_key" )
        )
    )
);
?>
