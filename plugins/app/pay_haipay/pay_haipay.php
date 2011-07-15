<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_haipay extends paymentPlugin
{

    public $name = "运筹宝";
    public $logo = "HAIPAY";
    public $version = 20070615;
    public $charset = "gb2312";
    public $applyUrl = "http://www.haipay.com/shopexreg.aspx";
    public $submitUrl = "https://member.happycz.com/deposit/PaymentSend.aspx";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "RMB",
        "USD" => "02"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_USD"
    );
    public $desc = "运筹宝为广大SHOPEX商家推出至尊优惠、便捷申请活动：无预付，单笔费率最低1%，09年11月2日到至10年11月2日期间申请的用户，还将获得最高抵扣200元交易费的优惠。<a href=\"http://www.haipay.com/merchant_coupan_help.aspx\" target=\"_blank\">新开商户抵扣交易手续费操作流程</a><br/>\n为让用户更快体验运筹宝安全、快捷的支付、收款服务，特为SHOPEX商户开通快捷通道，您只需提供联系方式即能得到商户号，开通支付功能再完成注册，运筹宝：方便、省心、安全、便捷的在线金流服务。<a href=\"http://www.haipay.com/register_help.aspx\" target=\"_blank\">详细情况请参考本文档</a><br/>\nShopEx为方便商户使用，接入该支付工具，但对商户因该支付工具产生的损失与扣费概不\n负责。";
    public $intro = "运筹宝为广大SHOPEX商家推出至尊优惠、便捷申请活动：无预付，单笔费率最低1%，09年11月2日到至10年11月2日期间申请的用户，还将获得最高抵扣200元交易费的优惠。<a href=\"http://www.haipay.com/merchant_coupan_help.aspx\" target=\"_blank\">新开商户抵扣交易手续费操作流程</a><br/>\n为让用户更快体验运筹宝安全、快捷的支付、收款服务，特为SHOPEX商户开通快捷通道，您只需提供联系方式即能得到商户号，开通支付功能再完成注册，运筹宝：方便、省心、安全、便捷的在线金流服务。<a href=\"http://www.haipay.com/register_help.aspx\" target=\"_blank\">详细情况请参考本文档</a><br/>\nShopEx为方便商户使用，接入该支付工具，但对商户因该支付工具产生的损失与扣费概不\n负责。";
    public $M_Language = "1";
    public $orderby = 29;
    public $head_charset = "gb2312";
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merid = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $tmp_url = $this->callbackUrl;
        $tmp_urlserver = $this->serverCallbackUrl;
        $orderdate = date( "YmdHis", $payment['M_Time'] );
        $billNo = $payment['M_OrderId'];
        $cur = $this->system->loadModel( "system/cur" );
        $payment['M_Amount'] = number_format( $payment['M_Amount'], 2, ".", "" );
        $strcontent = ( $billNo."|".$orderdate."|".$payment['M_Amount'] * 100 )."|"."100"."|"."0"."|"."01"."|".$tmp_urlserver."|".$tmp_url."|".$tmp_url."|".""."|".""."|"."1"."|"."";
        $StrMd5 = md5( $merid."|"."01"."|".$strcontent."|".$ikey );
        $return['MerId'] = $merid;
        $return['EncType'] = "01";
        $return['Data'] = base64_encode( $strcontent );
        $return['Sign'] = urlencode( $StrMd5 );
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $billno = $in['MERORDERID'];
        $amount = $in['AMOUNT'];
        $paymentId = $billno;
        $money = "100";
        $orderId = $billno;
        $tradeno = "123455";
        $payment = $this->system->loadModel( "trading/payment" );
        $prow = $payment->getById( $paymentId );
        $cur = $this->system->loadModel( "system/cur" );
        $money = $cur->get_cur_money( $money, $prow['currency'] );
        return PAY_ERROR;
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
        $certid = $this->system->getConf( "certificate.id" );
        $url = urlencode( $this->system->base_url( ) );
        $tmp_form = "<a href=\"http://www.haipay.com/shopexreg.aspx?certi_id=".$certid."&url=".$url."\n\" target=\"_blank\"><img src=\"".$this->system->base_url( )."plugins/payment/images/HAIPAY_apply.gif\"></a>&nbsp;&nbsp;&nbsp;<font color=\"green\">绿卡用户申请快钱支付，费率更低，</font><a href=\"http://www.shopex.cn/shopex_price/sq/index.html\" target=\"_blank\"><font color=\"green\">点击了解绿卡</font></a>";
        return $tmp_form;
    }

}

?>
