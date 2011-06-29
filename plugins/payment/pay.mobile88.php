<?php
require('paymentPlugin.php');
class pay_mobile88 extends paymentPlugin{

    var $name = 'MOBILE88';//MOBILE88
    var $logo = 'MOBILE88';
    var $version = 20070902;
    var $charset = 'utf-8';
    var $submitUrl = 'https://www.mobile88.com/epayment/entry.asp'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("MYR"=>"MYR");
    var $supportArea =  array("AREA_MYR");
    var $desc = 'www.mobile88.com';
    var $orderby = 43;
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        
        $ordAmount = number_format($this->M_Amount, 2, ".", "");
        $tmpOrdAmount = str_replace(".", "", $ordAmount);
        $sha1 = $this->system->loadModel('utility/sha1');
        //$Signature = base64_encode($sha1->sha1($ikey.$merId.$order->M_OrderId.$tmpOrdAmount.$order->M_Currency, true));
        $Signature = base64_encode($sha1->sha1($ikey.$merId.$payment["M_OrderId"].$tmpOrdAmount.$payment["M_Currency"], true));

        $return['MerchantCode'] = $merId;
        $return['RefNo'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['PaymentId'] = "2";
        $return['Amount'] = $ordAmount;
        $return['Currency'] = $payment["M_Currency"];//$order->M_Currency;
        $return['ProdDesc'] = $payment["M_OrderNO"];//$order->M_OrderNO;
        $return['UserName'] = $payment["R_Name"];//$order->R_Name;
        $return['UserEmail'] = $payment["R_Email"];//$order->R_Email;
        $return['UserContact'] = $payment["R_Address"];//$order->R_Address;
        $return['Remark'] = "";
        $return['Signature'] = $Signature;
        $return['return_url'] =  $this->callbackUrl;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $MerchantCode = trim($in['MerchantCode']);    //商家ID
        $PaymentId = trim($in['PaymentId']);            //Payment Method Id
        $orderid = trim($in['RefNo']);            //交易号
        $amount = trim($in['Amount']);            //交易金额
        $Currency = trim($in['Currency']);        //Currency code. “MYR” only
        $TransId = trim($in['TransId']);            //MOBILE88交易ID
        $AuthCode = trim($in['AuthCode']);        //Bank’s approval code
        $succeed = trim($in['Status']);            //交易结果，"1"表示成功，"0"表示失败
        $ErrDesc = trim($in['ErrDesc']);
        $Signature = trim($in['Signature']);

        $paymentId = $orderid;
        $money = $amount;

        $key = $this->getConf($orderid, 'PrivateKey');
        $sha1 = $this->system->loadModel('utility/sha1');
        $text = $key.$MerchantCode.$orderid.$amount.$Currency;
        $mac = base64_encode($sha1->sha1($text, true));

        if (strtoupper($mac)==strtoupper($Signature)){
            switch ($succeed){
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
