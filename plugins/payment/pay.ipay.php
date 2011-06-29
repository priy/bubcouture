<?php
require('paymentPlugin.php');

class pay_ipay extends paymentPlugin{

    var $name = 'IPAY在线支付';//
    var $logo = 'IPAY';
    var $version = 20070615;
    var $charset = 'gb2312';
    var $submitUrl = 'http://www.ipay.cn/4.0/bank.shtml'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"");
    var $supportArea = array('AREA_CNY');
    var $desc = '';
    var $orderby = 23;

    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $v_mobile = "13800138000";
        //$md5string=md5($merId.$order->M_OrderId.$order->M_Amount.$order->R_Email.$v_mobile.$this->getConf('PrivateKey'));
        $md5string=md5($merId.$payment["M_OrderId"].$payment["M_Amount"].$payment["R_Email"].$v_mobile.$this->getConf($payment["M_OrderId"], 'PrivateKey'));

        //$order->P_Name = $order->P_Name?$order->P_Name:$order->R_Name;
        $payment["P_Name"] = $payment["P_Name"]?$payment["P_Name"]:$payment["R_Name"];

        
        $return['v_mid'] = $merId;
        $return['v_oid'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['v_amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['v_date'] = date("Ymd",$payment["M_Time"]);//date("Ymd",$order->M_Time);
        //$return['v_rname'] = $payment["R_Name"];//$order->R_Name;
        $return['v_name'] = $payment["R_Name"];
        $return['v_email'] = $payment["R_Email"];//$order->R_Email;
        $return['v_mobile'] = $v_mobile;
        //$return['v_rtel'] = $payment["R_Telephone"];//$order->R_Telephone;
        $return['v_tel'] = $payment["R_Telephone"];
        $return['v_rpost'] = $payment["R_PostCode"];//$order->R_PostCode;
        //$return['v_raddr'] = $payment["R_Address"];//$order->R_Address;
        $return['v_address'] = $payment["R_Address"];
        $return['v_rnote'] = $payment["M_Remark"];//$order->M_Remark;
        $return['v_payname'] = $payment["P_Name"];//$order->P_Name;
        $return['v_url'] = $this->callbackUrl;
        $return['v_md5'] = $md5string;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){
        $v_mid = trim($in['v_mid']);
        $v_oid = trim($in['v_oid']);
        //$v_pamount = trim($in['v_pamount']);
        $v_pamount = trim($in['v_amount']);
        $v_pmode = trim($in['v_pmode']);
        //$v_pstatus = trim($in['v_pstatus']);
        $v_pstatus = trim($in['v_status']);
        $v_pstring = trim($in['v_pstring']);
        //$v_pdate = trim($in['v_pdate']);
        $v_pdate = trim($in['v_date']);
        //$v_pmd5 = trim($in['v_pmd5']);
        $v_pmd5 = trim($in['v_md5']);
        $v_phpmd5 = trim($in['v_phpmd5']);
     
        
        //-----------------------------
        $paymentId = $orderid = substr($v_oid,-6);
        $money = $v_pamount;
        //$content = $v_mid.$v_oid.$v_pmode.$v_pstatus.$v_pstring.$v_pamount.$v_pdate.$ikey;
        $content = $v_pdate.$v_mid.$v_oid.$v_pamount.$v_pstatus.$this->getConf($payment["M_OrderId"], 'PrivateKey');
        if ($v_phpmd5 == md5($content)){
            if ($v_pstatus == 00 || $v_pstatus == 20){//if ($v_pstatus == 20){                
                return PAY_SUCCESS;
            }elseif ($v_pstatus == 12){
                $message = '交易失败';
                return PAY_FAILED;
            }elseif ($v_pstatus == 99){
                $message = '交易进行中';
                return PAY_PROGRESS;
            }
        }else{
            $message = '交易异常，Md5摘要认证错误';
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
