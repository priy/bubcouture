<?php
require('paymentPlugin.php');
class pay_icbc extends paymentPlugin{

    var $name = '中国工商银行[1.0.0.3版]';//工商银行
    var $logo = 'ICBC';
    var $version = 20070902;
    var $charset = 'gbk';
    var $submitUrl = 'https://B2C.icbc.com.cn/servlet/ICBCINBSEBusinessServlet'; //正式地址
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("CNY"=>"001");
    var $supportArea = array("AREA_CNY");
    var $intro = "中国工商银行网上银行B2C支付网关可以使用在windows主机和linux主机，请在申请工行网关接口时申请1.0.0.3版。";
    var $orderby = 8;
    var $head_charset="gbk";
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $keyPass = $this->getConf($payment["M_OrderId"], 'keyPass');
        $icbcno = $this->getConf($payment["M_OrderId"],'icbcno');
        $icbcFile = $this->getConf($payment["M_OrderId"],'icbcFile');
        $keyFile = $this->getConf($payment["M_OrderId"],'keyFile');
        $certFile = $this->getConf($payment["M_OrderId"],'certFile');
        $charset = $this->system->loadModel('utility/charset');
        if (is_dir(dirname(__FILE__)."/../../home/upload/icbc/"))
            $realpath = dirname(__FILE__)."/../../home/upload/icbc/";
        elseif (is_dir(dirname(__FILE__)."/../../cert/icbc/"))
            $realpath = dirname(__FILE__)."/../../cert/icbc/";
        $key = $realpath.$keyFile;//私钥文件
        $cert = $realpath.$certFile;//公钥文件
        $icbc = $realpath.$icbcFile;
        if(!file_exists($key)){ 
            die("ICBC key file not found!");
        }
         if(!file_exists($cert)){ 
            die("ICBC Cert file not found!");
         }
         //接口名称固定为“ICBC_PERBANK_B2C”
         $aREQ["interfaceName"] = "ICBC_PERBANK_B2C"; 
         //接口版本目前为“1.0.0.0”
         $aREQ["interfaceVersion"] = "1.0.0.3";
         //商城代码，ICBC提供
         $aREQ["merID"] = $merId;
         //商户帐号，ICBC提供
         $aREQ["merAcct"] = $icbcno;
         //接收银行通知地址，目前只支持http协议80端口
         $aREQ["merURL"] = $this->callbackUrl;
         //HS方式实时发送通知；AG方式不发送通知；
         $aREQ["notifyType"] = "HS";
         //订单号商户端产生，一天内不能重复,拼接上订单号和支付号。
         $aREQ["orderid"] = $payment['M_OrderId']."-".$payment['M_Time'];//$payment['M_OrderNO']."-".substr(trim($payment['M_OrderId']),0,10);
         //金额以分为单位
         $aREQ["amount"] = $payment['M_Amount'] * 100;
         //币种目前只支持人民币，代码为“001”
         $aREQ["curType"] = "001"; 
         //对于HS方式“0”：发送成功或者失败信息；“1”，只发送交易成功信息。
         $aREQ["resultType"] = 0;
         //14位时间戳
         $aREQ["orderDate"] = date("YmdHis",empty($payment['M_Time'])?time():$payment['M_Time']);
         //$aREQ["orderDate"] = "20080620".date("His",time());
         $aREQ["verifyJoinFlag"] = "0";
         //以上五个字段用于客户支付页面显示
         $aREQ["goodsID"] = "";
         //网关只认GB2312
         $aREQ["goodsName"]  = $payment['M_OrderNO'];
         //$aREQ["goodsName"]  = "中文";
         //$convert = new iconvex();
         //$aREQ["goodsName"]  = $convert->utf82gb($aREQ["goodsName"]);
         $aREQ["goodsNum"] = 1;
         //运费金额以分为单位
         $aREQ["carriageAmt"] = 0;
         $aREQ["merHint"] = "";
         //备注
         $aREQ["remark1"] = $charset->utf2local($payment['rnote'],"zh");
         //备注2
         $aREQ["remark2"] = "";
         //“1”判断该客户是否与商户联名；取值“0”不检验客户是否与商户联名。
         $aREQ["verifyJoinFlag"] = 0;

