<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( $this->system->getConf( "certificate.distribute" ) )
{
    $hidden = FALSE;
}
else
{
    $hidden = TRUE;
}
$db['specification'] = array(
    "columns" => array(
        "spec_id" => array(
            "type" => "number",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "规格id" ),
            "width" => 150,
            "editable" => FALSE
        ),
        "spec_name" => array(
            "type" => "varchar(50)",
            "default" => "",
            "required" => TRUE,
            "label" => __( "规格名称" ),
            "width" => 180,
            "modifier" => "row",
            "editable" => TRUE
        ),
        "alias" => array(
            "type" => "varchar(255)",
            "default" => "",
            "label" => __( "规格别名" ),
            "width" => 180
        ),
        "spec_show_type" => array(
            "type" => array(
                "select" => __( "下拉" ),
                "flat" => __( "平铺" )
            ),
            "default" => "flat",
            "required" => TRUE,
            "label" => __( "显示方式" ),
            "width" => 75,
            "editable" => TRUE
        ),
        "spec_type" => array(
            "type" => array(
                "text" => __( "文字" ),
                "image" => __( "图片" )
            ),
            "default" => "text",
            "required" => TRUE,
            "label" => __( "类型" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "spec_memo" => array(
            "type" => "varchar(50)",
            "default" => "",
            "required" => TRUE,
            "label" => __( "规格备注" ),
            "width" => 350,
            "editable" => FALSE
        ),
        "p_order" => array( "type" => "number", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "supplier_spec_id" => array( "type" => "number", "hidden" => TRUE ),
        "supplier_id" => array(
            "label" => __( "供应商" ),
            "width" => 100,
            "type" => "int unsigned",
            "hidden" => $hidden
        ),
        "lastmodify" => array(
            "label" => __( "供应商最后更新时间" ),
            "width" => 150,
            "type" => "time",
            "hidden" => $hidden
        )
    ),
    "comment" => "商店中商品规格"
);
?>
