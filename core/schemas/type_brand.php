<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['type_brand'] = array(
    "columns" => array(
        "type_id" => array( "type" => "int(10)", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "brand_id" => array( "type" => "number", "required" => TRUE, "default" => 0, "pkey" => TRUE, "editable" => FALSE ),
        "brand_order" => array( "type" => "number", "editable" => FALSE )
    )
);
?>
