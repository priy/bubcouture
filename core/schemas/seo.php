<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['seo'] = array(
    "columns" => array(
        "seo_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "source_id" => array( "type" => "varchar(100)", "required" => TRUE, "editable" => FALSE ),
        "type" => array( "type" => "varchar(50)", "required" => TRUE, "editable" => FALSE ),
        "store_key" => array( "type" => "varchar(100)", "required" => TRUE, "editable" => FALSE ),
        "value" => array( "type" => "text", "required" => TRUE, "editable" => FALSE )
    )
);
?>
