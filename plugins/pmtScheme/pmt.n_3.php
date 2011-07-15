<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class pmt_n_3
{

    public $name = "促销活动规则--顾客购买指定的商品，可获得翻倍积分或者x倍积分";
    public $memo = "顾客购买指定的商品，可获得翻倍积分或者x倍积分";
    public $pmts_solution = array
    (
        "type" => "goods",
        "condition" => array
        (
            0 => array
            (
                0 => "mLev"
            )
        ),
        "method" => array
        (
            0 => array
            (
                0 => "moreScore"
            )
        )
    );
    public $pmts_type = PMT_SCHEME_PROMOTION;

}

?>
