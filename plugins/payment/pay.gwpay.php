<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_gwpay extends paymentPlugin
{

    public $name = "Green World Payment";
    public $logo = "GWPAY";
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
    public $orderby = 22;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        if ( $this->getConf( "system.shoplang" ) == "en_US" )
        {
            $this->submitUrl = "https://gwpay.com.tw/form_Sc_to5e.php";
        }
        else
        {
            $this->submitUrl = "https://gwpay.com.tw/form_Sc_to5.php";
        }
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $payment['M_Amount'] = floor( $payment['M_Amount'] );
        $return['act'] = "auth";
        $return['client'] = $merId;
        $return['od_sob'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['email'] = $payment['R_Email'];
        $return['roturl'] = $this->callbackUrl;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $gwsr = $in['gwsr'];
        $payid = $in['od_sob'];
        $amount = $in['amount'];
        $succ = $in['succ'];
        $process_time = $in['process_time'];
        $process_date = $in['process_date'];
        $response_code = $in['response_code'];
        $msg = $in['response_msg'];
        $od_hoho = $in['od_hoho'];
        $auth_code = $in['auth_code'];
        $eci = $in['eci'];
        $paymentId = $payid;
        $money = $amount;
        $loginName = $this->getConf( $payid, "PrivateKey" );
        $s = $in['gwsr'];
        $s .= $in['response_code'];
        $s .= $in['process_time'];
        $s .= $in['amount'];
        $s .= $in['od_sob'];
        $s .= $in['auth_code'];
        $ret = $this->isRightPacket( $loginName, $s, $in['inspect'] );
        if ( $ret == TRUE )
        {
            switch ( $succ )
            {
            case "1" :
                return PAY_SUCCESS;
                break;
            case "0" :
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
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

    public function isRightPacket( $loginName, $s, $insp )
    {
        $s1 = md5( $s );
        $s2 = md5( $loginName );
        $s3 = md5( $s1 ^ $s2 );
        if ( $insp == $s3 )
        {
            return TRUE;
        }
        return FALSE;
    }

}

?>
