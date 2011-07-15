<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_deposit extends paymentPlugin
{

    public $name = "预存款支付";
    public $logo = "";
    public $version = 20080520;
    public $charset = "utf-8";
    public $applyUrl = "";
    public $submitUrl = "./plugins/app/pay_deposit/pay_deposit.php";
    public $submitButton = "";
    public $supportCurrency = array
    (
        "DEFAULT" => "1"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "预存款支付";
    public $orderby = 7;

    public function toSubmit( $payment )
    {
        $text = "orderid=".$payment['M_OrderId']."&amount=".$payment['M_Amount']."&currency=".$payment['M_Currency']."&merchant_url=".$this->callbackUrl."&merchant_key=".$payment['K_key'];
        $mac = strtoupper( md5( $text ) );
        $return['orderid'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['merchant_url'] = $this->callbackUrl;
        $return['currency'] = $payment['M_Currency'];
        $return['mac'] = $mac;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $orderid = trim( $in['orderid'] );
        $amount = trim( $in['amount'] );
        $currency = trim( $in['currency'] );
        $merchant_url = trim( $in['merchant_url'] );
        $mymac = trim( $in['mac'] );
        $paymentId = $orderid;
        $money = $amount;
        $key = $this->system->getConf( "certificate.token" );
        $text = "orderid=".$orderid."&amount=".$amount."&currency=".$currency."&merchant_url=".$merchant_url."&merchant_key=".$key;
        $mac = strtoupper( md5( $text ) );
        if ( strtoupper( $mac ) == strtoupper( $mymac ) )
        {
            return PAY_SUCCESS;
        }
        else
        {
            $message = "支付验证失败";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array( );
    }

}

?>
