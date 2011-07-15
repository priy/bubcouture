<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$db['refunds'] = array(
    "columns" => array(
        "refund_id" => array(
            "type" => "bigint unsigned",
            "required" => TRUE,
            "pkey" => TRUE,
            "extra" => "auto_increment",
            "label" => __( "退款单号" ),
            "width" => 110,
            "editable" => FALSE,
            "searchtype" => "has",
            "filtertype" => "yes",
            "filterdefalut" => TRUE
        ),
        "order_id" => array(
            "type" => "object:trading/order",
            "label" => __( "订单号" ),
            "width" => 110,
            "editable" => FALSE,
            "searchtype" => "tequal",
            "filtertype" => "normal",
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
        "account" => array(
            "type" => "varchar(50)",
            "label" => __( "退款帐号" ),
            "width" => 110,
            "editable" => FALSE,
            "searchtype" => "tequal",
            "filtertype" => "normal"
        ),
        "bank" => array(
            "type" => "varchar(50)",
            "label" => __( "退款银行" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "pay_account" => array(
            "type" => "varchar(250)",
            "label" => __( "退款人" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "currency" => array(
            "type" => "object:system/cur",
            "label" => __( "货币" ),
            "width" => 75,
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "money" => array(
            "type" => "money",
            "default" => "0",
            "required" => TRUE,
            "label" => __( "金额" ),
            "width" => 75,
            "editable" => FALSE,
            "searchtype" => "nequal",
            "filtertype" => "number",
            "filterdefalut" => TRUE
        ),
        "pay_type" => array(
            "type" => array(
                "online" => __( "在线支付" ),
                "offline" => __( "线下支付" ),
                "deposit" => __( "预存款支付" ),
                "recharge" => __( "预存款充值" )
            ),
            "default" => "offline",
            "label" => __( "支付类型" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "payment" => array( "type" => "number", "required" => TRUE, "default" => 0, "editable" => FALSE ),
        "paymethod" => array(
            "type" => "varchar(100)",
            "label" => __( "支付方式" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "normal",
            "filterdefalut" => TRUE
        ),
        "ip" => array( "type" => "varchar(20)", "editable" => FALSE ),
        "t_ready" => array(
            "type" => "time",
            "required" => TRUE,
            "default" => 0,
            "label" => __( "单据创建时间" ),
            "width" => 110,
            "editable" => FALSE,
            "hidden" => TRUE
        ),
        "t_sent" => array(
            "type" => "time",
            "label" => __( "退款时间" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "time"
        ),
        "t_received" => array( "type" => "int unsigned", "editable" => FALSE ),
        "status" => array(
            "type" => array(
                "ready" => __( "准备中" ),
                "progress" => __( "正在退款" ),
                "sent" => __( "款项已退" ),
                "received" => __( "用户收到退款" ),
                "cancel" => __( "已取消" )
            ),
            "default" => "ready",
            "required" => TRUE,
            "label" => __( "状态" ),
            "width" => 75,
            "editable" => FALSE
        ),
        "memo" => array( "type" => "longtext", "editable" => FALSE ),
        "title" => array( "type" => "varchar(255)", "required" => TRUE, "default" => "", "editable" => FALSE ),
        "send_op_id" => array(
            "type" => "object:admin/operator",
            "label" => __( "操作员" ),
            "width" => 110,
            "editable" => FALSE,
            "filtertype" => "yes"
        ),
        "disabled" => array( "type" => "bool", "default" => "false", "required" => TRUE, "editable" => FALSE )
    ),
    "comment" => "存放发给用户的款项记录",
    "index" => array(
        "ind_disabled" => array(
            "columns" => array( 0 => "disabled" )
        )
    )
);
?>
