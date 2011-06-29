<?php
require('paymentPlugin.php');
class pay_alipaytrad extends paymentPlugin{

    var $name = '支付宝';
    var $logo = 'ALIPAYTRAD';
    var $version = 20080523;
    var $charset = 'utf8';
    //var $applyUrl = 'https://www.alipay.com/himalayas/market.htm';
    //var $intro = '支付宝网站(www.alipay.com) 是国内先进的网上支付平台。<br>本接口是支付宝免费接口，无需预付交易手续费即可使用，目前支付宝按照每笔<font color="red">1.5%</font>的比例（以支付宝最新规定为准）按单自动扣除手续费。<br><br><font color="red">本接口需点击下方【立即申请支付宝】链接进行免费的在线签约后方可使用。</font>';
    var $submitUrl = 'https://www.alipay.com/cooperate/gateway.do?_input_charset=utf-8'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"01");
    var $supportArea =  array("AREA_CNY");
    var $orderby = 4;
    //var $applyProp = array("postmethod"=>"GET","type"=>"from_agent_contract","id"=>"C433530444855584111X");
    var $head_charset = "utf-8";
    function pay_alipaytrad(&$system){
        parent::paymentPlugin($system);
        $regIp=isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        $this->intro='<b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>ShopEx联合支付宝推出优惠套餐：无预付/年费，单笔费率1.5%，无流量限制。</b><div style="padding:10px 0 0 388px"><a  href="javascript:void(0)" onclick="document.ALIPAYFORM.submit();"><img src="../plugins/payment/images/alipaysq.png"></a></div><div>如果您已经和支付宝签约了其他套餐，同样可以点击上面申请按钮重新签约，即可享受新的套餐。<br>如果不需要更换套餐，请将签约合作者身份ID等信息在下面填写即可，<a href="http://www.shopex.cn/help/ShopEx48/help_shopex48-1235733634-11323.html" target="_blank">点击这里查看使用帮助</a><form name="ALIPAYFORM" method="GET" action="http://top.shopex.cn/recordpayagent.php" target="_blank"><input type="hidden" name="payagentname" value="支付宝"><input type="hidden" name="payagentkey" value="ALIPAY"><input type="hidden" name="market_type" value="from_agent_contract"><input type="hidden" name="customer_external_id" value="C433530444855584111X"><input type="hidden" name="regIp" value="'.$regIp.'"><input type="hidden" name="domain" value="'.$this->system->base_url().'"></form></div>';
    }
    function toSubmit($payment){
        
        $merId = $this->getConf($payment['M_OrderId'], 'member_id'); //帐号
        $pKey = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        $key = $pKey==''?'afsvq2mqwc7j0i69uzvukqexrzd0jq6h':$pKey;//私钥值，
        $ret_url = $this->callbackUrl;
        $server_url = $this->serverCallbackUrl;
        
        $amount = number_format($payment['M_Amount'],2,".","");
        $shopName = $this->system->getConf('system.shopname');
        if(strpos($shopName,'&')){
            $message = '网店名称中含有非法字符，请联系你的商户！';
            return PAY_FAILED;
        }
        $subject = $shopName." 订单号:".$payment['M_OrderNO'];
        $subject = str_replace("'",'`',trim($subject));
        $subject = str_replace('"','`',$subject);
        $orderDetail = str_replace("'",'`',trim($orderDetail));
        $orderDetail = str_replace('"','`',$orderDetail);
        $return = array();
        $virtual_method = $this->getConf($payment['M_OrderId'], 'virtual_method');
        $real_method = $this->getConf($payment['M_OrderId'], 'real_method');
        switch ($real_method){
            case '0': 
                $return['service'] = 'trade_create_by_buyer';
                break;
            case '1':
                $return['service'] = 'create_direct_pay_by_user';
                break;
            case '2':
                $return['service'] = 'create_partner_trade_by_buyer';
                break;
        }


        $return['logistics_type'] = "POST";
        $return['logistics_payment'] = "BUYER_PAY";
        $return['logistics_fee'] = '0.00';

        $return['agent'] = 'C433530444855584111X';
        $return['payment_type'] = 1;
        $return['partner'] = $this->getConf($payment['M_OrderId'], 'PrivateKey')==''?'2088002003028751':$merId;
        $return['return_url'] = $ret_url;        
        $return['notify_url'] = $server_url;
        $return['subject'] = $subject;
        $return['body'] = '网店订单';
        $return['out_trade_no'] = $payment['M_OrderId'];
        $return['price'] = $amount;
        $return['quantity'] = 1;
        
        if(preg_match("/^\d{16}$/",$merId)){
            $return['seller_id'] = $merId;
        }else{
            $return['seller_email'] = $merId;
        }
        $return['buyer_msg'] =  $payment['M_Remark']?$payment['M_Remark']:'无留言';
        $return['_input_charset'] = "utf-8";
        ksort($return);
        reset($return);
        $mac= "";
        foreach($return as $k=>$v)
        {
            $mac .= "&{$k}={$v}";
        }
        $mac = substr($mac,1);
        $return['sign'] = md5($mac.$key);  //验证信息
        $return['sign_type'] = 'MD5';  //验证信息
        //$return['ikey']=$key;
        unset($return['_input_charset']);
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $merId = $this->getConf($in['out_trade_no'],'member_id'); //帐号
        $pKey = $this->getConf($in['out_trade_no'],'PrivateKey');
        $key = $pKey==''?'afsvq2mqwc7j0i69uzvukqexrzd0jq6h':$pKey;//私钥值
        ksort($in);
        //检测参数合法性
        $temp = array();
        foreach($in as $k=>$v){
            if($k!='sign'&&$k!='sign_type'&&$k!='gOo'){
                $temp[] = $k.'='.$v;
            }
        }
        $testStr = implode('&',$temp).$key;
        if($in['sign']==md5($testStr)){
            $paymentId = $in['out_trade_no'];    //支付单号
            $money = $in['total_fee'];
            $message = $in['body'];
            $tradeno = $in['trade_no'];
            switch($in['trade_status']){
                case 'TRADE_FINISHED':
                    if($in['is_success']=='T'){                        
                        return PAY_SUCCESS;
                    }else{                        
                        return PAY_FAILED;
                    }
                    break;
                case 'TRADE_SUCCESS':
                    if($in['is_success']=='T'){                        
                        return PAY_SUCCESS;
                    }else{                        
                        return PAY_FAILED;
                    }
                    break;
                case 'WAIT_SELLER_SEND_GOODS':
                    if($in['is_success']=='T'){
                        return PAY_PROGRESS;
                    }else{                        
                        return PAY_FAILED;
                    }
                    break;
            }

        }else{
            $message = 'Invalid Sign';            
            return PAY_ERROR;
        } 
    }

    function serverCallback($in,&$paymentId,&$money,&$message){
        exit('reserved');
    }

    function applyForm($agentfield){
      $tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();">立即申请支付宝</a>';
      $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($agentfield as $key => $val){
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }
      $tmp_form.="</form>";
      return $tmp_form;
    }

    function getfields(){
        return array(
            'member_id'=>array(
                'label'=>'合作者身份(parterID)',
                'type'=>'string'
            ),
            'PrivateKey'=>array(
                'label'=>'交易安全校验码(key)',
                'type'=>'string'
            ),
            'real_method'=>array(
                    'label'=>'选择接口类型',
                    'type'=>'select',
                    'options'=>array('0'=>'使用标准双接口','2'=>'使用担保交易接口','1'=>'使用即时到帐交易接口',)
            )
        );
    }
}
?>
