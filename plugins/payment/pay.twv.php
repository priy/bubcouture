<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_twv extends paymentPlugin
{

    public $name = "台湾里网上支付";
    public $logo = "TWV";
    public $version = 20070902;
    public $charset = "big5";
    public $submitUrl = "https://www.twv.com.tw/openpay/pay.php";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "TWD" => "TWD"
    );
    public $supportArea = array
    (
        0 => "AREA_TWD"
    );
    public $desc = "台湾里网上支付";
    public $orderby = 37;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $payment['M_Language'] = "tchinese";
        $payment['M_Amount'] = floor( $payment['M_Amount'] );
        $verify = md5( $ikey."|".$merId."|".$payment['M_OrderId']."|".$payment['M_Amount']."|".$this->getConf( $payment['M_OrderId'], "SecondPrivateKey" ) );
        $return['mid'] = $merId;
        $return['ordernum'] = $payment['M_OrderId'];
        $return['txid'] = $payment['M_OrderId'];
        $return['iid'] = "0";
        $return['amount'] = $payment['M_Amount'];
        $return['cname'] = $payment['R_Name'];
        $return['caddress'] = $payment['R_Address'];
        $return['language'] = $payment['M_Language'];
        $return['version'] = "1.0";
        $return['return_url'] = $this->callbackUrl;
        $return['verify'] = $verify;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $merid = $in['merid'];
        $payid = $in['txid'];
        $amount = $in['amount'];
        $succ = $in['status'];
        $ordid = $in['tid'];
        $pay_type = $in['pay_type'];
        $error_code = $in['error_code'];
        $msg = $in['error_desc'];
        $md5string = $in['verify'];
        $paymentId = $payid;
        $money = $amount;
        $md5key = $this->getConf( $payid, "PrivateKey" );
        $content = "2efdd6e617bc0114866c89e911a4e3de|".$payid.$amount.$pay_type.$succ.$ordid.$PAY_KEY['TWV'];
        if ( $md5string = md5( $content ) )
        {
            switch ( $succ )
            {
            case "1" :
                return PAY_SUCCESS;
                break;
            case "2" :
                $message = "支付失败,请立即与商店管理员联系";
                return PAY_FAILED;
                break;
            case "3" :
                $message = "支付失败,请立即与商店管理员联系";
                return PAY_FAILED;
                break;
            }
            else
            {
                $message = "签名认证失败,请立即与商店管理员联系";
                return PAY_ERROR;
            }
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" ),
            "SecondPrivateKey" => array( "label" => "第二私钥", "type" => "string" )
        );
    }

}

?>
