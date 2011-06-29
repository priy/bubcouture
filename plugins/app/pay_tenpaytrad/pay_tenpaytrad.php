<?PHP
require('paymentPlugin.php');
class pay_tenpaytrad extends paymentPlugin{
    var $name = "腾讯财付通[担保交易]";
    var $logo = 'TENPAYTRAD';
    var $version = 20080618;
    var $charset = 'gb2312';
    var $applyUrl = 'https://www.tenpay.com/mchhelper/mch_register_c2c.shtml';//担保
    var $submitUrl = 'https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"1");
    var $supportArea =  array("AREA_CNY");
    var $desc = '财付通是腾讯公司为促进中国电子商务的发展需要，满足互联网用户价值需求，针对网上交易安全而精心推出的一系列服务。';
    var $intro = '财付通是腾讯公司于2005年9月正式推出专业在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。<br>财付通构建全新的综合支付平台，业务覆盖B2B、B2C和C2C各领域，提供卓越的网上支付及清算服务。<br>财付通先后荣膺2006年电子支付平台十佳奖、2006年最佳便捷支付奖、2006年中国电子支付最具增长潜力平台奖和2007年最具竞争力电子支付企业奖等奖项，并于2007年首创获得“国家电子商务专项基金”资金支持。<br><br><font color="red">本接口需点击【立即申请财付通担保账户】链接进行在线签约和付费后方可使用。</font>';
    var $applyProp = array("postmethod"=>"get","sp_suggestuser"=>"2289480");//代理注册参数组
    var $orderby = 13;
    var $head_charset='gb2312';
    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'],'member_id');
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        //$authtype = $this->getConf($payment['M_OrderId'],'authtype');
        $mchname = $payment['M_OrderNO'];
        $return['attach'] = $payment['M_OrderId'];
        //$return['chnid'] = $authtype?"1202822001":"2289480";
        $return['chnid'] = "2289480";
        $return['cmdno'] = "12";
        $return['encode_type'] = "1";
        $return['mch_desc'] = "";
        $return['mch_name'] = $mchname;
		$return['mch_price'] = number_format($payment['M_Amount'],$this->system->getConf('site.decimal_digit'),".","")*100;
        //$return['mch_price'] = ceil($payment['M_Amount'] * 100);
        $return['mch_returl'] = $this->serverCallbackUrl;
        $return['mch_type'] = "1";
        $return['mch_vno'] = $payment['M_OrderId'];
        $return['need_buyerinfo'] = "2";
        $return['seller'] = $merId;
        $return['show_url'] = $this->callbackUrl;
        $return['transport_desc'] = "";
        $return['transport_fee'] = 0;
        $return['version'] = "2";
        $return['ikey'] = $ikey;
        return $return;
    }
    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        if(isset($in['PcacheTime']))
            unset($in['PcacheTime']);
        $cmdno            = $in["cmdno"];
        $version        = $in["version"];
        $retcode        = $in["retcode"];
        $status            = $in["status"];
        $seller            = $in["seller"];
        $total_fee        = $in["total_fee"];
        $trade_price    = $in["trade_price"];
        $transport_fee    = $in["transport_fee"];
        $buyer_id        = $in["buyer_id"];
        $chnid             = $in["chnid"];
        $cft_tid        = $in["cft_tid"];
        $smch_vno        = $in["mch_vno"];
        $attach            = $in["attach"];
        $version        = $in["version"];
        $sign            = $in["sign"];
        $ikey = $this->getConf($in["attach"], 'PrivateKey');
        $paymentId=$attach;
        $money=$total_fee/100;
        $param=array(
            'interfaceName',
            'interfaceVersion',
            'orderid',
            'TranSerialNo',
            'amount',
            'curType',
            'merID',
            'merAcct',
            'verifyJoinFlag',
            'JoinFlag',
            'UserNum',
            'resultType',
            'orderDate',
            'notifyDate',
            'tranStat',
            'comment',
            'remark1',
            'remark2',
            'signMsg'
        );
        foreach($in as $k => $val){
            if ($k<>"sign"&&!in_array($k,$param))
                $str.=$k."=".$val."&";
        }
        $tradeno = $in['cft_tid'];
        $str=substr($str,0,strlen($str)-1);
        $strLocalSign = strtoupper(md5($str."&key=".$ikey));
        if ($strLocalSign  == $sign){
            if ($retcode == "0"){
                if($status==3||$status==5){
                    $message="支付成功";
                    return PAY_SUCCESS;
                }
                elseif ($status==1||$status==4){
                    $message="支付过程处理中";
                    return PAY_PROGRESS;
                }
                elseif ($status==9){
                    $message="退款成功！";
                    return PAY_REFUND_SUCCESS;
                }
                else{
                    $message="支付失败";
                    return PAY_FAILED;
                }
            }
        }
        else{
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
            )/*,
            'authtype'=>array(
                'label'=>'商家支付模式',
                'type'=>'select',
                'options'=>array('0'=>'套餐包量商家','1'=>'单笔支付商家')
            )*/
        );
    }
    function applyForm($agentfield){
        //$tmp_form.='<a href="javascript:void(0)" onclick="document.applyFormAgain.submit()">立即申请财付通<font color="red"><b>套餐</b></font>担保账户(适合大商家)</a><br>';
        //$tmp_form.='<a href="javascript:void(0)" onclick="document.applyFormAgainDb.submit()">立即申请财付通<font color="red"><b>单笔</b></font>担保账户(适合小商家)</a>';


        /*$tmp_form.="<form name='applyFormAgainDb' method='".$agentfield['postmethod']."'  action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
        foreach($agentfield as $key => $val){
            if ($key=="payagentkey")
                $tmp_form.="<input type='hidden' name='".$key."' value='".$val."DB'>";
            else{
               if ($key=="sp_suggestuser")
                   $val='1202822001';
               $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
            }
        }
        $tmp_form.="</form>";*/

        return $tmp_form;
    }
}
?>
