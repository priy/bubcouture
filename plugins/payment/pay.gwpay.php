<?php
require('paymentPlugin.php');
class pay_gwpay extends paymentPlugin{

    var $name = 'Green World Payment';//Green World Payment
    var $logo = 'GWPAY';
    var $version = 20070902;
    var $charset = 'big5';
    var $submitUrl = 'https://www.twv.com.tw/openpay/pay.php'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("TWD"=>"TWD");
    var $supportArea =  array("AREA_TWD");
    var $orderby = 22;
    var $cur_trading = true;    //支持真实的外币交易
        
    function toSubmit($payment){
        if ($this->getConf('system.shoplang') == "en_US")
            $this->submitUrl = "https://gwpay.com.tw/form_Sc_to5e.php";
        else
            $this->submitUrl = "https://gwpay.com.tw/form_Sc_to5.php";
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
                
        $payment["M_Amount"] = Floor($payment["M_Amount"]);//$order->M_Amount = Floor($order->M_Amount);
        $return['act'] = "auth";
        $return['client'] = $merId;
        $return['od_sob'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['email'] = $payment["R_Email"];//$order->R_Email;
        $return['roturl'] = $this->callbackUrl;

        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $gwsr = $in["gwsr"];
        $payid = $in["od_sob"];
        $amount = $in["amount"];
        $succ = $in["succ"];
        $process_time = $in["process_time"];
        $process_date = $in["process_date"];
        $response_code = $in["response_code"];
        $msg = $in["response_msg"];
        $od_hoho = $in["od_hoho"];
        $auth_code = $in["auth_code"];
        $eci = $in["eci"];

        $paymentId = $payid;
        $money = $amount;

        $loginName = $this->getConf($payid, 'PrivateKey');

        //============= recheck flow =======================
        $s = $in['gwsr'];
        $s .= $in['response_code'];
        $s .= $in['process_time'];
        $s .= $in['amount'];
        $s .= $in['od_sob'];
        $s .= $in['auth_code'];
        $ret=$this->isRightPacket($loginName, $s, $in['inspect']);

        //验证
        if($ret == true){
            switch ($succ){
                //成功支付
                case "1":
                    return PAY_SUCCESS;
                    break;
                //支付失败
                case "0":
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
                )
            );
    }
    
    function isRightPacket($loginName,$s,$insp) {
        $s1 = md5($s);
        $s2 = md5($loginName);
        $s3 = md5($s1 ^ $s2);
        if($insp == $s3)
            return true;
        return false; 
    }
}
?>