         //构造V3版的xml
         $tranData = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"no\"?><B2CReq><interfaceName>".$aREQ["interfaceName"]."</interfaceName><interfaceVersion>".$aREQ["interfaceVersion"]."</interfaceVersion><orderInfo><orderDate>".$aREQ["orderDate"]."</orderDate><orderid>".$aREQ["orderid"]."</orderid><amount>".$aREQ["amount"]."</amount><curType>".$aREQ["curType"]."</curType><merID>".$aREQ["merID"]."</merID><merAcct>".$aREQ["merAcct"]."</merAcct></orderInfo><custom><verifyJoinFlag>".$aREQ["verifyJoinFlag"]."</verifyJoinFlag><Language>ZH_CN</Language></custom><message><goodsID>".$aREQ["goodsID"]."</goodsID><goodsName>".$aREQ["goodsName"]."</goodsName><goodsNum>".$aREQ["goodsNum"]."</goodsNum><carriageAmt>".$aREQ["carriageAmt"]."</carriageAmt><merHint>".$aREQ["merHint"]."</merHint><remark1>".$aREQ["remark1"]."</remark1><remark2>".$aREQ["remark2"]."</remark2><merURL>".$aREQ["merURL"]."</merURL><merVAR>".$payment['M_OrderId']."</merVAR></message></B2CReq>";
         if (strtoupper(substr(PHP_OS,0,3))=="WIN"){
             $bb = new COM('ICBCEBANKUTIL.B2CUtil');
             $rc=$bb->init($icbc,$cert,$key,$keyPass);
             $merSignMsg = $bb->signC($tranData, strlen($tranData));
         }
         else{
             //商户签名数据BASE64编码
             $cmd = "/bin/icbc_sign '{$key}' '{$keyPass}' '{$tranData}'";
             //error_log($cmd,3,__FILE__.".log");
             $handle = popen($cmd, 'r');
             $merSignMsg = fread($handle, 2096);
             pclose($handle);
         } 
         $fp = fopen($cert,"rb");
         $merCert = fread($fp,filesize($cert));
         $merCert = base64_encode($merCert);
         fclose($fp);
         $aFinalReq['interfaceName'] = $aREQ["interfaceName"];
         $aFinalReq['interfaceVersion'] = $aREQ["interfaceVersion"];
         $aFinalReq['tranData'] = base64_encode($tranData);
         $aFinalReq['merSignMsg'] = $merSignMsg;
         $aFinalReq['merCert'] = $merCert;
         foreach($aFinalReq as $key=>$val) {
            $return[$key]=$val;
         }
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $paymentId=$in['merVAR'];
        if (is_dir(dirname(__FILE__)."/../../home/upload/icbc/"))
            $realpath = dirname(__FILE__)."/../../home/upload/icbc/";
        elseif (is_dir(dirname(__FILE__)."/../../cert/icbc/"))
            $realpath = dirname(__FILE__)."/../../cert/icbc/";
        $merId = $this->getConf($paymentId,'member_id');
        $icbc = $realPath.$this->getConf($paymentId,'icbcFile');
        $cert = $realPath.$this->getConf($paymentId,'certFile');
        $key = $realPath.$this->getConf($paymentId,'keyFile');
        $keyPass = $this->getConf($paymentId,'keyPass');
        $notifyData = base64_decode($in['notifyData']);
        if (strtoupper(substr(PHP_OS,0,3))=="WIN"){
            $bb = new COM('ICBCEBANKUTIL.B2CUtil');
            $bb->init($icbc,$cert,$key,$keyPass);
            $isok = $bb->verifySignC($notifyData,strlen($notifyData),$in['signMsg'],strlen($in['signMsg']));
        }
        else{
            $cmd = "/bin/icbc_verify '{$icbcpubcert}' '{$notifyData}' '{$signMsg}'";
            $handle = popen($cmd, 'r');
            $isok = fread($handle, 8);
            pclose($handle);
        }
        if ($isok == 0){
            preg_match("/\<amount\>(.*)\<\/amount\>.+\<TranSerialNo\>(.*)\<\/TranSerialNo\>.+\<tranStat\>(.*)\<\/tranStat\>/i",$notifyData,$rnt);
            $money = $rnt[1]/100;
            $tradeno = $rnt[2];
            if ($rnt[3]==1){
                $message="支付成功！";
                return PAY_SUCCESS;
            }
            else{
                $message = "支付失败！";
                return PAY_FAILED;
            }
        }
        else{
            $message = "验证签名错误！";
            return PAY_ERROR;
        }
    }
    function getfields(){
            return array(
                'member_id'=>array(
                     'label'=>'商城代码',
                     'type'=>'string',
                     'helpMsg'=>'此处填写您的支付帐号、客户号或客户id等，此帐号在支付服务提供商处取得；'
                 ),
                 'icbcno'=>array(
                     'label'=>'工行帐号',
                     'type'=>'string',
                 ),
                 'keyFile'=>array(
                     "label"=>'商户私钥文件',
                     "type"=>'file'
                 ),
                 'certFile'=>array(
                     "label"=>'商户公钥文件',
                     "type"=>'file'
                 ),
                 'icbcFile'=>array(
                      "label"=>'工行公钥文件',
                      "type"=>'file'
                 ),
                 'keyPass'=>array(
                     "label"=>'私钥保护密码',
                     "type"=>'string'
                 )
            );
    }
}
?>
