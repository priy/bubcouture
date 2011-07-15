<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['lnk_roles'] = array(
    "columns" => array(
        "op_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "role_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE )
    )
);
?>
