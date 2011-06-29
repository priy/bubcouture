<?php
require('paymentPlugin.php');
class pay_chinapnr extends paymentPlugin{

    var $name = '天天付在线支付（汇付天下）';//网汇通在线支付
    var $logo = 'CHINAPNR';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://payment.chinapnr.com/pay/TransGet';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"156");
    var $supportArea =  array("AREA_CNY");
    var $desc = "汇付天下（ www.chinapnr.com）是中国最具创新支付公司，注册资本过亿。现申请“天天付”网上支付平台可获年服务费全免，手续费 0.8％，并免费享用“ T+1”结算，“单边账应急处理”等可选特色增值服务优惠！<br>申请方式:<br>电话:021-64486999-8862/13817018164 阮先生<br>邮箱:<a href='mailto:robin.ruan@chinapnr.com'>robin.ruan@chinapnr.com</a>";
    var $orderby = 15;
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        
        $ordAmount = number_format($payment["M_Amount"], 2, '.', '');
        $MerDate = date("Ymd", $payment["M_Time"]?$payment["M_Time"]:time());
        $oldPath=dirname(__FILE__)."/../../cert/chinapnr/";
        $newPath=dirname(__FILE__)."/../../home/upload/chinapnr/";
        $MerKeyFile = $this->getConf($payment["M_OrderId"], 'key1');
        $PgKeyFile = $this->getConf($payment["M_OrderId"], 'key2');
        $payment["M_OrderNO"]=$payment["M_OrderNO"]?$payment["M_OrderNO"]:date("YmdHis",time());
        if(file_exists($oldPath.$MerKeyFile)||file_exists($newPath.$MerKeyFile)){
            $MerKeyFile=file_exists($oldPath.$MerKeyFile)?$oldPath.$MerKeyFile:$newPath.$MerKeyFile;
            $pnrObj = new COM("ChinaPnr.NetpayClient");
            if(strtolower(substr($_ENV["OS"],0,7)) == "windows"){
                $ChkValue = $pnrObj->SignOrder0($merId, $MerKeyFile, $payment["M_OrderId"], $ordAmount, $MerDate, "P", "", $payment["M_OrderNO"], $this->serverCallbackUrl, $this->callbackUrl);
            }else{
                 $ChkValue = $pnrObj->SignOrder($merId, $MerKeyFile, $payment["M_OrderId"], $ordAmount, $MerDate, "P", "", $payment["M_OrderNO"], $this->serverCallbackUrl, $this->callbackUrl);
            }
        }else{
            echo("read key file error!");
            exit;
        }
        $return['Version']="10";
        $return['MerId']= $merId;
        $return['MerDate']=$MerDate;
        $return['OrdId']=$payment["M_OrderId"];//$order->M_OrderId;
        $return['TransAmt']=$ordAmount;
        $return['TransType']="P";
        $return['GateId']="";
        $return['MerPriv']=$payment["M_OrderNO"];//$order->M_OrderNO;
        $return['ChkValue']=$ChkValue;
        $return['PageRetUrl']=$this->callbackUrl;
        $return['BgRetUrl']=$this->serverCallbackUrl;
                
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $PgKeyFile = $this->getConf($in["OrdId"], 'key2');
        $p_MerId = $in["MerId"];            //商户号
        $p_MerDate = $in["MerDate"];        //商户日期
        $p_OrdId = $in["OrdId"];            //商户订单
        $p_TransAmt = $in["TransAmt"];    //交易金额
        $p_TransType = $in["TransType"]; //交易类型
        $p_GateId = $in["GateId"];        //网关号
        $p_TransStat = $in["TransStat"]; //交易状态  "S"成功
        $p_MerPriv = $in["MerPriv"];        //商户私有域
        $p_SysDate = $in["SysDate"];        //系统日期
        $p_SysSeqId = $in["SysSeqId"];    //系统流水号
        $p_ChkValue = $in["ChkValue"];    //签名

        $paymentId = $p_OrdId;
        $money = $p_TransAmt;

        $pnrObj = new COM("ChinaPnr.NetpayClient");
        $oldPath=dirname(__FILE__)."/../../cert/chinapnr/";
        $newPath=dirname(__FILE__)."/../../home/upload/chinapnr/";
        $PgKeyFile=file_exists($oldPath.$PgKeyFile)?$oldPath.$PgKeyFile:$newPath.$PgKeyFile;
        if(strtolower(substr($_ENV["OS"],0,7)) == "windows"){
            $checkout = $pnrObj->VeriSignOrder0($p_MerId,$PgKeyFile,$p_OrdId,$p_TransAmt,$p_MerDate,$p_TransType,$p_TransStat,$p_GateId,$p_MerPriv,$p_SysDate,$p_SysSeqId,$p_ChkValue);
        }else{
            $checkout = $pnrObj->VeriSingOrder($p_MerId,$PgKeyFile,$p_OrdId,$p_TransAmt,$p_MerDate,$p_TransType,$p_TransStat,$p_GateId,$p_MerPriv,$p_SysDate,$p_SysSeqId,$p_ChkValue);
        }

        if ($checkout == 0){
            if($p_TransStat == "S"){
                return PAY_SUCCESS;
            }else{
                $message = '支付失败,请立即与商店管理员联系';
                return PAY_FAILED;
            }
        }else{
            $message = '签名认证失败,请立即与商店管理员联系';
            return PAY_ERROR;
        }
    }

    function getfields(){
        return array(
                'member_id'=>array(
                        'label'=>'客户号',
                        'type'=>'string',
                        'helpMsg'=>'此处填写您的支付帐号、客户号或客户id等，此帐号在支付服务提供商处取得；'
                    ),
                'PrivateKey'=>array(
                        'label'=>'私钥',
                        'type'=>'string',
                        'helpMsg'=>'证书拆分过程中输入的保存私钥 “口令”'
                ),
                "key1"=>array(
                        'label'=>'私钥文件1',
                        'type'=>'file',
                        'helpMsg'=>'此处上传支付网关给你的“MerPrK你的商户号.key”文件；'
                ),
                'key2'=>array(
                        'label'=>'私钥文件2',
                        'type'=>'file',
                        'helpMsg'=>'此处上传支付网关给你的“PgPubk.key”文件；'
                )
            );
    }
}
?>
