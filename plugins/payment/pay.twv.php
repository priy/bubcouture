<?php
require('paymentPlugin.php');
class pay_twv extends paymentPlugin{

    var $name = '台湾里网上支付';//台湾里网上支付
    var $logo = 'TWV';
    var $version = 20070902;
    var $charset = 'big5';
    var $submitUrl = 'https://www.twv.com.tw/openpay/pay.php'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("TWD"=>"TWD");
    var $supportArea =  array("AREA_TWD");
    var $desc = '台湾里网上支付';
    var $orderby = 37;
    var $cur_trading = true;    //支持真实的外币交易
        
    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'], 'member_id');
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        //$order->M_Language = "tchinese";
        $payment["M_Language"] = "tchinese";//
        
        //$order->M_Amount = Floor($order->M_Amount);
        //$verify = md5($ikey."|".$merId."|".$order->M_OrderId."|".$order->M_Amount."|".$this->getConf('SecondPrivateKey'));
        
        $payment["M_Amount"] = Floor($payment["M_Amount"]);
        $verify = md5($ikey."|".$merId."|".$payment["M_OrderId"]."|".$payment["M_Amount"]."|".$this->getConf($payment['M_OrderId'], 'SecondPrivateKey'));

        $return["mid"] = $merId;
        $return["ordernum"] = $payment["M_OrderId"];//$order->M_OrderId;
        $return["txid"] = $payment["M_OrderId"];//$order->M_OrderId;
        $return["iid"] = "0";
        $return["amount"] = $payment["M_Amount"];//$order->M_Amount;
        $return["cname"] = $payment["R_Name"];//$order->R_Name;
        $return["caddress"] = $payment["R_Address"];//$order->R_Address;
        $return["language"] = $payment["M_Language"];//$order->M_Language;
        $return["version"] = "1.0";
        $return["return_url"] = $this->callbackUrl;
        $return["verify"] = $verify;

        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $merid = $in["merid"];
        $payid = $in["txid"];
        $amount = $in["amount"];
        $succ = $in["status"];
        $ordid = $in["tid"];
        $pay_type = $in["pay_type"];
        $error_code = $in["error_code"];
        $msg = $in["error_desc"];
        $md5string = $in["verify"];
        
        $paymentId = $payid;
        $money = $amount;

        $md5key = $this->getConf($payid, 'PrivateKey');
        //content为用来验证签名的消息内容，包括账单号、金额、交易日旗、成功与否标志位
        $content="2efdd6e617bc0114866c89e911a4e3de|".$payid.$amount.$pay_type.$succ.$ordid.$PAY_KEY["TWV"];
        //验证
        if ($md5string = md5($content)){
            switch ($succ){
                //成功支付
                case "1":
                    return PAY_SUCCESS;
                    break;
                //支付失败
                case "2":
                    $message = '支付失败,请立即与商店管理员联系';
                    return PAY_FAILED;
                    break;
                case "3":
                    $message = '支付失败,请立即与商店管理员联系';
                    return PAY_FAILED;
                    break;
            }
        }else{
            $message = '签名认证失败,请立即与商店管理员联系';
            return PAY_ERROR;
        }

        #mt_srand((double)microtime()*1000000);
        #$randval = mt_rand(10000000,99999999);
        #$signstr = substr(hexdec(md5($tmp_orderno.$state.mktime().$randval)), 0, 10);
        #$Order->updateOrderSign($tmp_orderno, $signstr);
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
                ),
                'SecondPrivateKey'=>array(
                        'label'=>'第二私钥',
                        'type'=>'string'
                )
            );
    }

}
?>
