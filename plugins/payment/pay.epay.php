<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_epay extends paymentPlugin
{

    public $name = "EPAY网上支付";
    public $logo = "EPAY";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "http://www.ipost.cn/pay/pay.aspx";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "TWD" => "TWD"
    );
    public $supportArea = array
    (
        0 => "AREA_TWD"
    );
    public $desc = "";
    public $orderby = 24;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $payment['M_Amount'] *= 100;
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $lnkStr = trim( $merId ).":".trim( $this->getConf( $payment['M_OrderId'], "SecondPrivateKey" ) ).":".trim( $payment['M_OrderId'] ).":".trim( $payment['M_Amount'] ).":".trim( $this->getConf( "PrivateKey" ) );
        $strCountSignature = md5( $lnkStr );
        $return['epayClientMerchID'] = $merId;
        $return['epayClientMerchPwd'] = $this->getConf( $payment['M_OrderId'], "SecondPrivateKey" );
        $return['epayClientOrderNum'] = $payment['M_OrderId'];
        $return['epayClientOrderAmount'] = $payment['M_Amount'];
        $return['signature'] = $strCountSignature;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $v_merid = trim( $in['epayClientMerchID'] );
        $v_merpwd = trim( $in['epayClientMerchPwd'] );
        $v_orderid = trim( $in['epayClientOrderNum'] );
        $v_status = trim( $in['epayClientOrderTranStatus'] );
        $v_pmd5 = trim( $in['signature'] );
        $paymentId = $v_orderid;
        $money = "";
        $lnkStr = trim( $v_merid ).":".trim( $v_merpwd ).":".trim( $v_orderid ).":".trim( $v_status ).":".trim( $ikey );
        if ( $v_pmd5 == md5( $lnkStr ) )
        {
            if ( $v_status == "Y" )
            {
                return PAY_SUCCESS;
            }
            else
            {
                $message = "交易失败";
                return PAY_FAILED;
            }
        }
        else
        {
            $message = "验证失败";
            return PAY_ERROR;
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
