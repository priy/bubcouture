<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_ips3 extends paymentPlugin
{

    public $name = "环讯IPS网上支付3.0";
    public $logo = "IPS3";
    public $version = 20070615;
    public $charset = "gb2312";
    public $submitUrl = "http://pay.ips.com.cn/ipayment.aspx";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "RMB",
        "USD" => "02"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_USD"
    );
    public $desc = "";
    public $M_Language = "1";
    public $orderby = 10;
    public $head_charset = "gb2312";
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merid = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $tmp_url = $this->callbackUrl;
        $tmp_urlserver = $this->serverCallbackUrl;
        $orderdate = date( "Ymd", $payment['M_Time'] );
        $billNo = $merid.$payment['M_OrderId'];
        $cur = $this->system->loadModel( "system/cur" );
        $payment['M_Amount'] = $cur->get_cur_money( $payment['M_Amount'], $payment['M_Currency'] );
        $tmpAmount = $payment['M_Amount'] / $cur->_in_cur['cur_rate'];
        if ( 1 <= $tmpAmount )
        {
            $tmpAmount = intval( $tmpAmount );
        }
        $payment['M_Amount'] = number_format( $tmpAmount, 2, ".", "" );
        $StrMd5 = md5( $billNo.$payment['M_Amount'].$orderdate.$payment['M_Currency'].$ikey );
        $return['Mer_code'] = $merid;
        $return['Billno'] = $payment['M_OrderId'];
        $return['Amount'] = $payment['M_Amount'];
        $return['Date'] = $orderdate;
        $return['Currency_Type'] = $payment['M_Currency'];
        $return['Gateway_Type'] = "01";
        $return['Lang'] = "GB";
        $return['Merchanturl'] = $tmp_url;
        $return['FailUrl'] = $tmp_url;
        $return['DispAmount'] = "";
        $return['OrderEncodeType'] = "1";
        $return['RetEncodeType'] = "12";
        $return['Rettype'] = "1";
        $return['ServerUrl'] = $tmp_urlserver;
        $return['SignMD5'] = $StrMd5;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $billno = $in['billno'];
        $amount = $in['amount'];
        $mydate = $in['date'];
        $succ = $in['succ'];
        $msg = $in['msg'];
        $attach = $in['attach'];
        $ipsbillno = $in['ipsbillno'];
        $retEncodeType = $in['retencodetype'];
        $currency_type = $in['Currency_type'];
        $signature = $in['signature'];
        $paymentId = $billno;
        $money = $amount;
        $orderId = $billno;
        $tradeno = $in['ipsbillno'];
        $payment = $this->system->loadModel( "trading/payment" );
        $prow = $payment->getById( $paymentId );
        $cur = $this->system->loadModel( "system/cur" );
        $money = $cur->get_cur_money( $money, $prow['currency'] );
        if ( $succ == "Y" )
        {
            $content = $billno.$amount.$mydate.$succ.$ipsbillno.$currency_type;
            $cert = $this->getConf( $billno, "PrivateKey" );
            if ( $content == "" || $cert == "" )
            {
                $signature1 = "";
            }
            else
            {
                $signature_1ocal = md5( $content.$cert );
            }
            if ( $signature_1ocal == $signature )
            {
                return PAY_SUCCESS;
            }
            else
            {
                $message = "交易异常，Md5摘要认证错误";
                return PAY_ERROR;
            }
        }
        else
        {
            $message = "交易失败";
            return PAY_FAILED;
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
