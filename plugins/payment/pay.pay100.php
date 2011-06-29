<?php
require('paymentPlugin.php');
class pay_pay100 extends paymentPlugin{

    var $name = 'PAY100.COM 百付通';//PAY100.COM 百付通
    var $logo = 'PAY100';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://www.pay100.com/interface/Professional/paypre.aspx'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"1001");
    var $supportArea =  array("AREA_CNY");
    var $desc = 'PAY100.COM';
    var $orderby = 26;
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        $tmp_url = $this->url."index.php?gOo=pay100_reply.do&";
        //$orderdate = date("Y-m-d H:i:s",$order->M_Time);
        //$strRnote = strtoupper($order->M_Remark);
        //$StrContent = "1001".$merId.$order->M_OrderId.$order->M_Amount.$order->M_Currency.$orderdate.$order->M_OrderNO.$strRnote."1"."1"."1".$this->callbackUrl.$$this->callbackUrl.$ikey;

        $orderdate = date("Y-m-d H:i:s",$payment["M_Time"]);
        $strRnote = strtoupper($payment["M_Remark"]);
        $StrContent = "1001".$merId.$payment["M_OrderId"].$payment["M_Amount"].$payment["M_Currency"].$orderdate.$payment["M_OrderNO"].$strRnote."1"."1"."1".$this->callbackUrl.$$this->callbackUrl.$ikey;

        $return['OrderType'] = "1001";
        $return['CoagentID'] = "";
        $return['InceptUserName'] = $merId;
        $return['OrderNumber'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['Amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['MoneyCode'] = $payment["M_Currency"];//$order->M_Currency;
        $return['TransDateTime'] = $orderdate;
        $return['Title'] = $payment["M_OrderNO"];//$order->M_OrderNO;
        $return['Content'] = $payment["M_Remark"];//$order->M_Remark;
        $return['CompleteReturn'] = "1";
        $return['FailReturn'] = "1";
        $return['ReturnValidate'] = "1";
        $return['ReturnUrl'] =  $this->callbackUrl;
        $return['RedirectUrl'] = $this->callbackUrl;
        $return['SignCode'] = strtoupper(md5($StrContent));
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        $OrderType = $in['OrderType'];    //商家ID
        $InceptUserName = $in['InceptUserName'];            //交易号
        $PayUserName = $in['PayUserName'];                //交易金额
        $OrderNumber = $in['OrderNumber'];                    //交易日期
        $StateCode = $in['StateCode'];            //交易结果，"Y"表示成功，"N"表示失败
        $Amount = $in['Amount'];
        $MoneyCode = $in['MoneyCode'];    //商家ID
        $TransDateTime = $in['TransDateTime'];            //交易号
        $TransCompleteDateTime = $in['TransCompleteDateTime'];                //交易金额
        $TransType = $in['TransType'];                    //交易日期
        $PledgeDay = $in['PledgeDay'];            //交易结果，"Y"表示成功，"N"表示失败
        $Memo1 = $in['Memo1'];
        $Memo2 = $in['Memo2'];    //商家ID
        $SignCode = $in['SignCode'];    //商家ID
    
        $paymentId = $OrderNumber;
        $money = $Amount;

        $key = $this->getConf($OrderNumber, 'PrivateKey');
        $strText = $OrderType.$InceptUserName.$PayUserName.$OrderNumber.$StateCode.$Amount.$MoneyCode
                .$TransDateTime.$TransCompleteDateTime.$TransType.$PledgeDay.$Memo1.$Memo2.$key;
        $mac = md5($strText);
        if (strtoupper($mac)==strtoupper($SignCode)){
            switch ($StateCode){
                //成功支付
                case "1001":
                    return PAY_SUCCESS;
                    break;
                //支付失败
                case "1002":
                    $message = '支付失败,请立即与商店管理员联系';
                    return PAY_FAILED;
                    break;
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
