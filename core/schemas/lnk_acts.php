<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['lnk_acts'] = array(
    "columns" => array(
        "role_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "action_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE )
    )
);
?>
