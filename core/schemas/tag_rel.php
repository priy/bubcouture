<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['tag_rel'] = array(
    "columns" => array(
        "tag_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "rel_id" => array( "type" => "bigint unsigned", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE )
    )
);
?>
