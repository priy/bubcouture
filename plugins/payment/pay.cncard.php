<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_cncard extends paymentPlugin
{

    public $name = "云网在线支付";
    public $logo = "CNCARD";
    public $version = 20070902;
    public $charset = "gb2312";
    public $applyUrl = "http://www.cncard.net/api/agentreg.asp";
    public $submitUrl = "https://www.cncard.net/purchase/getorder.asp";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "CNY"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $intro = "";
    public $applyProp = array
    (
        "postmethod" => "get",
        "aid" => "10054",
        "sign" => "79cccba5af191e88fb9edd3949796053"
    );
    public $orderby = 9;
    public $head_charset = "gb2312";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $payment['M_Currency'] = "0";
        $orderdate = date( "Ymd", $payment['M_Time'] );
        $md5string = md5( $merId.$payment['M_OrderId'].$payment['M_Amount'].$orderdate."0"."1".$this->callbackUrl."0"."0".$ikey );
        $return['c_mid'] = $merId;
        $return['c_order'] = $payment['M_OrderId'];
        $charset = $this->system->loadModel( "utility/charset" );
        $return['c_name'] = $payment['R_Name'];
        $return['c_address'] = $payment['R_Address'];
        $return['c_tel'] = $payment['R_Telephone'];
        $return['c_post'] = $payment['R_Postcode'];
        $return['c_email'] = $payment['R_Email'];
        $return['c_orderamount'] = $payment['M_Amount'];
        $return['c_ymd'] = $orderdate;
        $return['c_moneytype'] = $payment['M_Currency'];
        $return['c_retflag'] = "1";
        $return['c_returl'] = $this->callbackUrl;
        $return['c_language'] = "0";
        $return['notifytype'] = "0";
        $return['c_signstr'] = $md5string;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $c_order = $in['c_order'];
        $c_orderamount = $in['c_orderamount'];
        $c_succmark = $in['c_succmark'];
        $c_cause = $in['c_cause'];
        $c_signstr = $in['c_signstr'];
        $tradeno = $in['c_transnum'];
        $ikey = $this->getConf( $c_order, "PrivateKey" );
        $content = md5( $in['c_mid'].$in['c_order'].$in['c_orderamount'].$in['c_ymd'].$in['c_transnum'].$in['c_succmark'].$in['c_moneytype'].$in['c_memo1'].$in['c_memo2'].$ikey );
        $paymentId = $c_order;
        $money = $c_orderamount;
        if ( $c_signstr != $content )
        {
            $message = "签名认证失败,请立即与商店管理员联系";
            return PAY_ERROR;
        }
        else if ( $c_succmark == "Y" )
        {
            $message = "支付成功";
            return PAY_SUCCESS;
        }
        else
        {
            $message = "支付失败,请立即与商店管理员联系"."({$c_cause})";
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

    public function applyForm( $agentfield )
    {
        $tmp_form .= "<a href=\"javascript:void(0)\" onclick=\"document.applyForm.submit()\">立即注册</a>";
        $tmp_form .= "<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
        foreach ( $agentfield as $key => $val )
        {
            $tmp_form .= "<input type='hidden' name='".$key."' value='".$val."'>";
        }
        $tmp_form .= "</form>";
        return $tmp_form;
    }

}

?>
