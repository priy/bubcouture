<?php
require('paymentPlugin.php');
class pay_99bill extends paymentPlugin{

    var $name = '快钱网上支付';//快钱网上支付
    var $logo = '99BILL';
    var $version = 20070902;
    var $charset = 'utf8';
    var $applyUrl = 'https://www.99bill.com/website/signup/memberunitedsignup.htm';
    //var $submitUrl = 'https://www.99bill.com/webapp/receiveMerchantInfoAction.do';
    var $submitUrl = 'https://www.99bill.com/gateway/recvMerchantInfoAction.htm';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"1");
    var $supportArea =  array("AREA_CNY");
    var $desc = 'ShopEx联合快钱推出：免费签约，1%优惠费率，更有超值优惠的信用卡支付。<bR>快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。';
    var $intro = '<b><h3>ShopEx联合快钱推出：免费签约，1%优惠费率，更有超值优惠的信用卡支付。</h3></b><bR>快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。<b><h3>注：本接口为银行直连，数据显示，可以提升78%的潜在消费者完成购买行为。</h3></b>';
     var $applyProp = array("postmethod"=>"post","version"=>"150120","inputCharset"=>1,"signType"=>1,"merchantMbrCode"=>'10017518267',"requestId"=>'',"registerType"=>1,"userId"=>'',"userType"=>"2","userEmail"=>"","userMobile"=>"","userName"=>"","linkMan"=>"","linkTel"=>"","orgName"=>"","websiteAddr"=>"","backUrl"=>'',"ext1"=>"","ext2"=>"",'key'=>'LHLEF8EA4ZY853NF');
    var $orderby = 1;
    var $head_charset="utf-8";
    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'], 'member_id');
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');//私钥值，商户可上99BILL快钱后台自行设定
        $connecttype = $this->getConf($payment['M_OrderId'], 'ConnectType');
        if ($connecttype){
            $bankId = $payment['payExtend']['bankId'];
            $payType='10';
        }
        $payment['M_Amount']=ceil($payment['M_Amount'] * 100);
        $orderTime = date('YmdHis',$payment['M_Time']?$payment['M_Time']:time());
        $return['inputCharset']="1";
        $return['bgUrl'] = $this->callbackUrl;
        $return['version'] = "v2.0";
        $return['language']="1";
        $return['signType']="1";
        $return['merchantAcctId'] = $merId;
        $return['payerName']=$payment['P_Name'];
        $return['payerContactType']="1";//支付人联系方式类型.固定选择值，目前只能为电子邮件
        $return['payerContact']=$payment['P_Email'];//支付人联系方式
        $return['orderId']= $payment['M_OrderId'];
        $return['orderAmount'] = $payment['M_Amount'];
        $return['orderTime'] = $orderTime;
        $return['productName'] = $payment['M_OrderNO'];
        $return['productNum'] = "1";
        $return['productId'] = "";
        $return['productDesc'] = $payment['M_Remark'];
        $return['ext1']= "";
        $return['ext2'] = "";
        $return['payType'] = $payType?$payType:"00";
        $return['bankId'] = $bankId?$bankId:'';
        $return['redoFlag'] = 1;//是否重复提交同一个订单
        $return['pid'] = "10017518267";//合作ID
        foreach($return as $k=>$v){
            if ($v)
                $str.=$k."=".$v."&";
        }
        $signMsg=strtoupper(md5(substr($str,0,strlen($str)-1)."&key=".$ikey));
        $return['signMsg']=$signMsg;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){


        $system = &$GLOBALS['system'];
        $url = $system->mkUrl('paycenter',$act='result');
        $merchantAcctId=trim($in['merchantAcctId']);
        $version=trim($in['version']);
        $language=trim($in['language']);
        $signType=trim($in['signType']);
        $payType=trim($in['payType']);
        $orderId=trim($in['orderId']);
        $orderTime=trim($in['orderTime']);
        $bankId = trim($in['bankId']);
        //获取原始订单金额
        ///订单提交到快钱时的金额，单位为分。
        ///比方2 ，代表0.02元
        $orderAmount=trim($in['orderAmount']);
        $dealId=trim($in['dealId']); //获取该交易在快钱的交易号
        $bankDealId=trim($in['bankDealId']); //如果使用银行卡支付时，在银行的交易号。如不是通过银行支付，则为空
        $dealTime=trim($in['dealTime']);
        //获取实际支付金额
        ///单位为分
        ///比方 2 ，代表0.02元
        $payAmount=trim($in['payAmount']);
        //获取交易手续费
        ///单位为分
        ///比方 2 ，代表0.02元
        $fee=trim($in['fee']);
        //获取处理结果
        ///10代表 成功; 11代表 失败
        ///00代表 下订单成功（仅对电话银行支付订单返回）;01代表 下订单失败（仅对电话银行支付订单返回）
        $payResult=trim($in['payResult']);
        $errCode=trim($in['errCode']);
        $signMsg=trim($in['signMsg']);    //获取加密签名串

        $key=$this->getConf($orderId,'PrivateKey');

        //生成加密串。必须保持如下顺序。
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"merchantAcctId",$merchantAcctId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"version",$version);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"language",$language);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"signType",$signType);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"payType",$payType);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"bankId",$bankId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"orderId",$orderId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"orderTime",$orderTime);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"orderAmount",$orderAmount);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"dealId",$dealId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"bankDealId",$bankDealId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"dealTime",$dealTime);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"payAmount",$payAmount);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"fee",$fee);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"ext1",$ext1);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"ext2",$ext2);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"payResult",$payResult);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"errCode",$errCode);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal,"key",$key);
        $merchantSignMsg= md5($merchantSignMsgVal);
        $paymentId=$orderId;
        $money = $payAmount/100;
        $tradeno = $dealId;
        $system = &$GLOBALS['system'];
        //$sUrl = $system->base_url();
        $url = $system->mkUrl('paycenter',$act='result');

        ///首先进行签名字符串验证
        if(strtoupper($signMsg) == strtoupper($merchantSignMsg)){

            switch($payResult){
                  case "10":
                    $rtnOk=1;
                    $rtnUrl=$url."?payment_id=".$orderId;
                    echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
                    return PAY_SUCCESS;
                    break;
                  default:
                    $rtnOk=1;
                    $rtnUrl=$url."?payment_id=".$orderId;
                    echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
                    return PAY_FAIL;
                    break;
            }
        }else{

            $message="签名认证失败！";
            $rtnOk=1;
            $rtnUrl=$url."?payment_id=".$orderId;
            echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
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
                ),
                'ConnectType'=>array(
                    'label'=>'顾客付款类型',
                    'type'=>'radio',
                    'options'=>array('0'=>'登录快钱支付','1'=>'银行直接支付'),
                    'event'=>'showbank',
                    'eventscripts'=>'<script>function showbank(obj){if (obj.value==1){$(\'bankShow\').show();}else {$(\'bankShow\').hide();}}</script>',
                    'extendcontent'=>array(
                        array(
                            "property"=>array(
                                "type"=>"checkbox",//后台显示方式
                                "name"=>"bankId",
                                "size"=>6,
                                "extconId"=>"bankShow",
                                "display"=>0,
                                "fronttype"=>"radio", //前台显示方式
                                "frontsize"=>6,
                                "frontname"=>"showbank"
                            ),
                            "value"=>array(
                                array("value"=>"ICBC","imgname"=>"bank_icbc.gif","name"=>"中国工商银行"),
                                array("value"=>"CMB","imgname"=>"bank_cmb.gif","name"=>"招商银行"),
                                array("value"=>"ABC","imgname"=>"bank_abc.gif","name"=>"中国农业银行"),
                                array("value"=>"CCB","imgname"=>"bank_ccb.gif","name"=>"中国建设银行"),
                                array("value"=>"SPDB","imgname"=>"bank_spdb.gif","name"=>"上海浦东发展银行"),
                                array("value"=>"BCOM","imgname"=>"bank_bcom.gif","name"=>"交通银行"),
                                array("value"=>"CMBC","imgname"=>"bank_cmbc.gif","name"=>"中国民生银行"),
                                array("value"=>"SDB","imgname"=>"bank_sdb.gif","name"=>"深圳发展银行"),
                                array("value"=>"GDB","imgname"=>"bank_gdb.gif","name"=>"广东发展银行"),
                                array("value"=>"CITIC","imgname"=>"bank_citic.gif","name"=>"中信银行"),
                                array("value"=>"HXB","imgname"=>"bank_hxb.gif","name"=>"华夏银行"),
                                array("value"=>"CIB","imgname"=>"bank_cib.gif","name"=>"兴业银行"),
                                array("value"=>"GZRCC","imgname"=>"bank_gzrcc.gif","name"=>"广州市农村信用合作社"),
                                array("value"=>"GZCB","imgname"=>"bank_gzcb.gif","name"=>"广州市商业银行"),
                                array("value"=>"SHRCC","imgname"=>"bank_shrcc.gif","name"=>"上海农村商业银行"),
                                array("value"=>"POST","imgname"=>"bank_post.gif","name"=>"中国邮政储蓄"),
                                array("value"=>"BOB","imgname"=>"bank_bob.gif","name"=>"北京银行"),
                                array("value"=>"BOC","imgname"=>"bank_boc.gif","name"=>"中国银行"),
                                array("value"=>"CBHB","imgname"=>"bank_cbhb.gif","name"=>"渤海银行"),
                                array("value"=>"BJRCB","imgname"=>"bank_bjrcb.gif","name"=>"北京农村商业银行"),
                                array("value"=>"CEB","imgname"=>"bank_ceb.gif","name"=>"中国光大银行"),
                                array("value"=>"NJCB","imgname"=>"bank_njcb.gif","name"=>"南京银行"),
                                array("value"=>"BEA","imgname"=>"bank_bea.gif","name"=>"东亚银行"),
                                array("value"=>"NBCB","imgname"=>"bank_nbcb.gif","name"=>"宁波银行"),
                                array("value"=>"HZB","imgname"=>"bank_hzb.gif","name"=>"杭州银行"),
                                array("value"=>"PAB","imgname"=>"bank_pab.gif","name"=>"平安银行")
                            )
                        )

                    )
                )
            );
    }

    function appendParam($returnStr,$paramId,$paramValue){
        if($returnStr != ""){
            if($paramValue != ""){
                $returnStr.="&".$paramId."=".$paramValue;
            }
        }else{
            If($paramValue!=""){
                $returnStr=$paramId."=".$paramValue;
            }
        }
        return $returnStr;
    }
    function applyForm($agentfield){
         /*$tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();"><img src="'.$this->system->base_url().'plugins/payment/images/99bill_apply.gif"></a>&nbsp;&nbsp;&nbsp;<font color="green">绿卡用户申请快钱支付，费率更低，</font><a href="http://www.shopex.cn/shopex_price/sq/index.html" target="_blank"><font color="green">点击了解绿卡</font></a>';
          $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
          foreach($agentfield as $key => $val){
              if ($key=="requestId"){
                  $val=date("YmdHis");
                  $this->applyProp[$key]=$val;
              }elseif ($key=="userId"){
                  $val=mt_rand(111111,999999)."_".date("YmdHis");
                  $this->applyProp[$key]=$val;
              }elseif ($key=="backUrl"){
                  $val=$this->system->base_url()."plugins/payment/reg/pay.99billreg.php";
                  $this->applyProp[$key]=$val;
              }
              elseif ($key=="ext1"){
                  $val=$this->system->getConf('certificate.auth_type');
                  $val=$val?$val:'free';
                  $this->applyProp[$key]=$val;
              }
              if ($key<>'key')
                $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
          }
          unset($this->applyProp['postmethod']);
          foreach($this->applyProp as $key => $val){
            $tmpStr=$this->appendParam($tmpStr,$key,$val);
          }
          $signMsg=strtoupper(md5($tmpStr));
          $tmp_form.="<input type='hidden' name='signMsg' value='".$signMsg."'>";
          $tmp_form.="</form>";
          return $tmp_form;*/
          $certid=$this->system->getConf('certificate.id');
          $url=urlencode($this->system->base_url());
          $tmp_form='<a href="http://service.shopex.cn/checkcert.php?pay_id=2&certi_id='.$certid.'&url='.$url.'
" target="_blank"><img src="'.$this->system->base_url().'plugins/payment/images/99bill_apply.gif"></a>&nbsp;&nbsp;&nbsp;<font color="green">绿卡用户申请快钱支付，费率更低，</font><a href="http://www.shopex.cn/shopex_price/sq/index.html" target="_blank"><font color="green">点击了解绿卡</font></a>';
          return $tmp_form;
/*
$certid=$this->system->getConf('certificate.id');
          $url=urlencode($this->system->base_url());
          $tmp_form='<a href="http://service.shopex.cn/checkcert.php?pay_id=2&certi_id='.$certid.'&url='.$url.'
" target="_blank"><img src="'.$this->system->base_url().'plugins/payment/images/99bill_apply.gif"></a>&nbsp;&nbsp;&nbsp;<font color="green">绿卡用户申请快钱支付，费率更低，</font><a href="http://www.shopex.cn/shopex_price/sq/index.html" target="_blank"><font color="green">点击了解绿卡</font></a>';
*/
    }
}
?>
