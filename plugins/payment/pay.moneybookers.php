<?php
require('paymentPlugin.php');
class pay_moneybookers extends paymentPlugin{

    var $name = 'MONEYBOOKERS';//MONEYBOOKERS
    var $logo = 'MONEYBOOKERS';
    var $version = 20070902;
    var $charset = 'utf-8';
    var $submitUrl = 'https://www.moneybookers.com/app/payment.pl'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("AUD"=>"AUD", "CAD"=>"CAD", "EUR"=>"EUR", "GBP"=>"GBP", "HKD"=>"HKD", "JPY"=>"JPY", "KRW"=>"KRW", "TWD"=>"TWD", "SGD"=>"SGD", "USD"=>"USD");
    var $supportArea =  array("AREA_AUD","AREA_CAD","AREA_EUR","AREA_GBP","AREA_HKD","AREA_JPY","AREA_KRW","AREA_TWD","AREA_SGD","AREA_USD");
    var $orderby = 24;
    var $cur_trading = true;    //支持真实的外币交易
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        $return['pay_to_email'] = $merId;
        $return['transaction_id'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['currency'] = $payment["M_Currency"];//$order->M_Currency;
        $return['pay_from_email'] = $payment["R_Email"];//$order->R_Email;
        $return['language'] = "en";
        $return['detail1_description'] = $payment["M_OrderNO"];//$order->M_OrderNO;
        $return['detail1_text'] = $payment["M_OrderNO"];//$order->M_OrderNO;
        $return['address'] = $payment["R_Address"];//$order->R_Address;
        $return['postal_code'] = $payment["R_PostCode"];//$order->R_PostCode;
        $return['firstname'] = $payment["R_Name"];//$order->R_Name;
        $return['confirmation_note'] = $payment["M_Remark"];//$order->M_Remark;
        $return['status_url'] = $this->callbackUrl;
        $return['return_url'] = $this->callbackUrl;
        $return['cancel_url'] = $this->callbackUrl;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        //=========================== 把商家的相关信息返回去 =======================
        $mer_email    =     $in['pay_to_email'];            //商家号
        $cus_email    =     $in['pay_from_email'];        //付钱客户号
        $mer_id        =     $in['merchant_id'];            //商家订单号
        $orderid    =     $in['transaction_id'];        //商家订单号
        $mb_orderid    =     $in['mb_transaction_id'];    //MB订单号
        $mb_amount    =     $in['mb_amount'];            //折合MB的金额
        $mb_currency=     $in['mb_currency'];            //MB的币种        
        $amount        =     $in['amount'];                //支付金额
        $currency    =     $in['currency'];                //币种
        $Status        =     $in['Status'];                //状态
    
        $paymentId = $orderid;
        $money = $amount;

        //接收md5加密认证
        $signMsg     =    $in['md5sig'];                //密匙
        //=========================== 开始加密 ====================================
        $key = $this->getConf($orderid, 'PrivateKey');
        //整合md5加密
        $text = $mer_id.$orderid.$key.$mb_amount.$mb_currency.$Status;
        $md5digest = strtoupper(md5($text));
        if ($md5digest == $signMsg){
            return PAY_SUCCESS;
        }else{
            $message = '支付信息不正确，可能被篡改。';
            return PAY_ERROR;
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
