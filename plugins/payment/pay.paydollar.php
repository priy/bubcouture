<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_paydollar extends paymentPlugin
{

    public $name = "PayDollar";
    public $logo = "PAYDOLLAR";
    public $version = 20070902;
    public $charset = "utf-8";
    public $submitUrl = "https://www.paydollar.com/b2c2/eng/payment/payForm.jsp";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "156",
        "HKD" => "344",
        "USD" => "840",
        "SGD" => "702",
        "JPY" => "392",
        "TWD" => "901",
        "AUD" => "036",
        "EUR" => "978",
        "GBP" => "826",
        "CAD" => "124"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_HKD",
        2 => "AREA_USD",
        3 => "AREA_SGD",
        4 => "AREA_JPY",
        5 => "AREA_TWD",
        6 => "AREA_AUD",
        7 => "AREA_EUR",
        8 => "AREA_GBP",
        9 => "AREA_CAD"
    );
    public $desc = "";
    public $orderby = 21;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $order->M_Language = "E";
        $tmp_url = $this->url."index.php?gOo=paydollar_reply.do&";
        $text = "merchant_id=".$merId."&orderid=".$payment['M_OrderId']."&amount=".$payment['M_Amount']."&merchant_url=".$this->callbackUrl."&merchant_key=".$ikey;
        $mac = strtoupper( md5( $text ) );
        $return['merchantId'] = $merId;
        $return['orderRef'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['currCode'] = $payment['M_Currency'];
        $return['lang'] = $payment['M_Language'];
        $return['successUrl'] = $this->callbackUrl;
        $return['failUrl'] = $this->callbackUrl;
        $return['cancelUrl'] = $this->callbackUrl;
        $return['payType'] = "N";
        $return['payMethod'] = "ALL";
        $return['remark'] = $payment['M_Remark'];
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        return PAY_SUCCESS;
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
