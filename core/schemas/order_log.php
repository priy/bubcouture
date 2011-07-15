<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['order_log'] = array(
    "columns" => array(
        "log_id" => array( "type" => "int(10)", "required" => TRUE, "pkey" => TRUE, "extra" => "auto_increment", "editable" => FALSE ),
        "order_id" => array( "type" => "object:trading/order", "editable" => FALSE ),
        "op_id" => array( "type" => "mediumint(8)", "editable" => FALSE ),
        "op_name" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "log_text" => array( "type" => "longtext", "editable" => FALSE ),
        "acttime" => array( "type" => "time", "editable" => FALSE ),
        "behavior" => array( "type" => "varchar(20)", "default" => "", "editable" => FALSE ),
        "result" => array(
            "type" => array(
                "success" => __( "成功" ),
                "failure" => __( "失败" )
            ),
            "default" => "success",
            "editable" => FALSE
        )
    )
);
?>
