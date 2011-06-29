<?php
require('paymentPlugin.php');
class pay_yeepay extends paymentPlugin{

    var $name = '易宝支付 (在线支付接口)';//
    var $logo = 'YEEPAY';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $applyUrl = 'https://www.yeepay.com/selfservice/AgentService.action';
    var $submitUrl = 'https://www.yeepay.com/app-merchant-proxy/node'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"01");
    var $supportArea = array('AREA_CNY');
    var $desc = '';
    var $intro = '首批通过国家信息安全系统认证、获得企业信用等级AAA级证书、注册资本1亿元；<br>1%手续费、0年费、支持上百种银行卡、信用卡及神州行卡支付；<br>网上签约、轻松结算、7X24小时客户服务、共享千万优质会员资源。';
    var $applyProp = array("postmethod"=>"POST","p0_Cmd"=>"AgentRegister","p1_MerId"=>"10000456219");//代理注册参数组
    var $orderby = 18;
    var $head_charset = "gb2312";

    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'], 'member_id');
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');

        $b = 64;
        if (strlen($ikey) > $b){
            $ikey = pack("H*",md5($ikey));
        }
        $ikey = str_pad($ikey, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $ikey ^ $ipad;
        $k_opad = $ikey ^ $opad;
        //$data = "Buy".$merId.$order->M_OrderId.$order->M_Amount.$order->M_Currency.$order->M_OrderNO.$order->R_Email.$this->callbackUrl."0".$merId;
        $data = "Buy".$merId.$payment["M_OrderId"].$payment["M_Amount"].$payment["M_Currency"].$payment["M_OrderNO"].$payment["R_Email"].$this->callbackUrl."0".$merId;
        $hmac = md5($k_opad . pack("H*",md5($k_ipad.$data)));
        
        $return["p0_Cmd"] = Buy;
        $return["p1_MerId"] = $merId;
        $return["p2_Order"] = $payment["M_OrderId"];//$order->M_OrderId;
        $return["p3_Amt"]    = $payment["M_Amount"];//$order->M_Amount;
        $return["p4_Cur"]    = $payment["M_Currency"];//$order->M_Currency;
        $return["p5_Pid"]    = $payment["M_OrderNO"];//$order->M_OrderNO;
        $return["p6_Pcat"]    = $payment["R_Email"];//$order->R_Email;
        $return["p7_Pdesc"] = "";
        $return["p8_Url"]    = $this->callbackUrl;
        $return["p9_SAF"]    = 0;
        $return["pa_MP"]    = $merId;
        $return["pd_FrpId"] = "";
        $return["hmac"]        = $hmac;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){        
        $sCmd = $in['r0_Cmd'];
        $sErrorCode = $in['r1_Code'];
        $sTrxId = $in['r2_TrxId'];
        $amount = $in['r3_Amt'];
        $cur = $in['r4_Cur'];
        $productId = $in['r5_Pid'];
        $orderId = $in['r6_Order'];
        $userId = $in['r7_Uid'];
        $MP = $in['r8_MP'];
        $bType = $in['r9_BType'];
        $svrHmac = $in['hmac'];
        $money = $amount;
        $paymentId = $orderId;
        $key = $this->getConf($orderId, 'PrivateKey');
        $data = $MP.$sCmd.$sErrorCode.$sTrxId.$amount.$cur.$productId.$orderId.$userId.$MP.$bType;
        $charset = $this->system->loadModel('utility/charset');
        //$data = iconv("GB2312","UTF-8",$data);
        $data = $charset->utf2local($data,'zh');

        $b = 64;
        if (strlen($key) > $b){
            $ikey = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        $hmac = md5($k_opad . pack("H*",md5($k_ipad.$data)));

        if (strtoupper($sCmd)=="BUY"&&$svrHmac == $hmac){
            if($sErrorCode == 1)
                return PAY_SUCCESS;
            else
                $message = '交易失败';
                return PAY_FAILED;
            
        }else{
            $message = '验证失败';
            return PAY_ERROR;# 
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
      $key = "A2mo49beDQC87v94nD400439270yff1Pt212H24FI6ET68GT84HC05AtjfB7";
      $msgtype = "AgentRegister";
      $merid = "10000456219";
      $domain = "";
      $data=$msgtype.$merid.$domain;
      $agentfield['hmac'] = $this->hmac($key,$data);
      $tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit();">立即注册</a>';
      $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($agentfield as $key => $val){
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }
      $tmp_form.="</form>";
      return $tmp_form;
   }
   function hmac($key, $data)
   {
        //需要配置环境支持iconv，否则中文参数不能正常处理
        if (function_exists('utf2local')){
            $key  = utf2local($key,'zh');
            $data = utf2local($data,'zh');
        }else{
            $key = iconv("GB2312","UTF-8",$key);
            $data = iconv("GB2312","UTF-8",$data);
        }
        //$key = utf2local($key,"zh");
        //$data = utf2local($data,"zh");
        //$key = local2utf($key,"zh");
        //$data = local2utf($data,"zh");

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
        $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }
}
?>
