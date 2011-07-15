<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_paypal extends paymentPlugin
{

    public $name = "PayPal";
    public $logo = "PAYPAL";
    public $version = 20070902;
    public $charset = "UTF-8";
    public $applyUrl = "https://www.paypal.com/row/mrb/pal=XE8XBENY4W9RY";
    public $submitUrl = "https://www.paypal.com/cgi-bin/webscr";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "USD" => "USD",
        "CAD" => "CAD",
        "EUR" => "EUR",
        "GBP" => "GBP",
        "JPY" => "JPY",
        "AUD" => "AUD",
        "NZD" => "NZD",
        "CHF" => "CHF",
        "HKD" => "HKD",
        "SGD" => "SGD",
        "SEK" => "SEK",
        "DKK" => "DKK",
        "PLZ" => "PLZ",
        "NOK" => "NOK",
        "HUF" => "HUF",
        "CSK" => "CSK"
    );
    public $supportArea = array
    (
        0 => "AREA_USD",
        1 => "AREA_CAD",
        2 => "AREA_EUR",
        3 => "AREA_GBP",
        4 => "AREA_JPY",
        5 => "AREA_AUD",
        6 => "AREA_NZD",
        7 => "AREA_CHF",
        8 => "AREA_HKD",
        9 => "AREA_SGD",
        10 => "AREA_SEK",
        11 => "AREA_DKK",
        12 => "AREA_PLZ",
        13 => "AREA_NOK",
        14 => "AREA_HUF",
        15 => "AREA_CSK"
    );
    public $desc = "PayPal 是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准，在全球 103个国家和地区支持多达 16种外币，并拥有 1亿 3千万的客户资源，支持流行的国际信用卡支付。外贸网站首选。<br><a href='https://www.paypal.com/row/mrb/pal=J7QXH9YWP2YV4' target='_blank'><img src='images/apply-imm.gif' border='0' align=right></a> ";
    public $intro = "PayPal 是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准，在全球 103个国家和地区支持多达 16种外币，并拥有 1亿 3千万的客户资源，支持流行的国际信用卡支付。外贸网站首选。<br><font color='red'>本接口需点击【立即申请PAYPAL】链接进行在线签约后方可使用。</font> ";
    public $applyProp = array
    (
        "postmethod" => "POST"
    );
    public $orderby = 20;
    public $cur_trading = TRUE;
    public $head_charset = "utf-8";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $return['cmd'] = "_xclick";
        $return['business'] = $merId;
        $return['item_name'] = "Payment:".$payment['M_OrderNO'];
        $return['item_number'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['currency_code'] = $payment['M_Currency'];
        $return['return'] = $this->callbackUrl;
        $return['notify_url'] = $this->serverCallbackUrl;
        $return['lc'] = "US";
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $paymentId = $in['item_number'];
        $money = $in['amt'];
        if ( $in['st'] == "Pending" )
        {
            $succ = "Y";
        }
        else
        {
            $succ = "N";
        }
        switch ( $succ )
        {
        case "Y" :
            return PAY_SUCCESS;
            break;
        case "N" :
            $message = "支付失败,请立即与商店管理员联系(".$errcode.")";
            return PAY_FAILED;
            break;
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
        $tmp_form .= "<a href=\"javascript:void(0)\" onclick=\"document.applyForm.submit()\">立即申请PAYPAL</a>";
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
