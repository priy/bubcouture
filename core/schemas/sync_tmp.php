<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['sync_tmp'] = array(
    "columns" => array(
        "tmp_id" => array( "type" => "int unsigned", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment" ),
        "s_type" => array(
            "type" => array(
                "goods_type" => __( "" ),
                "spec" => __( "" ),
                "brand" => __( "" ),
                "goods_cat" => __( "" )
            ),
            "required" => TRUE,
            "default" => "goods_type"
        ),
        "ob_id" => array( "type" => "number", "required" => TRUE, "default" => 0 ),
        "supplier_id" => array( "type" => "int unsigned", "required" => TRUE, "default" => 0 ),
        "s_data" => array( "type" => "longtext" )
    )
);
?>
