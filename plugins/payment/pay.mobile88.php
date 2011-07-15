<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_mobile88 extends paymentPlugin
{

    public $name = "MOBILE88";
    public $logo = "MOBILE88";
    public $version = 20070902;
    public $charset = "utf-8";
    public $submitUrl = "https://www.mobile88.com/epayment/entry.asp";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "MYR" => "MYR"
    );
    public $supportArea = array
    (
        0 => "AREA_MYR"
    );
    public $desc = "www.mobile88.com";
    public $orderby = 43;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $ordAmount = number_format( $this->M_Amount, 2, ".", "" );
        $tmpOrdAmount = str_replace( ".", "", $ordAmount );
        $sha1 = $this->system->loadModel( "utility/sha1" );
        $Signature = base64_encode( $sha1->sha1( $ikey.$merId.$payment['M_OrderId'].$tmpOrdAmount.$payment['M_Currency'], TRUE ) );
        $return['MerchantCode'] = $merId;
        $return['RefNo'] = $payment['M_OrderId'];
        $return['PaymentId'] = "2";
        $return['Amount'] = $ordAmount;
        $return['Currency'] = $payment['M_Currency'];
        $return['ProdDesc'] = $payment['M_OrderNO'];
        $return['UserName'] = $payment['R_Name'];
        $return['UserEmail'] = $payment['R_Email'];
        $return['UserContact'] = $payment['R_Address'];
        $return['Remark'] = "";
        $return['Signature'] = $Signature;
        $return['return_url'] = $this->callbackUrl;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $MerchantCode = trim( $in['MerchantCode'] );
        $PaymentId = trim( $in['PaymentId'] );
        $orderid = trim( $in['RefNo'] );
        $amount = trim( $in['Amount'] );
        $Currency = trim( $in['Currency'] );
        $TransId = trim( $in['TransId'] );
        $AuthCode = trim( $in['AuthCode'] );
        $succeed = trim( $in['Status'] );
        $ErrDesc = trim( $in['ErrDesc'] );
        $Signature = trim( $in['Signature'] );
        $paymentId = $orderid;
        $money = $amount;
        $key = $this->getConf( $orderid, "PrivateKey" );
        $sha1 = $this->system->loadModel( "utility/sha1" );
        $text = $key.$MerchantCode.$orderid.$amount.$Currency;
        $mac = base64_encode( $sha1->sha1( $text, TRUE ) );
        if ( strtoupper( $mac ) == strtoupper( $Signature ) )
        {
            switch ( $succeed )
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
                $message = "支付信息不正确，可能被篡改。";
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

}

?>
