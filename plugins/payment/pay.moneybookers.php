<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_moneybookers extends paymentPlugin
{

    public $name = "MONEYBOOKERS";
    public $logo = "MONEYBOOKERS";
    public $version = 20070902;
    public $charset = "utf-8";
    public $submitUrl = "https://www.moneybookers.com/app/payment.pl";
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
        "TWD" => "TWD",
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
        7 => "AREA_TWD",
        8 => "AREA_SGD",
        9 => "AREA_USD"
    );
    public $orderby = 24;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $return['pay_to_email'] = $merId;
        $return['transaction_id'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['currency'] = $payment['M_Currency'];
        $return['pay_from_email'] = $payment['R_Email'];
        $return['language'] = "en";
        $return['detail1_description'] = $payment['M_OrderNO'];
        $return['detail1_text'] = $payment['M_OrderNO'];
        $return['address'] = $payment['R_Address'];
        $return['postal_code'] = $payment['R_PostCode'];
        $return['firstname'] = $payment['R_Name'];
        $return['confirmation_note'] = $payment['M_Remark'];
        $return['status_url'] = $this->callbackUrl;
        $return['return_url'] = $this->callbackUrl;
        $return['cancel_url'] = $this->callbackUrl;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $mer_email = $in['pay_to_email'];
        $cus_email = $in['pay_from_email'];
        $mer_id = $in['merchant_id'];
        $orderid = $in['transaction_id'];
        $mb_orderid = $in['mb_transaction_id'];
        $mb_amount = $in['mb_amount'];
        $mb_currency = $in['mb_currency'];
        $amount = $in['amount'];
        $currency = $in['currency'];
        $Status = $in['Status'];
        $paymentId = $orderid;
        $money = $amount;
        $signMsg = $in['md5sig'];
        $key = $this->getConf( $orderid, "PrivateKey" );
        $text = $mer_id.$orderid.$key.$mb_amount.$mb_currency.$Status;
        $md5digest = strtoupper( md5( $text ) );
        if ( $md5digest == $signMsg )
        {
            return PAY_SUCCESS;
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
