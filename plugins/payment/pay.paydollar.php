<?php
require('paymentPlugin.php');
class pay_paydollar extends paymentPlugin{

    var $name = 'PayDollar';//PayDollar
    var $logo = 'PAYDOLLAR';
    var $version = 20070902;
    var $charset = 'utf-8';
    var $submitUrl = 'https://www.paydollar.com/b2c2/eng/payment/payForm.jsp'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"156", "HKD"=>"344", "USD"=>"840", "SGD"=>"702", "JPY"=>"392", "TWD"=>"901", "AUD"=>"036", "EUR"=>"978", "GBP"=>"826", "CAD"=>"124");
    var $supportArea =  array("AREA_CNY","AREA_HKD","AREA_USD","AREA_SGD","AREA_JPY","AREA_TWD","AREA_AUD","AREA_EUR","AREA_GBP","AREA_CAD");
    var $desc = '';
    var $orderby = 21;
    var $cur_trading = true;    //支持真实的外币交易
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');//私钥值，商户可上99BILL快钱后台自行设定
                
        $order->M_Language = "E";
        $tmp_url = $this->url."index.php?gOo=paydollar_reply.do&";
        
        //$text="merchant_id=".$merId."&orderid=".$this->M_OrderId."&amount=".$this->M_Amount."&merchant_url=".$this->callbackUrl."&merchant_key=".$ikey;
        $text="merchant_id=".$merId."&orderid=".$payment["M_OrderId"]."&amount=".$payment["M_Amount"]."&merchant_url=".$this->callbackUrl."&merchant_key=".$ikey;
        $mac = strtoupper(md5($text));

        $return['merchantId'] = $merId;
        $return['orderRef'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['currCode'] = $payment["M_Currency"];//$order->M_Currency;
        $return['lang'] = $payment["M_Language"];//$order->M_Language;
        $return['successUrl'] = $this->callbackUrl;
        $return['failUrl'] = $this->callbackUrl;
        $return['cancelUrl'] = $this->callbackUrl;
        $return['payType'] =  "N";
        $return['payMethod'] = "ALL";
        $return['remark'] = $payment["M_Remark"];//$order->M_Remark;
                
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        //老系统中没有其返回处理函数
        return PAY_SUCCESS;
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
