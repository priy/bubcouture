<?php
require('paymentPlugin.php');
class pay_deposit extends paymentPlugin{

    var $name = '预存款支付';//快钱网上支付
    var $logo = '';
    var $version = 20080520;
    var $charset = 'utf-8';
    var $applyUrl = '';
    var $submitUrl = './plugins/app/pay_deposit/pay_deposit.php';
    var $submitButton = ''; ##需要完善的地方
    var $supportCurrency =  array("DEFAULT"=>"1");
    var $supportArea =  array("AREA_CNY");
    var $desc = '预存款支付';
    var $orderby = 7;

    function toSubmit($payment){

        $text="orderid=".$payment['M_OrderId']."&amount=".$payment['M_Amount']."&currency=".$payment['M_Currency']."&merchant_url=".$this->callbackUrl."&merchant_key=".$payment['K_key'];
        $mac = strtoupper(md5($text)); //对参数串进行私钥加密取得值
        $return['orderid']= $payment['M_OrderId'];    //$order->M_OrderId
        $return['amount']= $payment['M_Amount'];    //$order->M_Amount
        $return['merchant_url']=$this->callbackUrl;
        $return['currency']=$payment['M_Currency']; //$order->M_Currency
        $return['mac']=$mac;

        return $return;

    }

    function callback($in,&$paymentId,&$money,&$message){

        $orderid = trim($in['orderid']);            //交易号
        $amount = trim($in['amount']);                //交易金额
        $currency = trim($in['currency']);
        $merchant_url = trim($in['merchant_url']);
        $mymac = trim($in['mac']);

        $paymentId = $orderid;
        $money = $amount;
        $key = $this->system->getConf('certificate.token');
        $text = "orderid=".$orderid."&amount=".$amount."&currency=".$currency."&merchant_url=".$merchant_url."&merchant_key=".$key;
        $mac = strtoupper(md5($text));
        if (strtoupper($mac)==strtoupper($mymac)){
            return PAY_SUCCESS;
        }else{
            $message = '支付验证失败';
            return PAY_ERROR;
        }
    }

    function getfields(){
        return array();
    }
}
?>
