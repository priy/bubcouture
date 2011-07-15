<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_2checkout extends paymentPlugin
{

    public $name = "2CHECKOUT";
    public $logo = "2CHECKOUT";
    public $version = 20070902;
    public $charset = "utf-8";
    public $submitUrl = "https://www.2checkout.com/2co/buyer/purchase";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "AUD" => "AUD",
        "CAD" => "CAD",
        "EUR" => "EUR",
        "GBP" => "GBP",
        "HKD" => "HKD",
        "JPY" => "JPY",
        "KRW" => "KRW",
        "SGD" => "SGD",
        "USD" => "USD"
    );
    public $supportArea = array
    (
        0 => "AREA_AUD",
        1 => "AREA_CAD",
        2 => "AREA_EUR",
        3 => "AREA_GBP",
        4 => "AREA_HKD",
        5 => "AREA_JPY",
        6 => "AREA_KRW",
        7 => "AREA_SGD",
        8 => "AREA_USD"
    );
    public $desc = "2Checkout.com";
    public $orderby = 40;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $tmp_url = $this->callbackUrl;
        $return['sid'] = $merId;
        $return['cart_order_id'] = $payment['M_OrderId'];
        $return['quantity'] = "1";
        $return['invoice_num'] = $payment['M_OrderNO'];
        $return['total'] = $payment['M_Amount'];
        $return['card_holder_name'] = $payment['R_Email'];
        $return['lang'] = "en";
        $return['email'] = $payment['R_Email'];
        $return['return_url'] = $tmp_url;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $orderid = $in['order_number'];
        $mer_id = $in['card_holder_name'];
        $credit_card_processed = $in['credit_card_processed'];
        $amount = $in['product_id'];
        $product_id = $in['total'];
        $md5sig = $in['key'];
        $paymentId = $orderid;
        $money = $amount;
        $key = $this->getConf( $orderid, "PrivateKey" );
        $text = $key.$mer_id.$orderid.$amount;
        $md5digest = strtoupper( md5( $text ) );
        if ( $md5digest == $md5sig )
        {
            if ( $Order->paycur == $currency && $Order->paymoney <= $amount )
            {
                return PAY_SUCCESS;
            }
            else
            {
                $message = "更新数据库，支付失败。";
                return PAY_FAILED;
            }
        }
        else
        {
            $message = "支付信息不正确，可能被篡改。";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

}

?>
