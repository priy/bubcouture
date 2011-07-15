<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['dapi'] = array(
    "columns" => array(
        "func" => array( "type" => "varchar(60)", "required" => TRUE, "pkey" => TRUE ),
        "last_update" => array( "type" => "time", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "checksum" => array( "type" => "varchar(32)", "editable" => FALSE ),
        "code" => array( "type" => "text", "required" => TRUE, "default" => "", "editable" => FALSE )
    )
);
?>
