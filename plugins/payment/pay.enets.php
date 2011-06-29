<?php
require('paymentPlugin.php');
class pay_enets extends paymentPlugin{

    var $name = 'eNETS Payment Services';//eNETS Payment Services
    var $logo = 'ENETS';
    var $version = 20070902;
    var $charset = 'big5';
    var $submitUrl = 'https://www.enetspayments.com.sg/masterMerchant/collectionPage.jsp'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("USD"=>"USD","SGD"=>"SGD");
    var $supportArea =  array("AREA_USD","AREA_SGD");
    var $desc = 'Website: https://www.enetspayments.com.sg/';
    var $orderby = 42;
    var $cur_trading = true;    //支持真实的外币交易
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        //$ikey = $this->getConf('PrivateKey');

        $return['mid']=$merId;
        $return['amount']=$payment["M_Amount"];//$order->M_Amount;
        $return['txnRef']=$payment["M_OrderId"];//$order->M_OrderId;
            
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        /*************************************
        enet返回接收程序
        https://www.enetspayments.com.sg/
        1.通知服务商返回地址为
        http://商店网址/plugins/payment/pay.ENETS.php?gOo=ZW5ldHNfcmVwbHkucGhw&shopexac=XXXXXXXXXXXX
        其中shopexac必须与SHOPEX后台设定的私钥相同(即握手口令)
        2.设置后台私钥
        **************************************/
        if($_SERVER["REQUEST_METHOD"]!="POST"){
            $message = 'Illegal request 1';
            return PAY_ERROR;
        }
        $shopexac = $in["shopexac"];
        $ikey = $this->getConf($in["TxnRef"], 'PrivateKey');
        if($shopexac!=$ikey){
            $message = 'Illegal request 2';
            return PAY_ERROR;
        }
        $mydate = substr($orderno, 0, 8);
        $succ = $in["txnStatus"];
        $payid = $in["TxnRef"];
        
        $paymentId = $payid;
        $money = $in['amount'];

        $errcode = $in["errorCode"];
        switch ($succ){
            //成功支付
            case "succ":
                return PAY_SUCCESS;
                break;
            //支付失败
            case "fail":
                $message = '支付失败,请立即与商店管理员联系'."(".$errcode.")";
                return 交易失败;
                break;
            default:
                $message = 'Illegal request 3';
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
