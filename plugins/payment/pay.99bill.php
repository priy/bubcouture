<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_99bill extends paymentPlugin
{

    public $name = "快钱网上支付";
    public $logo = "99BILL";
    public $version = 20070902;
    public $charset = "utf8";
    public $applyUrl = "https://www.99bill.com/website/signup/memberunitedsignup.htm";
    public $submitUrl = "https://www.99bill.com/gateway/recvMerchantInfoAction.htm";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "1"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "ShopEx联合快钱推出：免费签约，1%优惠费率，更有超值优惠的信用卡支付。<bR>快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。";
    public $intro = "<b><h3>ShopEx联合快钱推出：免费签约，1%优惠费率，更有超值优惠的信用卡支付。</h3></b><bR>快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。<b><h3>注：本接口为银行直连，数据显示，可以提升78%的潜在消费者完成购买行为。</h3></b>";
    public $applyProp = array
    (
        "postmethod" => "post",
        "version" => "150120",
        "inputCharset" => 1,
        "signType" => 1,
        "merchantMbrCode" => "10017518267",
        "requestId" => "",
        "registerType" => 1,
        "userId" => "",
        "userType" => "2",
        "userEmail" => "",
        "userMobile" => "",
        "userName" => "",
        "linkMan" => "",
        "linkTel" => "",
        "orgName" => "",
        "websiteAddr" => "",
        "backUrl" => "",
        "ext1" => "",
        "ext2" => "",
        "key" => "LHLEF8EA4ZY853NF"
    );
    public $orderby = 1;
    public $head_charset = "utf-8";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $connecttype = $this->getConf( $payment['M_OrderId'], "ConnectType" );
        if ( $connecttype )
        {
            $bankId = $payment['payExtend']['bankId'];
            $payType = "10";
        }
        $payment['M_Amount'] = ceil( $payment['M_Amount'] * 100 );
        $orderTime = date( "YmdHis", $payment['M_Time'] ? $payment['M_Time'] : time( ) );
        $return['inputCharset'] = "1";
        $return['bgUrl'] = $this->callbackUrl;
        $return['version'] = "v2.0";
        $return['language'] = "1";
        $return['signType'] = "1";
        $return['merchantAcctId'] = $merId;
        $return['payerName'] = $payment['P_Name'];
        $return['payerContactType'] = "1";
        $return['payerContact'] = $payment['P_Email'];
        $return['orderId'] = $payment['M_OrderId'];
        $return['orderAmount'] = $payment['M_Amount'];
        $return['orderTime'] = $orderTime;
        $return['productName'] = $payment['M_OrderNO'];
        $return['productNum'] = "1";
        $return['productId'] = "";
        $return['productDesc'] = $payment['M_Remark'];
        $return['ext1'] = "";
        $return['ext2'] = "";
        $return['payType'] = $payType ? $payType : "00";
        $return['bankId'] = $bankId ? $bankId : "";
        $return['redoFlag'] = 1;
        $return['pid'] = "10017518267";
        foreach ( $return as $k => $v )
        {
            if ( $v )
            {
                $str .= $k."=".$v."&";
            }
        }
        $signMsg = strtoupper( md5( substr( $str, 0, strlen( $str ) - 1 )."&key=".$ikey ) );
        $return['signMsg'] = $signMsg;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        $url = $system->mkUrl( "paycenter", $act = "result" );
        $merchantAcctId = trim( $in['merchantAcctId'] );
        $version = trim( $in['version'] );
        $language = trim( $in['language'] );
        $signType = trim( $in['signType'] );
        $payType = trim( $in['payType'] );
        $orderId = trim( $in['orderId'] );
        $orderTime = trim( $in['orderTime'] );
        $bankId = trim( $in['bankId'] );
        $orderAmount = trim( $in['orderAmount'] );
        $dealId = trim( $in['dealId'] );
        $bankDealId = trim( $in['bankDealId'] );
        $dealTime = trim( $in['dealTime'] );
        $payAmount = trim( $in['payAmount'] );
        $fee = trim( $in['fee'] );
        $payResult = trim( $in['payResult'] );
        $errCode = trim( $in['errCode'] );
        $signMsg = trim( $in['signMsg'] );
        $key = $this->getConf( $orderId, "PrivateKey" );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "merchantAcctId", $merchantAcctId );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "version", $version );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "language", $language );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "signType", $signType );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "payType", $payType );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "bankId", $bankId );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "orderId", $orderId );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "orderTime", $orderTime );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "orderAmount", $orderAmount );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "dealId", $dealId );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "bankDealId", $bankDealId );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "dealTime", $dealTime );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "payAmount", $payAmount );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "fee", $fee );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "ext1", $ext1 );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "ext2", $ext2 );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "payResult", $payResult );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "errCode", $errCode );
        $merchantSignMsgVal = $this->appendParam( $merchantSignMsgVal, "key", $key );
        $merchantSignMsg = md5( $merchantSignMsgVal );
        $paymentId = $orderId;
        $money = $payAmount / 100;
        $tradeno = $dealId;
        $system =& $GLOBALS['GLOBALS']['system'];
        $url = $system->mkUrl( "paycenter", $act = "result" );
        if ( strtoupper( $signMsg ) == strtoupper( $merchantSignMsg ) )
        {
            switch ( $payResult )
            {
            case "10" :
                $rtnOk = 1;
                $rtnUrl = $url."?payment_id=".$orderId;
                echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
                return PAY_SUCCESS;
                break;
            default :
                $rtnOk = 1;
                $rtnUrl = $url."?payment_id=".$orderId;
                echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
                return PAY_FAIL;
                break;
            }
        }
        else
        {
            $message = "签名认证失败！";
            $rtnOk = 1;
            $rtnUrl = $url."?payment_id=".$orderId;
            echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" ),
            "ConnectType" => array(
                "label" => "顾客付款类型",
                "type" => "radio",
                "options" => array( "0" => "登录快钱支付", "1" => "银行直接支付" ),
                "event" => "showbank",
                "eventscripts" => "<script>function showbank(obj){if (obj.value==1){\$('bankShow').show();}else {\$('bankShow').hide();}}</script>",
                "extendcontent" => array(
                    array(
                        "property" => array( "type" => "checkbox", "name" => "bankId", "size" => 6, "extconId" => "bankShow", "display" => 0, "fronttype" => "radio", "frontsize" => 6, "frontname" => "showbank" ),
                        "value" => array(
                            array( "value" => "ICBC", "imgname" => "bank_icbc.gif", "name" => "中国工商银行" ),
                            array( "value" => "CMB", "imgname" => "bank_cmb.gif", "name" => "招商银行" ),
                            array( "value" => "ABC", "imgname" => "bank_abc.gif", "name" => "中国农业银行" ),
                            array( "value" => "CCB", "imgname" => "bank_ccb.gif", "name" => "中国建设银行" ),
                            array( "value" => "SPDB", "imgname" => "bank_spdb.gif", "name" => "上海浦东发展银行" ),
                            array( "value" => "BCOM", "imgname" => "bank_bcom.gif", "name" => "交通银行" ),
                            array( "value" => "CMBC", "imgname" => "bank_cmbc.gif", "name" => "中国民生银行" ),
                            array( "value" => "SDB", "imgname" => "bank_sdb.gif", "name" => "深圳发展银行" ),
                            array( "value" => "GDB", "imgname" => "bank_gdb.gif", "name" => "广东发展银行" ),
                            array( "value" => "CITIC", "imgname" => "bank_citic.gif", "name" => "中信银行" ),
                            array( "value" => "HXB", "imgname" => "bank_hxb.gif", "name" => "华夏银行" ),
                            array( "value" => "CIB", "imgname" => "bank_cib.gif", "name" => "兴业银行" ),
                            array( "value" => "GZRCC", "imgname" => "bank_gzrcc.gif", "name" => "广州市农村信用合作社" ),
                            array( "value" => "GZCB", "imgname" => "bank_gzcb.gif", "name" => "广州市商业银行" ),
                            array( "value" => "SHRCC", "imgname" => "bank_shrcc.gif", "name" => "上海农村商业银行" ),
                            array( "value" => "POST", "imgname" => "bank_post.gif", "name" => "中国邮政储蓄" ),
                            array( "value" => "BOB", "imgname" => "bank_bob.gif", "name" => "北京银行" ),
                            array( "value" => "BOC", "imgname" => "bank_boc.gif", "name" => "中国银行" ),
                            array( "value" => "CBHB", "imgname" => "bank_cbhb.gif", "name" => "渤海银行" ),
                            array( "value" => "BJRCB", "imgname" => "bank_bjrcb.gif", "name" => "北京农村商业银行" ),
                            array( "value" => "CEB", "imgname" => "bank_ceb.gif", "name" => "中国光大银行" ),
                            array( "value" => "NJCB", "imgname" => "bank_njcb.gif", "name" => "南京银行" ),
                            array( "value" => "BEA", "imgname" => "bank_bea.gif", "name" => "东亚银行" ),
                            array( "value" => "NBCB", "imgname" => "bank_nbcb.gif", "name" => "宁波银行" ),
                            array( "value" => "HZB", "imgname" => "bank_hzb.gif", "name" => "杭州银行" ),
                            array( "value" => "PAB", "imgname" => "bank_pab.gif", "name" => "平安银行" )
                        )
                    )
                )
            )
        );
    }

    public function appendParam( $returnStr, $paramId, $paramValue )
    {
        if ( $returnStr != "" )
        {
            if ( $paramValue != "" )
            {
                $returnStr .= "&".$paramId."=".$paramValue;
            }
        }
        else if ( $paramValue != "" )
        {
            $returnStr = $paramId."=".$paramValue;
        }
        return $returnStr;
    }

    public function applyForm( $agentfield )
    {
        $certid = $this->system->getConf( "certificate.id" );
        $url = urlencode( $this->system->base_url( ) );
        $tmp_form = "<a href=\"http://service.shopex.cn/checkcert.php?pay_id=2&certi_id=".$certid."&url=".$url."\n\" target=\"_blank\"><img src=\"".$this->system->base_url( )."plugins/payment/images/99bill_apply.gif\"></a>&nbsp;&nbsp;&nbsp;<font color=\"green\">绿卡用户申请快钱支付，费率更低，</font><a href=\"http://www.shopex.cn/shopex_price/sq/index.html\" target=\"_blank\"><font color=\"green\">点击了解绿卡</font></a>";
        return $tmp_form;
    }

}

?>
