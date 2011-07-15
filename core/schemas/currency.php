<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['currency'] = array(
    "columns" => array(
        "cur_code" => array(
            "type" => "varchar(8)",
            "required" => TRUE,
            "default" => "",
            "pkey" => TRUE,
            "comment" => __( "货币代码" ),
            "editable" => FALSE
        ),
        "cur_name" => array(
            "type" => "varchar(20)",
            "required" => TRUE,
            "default" => "",
            "comment" => __( "货币名称" ),
            "editable" => TRUE
        ),
        "cur_sign" => array(
            "type" => "varchar(5)",
            "comment" => __( "货币符号" ),
            "editable" => TRUE
        ),
        "cur_rate" => array(
            "type" => "decimal(10,4)",
            "default" => "1.0000",
            "required" => TRUE,
            "comment" => __( "汇率" ),
            "editable" => TRUE
        ),
        "def_cur" => array(
            "type" => "bool",
            "required" => TRUE,
            "default" => "false",
            "comment" => __( "是否默认币别" ),
            "editable" => FALSE
        ),
        "disabled" => array(
            "type" => "bool",
            "default" => "false",
            "comment" => __( "失效" ),
            "editable" => FALSE
        )
    ),
    "comment" => "货币",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
