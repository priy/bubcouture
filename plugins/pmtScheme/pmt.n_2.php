<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class pmt_n_2
{

    public $name = "促销活动规则--商品直接打折，如全场女鞋8折。可以对商品任意折扣，适合低价清货促销";
    public $memo = "商品直接打折，如全场女鞋8折。可以对商品任意折扣，适合低价清货促销";
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
                0 => "discount"
            )
        )
    );
    public $pmts_type = PMT_SCHEME_PROMOTION;

}

?>
