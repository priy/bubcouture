<?PHP
require('paymentPlugin.php');
class pay_abank extends paymentPlugin{
    var $name="收汇宝";
    var $logo="ABANK";
    var $version=20080504;
    var $charset="utf-8";
    var $applyUrl="";
    var $intro="";
    var $submitUrl="https://payment.ctopay.com/payment/Interface";
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency=array("CNY"=>1,"USD"=>10);
    var $supportArea =  array("AREA_CNY");
    var $orderby = 26;
    var $method = "post";
    var $head_charset = "utf-8";
    var $cur_trading = true;    //支持真实的外币交易

    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'], 'member_id'); //帐号
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        $language = $this->getConf($payment['M_OrderId'],'language');
        $currency = $this->getConf($payment['M_OrderId'],'currency');
        $ctype = $this->getConf($payment['M_OrderId'], 'cardtype');
        if ($ctype)
            $this->submitUrl='https://payment.ttopay.com/payment/Interface';
        else
            $this->submitUrl='https://payment.xtopay.com/payment/Interface';
        $return['MerNo'] = $merId;
        $return['BillNo'] = $payment['M_OrderId'];
        $return['Amount'] = $payment['M_Amount'];
        $return['Language'] = $language?$language:($this->supportCurrency[$payment['M_Currency']]==10?2:1);
        $return['Currency'] = $currency?$currency:$this->supportCurrency[$payment['M_Currency']];
        $return['ReturnURL'] = $this->callbackUrl;
        
        $md5src = $merId.$payment['M_OrderId'].$return['Currency'].$payment['M_Amount'].$return['Language'].$this->callbackUrl.$ikey;
        $return['MD5info']=strtoupper(md5($md5src));    //MD5签名验证
        $return['Remark'] = $payment['M_Remark'];

        return $return;
    }
    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $merId = $this->getConf($in["BillNo"], 'member_id'); //帐号
        $ikey = $this->getConf($in["BillNo"], 'PrivateKey');
        $BillNo = $in["BillNo"];        //订单号
        $Amount = $in["Amount"];   //订单金额
        $Succeed = $in["Succeed"];    //Y-成功 N-失败
        $Result = $in["Result"];   //支付失败时为失败原因
        $MD5info = $in["MD5info"];
        $Remark = $in["Remark"];
        $Currency = $in["Currency"];
        $md5src = $BillNo.$Currency.$Amount.$Succeed.$ikey;
        $md5sign = strtoupper(md5($md5src));
        $paymentId=$BillNo;
        $tradeno = $in['TradeNo'];
        $money = $Amount;
        $message = urldecode($result);
        if ($MD5info<>$md5sign){
            return PAY_ERROR;
        }else{
            if ($Succeed){
                return PAY_SUCCESS;
            }else{
                return PAY_FAILED;
            }
        }
    }
    function getfields(){
        return array(
            'member_id'=>array(
                'label'=>'帐户',
                'type'=>'string'
            ),
            'PrivateKey'=>array(
                'label'=>'私钥',
                'type'=>'string'
            ),
            'cardtype'=>array(
                'label'=>'类型',
                'type'=>'select',
                'options'=>array('0'=>'内卡','1'=>'外卡')
            ),
            'currency'=>array(
                'label'=>'收汇宝通道参数',
                'type'=>'select',
                'options'=>array('1'=>1,'2'=>2,'3'=>3,'6'=>6,'7'=>7,'10'=>10,'15'=>15)
            ),
            'language'=>array(
                'label'=>'语言',
                'type'=>'select',
                'options'=>array('1'=>'中文','2'=>'English')
            )
        );
    }
}
?>
