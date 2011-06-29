<?php
require('paymentPlugin.php');
class pay_cncard extends paymentPlugin{

    var $name = '云网在线支付';//云网在线支付
    var $logo = 'CNCARD';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $applyUrl = 'http://www.cncard.net/api/agentreg.asp';
    var $submitUrl = 'https://www.cncard.net/purchase/getorder.asp'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"CNY");
    var $supportArea =  array("AREA_CNY");
    var $intro='';
    var $applyProp = array("postmethod"=>"get","aid"=>"10054","sign"=>"79cccba5af191e88fb9edd3949796053");
    var $orderby = 9;
    var $head_charset = "gb2312";
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        $payment['M_Currency'] = "0";

        $orderdate = date("Ymd",$payment["M_Time"]);//date("Ymd",$order->M_Time);
        $md5string = md5($merId.$payment["M_OrderId"].$payment["M_Amount"].$orderdate."0"."1".$this->callbackUrl."0"."0".$ikey);

        $return['c_mid'] = $merId;
        $return['c_order'] = $payment['M_OrderId'];//$order->M_OrderId;
        $charset=$this->system->loadModel('utility/charset');
        $return['c_name'] = $payment['R_Name'];
        $return['c_address'] = $payment['R_Address'];
        $return['c_tel'] = $payment['R_Telephone'];
        $return['c_post'] = $payment['R_Postcode'];
        $return['c_email'] = $payment["R_Email"];//$order->R_Email;
        $return['c_orderamount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['c_ymd'] = $orderdate;
        $return['c_moneytype'] = $payment["M_Currency"];//$order->M_Currency;
        $return['c_retflag'] = "1";
        $return['c_returl'] = $this->callbackUrl;
        $return['c_language'] = "0";
        $return['notifytype'] = "0";
        $return['c_signstr'] = $md5string;        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){    
        $c_order = $in["c_order"];        //订单号
        $c_orderamount = $in["c_orderamount"];   //订单金额
        $c_succmark = $in["c_succmark"];    //Y-成功 N-失败
        $c_cause = $in["c_cause"];   //支付失败时为失败原因
        $c_signstr = $in["c_signstr"];
        $tradeno = $in['c_transnum'];
        $ikey = $this->getConf($c_order, 'PrivateKey');
        //-----------重新计算md5的值--------------------------------------------
        $content= md5($in["c_mid"].$in["c_order"].$in["c_orderamount"].$in["c_ymd"].$in["c_transnum"].$in["c_succmark"].$in["c_moneytype"].$in["c_memo1"].$in["c_memo2"].$ikey);
        $paymentId=$c_order;
        $money = $c_orderamount;
        
        if ($c_signstr!=$content){
            $message = '签名认证失败,请立即与商店管理员联系';
            return PAY_ERROR;
        }else{
            if($c_succmark == "Y"){
                $message="支付成功";
                return PAY_SUCCESS;
            }else{
                $message = '支付失败,请立即与商店管理员联系'."($c_cause)";
                return PAY_FAILED;
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
   function applyForm($agentfield){
      $tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即注册</a>';
      $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($agentfield as $key => $val){
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }
      $tmp_form.="</form>";
      return $tmp_form;
   }
}
?>
