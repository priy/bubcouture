<?php
require('paymentPlugin.php');
class pay_ecs extends paymentPlugin{

    var $name = 'ECS Payment Gateway ';
    var $logo = 'ECS';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://secure.cps.lv/scripts/rprocess.dll?authorize'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("USD"=>"USD", "EUR"=>"EUR");
    var $supportArea =  array("AREA_USD","AREA_EUR");
    var $desc = '';
    var $orderby = 44;
    var $cur_trading = true;    //支持真实的外币交易
    
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        ##$tmp_url = $_SERVER['HTTP_HOST'];
        $tmp_url = $this->callbackUrl;
        //$mac="goodsTitle".$order->M_OrderId."goodsBid".$order->M_Amount."ordinaryFee0.00expressFee0.00sellerEmail".$merId."no".$order->M_OrderId."memo".$ikey;
        $mac="goodsTitle".$payment["M_OrderId"]."goodsBid".$payment["M_Amount"]."ordinaryFee0.00expressFee0.00sellerEmail".$merId."no".$payment["M_OrderId"]."memo".$ikey;
        $mac = md5($mac); //对参数串进行私钥加密取得值
        
        $return["Merchant"] = $merId;
        $return["Account"] = $ikey;
        $return["Service"] = $this->getConf($payment["M_OrderId"], 'SecondPrivateKey');
        $return["OrderID"] = $payment["M_OrderNO"];//$order->M_OrderNO;
        $return["ReferenceID"] = $payment["M_OrderId"];//$order->M_OrderId;
        $return["Amount"] = $payment["M_Amount"];//$order->M_Amount;
        $return["Currency"] = $payment["M_Currency"];//$order->M_Currency;
        $return["Description"] = $payment["M_Remark"];//$order->M_Remark;
        $return["Customer"] = $payment["R_Name"];//$order->R_Name;
        $return["Email"] = $payment["R_Email"];//$order->R_Email;
        $return["IP"] = $_SERVER["REMOTE_ADDR"];
        $return["Site"] = $tmp_url;
                    
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $merid = $in["merid"];
        ##$payid = $this->intString($in["ReferenceID"], 6);
        $payid = $in["ReferenceID"];
        $succ = $in["Status"];
        $ErrorCode = $in["ErrorCode"];
        $Message = $in["Message"];
        $orderno = $in["OrderID"];

        $paymentId = $payid;
        $money = '';

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


        #mt_srand((double)microtime()*1000000);
        #$randval = mt_rand(10000000,99999999);
        #$signstr = substr(hexdec(md5($tmp_orderno.$state.mktime().$randval)), 0, 10);
        #$Order->updateOrderSign($tmp_orderno, $signstr);
    }

    function getfields(){    //EGOLD没有商户私钥
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

    function intString($intvalue,$len){
        $intstr=strval($intvalue);
        //echo strlen($intstr);
        for ($i=1;$i<=$len-strlen($intstr);$i++){
            $tmpstr .= "0";
        }
        return $tmpstr.$intstr;
    }
}
?>
