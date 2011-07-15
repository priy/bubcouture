<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_yeepay extends paymentPlugin
{

    public $name = "易宝支付 (在线支付接口)";
    public $logo = "YEEPAY";
    public $version = 20070902;
    public $charset = "gb2312";
    public $applyUrl = "https://www.yeepay.com/selfservice/AgentService.action";
    public $submitUrl = "https://www.yeepay.com/app-merchant-proxy/node";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "01"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "";
    public $intro = "首批通过国家信息安全系统认证、获得企业信用等级AAA级证书、注册资本1亿元；<br>1%手续费、0年费、支持上百种银行卡、信用卡及神州行卡支付；<br>网上签约、轻松结算、7X24小时客户服务、共享千万优质会员资源。";
    public $applyProp = array
    (
        "postmethod" => "POST",
        "p0_Cmd" => "AgentRegister",
        "p1_MerId" => "10000456219"
    );
    public $orderby = 18;
    public $head_charset = "gb2312";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $b = 64;
        if ( $b < strlen( $ikey ) )
        {
            $ikey = pack( "H*", md5( $ikey ) );
        }
        $ikey = str_pad( $ikey, $b, chr( 0 ) );
        $ipad = str_pad( "", $b, chr( 54 ) );
        $opad = str_pad( "", $b, chr( 92 ) );
        $k_ipad = $ikey ^ $ipad;
        $k_opad = $ikey ^ $opad;
        $data = "Buy".$merId.$payment['M_OrderId'].$payment['M_Amount'].$payment['M_Currency'].$payment['M_OrderNO'].$payment['R_Email'].$this->callbackUrl."0".$merId;
        $hmac = md5( $k_opad.pack( "H*", md5( $k_ipad.$data ) ) );
        $return['p0_Cmd'] = Buy;
        $return['p1_MerId'] = $merId;
        $return['p2_Order'] = $payment['M_OrderId'];
        $return['p3_Amt'] = $payment['M_Amount'];
        $return['p4_Cur'] = $payment['M_Currency'];
        $return['p5_Pid'] = $payment['M_OrderNO'];
        $return['p6_Pcat'] = $payment['R_Email'];
        $return['p7_Pdesc'] = "";
        $return['p8_Url'] = $this->callbackUrl;
        $return['p9_SAF'] = 0;
        $return['pa_MP'] = $merId;
        $return['pd_FrpId'] = "";
        $return['hmac'] = $hmac;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $sCmd = $in['r0_Cmd'];
        $sErrorCode = $in['r1_Code'];
        $sTrxId = $in['r2_TrxId'];
        $amount = $in['r3_Amt'];
        $cur = $in['r4_Cur'];
        $productId = $in['r5_Pid'];
        $orderId = $in['r6_Order'];
        $userId = $in['r7_Uid'];
        $MP = $in['r8_MP'];
        $bType = $in['r9_BType'];
        $svrHmac = $in['hmac'];
        $money = $amount;
        $paymentId = $orderId;
        $key = $this->getConf( $orderId, "PrivateKey" );
        $data = $MP.$sCmd.$sErrorCode.$sTrxId.$amount.$cur.$productId.$orderId.$userId.$MP.$bType;
        $charset = $this->system->loadModel( "utility/charset" );
        $data = $charset->utf2local( $data, "zh" );
        $b = 64;
        if ( $b < strlen( $key ) )
        {
            $ikey = pack( "H*", md5( $key ) );
        }
        $key = str_pad( $key, $b, chr( 0 ) );
        $ipad = str_pad( "", $b, chr( 54 ) );
        $opad = str_pad( "", $b, chr( 92 ) );
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        $hmac = md5( $k_opad.pack( "H*", md5( $k_ipad.$data ) ) );
        if ( strtoupper( $sCmd ) == "BUY" && $svrHmac == $hmac )
        {
            if ( $sErrorCode == 1 )
            {
                return PAY_SUCCESS;
            }
            else
            {
                $message = "交易失败";
            }
            return PAY_FAILED;
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
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

    public function applyForm( $agentfield )
    {
        $key = "A2mo49beDQC87v94nD400439270yff1Pt212H24FI6ET68GT84HC05AtjfB7";
        $msgtype = "AgentRegister";
        $merid = "10000456219";
        $domain = "";
        $data = $msgtype.$merid.$domain;
        $agentfield['hmac'] = $this->hmac( $key, $data );
        $tmp_form .= "<a href=\"javascript:void(0)\" onclick=\"document.applyForm.submit();\">立即注册</a>";
        $tmp_form .= "<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
        foreach ( $agentfield as $key => $val )
        {
            $tmp_form .= "<input type='hidden' name='".$key."' value='".$val."'>";
        }
        $tmp_form .= "</form>";
        return $tmp_form;
    }

    public function hmac( $key, $data )
    {
        if ( function_exists( "utf2local" ) )
        {
            $key = utf2local( $key, "zh" );
            $data = utf2local( $data, "zh" );
        }
        else
        {
            $key = iconv( "GB2312", "UTF-8", $key );
            $data = iconv( "GB2312", "UTF-8", $data );
        }
        $b = 64;
        if ( $b < strlen( $key ) )
        {
            $key = pack( "H*", md5( $key ) );
        }
        $key = str_pad( $key, $b, chr( 0 ) );
        $ipad = str_pad( "", $b, chr( 54 ) );
        $opad = str_pad( "", $b, chr( 92 ) );
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5( $k_opad.pack( "H*", md5( $k_ipad.$data ) ) );
    }

}

?>
