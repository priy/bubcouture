<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_egold extends paymentPlugin
{

    public $name = "EGOLD";
    public $logo = "EGOLD";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://www.e-gold.com/sci_asp/payments.asp";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "USD" => "USD",
        "EUR" => "EUR",
        "GBP" => "GBP",
        "CAD" => "CAD",
        "AUD" => "AUD",
        "JPY" => "JPY"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_EUR",
        2 => "AREA_GBP",
        3 => "AREA_CAD",
        4 => "AREA_AUD",
        5 => "AREA_AUD",
        6 => "AREA_JPY"
    );
    public $desc = "";
    public $orderby = 25;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $return['PAYMENT_METAL_ID'] = "1";
        $return['PAYMENT_ID'] = $payment['M_OrderId'];
        $return['PAYEE_ACCOUNT'] = $merId;
        $return['PAYEE_NAME'] = $_SERVER['HTTP_HOST'];
        $return['PAYMENT_AMOUNT'] = $payment['M_Amount'];
        $return['PAYMENT_UNITS'] = "1";
        $return['PAYMENT_URL'] = $this->callbackUrl;
        $return['PAYMENT_URL_METHOD'] = "POST";
        $return['NOPAYMENT_URL'] = $this->getConf( "system.shopurl" );
        $return['NOPAYMENT_URL_METHOD'] = "POST";
        $return['BAGGAGE_FIELDS'] = "";
        $return['PRODUCTNAME'] = "";
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $v_mid = trim( $in['PAYEE_ACCOUNT'] );
        $v_oid = trim( $in['PAYMENT_ID'] );
        $v_amount = trim( $in['PAYMENT_AMOUNT'] );
        $paymentId = $v_oid;
        $money = $v_amount;
        $message = "";
        return PAY_SUCCESS;
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" )
        );
    }

}

?>
