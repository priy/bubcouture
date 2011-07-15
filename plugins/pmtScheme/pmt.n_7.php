<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class pmt_n_7
{

    public $name = "促销活动规则--购物车中商品总金额大于指定金额,就可立减某金额";
    public $memo = "购物车的金额大于指定金额,就可立减某金额";
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
                0 => "lessMoney"
            )
        )
    );
    public $pmts_type = PMT_SCHEME_PROMOTION;

}

?>
