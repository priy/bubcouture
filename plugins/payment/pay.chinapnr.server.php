<?php
require('paymentPlugin.php');
class pay_chinapnr extends paymentPlugin{
function pay_chinapnr_callback($in,&$paymentId,&$money,&$message,&$tradeno){
    $PgKeyFile = $this->getConf($in["OrdId"], 'key2');
    $p_MerId = $in["MerId"];            //商户号
    $p_MerDate = $in["MerDate"];        //商户日期
    $p_OrdId = $in["OrdId"];            //商户订单
    $money = $p_TransAmt = $in["TransAmt"];    //交易金额
    $p_TransType = $in["TransType"]; //交易类型
    $p_GateId = $in["GateId"];        //网关号
    $p_TransStat = $in["TransStat"]; //交易状态  "S"成功
    $p_MerPriv = $in["MerPriv"];        //商户私有域
    $p_SysDate = $in["SysDate"];        //系统日期
    $tradeno = $p_SysSeqId = $in["SysSeqId"];    //系统流水号
    $p_ChkValue = $in["ChkValue"];    //签名
    $paymentId = $p_OrdId;
    $pnrObj = new COM("ChinaPnr.SecureLink");
    if(strtolower(substr($_ENV["OS"],0,7)) == "windows"){
        $checkout = $pnrObj->VeriSingOrder0($p_MerId,$PgKeyFile,$p_OrdId,$p_TransAmt,$p_MerDate,$p_TransType,$p_TransStat,$p_GateId,$p_MerPriv,$p_SysDate,$p_SysSeqId,$p_ChkValue);
    }else{
        $checkout = $pnrObj->VeriSingOrder($p_MerId,$PgKeyFile,$p_OrdId,$p_TransAmt,$p_MerDate,$p_TransType,$p_TransStat,$p_GateId,$p_MerPriv,$p_SysDate,$p_SysSeqId,$p_ChkValue);
    }

    if ($checkout == 0){
        if($p_TransStat == "S")    {    
            return PAY_SUCCESS;
            echo "RECV_ORD_ID_".$p_OrdId;
        }
    }
}

function pay_CHINAPNR_relay($status){
    switch ($status){
        case PAY_FAILED:
            break;
        case PAY_TIMEOUT:
            break;
        case PAY_SUCCESS:
            echo "RECV_ORD_ID_".$p_OrdId;
            break;
        case PAY_CANCEL:
            break;
        case PAY_ERROR:
            break;
        case PAY_PROGRESS:
            break;
    }
}
}
?>
