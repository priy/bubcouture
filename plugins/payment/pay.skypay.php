<?PHP
    require("paymentPlugin.php");
    class pay_skypay extends paymentPlugin{
        var $name = "我付了储值卡支付(OK卡等)";
        var $logo = 'SKYPAY';
        var $version = '20080901';
        var $charset = 'gb2312';
        var $info = '我付了支付平台( http://www.wofule.com.cn )专注集中于为商户提供各种非银行卡资金源（联华OK卡、手机储值卡等）线上和线下的全方位支付服务，通过掌捷的非银行支付服务可以为商户提供多渠道的资金来源，同时带来更多潜在的用户群体。（中国市场上十亿多张的预付费的卡，并且每秒钟有1张预付费卡在增值。由于预付卡市场有超过90%的现存卡并不是自买自用型的,因此决定了大量的预付费卡持有者具有很强的消费欲望,并且希望能够找到更多的商户进行基于预付费卡的消费）';
        var $intro="我付了储值卡支付网关支持了如百联OK卡等在内的国内主流预付费卡（消费储值卡）的支付，商户添加我付了储值卡支付网关后，即可让用户持消费储值卡在商户处消费。<br>中国市场上十亿多张的预付费的卡，并且每秒钟有1张预付费卡在增值。由于预付卡市场有超过90%的现存卡并不是自买自用型的，因此决定了大量的预付费卡持有者具有很强的消费欲望。<br>集成我付了储值卡支付网关后，可以为商户吸引大量的预付费卡用户群及消费资金，极大提升商户的销售收入。<br><br>网关自助接入服务：<a href='http://www.wofule.com.cn/merchant_regist' target='_blank'>立即申请我付了</a><br>客服电话：400-820-7040 ";
        var $submitUrl='http://gateway.wofule.com.cn/gateway/paymentorder';
        var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
        var $supportCurrency =  array("CNY"=>"CNY");
        var $supportArea =  array("AREA_CNY");
        var $head_charset = 'gb2312';
        var $method = 'post';
        var $orderby = 12;
        function toSubmit($payment){
            $merid=$this->getConf($payment['M_OrderId'],'member_id');
            $key = $this->getConf($payment['M_OrderId'],'PrivateKey');
            $charset = $this->system->loadModel('utility/charset');
            $shopName = $this->system->getConf('system.shopname');
            $return['mer_id'] = $merid;
            $return['order_date'] =date("Ymd",$payment['M_Time']);
            $return['order_no']    = $payment['M_OrderId'];
            $return['order_time'] = date("His",$payment['M_Time']);
            $return['order_amount'] = $payment['M_Amount']*100;
            $return['mobile'] =    $payment['R_Mobile'];
            $return['item_name'] = $payment['M_OrderNO'];
            $return['mer_url'] = $this->callbackUrl;
            $return['result_url'] =    $this->serverCallbackUrl;
            $return['payment_type'] = '';
            $signString='';
            foreach($return as $k => $v){
                //if ($k=="item_name")
                   //$v = $charset->utf2local($v,'zh');
                $signString.=$k."=".$v.",";
            }
            $signString=substr($signString,0,strlen($signString)-1);
            $sign=md5($signString.$key);
            $return['sign'] = $sign;
            return $return;
        }
        function callback($in,&$paymentId,&$money,&$message,&$tradeno){
            $privatekey = $this->getConf($in['order_no'],"PrivateKey");
            $paymentId = $in['order_no'];
            $money = $in['order_amount']/100;
            $merId = trim ($in['mer_id']);
            $orderDate = trim ($in['order_date']);
            $orderNo = trim ($in['order_no']); 
            $finishTime = trim ($in['finish_time']); 
            $orderAmount = trim ($in['order_amount']);
            $status = trim ($in['status']);
            $mobile = trim ($in['mobile']);
            $orderParam = trim ($in['mer_order_param']); 
            $paymentType = trim ($in['payment_type']);
            $brandCode = trim ($in['brand_code']); 
            $sign = trim ($in['sign']);
            $data = "mer_id=".$merId.",order_date=".$orderDate.",order_no=".$orderNo;
            $data.=",finish_time=".$finishTime.",order_amount=".$orderAmount;
            $data.=",status=".$status.",mobile=".$mobile;
            $data.=",payment_type=".$paymentType;
            $data=$data.$privatekey;
            if (md5($data)==$sign){
                echo "success";
                $message="支付成功！";
                return PAY_SUCCESS;
            }
            else{
                echo "fail";
                $message = "支付失败！";
                return PAY_FAIL;
            }
        }
        function getfields(){
            return array(
                'member_id'=>array(
                    'label'=>'客户号',
                    'type'=>'string'
                ),
                'PrivateKey'=>array(
                    'label'=>'密钥',
                    'type'=>'string'
                )
            );
        }
    }
?>