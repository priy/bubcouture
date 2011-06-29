<?php
require('paymentPlugin.php');
class pay_egold extends paymentPlugin{

    var $name = 'EGOLD';//EGOLD 
    var $logo = 'EGOLD';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://www.e-gold.com/sci_asp/payments.asp'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("USD"=>"USD", "EUR"=>"EUR", "GBP"=>"GBP", "CAD"=>"CAD", "AUD"=>"AUD", "JPY"=>"JPY");
    var $supportArea =  array("AREA_CNY","AREA_EUR","AREA_GBP","AREA_CAD","AREA_AUD","AREA_AUD","AREA_JPY");
    var $desc = '';
    var $orderby = 25;
    var $cur_trading = true;    //支持真实的外币交易
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        $return['PAYMENT_METAL_ID'] = "1";
        $return['PAYMENT_ID'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['PAYEE_ACCOUNT'] = $merId;
        $return['PAYEE_NAME'] = $_SERVER["HTTP_HOST"];
        $return['PAYMENT_AMOUNT'] = $payment["M_Amount"];//$order->M_Amount;
        $return['PAYMENT_UNITS'] = "1";
        $return['PAYMENT_URL'] = $this->callbackUrl;
        $return['PAYMENT_URL_METHOD'] = "POST";
        //EGOLD支付成功跟失败在前台都会有返回，而且必须指定地址，如果支付不成功，就让他返回网店首页好了。
        $return['NOPAYMENT_URL'] = $this->getConf('system.shopurl');
        $return['NOPAYMENT_URL_METHOD'] = "POST";
        $return['BAGGAGE_FIELDS'] = "";
        $return['PRODUCTNAME'] = "";

        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $v_mid = trim($in['PAYEE_ACCOUNT']);//网店主EGOLD号码
        $v_oid = trim($in['PAYMENT_ID']);//订单号
        $v_amount = trim($in['PAYMENT_AMOUNT']);//支付金额

        $paymentId = $v_oid;
        $money = $v_amount;
        $message = '';

        return PAY_SUCCESS;

        #mt_srand((double)microtime()*1000000);
        #$randval = mt_rand(10000000,99999999);
        #$signstr = substr(hexdec(md5($tmp_orderno.$state.mktime().$randval)), 0, 10);
        #$Order->updateOrderSign($tmp_orderno, $signstr);
    }

    function getfields(){    //EGOLD没有商户私钥
        return array(
                'member_id'=>array(
                        'label'=>'客户号',
                        'type'=>'string'
                    )
            );
    }
}
?>
