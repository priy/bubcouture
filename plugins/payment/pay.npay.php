<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_npay extends paymentPlugin
{

    public $name = "NPAY";
    public $logo = "NPAY";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "http://www.npay.com.cn/4.0/bank.shtml";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "CNY"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "";
    public $orderby = 21;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $md5string = md5( $merId.$payment['M_OrderId'].$payment['M_Amount'].$payment['R_Email'].$payment['R_Telephone'].$ikey );
        $md5string = strtoupper( $md5string );
        $return['v_mid'] = $merId;
        $return['v_oid'] = $payment['M_OrderId'];
        $return['v_amount'] = $payment['M_Amount'];
        $return['v_email'] = $payment['R_Email'];
        $return['v_mobile'] = $payment['R_Telephone'];
        $return['v_md5'] = $md5string;
        $return['v_url'] = $this->callbackUrl;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $v_mid = trim( $in['v_mid'] );
        $v_oid = trim( $in['v_oid'] );
        $v_amount = trim( $in['v_amount'] );
        $v_date = trim( $in['v_date'] );
        $v_status = trim( $in['v_status'] );
        $v_md5info = strtoupper( trim( $in['v_md5'] ) );
        $paymentId = $v_oid;
        $money = $v_amount;
        $ikey = $this->getConf( $v_oid, "PrivateKey" );
        $content = $v_date.$v_mid.$v_oid.$v_amount.$v_status.$ikey;
        $md5string = strtoupper( md5( $content ) );
        if ( $v_md5info != $md5string )
        {
            $message = "签名认证失败,请立即与商店管理员联系";
            return PAY_ERROR;
        }
        else if ( $v_status == "00" )
        {
            return PAY_SUCCESS;
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
