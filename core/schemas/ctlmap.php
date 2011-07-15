<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['ctlmap'] = array(
    "columns" => array(
        "controller" => array( "type" => "varchar(100)", "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "plugin" => array( "type" => "varchar(100)", "required" => TRUE, "editable" => FALSE )
    )
);
?>
