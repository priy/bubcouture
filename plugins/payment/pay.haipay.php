<?php
require('paymentPlugin.php');

class pay_haipay extends paymentPlugin{

    var $name = '运筹宝';//运筹宝
    var $logo = 'HAIPAY';
    var $version = 20070615;
    var $charset = 'gb2312';
    var $applyUrl = 'http://www.haipay.com/shopexreg.aspx';
    //var $submitUrl = 'http://lechongadmin.vicp.net:8012/payment.aspx'; //订单提交测试交易地址
    var $submitUrl= 'https://member.happycz.com/deposit/PaymentSend.aspx'; //订单提交正式交易地址
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"RMB", "USD"=>"02");
    var $supportArea = array('AREA_CNY','AREA_USD');
    var $desc = '运筹宝为广大SHOPEX商家推出至尊优惠、便捷申请活动：无预付，单笔费率最低1%，09年11月2日到至10年11月2日期间申请的用户，还将获得最高抵扣200元交易费的优惠。<a href="http://www.haipay.com/merchant_coupan_help.aspx" target="_blank">新开商户抵扣交易手续费操作流程</a><br/>
为让用户更快体验运筹宝安全、快捷的支付、收款服务，特为SHOPEX商户开通快捷通道，您只需提供联系方式即能得到商户号，开通支付功能再完成注册，运筹宝：方便、省心、安全、便捷的在线金流服务。<a href="http://www.haipay.com/register_help.aspx" target="_blank">详细情况请参考本文档</a><br/>
ShopEx为方便商户使用，接入该支付工具，但对商户因该支付工具产生的损失与扣费概不
负责。';
    var $intro = '运筹宝为广大SHOPEX商家推出至尊优惠、便捷申请活动：无预付，单笔费率最低1%，09年11月2日到至10年11月2日期间申请的用户，还将获得最高抵扣200元交易费的优惠。<a href="http://www.haipay.com/merchant_coupan_help.aspx" target="_blank">新开商户抵扣交易手续费操作流程</a><br/>
为让用户更快体验运筹宝安全、快捷的支付、收款服务，特为SHOPEX商户开通快捷通道，您只需提供联系方式即能得到商户号，开通支付功能再完成注册，运筹宝：方便、省心、安全、便捷的在线金流服务。<a href="http://www.haipay.com/register_help.aspx" target="_blank">详细情况请参考本文档</a><br/>
ShopEx为方便商户使用，接入该支付工具，但对商户因该支付工具产生的损失与扣费概不
负责。';
    var $M_Language  = "1";
    var $orderby = 29;
    var $head_charset = "gb2312";
    var $cur_trading = true;    //支持真实的外币交易

    function toSubmit($payment){
        $merid=$this->getConf($payment['M_OrderId'],'member_id');
        $ikey = $this->getConf($payment['M_OrderId'],'PrivateKey');
        $tmp_url = $this->callbackUrl;
        $tmp_urlserver = $this->serverCallbackUrl; //todo: 服务器端的对话须商定
        $orderdate = date("YmdHis",$payment["M_Time"]);
        $billNo = $payment["M_OrderId"];//;
        $cur=$this->system->loadModel('system/cur');
        $payment['M_Amount'] = $cur->get_cur_money($payment['M_Amount'],$payment['M_Currency']);
        $payment['M_Amount']=number_format($payment['M_Amount']/$cur->_in_cur['cur_rate']/$cur->_in_cur['cur_rate'],2,'.','');
        $strcontent = $billNo . '|'.$orderdate.'|'.$payment['M_Amount']*100 .'|'. '100'.'|' .'0'.'|'. '01' .'|'. $tmp_urlserver.'|'.$tmp_url.'|'.$tmp_url.'|'.''.'|'.''.'|'.'1'.'|'.''; //  //
        $StrMd5 = MD5($merid.'|'.'01'.'|'.$strcontent.'|'.$ikey);
        $return['MerId'] = $merid;
        $return['EncType'] = '01';
        $return['Data'] = base64_encode($strcontent);
        $return['Sign'] = urlencode($StrMd5);
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){

        $billno=$in['MERORDERID'];
        $amount=$in['AMOUNT'];
        $paymentId = $billno;
        $money="100";
        $orderId = $billno;
        //$myfile = '../paylog.txt';
        //$file_pointer = fopen($myfile,"a");
        //fwrite($file_pointer,$orderId.'<br />');
        //fclose($file_pointer);
        $tradeno = "123455";
        $payment = $this->system->loadModel('trading/payment');
        $prow=$payment->getById($paymentId);
        $cur = $this->system->loadModel('system/cur');
        $money = $cur->get_cur_money($money,$prow['currency']);
        return PAY_ERROR;
        
    }

    function getfields(){
        return array(
                'member_id'=>array(
                        'label'=>'客户号',
                        'type'=>'string'
                    ),
                'PrivateKey'=>array(
                        'label'=>'私钥',
                        'type'=>'string'
                )
            );
    }
    
    function applyForm($agentfield){
    $certid=$this->system->getConf('certificate.id');
        $url=urlencode($this->system->base_url());
        $tmp_form='<a href="http://www.haipay.com/shopexreg.aspx?certi_id='.$certid.'&url='.$url.'
" target="_blank"><img src="'.$this->system->base_url().'plugins/payment/images/HAIPAY_apply.gif"></a>&nbsp;&nbsp;&nbsp;<font color="green">绿卡用户申请快钱支付，费率更低，</font><a href="http://www.shopex.cn/shopex_price/sq/index.html" target="_blank"><font color="green">点击了解绿卡</font></a>';
        return $tmp_form;
    }
}
?>
