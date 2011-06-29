<?php
require('paymentPlugin.php');
class pay_epay extends paymentPlugin{

    var $name = 'EPAY网上支付';//EPAY网上支付
    var $logo = 'EPAY';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'http://www.ipost.cn/pay/pay.aspx'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("TWD"=>"TWD");
    var $supportArea = array('AREA_TWD');
    var $desc = '';
    var $orderby = 24;
    var $cur_trading = true;    //支持真实的外币交易

    function toSubmit($payment){
        //$order->M_Amount *= 100 ; 
        $payment["M_Amount"]  *= 100 ;
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');

        //$lnkStr = trim($merId).":".trim($this->getConf('SecondPrivateKey')).":".trim($order->M_OrderId).":".trim($order->M_Amount).":".trim($this->getConf('PrivateKey'));
        $lnkStr = trim($merId).":".trim($this->getConf($payment["M_OrderId"], 'SecondPrivateKey')).":".trim($payment["M_OrderId"]).":".trim($payment["M_Amount"]).":".trim($this->getConf('PrivateKey'));
        $strCountSignature = MD5($lnkStr);
        
        $return['epayClientMerchID'] = $merId;
        $return['epayClientMerchPwd'] = $this->getConf($payment["M_OrderId"], 'SecondPrivateKey');
        $return['epayClientOrderNum'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['epayClientOrderAmount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['signature'] = $strCountSignature;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){        
        $v_merid = trim($in['epayClientMerchID']);
        $v_merpwd = trim($in['epayClientMerchPwd']);
        $v_orderid = trim($in['epayClientOrderNum']);
        $v_status = trim($in['epayClientOrderTranStatus']);
        $v_pmd5 = trim($in['signature']);
        
        $paymentId = $v_orderid;
        $money = '';

        $lnkStr = trim($v_merid).":".trim($v_merpwd).":".trim($v_orderid).":".trim($v_status).":".trim($ikey);
        
        if ($v_pmd5 == md5($lnkStr)){
            //验证成功
            if($v_status == "Y"){
                return PAY_SUCCESS;
            }else{
                $message = '交易失败';
                return PAY_FAILED;
            }
        }else{
            $message = '验证失败';
            return PAY_ERROR;# 交易失败
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
                ),
                'SecondPrivateKey'=>array(
                        'label'=>'第二私钥',
                        'type'=>'string'
                )
            );
    }
}
?>
