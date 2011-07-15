<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_wangyin extends paymentPlugin
{

    public $name = "网银在线支付（内卡）";
    public $logo = "WANGYIN";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://pay3.chinabank.com.cn/PayGate";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "0"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "网银网上支付是独立的安全支付平台，为商户提供便捷、安全、稳定的电子商务支付解决方案。点击本链接申请即可获得网银在线网关特别优惠价:年服务费全免，手续费1％！";
    public $orderby = 14;
    public $head_charset = "gb2312";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $charset = $this->system->loadModel( "utility/charset" );
        if ( $payment['M_Currency'] == "" )
        {
            $payment['M_Currency'] = "0";
        }
        $v_md5info = strtoupper( md5( trim( number_format( $payment['M_Amount'], 2, ".", "" ) ).trim( $payment['M_Currency'] ).trim( $payment['M_OrderId'] ).trim( $merId ).trim( $this->callbackUrl ).trim( $ikey ) ) );
        $payment['P_Name'] = $payment['P_Name'] ? $payment['P_Name'] : $payment['R_Name'];
        $return['v_mid'] = $merId;
        $return['v_oid'] = $payment['M_OrderId'];
        $return['v_amount'] = number_format( $payment['M_Amount'], 2, ".", "" );
        $return['v_moneytype'] = $payment['M_Currency'];
        $return['v_url'] = $this->callbackUrl;
        $return['v_md5info'] = $v_md5info;
        $return['remark1'] = $payment['M_Remark'];
        $return['remark2'] = $payment['R_Address'];
        $return['v_rcvname'] = $payment['R_Name'];
        $return['v_rcvaddr'] = $payment['R_Address'];
        $return['v_rcvtel'] = $payment['R_Telephone'];
        $return['v_rcvpost'] = $payment['R_Postcode'];
        $return['v_rcvemail'] = $payment['R_Email'];
        $return['v_rcvmobile'] = $payment['R_Mobile'];
        $return['v_ordername'] = $payment['P_Name'];
        $return['v_orderaddr'] = $payment['P_Address'];
        $return['v_ordertel'] = $payment['P_Telephone'];
        $return['v_orderpost'] = $payment['P_PostCode'];
        $return['v_orderemail'] = $payment['P_Email'];
        $return['v_ordermobile'] = $payment['P_Mobile'];
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $ikey = $this->getConf( $in['v_oid'], "PrivateKey" );
        $v_oid = trim( $in['v_oid'] );
        $v_amount = trim( $in['v_amount'] );
        $v_pmode = trim( $in['v_pmode'] );
        $v_pstatus = trim( $in['v_pstatus'] );
        $v_pstring = trim( $in['v_pstring'] );
        $v_moneytype = trim( $in['v_moneytype'] );
        $v_md5info = trim( $in['v_md5str'] );
        $paymentId = trim( $in['v_oid'] );
        $money = $v_amount;
        $content = $v_oid.$v_pstatus.$v_amount.$v_moneytype.$ikey;
        $md5string = strtoupper( md5( $content ) );
        if ( $v_md5info != $md5string )
        {
            $message = "验证失败";
            return PAY_ERROR;
        }
        else if ( $v_pstatus == "20" )
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
