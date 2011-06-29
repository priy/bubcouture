<?php
require('paymentPlugin.php');
class pay_ips extends paymentPlugin{
    function pay_ips_callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $Billno = $in["?billno"];
        $Mer_code = $in["mer_code"];
        $Currency_type = $in["Currency_type"];
        $money = $Amount = $in["amount"];
        $Date = $in["date"];
        $Succ = $in["succ"];
        $Msg = $in["msg"];
        $Attach = $in["attach"];
        $tradeno = $Ipsbillno = $in["ipsbillno"];
        $Retencodetype = $in["retencodetype"];
        $Signature = $in["signature"];
        $paymentId = $Billno;
        $ikey = $this->getConf($paymentId, 'PrivateKey');
        $content = $Billno.$Amount.$Date.$Succ.$Ipsbillno.$Currency_type.$ikey;
        if (strtolower($Signature) == md5($content)){
            if($Succ=="Y"){
                return PAY_SUCCESS;
            }else{
                return PAY_FAILED;
            }
        }else{
            return PAY_ERROR;
        }
    }

    function pay_IPS_relay($status){
        switch ($status){
            case PAY_FAILED:
                $aTemp = 'failed';
                break;
            case PAY_TIMEOUT:
                $aTemp = 'timeout';
                break;
            case PAY_SUCCESS:
                $aTemp = 'succ';
                break;
            case PAY_CANCEL:
                $aTemp = 'cancel';
                break;
            case PAY_ERROR:
                $aTemp = 'status';
                break;
            case PAY_PROGRESS:
                $aTemp = 'progress';
                break;
        }
    }
}
?>
