<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['promotion_scheme'] = array(
    "columns" => array(
        "pmts_id" => array( "type" => "number", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "pmts_name" => array( "type" => "varchar(250)", "editable" => FALSE ),
        "pmts_memo" => array( "type" => "longtext", "editable" => FALSE ),
        "pmts_solution" => array( "type" => "longtext", "editable" => FALSE ),
        "pmts_type" => array( "type" => "tinyint(3)", "required" => TRUE, "default" => 0, "editable" => FALSE )
    )
);
?>
