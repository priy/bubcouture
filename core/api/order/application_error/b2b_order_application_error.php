<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$app_error = array(
    "order_not_belong_to_dealer" => array( "no" => "b_order_001", "debug" => "", "level" => "error", "desc" => "此订单不属于经销商", "info" => "" ),
    "order_has_orderitem" => array( "no" => "b_order_002", "debug" => "", "level" => "error", "desc" => "订单没有对应的商品项", "info" => "" ),
    "order_id_not_null" => array( "no" => "b_order_003", "debug" => "", "level" => "error", "desc" => "订单ID不能为空", "info" => "" ),
    "dealer_id_not_null" => array( "no" => "b_order_004", "debug" => "", "level" => "error", "desc" => "经销商ID不能为空", "info" => "" ),
    "dealer_order_id_not_null" => array( "no" => "b_order_005", "debug" => "", "level" => "error", "desc" => "经销商订单ID不能为空", "info" => "" ),
    "tax_company_must_tax" => array( "no" => "b_order_006", "debug" => "", "level" => "error", "desc" => "如果要开票,必须要交税", "info" => "" ),
    "order_not_valid" => array( "no" => "b_order_007", "debug" => "", "level" => "error", "desc" => "订单无效", "info" => "" ),
    "order_not_dealer" => array( "no" => "b_order_008", "debug" => "", "level" => "error", "desc" => "此订单不是经销商订单", "info" => "" ),
    "not_active" => array( "no" => "b_order_009", "debug" => "", "level" => "error", "desc" => "订单状态未激活", "info" => "" ),
    "not_pay" => array( "no" => "b_order_010", "debug" => "", "level" => "error", "desc" => "订单未支付", "info" => "" ),
    "already_full_refund" => array( "no" => "b_order_011", "debug" => "", "level" => "error", "desc" => "订单已全额退款", "info" => "" ),
    "already_pay" => array( "no" => "b_order_012", "debug" => "", "level" => "error", "desc" => "订单已支付", "info" => "" ),
    "go_process" => array( "no" => "b_order_013", "debug" => "", "level" => "error", "desc" => "订单支付中", "info" => "" ),
    "already_part_refund" => array( "no" => "b_order_014", "debug" => "", "level" => "error", "desc" => "订单已部分退款", "info" => "" ),
    "no_full_pay" => array( "no" => "b_order_015", "debug" => "", "level" => "error", "desc" => "订单未完成支付", "info" => "" ),
    "already_shipping" => array( "no" => "b_order_016", "debug" => "", "level" => "error", "desc" => "订单已配送", "info" => "" ),
    "not_shipping" => array( "no" => "b_order_017", "debug" => "", "level" => "error", "desc" => "订单未配送", "info" => "" ),
    "must_not_shipping" => array( "no" => "b_order_018", "debug" => "", "level" => "error", "desc" => "订单必须未配送", "info" => "" ),
    "must_not_pay" => array( "no" => "b_order_019", "debug" => "", "level" => "error", "desc" => "订单必须未支付", "info" => "" ),
    "is_dead" => array( "no" => "b_order_020", "debug" => "", "level" => "error", "desc" => "此订单是死单", "info" => "" ),
    "must_not_pending" => array( "no" => "b_order_021", "debug" => "", "level" => "error", "desc" => "订单必须是暂停发货", "info" => "" ),
    "order_exist" => array( "no" => "b_order_022", "debug" => "", "level" => "error", "desc" => "订单已经存在", "info" => "" ),
    "payment_is_out_of_order_amount" => array( "no" => "b_order_023", "debug" => "", "level" => "error", "desc" => "支付金额已经超过订单总金额", "info" => "" ),
    "order_not_payed" => array( "no" => "b_order_024", "debug" => "", "level" => "error", "desc" => "此订单不需要支付", "info" => "" )
);
return $app_error;
?>
