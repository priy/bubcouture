<?php
require('paymentPlugin.php');
class pay_wangjin_out extends paymentPlugin{

    var $name = '网银在线支付（外卡）';//网银在线支付（内卡）
    var $logo = 'WANGJIN_OUT';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://pay3.chinabank.com.cn/PayGate'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"CNY");
    var $supportArea = array('AREA_CNY');
    var $desc = '网银网上支付是独立的安全支付平台，为商户提供便捷、安全、稳定的电子商务支付解决方案。点击本链接申请即可获得网银在线网关特别优惠价:年服务费全免，手续费1％！';
    var $orderby = 14;

    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        $order->M_Language = 'EN';
        
        //$orderdate = date("Ymd",$order->M_Time);
        //$md5string=strtoupper(md5($order->M_Amount.$order->M_Currency.$order->M_OrderId.$merId.$order->callbackUrl.$ikey));
        //$order->P_Name = $order->P_Name?$order->P_Name:$order->R_Name;
        
        $orderdate = date("Ymd",$payment["M_Time"]);
        $md5string=strtoupper(md5($payment["M_Amount"].$payment["M_Currency"].$payment["M_OrderId"].$merId.$this->callbackUrl.$ikey));
        $$payment["P_Name"] = $payment["P_Name"]?$payment["P_Name"]:$payment["R_Name"];

        $return["v_mid"] = $merId;
        $return["v_oid"] = $payment["M_OrderId"];//$order->M_OrderId;
        $return["v_amount"] = number_format($payment["M_Amount"],2,".","");//$order->M_Amount;
        $return["v_rcvname"] = $payment["R_Name"];//$order->R_Name;
        $return["v_rcvtel"] = $payment["R_Telephone"];//$order->R_Telephone;
        $return["v_rcvpost"] = $payment["R_PostCode"];//$order->R_PostCode;
        $return["v_rcvaddr"] = $payment["R_Address"];//$order->R_Address;
        $return["v_ordername"] = $payment["P_Name"];//$order->P_Name;
        $return["v_ymd"] = $orderdate;        
        $return["v_orderemail"] = $payment["R_Email"];//$order->R_Email;
        $return["v_orderstatus"] = "0";
        $return["bankid"] = "3D";
        $return["v_moneytype"] = $payment["M_Currency"];//$order->M_Currency;
        $return["v_language"] = $payment["M_Language"];//$order->M_Language;
        $return["v_url"] = $this->callbackUrl;
        $return["v_md5info"] = $md5string;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){        
        $v_oid = trim($_POST["v_oid"]);
        $v_amount = trim($in["v_amount"]);
        $v_pmode = trim($in["v_pmode"]);
        $v_pstatus = trim($in["v_pstatus"]);
        $v_pstring = trim($in["v_pstring"]);
        $v_moneytype = trim($in["v_moneytype"]);
        $v_md5info = trim($in["v_md5info"]);
        $paypmentId = $v_oid;
        $money = $v_amount;
        $ikey = $this->getConf($v_oid, 'PrivateKey');
        //-----------重新计算md5的值--------------------------------------------
        $content=$v_oid.$v_pstatus.$v_amount.$v_moneytype.$ikey;
        $md5string = strtoupper(md5($content));

        if ($v_md5info != $md5string){
            $message = "签名认证失败,请立即与商店管理员联系<br />Your payment may success. please contact the administrator ASAP.";
            return PAY_ERROR;
        }else{
            if($v_pstatus == "20"){
                return PAY_SUCCESS;
            }else{
                $message = "Payment Failed.";
                return PAY_FAILED;# 交易失败
            }
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
