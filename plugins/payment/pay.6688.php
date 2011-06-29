<?php
require('paymentPlugin.php');
class pay_6688 extends paymentPlugin{

    var $name = '6688网上支付';//6688网上支付
    var $logo = '6688';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'http://pay.6688.com/paygate/frame.asp'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"");
    var $supportArea = array('AREA_CNY');
    var $desc = '';
    var $orderby = 30;
    

    function toSubmit($payment){
        $this->payment = '';//todo
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $md5string=md5("tmbrid=" .$merId. "&tsummoney=" .$this->M_Amount. "&tcontent1=" .$this->M_Remark. "&todrid=" .$this->M_OrderId. "&tpwd=" .$this->getConf($payment["M_OrderId"], 'PrivateKey'));
        $tSupperComRegflag = 0;

        $return['tmbrid']    =    $merId;
        $return['toname']    =    $this->payment;//todo看文档这里是什么
        $return['tsummoney']=    $payment["M_Amount"];//$order->M_Amount;
        $return['trname']    =    $payment["R_Name"];//$order->R_Name;
        $return['traddress']=    $payment["R_Address"];//$order->R_Address;
        $return['todrid']    =    $payment["M_OrderId"];//$order->M_OrderId;
        $return['temail']    =    $payment["R_Email"];//$order->R_Email;
        $return['trphone']    =    $payment["R_Telephone"];//$order->R_Telephone;
        $return['trzipcode']=    $payment["R_PostCode"];//$order->R_PostCode;
        $return['tuserurl']    =    $this->callbackUrl;
        $return['tcontent1']=    $payment["M_Remark"];//$order->M_Remark;
        $return['tSupperComRegflag']=$tSupperComRegflag;
        $return['mac']=$md5string;

        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){        
        $billNo = $_POST["billNo"];
        $amount = $_POST["amount"];
        $succ = $_POST["succ"];
        $Mac = $_POST["Mac"];
        
        $paymentId = $billNo;
        $money = $amount;
        $message = '';
        
        $ikey = $this->getConf($billNo, 'PrivateKey');
        $content="billNo=".$billNo."&amount=".$amount."&succ=".$succ."&pwd=".$ikey;
        
        if ($Mac == md5($content)){
            switch ($succ) {
                //成功支付
                case "Y":
                    return PAY_SUCCESS;
                    break;
                //支付失败
                case "N":
                    $message = '支付失败';
                    return PAY_FAILED;
                    break;
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
                )
            );
    }
}
?>
