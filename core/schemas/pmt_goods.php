<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['pmt_goods'] = array(
    "columns" => array(
        "pmt_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "goods_id" => array( "type" => "object:goods/products", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "count" => array( "type" => "number", "default" => 0, "editable" => FALSE )
    )
);
?>
