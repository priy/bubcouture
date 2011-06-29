<?php
require('paymentPlugin.php');

class pay_ips3 extends paymentPlugin{

    var $name = '环讯IPS网上支付3.0';//环讯IPS网上支付3.0
    var $logo = 'IPS3';
    var $version = 20070615;
    var $charset = 'gb2312';
    var $submitUrl = 'http://pay.ips.com.cn/ipayment.aspx'; // https://pay.ips.com.cn/ipayment.aspx 测试地址
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"RMB", "USD"=>"02");
    var $supportArea = array('AREA_CNY','AREA_USD');
    var $desc = '';
    var $M_Language  = "1";
    var $orderby = 10;
    var $head_charset = "gb2312";
    var $cur_trading = true;    //支持真实的外币交易

    function toSubmit($payment){
        //$this->submitUrl='http://pay.ips.com.cn/merchant_new.asp';
        $merid=$this->getConf($payment['M_OrderId'],'member_id');
        $ikey = $this->getConf($payment['M_OrderId'],'PrivateKey');
        $tmp_url = $this->callbackUrl;
        $tmp_urlserver = $this->serverCallbackUrl; //todo: 服务器端的对话须商定
        $orderdate = date("Ymd",$payment["M_Time"]);
        $billNo = $merid.$payment["M_OrderId"];//;
        $cur=$this->system->loadModel('system/cur');
        $payment['M_Amount'] = $cur->get_cur_money($payment['M_Amount'],$payment['M_Currency']);
        $tmpAmount = $payment['M_Amount']/$cur->_in_cur['cur_rate'];
        if ($tmpAmount>=1){
            $tmpAmount=intval($tmpAmount);
        }
        $payment['M_Amount']=number_format($tmpAmount,2,'.','');
        $StrMd5 = md5($billNo.$payment["M_Amount"].$orderdate.$payment['M_Currency'].$ikey);
        $return['Mer_code'] = $merid;
        $return['Billno'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['Amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['Date'] = $orderdate;
        $return['Currency_Type'] = $payment['M_Currency'];
        $return['Gateway_Type'] = '01';//$order->M_Currency;
        $return['Lang'] = 'GB';
        $return['Merchanturl'] = $tmp_url;
        $return['FailUrl'] = $tmp_url;
        $return['DispAmount'] = ''; //todo:需要在订单生成的时候做转换，主要用于外币支付时,紧做显示用不参与交易    
        $return['OrderEncodeType'] = "1";
        $return['RetEncodeType'] = "12";
        $return['Rettype'] = "1";
        $return['ServerUrl'] = $tmp_urlserver;
        $return['SignMD5'] = $StrMd5;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $billno=$in['billno'];
        $amount=$in['amount'];
        $mydate=$in['date'];
        $succ=$in['succ'];
        $msg=$in['msg'];
        $attach=$in['attach'];
        $ipsbillno=$in['ipsbillno'];
        $retEncodeType=$in['retencodetype'];
        $currency_type=$in['Currency_type'];
        $signature=$in['signature'];
        $paymentId = $billno;
        $money=$amount;
        $orderId = $billno;
        $tradeno = $in['ipsbillno'];
        $payment = $this->system->loadModel('trading/payment');
        $prow=$payment->getById($paymentId);
        $cur = $this->system->loadModel('system/cur');
        $money = $cur->get_cur_money($money,$prow['currency']);
        if ($succ=="Y"){
            $content=$billno . $amount . $mydate . $succ . $ipsbillno .$currency_type;

            #在该字段中放置商户登陆merchant.ips.com.cn的网站中的证书#
            $cert=$this->getConf($billno, 'PrivateKey');

            //Md5摘要认证
            if ($content=="" || $cert=="")
                $signature1="";
            else
                $signature_1ocal=md5($content.$cert);    

            if ($signature_1ocal==$signature){                
                return PAY_SUCCESS;
            }else{
                $message = '交易异常，Md5摘要认证错误';
                return PAY_ERROR;# 交易异常，Md5摘要认证错误
            }
        }else{
            $message  = '交易失败';
            return PAY_FAILED;# 交易失败
        }
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
}
?>
