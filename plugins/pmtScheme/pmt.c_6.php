<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class pmt_c_6
{

    public $name = "优惠券规则--购物车中商品总金额大于指定金额，客户就可得到指定的%off折扣";
    public $memo = "购物车的金额大于指定金额，客户就可得到指定的%off折扣";
    public $pmts_solution = array
    (
        "type" => "order",
        "condition" => array
        (
            0 => array
            (
                0 => "mLev"
            ),
            1 => array
            (
                0 => "orderMoney_from"
            ),
            2 => array
            (
                0 => "orderMoney_to"
            )
        ),
        "method" => array
        (
            0 => array
            (
                0 => "discount"
            )
        )
    );
    public $pmts_type = PMT_SCHEME_COUPON;

}

?>
