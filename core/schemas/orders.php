<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['orders'] = array(
    "columns" => array(
        "order_id" => array(
            "type" => "bigint unsigned",
            "required" => TRUE,
            "default" => 0,
            "pkey" => TRUE,
            "label" => __( "订单号" ),
            "width" => 110,
            "primary" => TRUE,
            "searchtype" => "has",
            "editable" => FALSE,
            "filtertype" => "custom",
            "filtercustom" => array( "head" => "开头等于" ),
            "filterdefalut" => TRUE
        ),
        "member_id" => array(
            "type" => "object:member/member",
            "label" => __( "会员用户名" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "confirm" => array(
            "type" => "tinybool",
            "default" => "N",
            "required" => TRUE,
            "label" => __( "确认状态" ),
            "width" => 75,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "status" => array(
            "type" => array(
                "active" => __( "活动订单" ),
                "dead" => __( "死单" ),
                "finish" => __( "已完成" )
            ),
            "default" => "active",
            "required" => TRUE,
            "label" => __( "订单状态" ),
            "width" => 75,
            "hidden" => TRUE,
            "editable" => FALSE
        ),
        "pay_status" => array(
            "type" => array(
                0 => __( "未支付" ),
                1 => __( "已支付" ),
                2 => __( "已支付至担保方" ),
                3 => __( "部分付款" ),
                4 => __( "部分退款" ),
                5 => __( "全额退款" )
            ),
            "default" => 0,
            "required" => TRUE,
            "label" => __( "付款状态" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "ship_status" => array(
            "type" => array(
                0 => __( "未发货" ),
                1 => __( "已发货" ),
                2 => __( "部分发货" ),
                3 => __( "部分退货" ),
                4 => __( "已退货" )
            ),
            "default" => 0,
            "required" => TRUE,
            "label" => __( "发货状态" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "user_status" => array(
            "label" => __( "用户反馈" ),
            "type" => array(
                "null" => __( "无反馈" ),
                "payed" => __( "已支付" ),
                "shipped" => __( "已到收货" )
            ),
            "hidden" => TRUE,
            "default" => "null",
            "required" => TRUE,
            "editable" => FALSE
        ),
        "is_delivery" => array( "type" => "tinybool", "default" => "Y", "required" => TRUE, "editable" => FALSE ),
        "shipping_id" => array( "type" => "smallint(4) unsigned", "editable" => FALSE ),
        "shipping" => array(
            "type" => "varchar(100)",
            "label" => __( "配送方式" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "shipping_area" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "payment" => array(
            "type" => "object:trading/paymentcfg",
            "label" => __( "支付方式" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "weight" => array( "type" => "money", "editable" => FALSE ),
        "tostr" => array( "type" => "longtext", "editable" => FALSE ),
        "itemnum" => array( "type" => "number", "editable" => FALSE ),
        "acttime" => array(
            "label" => "更新时间",
            "type" => "time",
            "label" => __( "更新时间" ),
            "width" => 110,
            "editable" => FALSE
        ),
        "createtime" => array(
            "type" => "time",
            "label" => __( "下单时间" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "time",
            "filterdefalut" => TRUE
        ),
        "refer_id" => array(
            "type" => "varchar(50)",
            "label" => __( "首次来源ID" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "refer_url" => array(
            "type" => "varchar(200)",
            "label" => __( "首次来源URL" ),
            "width" => 150,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "refer_time" => array(
            "type" => "time",
            "label" => __( "首次来源时间" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "time"
        ),
        "c_refer_id" => array(
            "type" => "varchar(50)",
            "label" => __( "本次来源ID" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "c_refer_url" => array(
            "type" => "varchar(200)",
            "label" => __( "本次来源URL" ),
            "width" => 150,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "c_refer_time" => array(
            "type" => "time",
            "label" => __( "本次来源时间" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "time"
        ),
        "ip" => array( "type" => "varchar(15)", "editable" => FALSE ),
        "ship_name" => array(
            "type" => "varchar(50)",
            "label" => __( "收货人" ),
            "width" => 75,
            "searchtype" => "head",
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "ship_area" => array(
            "type" => "region",
            "label" => __( "收货地区" ),
            "searchable" => TRUE,
            "width" => 180,
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "ship_addr" => array(
            "type" => "varchar(100)",
            "label" => __( "收货地址" ),
            "searchtype" => "has",
            "width" => 180,
            "editable" => FALSE,
            "filtertype" => "normal"
        ),
        "ship_zip" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "ship_tel" => array(
            "type" => "varchar(30)",
            "label" => __( "收货人电话" ),
            "searchtype" => "has",
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "ship_email" => array( "type" => "varchar(150)", "editable" => FALSE ),
        "ship_time" => array( "type" => "varchar(50)", "editable" => FALSE ),
        "ship_mobile" => array(
            "label" => __( "收货人手机" ),
            "hidden" => TRUE,
            "searchtype" => "has",
            "type" => "varchar(50)",
            "editable" => FALSE
        ),
        "cost_item" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "is_tax" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "cost_tax" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "tax_company" => array( "type" => "varchar(255)", "editable" => FALSE ),
        "cost_freight" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "配送费用" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "number"
        ),
        "is_protect" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE ),
        "cost_protect" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "cost_payment" => array( "type" => "money", "editable" => FALSE ),
        "currency" => array( "type" => "varchar(8)", "editable" => FALSE ),
        "cur_rate" => array( "type" => "decimal(10,4)", "default" => "1.0000", "editable" => FALSE ),
        "score_u" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "score_g" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "score_e" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "advance" => array( "type" => "money", "default" => "0", "editable" => FALSE ),
        "discount" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "use_pmt" => array( "type" => "varchar(30)", "editable" => FALSE ),
        "total_amount" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "订单总额" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "number",
            "filterdefalut" => TRUE
        ),
        "final_amount" => array( "type" => "money", "default" => "0", "required" => TRUE, "editable" => FALSE ),
        "pmt_amount" => array( "type" => "money", "editable" => FALSE ),
        "payed" => array( "type" => "money", "default" => "0", "editable" => FALSE ),
        "markstar" => array( "type" => "tinybool", "default" => "N", "editable" => FALSE ),
        "memo" => array( "type" => "longtext", "editable" => FALSE ),
        "print_status" => array(
            "type" => "tinyint unsigned",
            "default" => 0,
            "required" => TRUE,
            "label" => __( "打印" ),
            "width" => 150,
            "editable" => FALSE
        ),
        "mark_text" => array(
            "type" => "longtext",
            "label" => __( "订单备注" ),
            "width" => 50,
            "html" => "order/order_remark.html",
            "editable" => FALSE,
            "searchtype" => "has",
            "filtertype" => "normal"
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "last_change_time" => array( "type" => "int(11)", "default" => 0, "required" => TRUE, "editable" => FALSE ),
        "use_registerinfo" => array( "type" => "bool", "default" => "false", "editable" => FALSE ),
        "mark_type" => array(
            "type" => "varchar(2)",
            "default" => "b1",
            "required" => TRUE,
            "label" => __( "订单备注图标" ),
            "hidden" => TRUE,
            "width" => 150,
            "editable" => FALSE
        ),
        "extend" => array( "type" => "varchar(255)", "default" => "false", "editable" => FALSE ),
        "is_has_remote_pdts" => array(
            "type" => array(
                "true" => __( "" ),
                "false" => __( "" )
            ),
            "required" => TRUE,
            "default" => "false"
        ),
        "order_refer" => array( "type" => "varchar(20)", "required" => TRUE, "default" => "local", "hidden" => TRUE ),
        "print_id" => array( "type" => "varchar(20)", "required" => FALSE, "label" => "订单打印编号" )
    ),
    "index" => array(
        "ind_ship_status" => array(
            "columns" => array( 0 => "ship_status" )
        ),
        "ind_pay_status" => array(
            "columns" => array( 0 => "pay_status" )
        ),
        "ind_status" => array(
            "columns" => array( 0 => "status" )
        ),
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        ),
        "ind_print_id" => array(
            "columns" => array( 0 => "print_id" )
        )
    )
);
?>
