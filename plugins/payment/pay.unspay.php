<?PHP
    require('paymentPlugin.php');
    class pay_unspay extends paymentPlugin{
        var $name="银生支付";
        var $logo = 'UNSPAY';
        var $charset="utf8";
        var $applyUrl = 'https://www.unspay.com/unspay/toCompanyRegister.do';
        var $submitUrl = 'https://www.unspay.com/unspay/page/linkbank/payRequest.do';
        var $supportCurrency =  array("CNY"=>"CNY");
        var $intro='银生支付成立于2006年5月30日，是一家致力于在传统互联网和移动通讯网领域大规模应用在线支付,并以此为基础提供全方位金融配套服务的电子金融服务提供商。公司将不断扩大应用范围，实现多行业、多渠道、多元化的战略目标。以成为一流的电子商务金融服务的行业引导者为公司愿景，打造成为服务于全球用户的安全可靠、快速便捷的电子支付及金融应用平台。 ';
        var $orderby = 4;
        var $head_charset="utf-8";
        var $applyProp = array("postmethod"=>"get","source"=>"shopex");
        function toSubmit($payment){
            $merchantId=$this->getConf($payment['M_OrderId'],'member_id');
            $key = $this->getConf($payment['M_OrderId'],'PrivateKey');
            $conntype = $this->getConf($payment['M_OrderId'], 'ConnectType');
            if ($conntype=='1'){
                $bankCode = $payment['payExtend']['bankId'];
                $assuredPay='false';
            }
            elseif ($conntype=="0")
                $assuredPay='';
            elseif ($conntype=="-1")
                $assuredPay='true';
            $return['version']='3.0.0';
            $return['merchantId']=$merchantId;
            $return['merchantUrl']=$this->callbackUrl;
            $return['responseMode']='3';
            $return['orderId']=$payment['M_OrderId'];
            $return['amount']=$payment['M_Amount'];
            $return['currencyType']=$this->supportCurrency[$payment['M_Currency']];
            $return['assuredPay']=$assuredPay;
            $return['time']=date("YmdHis",$payment['M_Time']);
            $return['remark']=$remark;
            $signStr = "merchantId=".$return['merchantId']."&merchantUrl=".$return['merchantUrl']."&responseMode=".$return['responseMode']."&orderId=".$return['orderId'];
            $signStr .= "&currencyType=".$return['currencyType']."&amount=".$return['amount']."&assuredPay=".$return['assuredPay']."&time=".$return['time'];
            $signStr .= "&remark=".$return['remark']."&merchantKey=".$key;
            $mac = strtoupper(md5($signStr));
            $return['mac']=$mac;
            $return['bankCode']=$bankCode;
            $return['commodity']=$this->getConf('system.shopname').'订单：'.$payment['M_OrderNO'];
            $return['b2b']=$b2b;
            return $return;
        }
        function callback($in,&$paymentId,&$money,&$message,&$tradeno){
            $merchantId    = $in["merchantId"];
            $merchantKey   = $this->getConf($in["orderId"],'PrivateKey');
            $responseMode  = $in["responseMode"];
            $orderId       = $in["orderId"];
            $currencyType  = $in["currencyType"];
            $amount        = $in["amount"];
            $returnCode    = $in["returnCode"];
            $returnMessage = $in["returnMessage"];
            $mac = $in["mac"];
            
            $signStr = "merchantId=".$merchantId."&responseMode=".$responseMode."&orderId=".$orderId."&currencyType=".$currencyType;
            $signStr .= "&amount=".$amount."&returnCode=".$returnCode."&returnMessage=".$returnMessage."&merchantKey=".$merchantKey;
            //md5加密 
            $paymentId = $orderId;
            $money = $amount;
            $nowMac = strtoupper(md5($signStr));
            if($nowMac == $mac){ //若mac校验匹配
                if ($returnCode=="0000"){
                    $message="支付成功!";
                    return PAY_SUCCESS;
                }
                elseif ($returnCode=="0001"){
                    $message="已付款，待发货";
                    return PAY_PROGRESS;
                }
                elseif ($returnCode=="0002"){
                    $message="已发货，待确认";
                    return PAY_PROGRESS;
                }else{
                    $message="支付失败!";
                    return PAY_FAIL;
                }
            }else{  //若mac校验不匹配
                $message="签名认证失败！";
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
                    'options'=>array('0'=>'空','1'=>'非担保交易','-1'=>'担保交易'),
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
                                array("value"=>"icbc","imgname"=>"bank_icbc.gif","name"=>"中国工商银行"),
                                array("value"=>"cmb","imgname"=>"bank_cmb.gif","name"=>"招商银行"),
                                array("value"=>"abc","imgname"=>"bank_abc.gif","name"=>"中国农业银行"),
                                array("value"=>"ccb","imgname"=>"bank_ccb.gif","name"=>"中国建设银行"),
                                array("value"=>"apdb","imgname"=>"bank_spdb.gif","name"=>"上海浦东发展银行"),
                                array("value"=>"bcom","imgname"=>"bank_bcom.gif","name"=>"交通银行"),
                                array("value"=>"cmbc","imgname"=>"bank_cmbc.gif","name"=>"中国民生银行"),
                                array("value"=>"spd","imgname"=>"bank_sdb.gif","name"=>"深圳发展银行"),
                                array("value"=>"gdb","imgname"=>"bank_gdb.gif","name"=>"广东发展银行"),
                                array("value"=>"citic","imgname"=>"bank_citic.gif","name"=>"中信银行"),
                                array("value"=>"hxb","imgname"=>"bank_hxb.gif","name"=>"华夏银行"),
                                array("value"=>"cib","imgname"=>"bank_cib.gif","name"=>"兴业银行"),
                                array("value"=>"gzcb","imgname"=>"bank_gzcb.gif","name"=>"广州市商业银行"),
                                array("value"=>"shrcc","imgname"=>"bank_shrcc.gif","name"=>"上海农村商业银行"),
                                array("value"=>"post","imgname"=>"bank_post.gif","name"=>"中国邮政储蓄"),
                                array("value"=>"bob","imgname"=>"bank_bob.gif","name"=>"北京银行"),
                                array("value"=>"boc","imgname"=>"bank_boc.gif","name"=>"中国银行"),
                                array("value"=>"ceb","imgname"=>"bank_ceb.gif","name"=>"中国光大银行"),
                                array("value"=>"bowz","imgname"=>"bank_wzb.gif","name"=>"温州银行")
                            )
                        )

                    )
                )
            );
        }
        function applyForm($agentfield){
            $tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();">立即申请银生账户</a>';
            $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
            foreach($agentfield as $key => $val)
                $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
            $tmp_form.="</form>";
            return $tmp_form;
        }
    }