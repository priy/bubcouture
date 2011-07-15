<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_paypal_cn extends paymentPlugin
{

    public $name = "PayPal 贝宝- 人民币支付";
    public $logo = "PAYPAL_CN";
    public $version = 20070902;
    public $charset = "GB2312";
    public $applyUrl = "";
    public $submitUrl = "https://www.paypal.com/cn/cgi-bin/webscr";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "CNY"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "PayPal贝宝 是 PayPal 支付平台的人民币业务品牌，客户通过 PayPal 贝宝可简单快捷地进行支付，款项实时到帐，推荐使用。<br><a href='http://www.paypal.com/cn/' target='_blank'><img src='images/apply-imm.gif' border='0' align=right></a> ";
    public $intro = "PayPal贝宝 是 PayPal 支付平台的人民币业务品牌，客户通过 PayPal 贝宝可简单快捷地进行支付，款项实时到帐，推荐使用。<br><a href='http://www.paypal.com/cn/' target='_blank'><img src='images/apply-imm.gif' border='0' align=right></a> ";
    public $orderby = 36;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $return['cmd'] = "_xclick";
        $return['business'] = $merId;
        $return['item_name'] = $payment['M_OrderNO'];
        $return['item_number'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['currency_code'] = $payment['M_Currency'];
        $return['bn'] = "shopex";
        $return['return'] = $this->callbackUrl;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $req = "cmd=_notify-validate";
        foreach ( $in as $key => $value )
        {
            $value = urlencode( stripslashes( $value ) );
            $req .= "&{$key}={$value}";
        }
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length:".strlen( $req )."\r\n\r\n";
        $fp = fsockopen( "ssl://www.paypal.com", 443, $errno, $errstr, 30 );
        $item_name = $in['item_name'];
        $item_number = $in['item_number'];
        $payment_status = $in['payment_status'];
        $payment_amount = $in['mc_gross'];
        $payment_currency = $in['mc_currency'];
        $txn_id = $in['txn_id'];
        $receiver_email = $in['receiver_email'];
        $payer_email = $in['payer_email'];
        $paymentId = $item_number;
        $money = $in['mc_gross'];
        if ( !$fp )
        {
            $succ = "N";
            $errcode = "1";
        }
        else
        {
            fputs( $fp, $header.$req."\r\n\r\n" );
            while ( !feof( $fp ) )
            {
                $res = fgets( $fp, 1024 );
                $retstr .= ",".$res;
                if ( strcmp( trim( $res ), "VERIFIED" ) == 0 )
                {
                    if ( trim( $payment_status ) == "Completed" )
                    {
                        $succ = "Y";
                    }
                    else
                    {
                        $succ = "N";
                        $errcode = "2";
                    }
                }
                else if ( strcmp( $res, "INVALID" ) == 0 )
                {
                    $succ = "N";
                    $errcode = "3";
                }
            }
        }
        fclose( $fp );
        switch ( $succ )
        {
        case "Y" :
            return PAY_SUCCESS;
            break;
        case "N" :
            return PAY_ERROR;
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

}

?>
