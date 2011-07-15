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
$db['goods_type'] = array(
    "columns" => array(
        "type_id" => array(
            "type" => "int(10)",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "类型序号" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "name" => array(
            "type" => "varchar(100)",
            "required" => TRUE,
            "default" => "",
            "label" => __( "类型名称" ),
            "width" => 150,
            "editable" => TRUE
        ),
        "alias" => array( "type" => "longtext", "editable" => FALSE ),
        "is_physical" => array(
            "type" => "intbool",
            "default" => "1",
            "required" => TRUE,
            "label" => __( "实体商品" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "supplier_id" => array(
            "label" => __( "供应商" ),
            "width" => 100,
            "type" => "int unsigned",
            "editable" => FALSE,
            "hidden" => $hidden
        ),
        "supplier_type_id" => array( "type" => "number", "editable" => FALSE, "hidden" => TRUE ),
        "schema_id" => array( "type" => "varchar(30)", "required" => TRUE, "default" => "custom", "hidden" => 1, "width" => 110, "editable" => FALSE ),
        "props" => array( "type" => "longtext", "editable" => FALSE ),
        "spec" => array( "type" => "longtext", "editable" => FALSE ),
        "setting" => array( "type" => "longtext", "comment" => "类型设置", "width" => 110, "editable" => FALSE ),
        "minfo" => array( "type" => "longtext", "editable" => FALSE ),
        "params" => array( "type" => "longtext", "editable" => FALSE ),
        "dly_func" => array( "type" => "intbool", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "ret_func" => array( "type" => "intbool", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "reship" => array(
            "default" => "normal",
            "required" => TRUE,
            "type" => array(
                "disabled" => __( "不支持退货" ),
                "func" => __( "通过函数退货" ),
                "normal" => __( "物流退货" ),
                "mixed" => __( "物流退货+函数式动作" )
            ),
            "editable" => FALSE
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "is_def" => array(
            "type" => "bool",
            "default" => "false",
            "required" => TRUE,
            "label" => __( "类型标示" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "lastmodify" => array(
            "label" => __( "供应商最后更新时间" ),
            "width" => 150,
            "type" => "time",
            "hidden" => $hidden
        )
    ),
    "comment" => "商品类型表",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
