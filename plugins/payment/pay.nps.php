<?php
require('paymentPlugin.php');
class pay_nps extends paymentPlugin{

    var $name = 'NPS网上支付－内卡';//NPS网上支付－内卡
    var $logo = 'NPS';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://payment.nps.cn/PHPReceiveMerchantAction.do'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"CNY");
    var $supportArea = array('AREA_CNY');
    var $desc = '';
    var $orderby = 19;

    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        $state = 0;
        $language = 1;
        $payment["M_Currency"]=1;
        if ($payment["R_Name"] == "") $payment["R_Name"] = "NA";
        if ($payment["R_Address"] == "") $payment["R_Address"] = "NA";
        if ($payment["R_PostCode"] == "") $payment["R_PostCode"] = "NA";
        if ($payment["R_Telephone"] == "") $payment["R_Telephone"] = "NA";
        if ($payment["R_Email"] == "") $payment["R_Email"] = "NA";
        
        $m_info = $merId."|".$payment["M_OrderId"]."|".$payment["M_Amount"]."|".$payment["M_Currency"]."|".$this->callbackUrl."|".$language ;
        $s_info = $payment["P_Name"]."|".$payment["P_Address"]."|".$payment["P_PostCode"]."|".$payment["P_Telephone"]."|".$payment["P_Email"] ;
        $r_info = $payment["R_Name"]."|".$payment["R_Address"]."|".$payment["R_PostCode"]."|".$payment["R_Telephone"]."|".$payment["R_Email"]."|".$payment["M_Remark"]."|".$state."|".date("Y-m-d H:i:s",$payment["M_Time"]) ;
        $OrderInfo = $m_info."|".$s_info."|".$r_info ; 
        $charset = $this->system->loadModel('utility/charset');
        $OrderInfo = $charset->utf2local($OrderInfo,'zh'); 
        $OrderInfo = $this->StrToHex($OrderInfo);
        $digest = strtoupper(md5($OrderInfo.$ikey));
        $return['M_ID'] = $merId;
        $return['digest'] = $digest;
        $return['OrderMessage'] = $OrderInfo;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){        
        $m_id        =    $in['m_id'];
        $m_orderid    =     $in['m_orderid'];            //商家订单号
        $m_oamount    =     $in['m_oamount'];            //支付金额
        $State        =    $in['m_status'];            //支付状态2成功,3失败
        //接收组件的加
        $OrderInfo    =    $in['OrderMessage'];        //订单加密信息
        $signMsg     =    $in['Digest'];                //密匙
        //接收新的md5加密认证
        $newmd5info    =    $in['newmd5info'];
        
        $paymentId = $m_orderid;
        $money = $m_oamount;
        $key = $this->getConf($m_orderid,"PrivateKey");
        $digest = strtoupper(md5($OrderInfo.$key));
        //新的整合md5加密
        $newtext = $m_id.$m_orderid.$m_oamount.$key.$State;
        $newMd5digest = strtoupper(md5($newtext));
        if ($digest == $signMsg){
            //解密
            $OrderInfo = $this->HexToStr($OrderInfo);
            //md5密匙认证
            if($newmd5info == $newMd5digest){
                if ($State == 2){
                    return PAY_SUCCESS;
                }else{
                    $message = '更新数据库，支付失败。';
                    return PAY_FAILED;
                }
            }else{
                $message = '支付信息不正确，可能被篡改。';
                return PAY_ERROR;
            }
        }else{
            $message = '支付信息不正确，可能被篡改。';
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
    /**** NPS 公共函数定义******/
    function stringToHex ($s) { 
        $r = ""; 
        $hexes = array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 
        for ($i=0; $i<strlen($s); $i++) {$r .= ($hexes [(ord($s{$i}) >> 4)] . $hexes [(ord($s{$i}) & 0xf)]);} 
        return $r; 
    } 

    function HexToStr($hex)
    {
        $string="";
        for ($i=0;$i<strlen($hex)-1;$i+=2)
            $string.=chr(hexdec($hex[$i].$hex[$i+1]));
        return $string;
    }

        
    function StrToHex($string)
    {
        $hex="";
        for ($i=0;$i<strlen($string);$i++)
            $hex.=dechex(ord($string[$i]));
        $hex=strtoupper($hex);
        return $hex;
    }
    /**** NPS 公共函数定义******/
}
?>
