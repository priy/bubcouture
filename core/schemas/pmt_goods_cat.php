<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['pmt_goods_cat'] = array(
    "columns" => array(
        "cat_id" => array( "type" => "int(10)", "default" => 0, "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "brand_id" => array( "type" => "number", "default" => 0, "required" => TRUE, "pkey" => TRUE, "editable" => FALSE ),
        "pmt_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE )
    )
);
?>
