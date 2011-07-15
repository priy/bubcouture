<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['sfiles'] = array(
    "columns" => array(
        "file_id" => array( "type" => "varchar(32)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "file_name" => array( "type" => "varchar(32)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "usedby" => array( "type" => "varchar(32)", "editable" => FALSE ),
        "file_type" => array( "type" => "varchar(32)", "editable" => FALSE ),
        "file_size" => array( "type" => "int(9)", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "cdate" => array( "type" => "time", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "misc" => array( "type" => "varchar(255)", "editable" => FALSE )
    ),
    "index" => array(
        "ind_usedby" => array(
            "columns" => array( 0 => "usedby" )
        )
    )
);
?>
