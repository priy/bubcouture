<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['product_memo'] = array(
    "columns" => array(
        "product_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "p_key" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "", "pkey" => TRUE, "editable" => FALSE ),
        "p_value" => array( "type" => "longtext", "editable" => FALSE )
    ),
    "comment" => "物品扩展信息"
);
?>
