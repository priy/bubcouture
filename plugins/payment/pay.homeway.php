<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_homeway extends paymentPlugin
{

    public $name = "和讯在线支付";
    public $logo = "HOMEWAY";
    public $version = 20070902;
    public $charset = "GB2312";
    public $submitUrl = "http://payment.homeway.com.cn/pay/pay_new.php3";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        0 => "CNY"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "和讯在线支付";
    public $orderby = 31;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $payment['M_Currency'] = "2002";
        $mer_key = "asdfghjk12345678";
        $payment['M_Amount'] *= 100;
        $info = $merId.$payment['M_Amount'].$payment['M_OrderId'].date( "Ymd", $payment['M_Time'] ).$payment['M_Currency'].$ikey;
        $msign = md5( $info );
        $return['MerchID'] = $merId;
        $return['OrderNum'] = $payment['M_OrderId'];
        $return['Amount'] = $payment['M_Amount'];
        $return['TransType'] = $payment['M_Currency'];
        $return['TransDate'] = date( "Ymd", $payment['M_Time'] );
        $return['Signature'] = $msign;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $OrderNo = $in['OrderNo'];
        $Amount = $in['Amount'];
        $TransType = $in['TransType'];
        $TransDate = $in['TransDate'];
        $Succeed = $in['Succeed'];
        $RetSign = $in['RetSign'];
        $paymentId = $OrderNo;
        $money = $Amount;
        if ( $Succeed == "Y" )
        {
            $info = $this->getConf( $OrderNo, "PrivateKey" ).$Succeed.$OrderNo.$this->getConf( "member_id" ).$Amount.$TransType.$TransDate;
            $MySign = md5( $info );
            if ( $RetSign == $MySign )
            {
                return PAY_SUCCESS;
            }
            else
            {
                $message = "验证失败";
                return PAY_ERROR;
            }
        }
        else
        {
            $message = "支付失败,请立即与商店管理员联系";
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
