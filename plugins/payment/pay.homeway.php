<?php
require('paymentPlugin.php');
class pay_homeway extends paymentPlugin{

    var $name = '和讯在线支付';//和讯在线支付
    var $logo = 'HOMEWAY';
    var $version = 20070902;
    var $charset = 'GB2312';
    var $submitUrl = 'http://payment.homeway.com.cn/pay/pay_new.php3'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY");
    var $supportArea =  array("AREA_CNY");
    var $desc = '和讯在线支付';
    var $orderby = 31;
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        
        //$order->M_Currency = "2002";
        $payment["M_Currency"] = "2002";//
        $mer_key="asdfghjk12345678";
        $payment["M_Amount"] *= 100;//$order->M_Amount *= 100;
        //$info = $merId.$order->M_Amount.$order->M_OrderId.date("Ymd",$order->M_Time).$order->M_Currency.$ikey;
        $info = $merId.$payment["M_Amount"].$payment["M_OrderId"].date("Ymd",$payment["M_Time"]).$payment["M_Currency"].$ikey;
        $msign = md5($info);
        
        $return['MerchID'] =  $merId;
        $return['OrderNum'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['Amount'] =  $payment["M_Amount"];//$order->M_Amount;
        $return['TransType'] = $payment["M_Currency"];//$order->M_Currency;
        $return['TransDate'] =  date("Ymd",$payment["M_Time"]);//date("Ymd",$order->M_Time);
        $return['Signature'] = $msign;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $OrderNo    =     $in['OrderNo'];            //商家订单号
        $Amount        =     $in['Amount'];            //支付金额
        $TransType    =     $in['TransType'];            //币种        
        $TransDate    =     $in['TransDate'];            //语言选择
        $Succeed    =    $in['Succeed'];                //支付状态2成功,3失败
        //接收组件的加密
        $RetSign     =    $in['RetSign'];                //密匙

        $paymentId = $OrderNo;
        $money = $Amount;

        if ( $Succeed == "Y" ){
            //支付标志为成功，但仍需要验证确认
            //    $MerchID = "EC_TEST00000";        //和讯支付平台为您分配的商户号
            //    $MerchKey = "QWERtyui12345678";        //双方约定的密钥
            //检查签名
            $info = $this->getConf($OrderNo, 'PrivateKey').$Succeed.$OrderNo.$this->getConf('member_id').$Amount.$TransType.$TransDate;
            $MySign = md5($info);
            
            if ( $RetSign == $MySign ){
                return PAY_SUCCESS;
            }else{
                $message = '验证失败';
                return PAY_ERROR;
            }
        }else{
            $message = '支付失败,请立即与商店管理员联系';
            return PAY_FAILED;
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
