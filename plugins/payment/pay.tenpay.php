<?php
require('paymentPlugin.php');
class pay_tenpay extends paymentPlugin{

    var $name = '腾讯财付通[即时到账]';//腾讯财付通
    var $logo = 'TENPAY';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $applyUrl = 'http://union.tenpay.com/mch/mch_register_b2c.shtml';//即时到帐
    //var $applyUrlAgain = 'https://www.tenpay.com/mchhelper/mch_register_c2c.shtml';//担保
    //var $submitUrl = 'http://portal.tenpay.com/cfbiportal/cgi-bin/cfbiin.cgi'; 
    var $submitUrl = 'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"1");
    var $supportArea =  array("AREA_CNY");
    var $desc = '财付通是腾讯公司为促进中国电子商务的发展需要，满足互联网用户价值需求，针对网上交易安全而精心推出的一系列服务。';
    var $intro = '财付通是腾讯公司于2005年9月正式推出专业在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。<br>财付通构建全新的综合支付平台，业务覆盖B2B、B2C和C2C各领域，提供卓越的网上支付及清算服务。<br>财付通先后荣膺2006年电子支付平台十佳奖、2006年最佳便捷支付奖、2006年中国电子支付最具增长潜力平台奖和2007年最具竞争力电子支付企业奖等奖项，并于2007年首创获得“国家电子商务专项基金”资金支持。<!--<br><br><font color="red">本接口需点击以下链接(二选一)进行在线签约后方可使用。</font>-->';    
    var $applyProp = array("postmethod"=>"get","sp_suggestuser"=>"2289480");//代理注册参数组
    var $orderby = 2;
    var $head_charset='gb2312';
    function toSubmit($payment){

        $merId = $this->getConf($payment['M_OrderId'], 'member_id');
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        $payment['M_Currency'] = "1";    //$order->M_Currency = "1";
        
        $orderdate = date("Ymd",$payment['M_Time']);    //$order->M_Time
        //$payment['M_Amount'] = ceil($payment['M_Amount'] * 100);    //$order->M_Amount

        $payment['M_Amount'] = (number_format($payment['M_Amount'],2,".","")*100);    //$order->M_Amount
        //$v_orderid = $merId.$orderdate."0000" . $payment['M_OrderId'];  //$order->M_OrderId
        $v_orderid = $merId.$orderdate.substr($payment['M_OrderId'],-10);
        //$md5string=strtoupper(md5("cmdno=1&date=".$orderdate."&bargainor_id=".$merId."&transaction_id=".$v_orderid."&sp_billno=".$payment['M_OrderNO']."&total_fee=".$payment['M_Amount']."&fee_type=1&return_url=".$this->callbackUrl."&attach=".$payment['M_OrderId']."&key=".$ikey)); 
        $authtype = $this->getConf($payment['M_OrderId'],'authtype');
        $agentid=$authtype?"1202822001":"1202822001";
        $subject = $payment['M_OrderNO'];
        $charset = $this->system->loadModel('utility/charset');
        $desc = $charset->utf2local($subject,'zh');
        $sp_billno = $charset->utf2local($subject,'zh');
        $str="agentid=".$agentid."&attach=".$payment['M_OrderId']."&bank_type=0&bargainor_id=".$merId."&cmdno=1&date=".$orderdate."&desc=".$desc."&fee_type=".$payment['M_Currency']."&key_index=1&return_url=".$this->callbackUrl."&sp_billno=".$sp_billno."&total_fee=".$payment['M_Amount']."&transaction_id=".$v_orderid."&ver=3&verify_relation_flag=1&key=".$ikey;
        $md5string=strtoupper(md5($str));
        $return["cmdno"] = "1";
        $return["date"] = $orderdate;
        $return["bank_type"] = "0";
        $return["desc"] = $subject;
        $return["purchaser_id"] = "";
        $return["bargainor_id"] = $merId;
        $return["transaction_id"] = $v_orderid;//$payment['M_OrderId'];
        $return["sp_billno"] = $payment['M_OrderNO'];   //$order->M_OrderNO;
        $return["total_fee"] = $payment['M_Amount'];    //$order->M_Amount;
        $return["fee_type"] =  $payment['M_Currency'];  //$order->M_Currency;
        $return["return_url"] = $this->callbackUrl;
        $return["attach"] = $payment['M_OrderId'];
        $return["agentid"] = $agentid;
        $return["key_index"]=1;
        $return["verify_relation_flag"]=1;
        $return["ver"] = 3;
        $return["ikey"]=$ikey;
        $return["sign"] = $md5string;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){ 
        $authtype = $this->getConf($in["attach"],'authtype');
        $agentid=$authtype?"1202822001":"1202822001";
        $cmdno=$in["cmdno"];
        $pay_result=$in["pay_result"];
        $pay_info=$in["pay_info"];
        $date=$in["date"];
        $bargainor_id=$in["bargainor_id"];
        $transaction_id=$in["transaction_id"];
        $sp_billno=$in["sp_billno"];
        $total_fee=$in["total_fee"];
        $fee_type=$in["fee_type"];
        $attach=$in["attach"];
        $sign=$in["sign"];
        $key_index=$in['key_index'];
        $ver = $in['ver'];
        $verify_relation_flag = $in['verify_relation_flag'];
        $mac ="";
        $v_orderid = substr($v_order_no,-6);
        $ikey = $this->getConf($in['attach'], 'PrivateKey');
        foreach($in as $key => $val){
            if ($key<>'pay_time'&&$key<>'sign'&&$val<>''){
                $str.=$key."=".urldecode(trim($val))."&";
            }
        }
        $str.="key=".$ikey; 
        $md5mac=strtoupper(md5($str));
        $paymentId=$in['attach'];
        $money=intval($total_fee)/100;
        $tradeno = $in['transaction_id'];
       // error_log(var_export($md5mac,true),3,'c:/dd.txt');
//error_log(var_export($sign,true),3,'c:/vv.txt');
        if($md5mac!=$sign){
           $message = '签名认证失败,请立即与商店管理员联系';
            return PAY_ERROR;
        }else{
            if($pay_result==0){
                return PAY_SUCCESS;
            }else{
                $message = '支付失败,请立即与商店管理员联系'.$pay_info;
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
                ),
                'authtype'=>array(
                    'label'=>'商家支付模式',
                    'type'=>'select',
                    'options'=>array('0'=>'套餐包量商家','1'=>'单笔支付商家')
                )
            );
    }
    function applyForm($agentfield){
      //$tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即注册即时到帐帐户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:void(0)" onclick="document.applyFormAgain.submit()">立即注册担保帐户</a>';
      //$tmp_form= '<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即申请财付通<font color="red"><b>套餐</b></font>即时账户(适合大商家)</a><br>';
      $tmp_form.='<a href="javascript:void(0)" onclick="document.applyFormAgain.submit()">立即申请财付通<font color="red"><b>单笔</b></font>即时账户(适合小商家)</a>';
      $tmp_tc_form="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      $tmp_db_form="<form name='applyFormAgain' method='".$agentfield['postmethod']."'  action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($agentfield as $key => $val){
          if ($key == "payagentkey"){
              $tmp_tc_form.="<input type='hidden' name='".$key."' value='".$val."JSDZ'>";
              $tmp_db_form.="<input type='hidden' name='".$key."' value='".$val."JSDZ'>";
          }
          else {
              $tmp_tc_form.="<input type='hidden' name='".$key."' value='".$val."'>";
              if ($key=="sp_suggestuser")
                  $val="1202822001";
              $tmp_db_form.="<input type='hidden' name='".$key."' value='".$val."'>";
          }
      }
      $tmp_tc_form.="</form>";
      $tmp_db_form.="</form>";
      $tmp_form.=$tmp_tc_form.$tmp_db_form;
      return $tmp_form;
   }
}
?>
