<?PHP
require_once("paymentPlugin.php");
class pay_hyl extends paymentPlugin{
    var $name = "广州银联网付通";
    var $logo = "HYL";
    var $version=20080619;
    var $charset="utf-8";
    var $applyUrl="";
    var $intro="广州银联网络支付有限公司于2000年2月2日建成开通网付通支付网关系统，开创了在网上为电子商务活动提供多种银行卡支付的先河。目前有十八家银行的二十多种类型的银行卡为银联在线及国内各大电子商务网站提供实时网上支付。<br><br>接口使用说明：<br>1:修改PHP的配置文件(php.ini)打开这几个模块<br>extension=php_curl.dll<br>extension=php_mcrypt.dll<br>extension=php_openssl.dll<br>2:修改插件文件plugins/payment/pay.hyl.php<br>DEFINE('OPEN_SSL_CONF_PATH', 'd:wwwsoftphpopensslopenssl.cnf');<br>将上句中的d:wwwsoftphpopensslopenssl.cnf修改为本机配置，路径视php版本而定。";
    var $submitUrl="http://test.gnete.com/Bin/Scripts/OpenVendor/Gnete/V34/GetOvOrder.asp";//测试环境
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency=array("CNY"=>"CNY");
    var $supportArea =  array("AREA_CNY");
    var $orderby = 17;
    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'], 'member_id'); //帐号
        $ikey  = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        $kpass = $this->getConf($paypment['M_OrderId'],'keyPass');
        $keyFile = $this->getConf($paypment['M_OrderId'],'keyFile');
        $certFile = $this->getConf($paypment['M_OrderId'],'certFile');
        $BankCode=$ResultMode=$Reserved01=$Reserved01="";
        $keyPass=$this->keypass?$this->keypass:"12345678";
        $SourceText = "MerId=".$merId."&";
        $SourceText .= "OrderNo=". $payment['M_OrderId']."&";
        $SourceText .= "OrderAmount=".$payment['M_Amount']."&";
        $SourceText .= "CurrCode=".$payment['M_Currency']."&";
        $SourceText .= "CallBackUrl=".$$this->callbackUrl."&";
        $SourceText .= "BankCode=".$BankCode."&";
        $SourceText .= "ResultMode=".$ResultMode."&";
        $SourceText .= "Reserved01=".$Reserved01."&";
        $SourceText .= "Reserved02=".$Reserved02;
        if (file_exists(dirname(__FILE__)."/../../home/upload/hyl/".$keyFile))
            $keyFile =dirname(__FILE__)."/../../home/upload/hyl/".$keyFile;
        elseif (file_exists(dirname(__FILE__)."/../../cert/hyl/".$keyFile))
            $keyFile =dirname(__FILE__)."/../../cert/hyl/".$keyFile;
        if (file_exists(dirname(__FILE__)."/../../home/upload/hyl/".$certFile))
            $certFile=dirname(__FILE__)."/../../home/upload/hyl/".$certFile;
        elseif (file_exists(dirname(__FILE__)."/../../cert/hyl/".$certFile))
            $certFile=dirname(__FILE__)."/../../cert/hyl/".$certFile;
        $keyPass = empty($kpass)?'12345678':$kpass;
        $obj=new NetTran();

        $obj->EncryptMsg($SourceText,$certFile);//信息加密
        $EncryptedMsg = $obj->getLastResult();

        $obj->SignMsg($SourceText,$keyFile,$keyPass);//信息签名
        $SignedMsg = $obj->getLastResult();

        $return['EncodeMsg'] = $EncryptedMsg;
        $return['SignMsg'] = $SignedMsg;
        return $return;
    }
    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $EncodeMsg = $in["EncodeMsg"];
        $SignMsg   = $in["SignMsg"];
        $oPay=$this->system->loadModel("trading/payment");
        $oPayF=$oPay->getPaymentFileByType("hyl");
        $kfile=$this->getConf($paymentId,'keyFile');
        $cfile=$this->getConf($paymentId,'certFile');
        $kPass=$this->getConf($paymentId,'keyPass');
        if (file_exists(dirname(__FILE__)."/../../home/upload/hyl/".$kfile))
            $keyFile =dirname(__FILE__)."/../../home/upload/hyl/".$kfile;
        elseif (file_exists(dirname(__FILE__)."/../../cert/hyl/".$kfile))
            $keyFile =dirname(__FILE__)."/../../cert/hyl/".$kfile;
        if (file_exists(dirname(__FILE__)."/../../home/upload/hyl/".$cfile))
            $certFile=dirname(__FILE__)."/../../home/upload/hyl/".$cfile;
        elseif (file_exists(dirname(__FILE__)."/../../cert/hyl/".$cfile))
            $certFile=dirname(__FILE__)."/../../cert/hyl/".$cfile;
        $KeyPass = empty($kPass)?'12345678':$kPass;

        $obj=new NetTran();
        $ret=$obj->DecryptMsg($EncodeMsg,$keyFile,$KeyPass);
        if ($ret==false){
            $message=$obj->getLastErrMsg();
            return PAY_ERROR;
        }
        else{
            $DecryptedMsg=$obj->getLastResult();
            $ret = $obj->VerifyMsg($SignMsg, $DecryptedMsg,$CertFile);
            if ($ret==false){
                return PAY_ERROR;
            }
            else{
                $DMsg=explode("&",$DecryptedMsg);
                $paymentId=substr($DMsg[0],strpos($DMsg[0],"=")+1);
                $money = substr($DMsg[2],strpos($DMsg[2],"=")+1);
                $message="支付成功！";
                return PAY_SUCCESS;
            }
        }
    }
    function getfields(){
        return array(
            'member_id'=>array(
                'label'=>'客户号',
                'type'=>'string'
            ),
                        /*
                    'PrivateKey'=>array(
                            'label'=>'私钥',
                            'type'=>'string'
                        ), */
            'keyFile'=>array(
                'label'=>'私钥文件',
                'type'=>'file'
            ),
            'certFile'=>array( 
                'label'=>'公钥文件',
                'type'=>'file'
            ),
            'keyPass'=>array(
                'label'=>'私钥保护密码',
                'type'=>'string'
            )
        );
    }
}
class NetTran{
    var $LastResult;
    var $LastErrMsg;
    var $CurrVer="广州好易联支付网络有限公司支付网关商户接口 版本号:3.4.0.1";
    function NetTran(){
        $v=substr(PHP_VERSION,0,1);
        if ($v<5)
            DEFINE("OPEN_SSL_CONF_PATH", "d:\\wwwsoft\\php\\openssl\\openssl.cnf");
        else
            DEFINE("OPEN_SSL_CONF_PATH", "d:\\wwwsoft\\php\\extras\\openssl\\openssl.cnf");
        DEFINE("OPEN_SSL_CERT_DAYS_VALID", 365);//1 year
        DEFINE("OPEN_SSL_IS_FILE", 1);
    }
    function __construct(){
        $this->NetTran();
    }
    function GenKey($keyLen){
        $tempstring ="0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef";
        $temp = str_shuffle($tempstring);
        $start =rand(0,strlen($tempstring)-$keyLen) ;
        return substr($temp,$start,$keyLen);

    }
    function EncryptMsg ($TobeEncrypted,$CertFile) 
    {
        $fp=fopen($CertFile,"r");
        if(!$fp){
            $this->LastErrMsg = "Error Number:-10005, Error Description: ER_FIND_CERT_FAILED（找不到证书）";
            return false;
        }
        $pub_key=fread($fp,8192);
        fclose($fp);
        $keyLen=128;
        if(strlen($TobeEncrypted)>$keyLen-11){
            $cipher = MCRYPT_3DES;//MCRYPT_3DES;
            $mode = 'nofb';
            $td = mcrypt_module_open($cipher, "", $mode, "");            
            $key_hex = $this->GenKey(mcrypt_enc_get_key_size ( $td)*2);;
            $key= pack("H" . strlen($key_hex), $key_hex);

            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ( $td), MCRYPT_RAND);
            mcrypt_generic_init($td,$key,$iv);

            $encrypted_data = mcrypt_generic($td, $TobeEncrypted);

            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            if(openssl_public_encrypt($key,$encryptedKey,$pub_key))
            {
                $this->LastResult=bin2hex($iv).bin2hex($encryptedKey).bin2hex($encrypted_data);
                return true;

            }else 
            {
                $this->LastErrMsg = "Error Number:-10022, Error Description: ER_ENCRYPT_ERROR（加密失败）|".openssl_error_string();
                return false;
            }
        }
        else
        {
            if( openssl_public_encrypt($TobeEncrypted,$crypttext,$pub_key))
            {
                $this->LastResult=bin2hex($crypttext);
                return true;
            }
            else 
            {
                $this->LastErrMsg = "Error Number:-10022, Error Description: ER_ENCRYPT_ERROR（加密失败）|".openssl_error_string();
                return false;
            }
        }            
    }
    function DecryptMsg ($TobeDecrypted, $KeyFile, $PassWord)
    {
        $fp=fopen($KeyFile,"r");
        if(!$fp) 
        {
            $this->LastErrMsg = "Error Number:-10005, Error Description: ER_FIND_CERT_FAILED（找不到证书）";
            return false;
        }
        $pri_key=fread($fp,8192);
        fclose($fp);
        $keyLen=128;
        if(strlen($TobeDecrypted)>$keyLen*2)
        {
            $iv_hex =substr($TobeDecrypted,0,16); 
            $key_hex  =substr($TobeDecrypted,16,256);
            $encrypted_hex=substr($TobeDecrypted,16+256);
            print($iv_hex."<br>".$key_hex."<br>".$encrypted_hex."<br>");

            $key= pack("H" . strlen($key_hex), $key_hex);
            $iv=pack("H" . strlen($iv_hex), $iv_hex);
            $encrypted=pack("H" . strlen($encrypted_hex), $encrypted_hex);
            $res = openssl_get_privatekey($pri_key,$PassWord); 
            if(!openssl_private_decrypt($key,$decryptedKey,$pri_key))
            {
                $this->LastErrMsg = "Error Number:-10015, Error Description: ER_PRIKEY_CANNOT_FOUND（没有找到匹配私钥）|".openssl_error_string();
                return false;
            }
            $cipher = MCRYPT_3DES;
            $mode = 'nofb';

            $td =mcrypt_module_open($cipher, '',$mode,''); 
            if(mcrypt_generic_init($td, $decryptedKey, $iv)<0)
            {
                $this->LastErrMsg = "Error Number:-10023, Error Description: ER_DECRYPT_ERROR（解密失败）|";
                return false;    
            }

            $decryptedText = mdecrypt_generic($td,$encrypted);

            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $this->LastResult=$decryptedText;
            return true;
        }
        else
        {
            $res = openssl_get_privatekey($pri_key,$PassWord); 
            if(openssl_private_decrypt($TobeDecrypted,$decryptedText,$pri_key))
            {
                $this->LastResult=bin2hex($decryptedText);
                return true;
            }
            else 
            {
                $this->LastErrMsg = "Error Number:-10023, Error Description: ER_DECRYPT_ERROR（解密失败）|".openssl_error_string();
                return false;
            }
        }
    }
    function SignMsg($TobeSigned, $KeyFile, $PassWord)
    {
        $fp=fopen($KeyFile,"r");
        if(!$fp) 
        {
            $this->LastErrMsg = "Error Number:-10005, Error Description: ER_FIND_CERT_FAILED（找不到证书）";
            return false;
        }
        $pri_key=fread($fp,8192);
        fclose($fp);
        $res = openssl_get_privatekey($pri_key,$PassWord); 
        if(openssl_sign($TobeSigned, $signature, $res))
        {
            $SignedMsg=bin2hex($signature);
            $this->LastResult=$SignedMsg;
            return true;

        }
        else 
        {
            $this->LastErrMsg = "Error Number:-10020, Error Description: ER_SIGN_ERROR（签名失败）|".openssl_error_string();
            return false;
        }    
    }
    function VerifyMsg($TobeVerified, $PlainText, $CertFile)
    {
        $fp=fopen($CertFile,"r");
        if(!$fp) 
        {
            $this->LastErrMsg = "Error Number:-10005, Error Description: ER_FIND_CERT_FAILED（找不到证书）";
            return false;
        }
        $pub_key=fread($fp,8192);
        fclose($fp);

        $res = openssl_get_publickey($pub_key);
        if (openssl_verify($PlainText, pack("H" . strlen($TobeVerified), $TobeVerified), $res)){
            return true;
        }
        else{
            $this->LastErrMsg = "Error Number:-10021, Error Description: ER_VERIFY_ERROR（验签失败）|".openssl_error_string();
            return false;
        }
    }
    function getLastResult(){
        return $this->LastResult;
    }
    function getLastErrMsg(){
        return $this->LastErrMsg;
    }
    function getCurrVer(){
        return $this->CurrVer;
    }
    function GetResult($MerId,$UserId,$Pwd,$PaySuc,$ShoppingTime,$BeginTime,$EndTime,$OrderNo){
        $configFile="D:\\myphp\\OpenVendor.ini";
        $fp=fopen($configFile,"r");
        if(!$fp) 
        {
            $this->LastErrMsg = "Error Number:-00008, Error Description: GETRESULT_GETURL_NULL（获取目标服务器URL失败）";
            return false;
        }
        $urlString=fgets($fp);
        fclose($fp);
        $postString   = "MerId=" . $MerId .
            "&UserId=" . $UserId .
            "&Pwd=" . $Pwd .
            "&PaySuc=" . $PaySuc .
            "&ShoppingTime=" . $ShoppingTime .
            "&BeginTime=" . $BeginTime .
            "&EndTime=" . $EndTime .
            "&OrderNo=" . $OrderNo;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$urlString);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postString);            
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
        curl_setopt($ch, CURLOPT_USERAGENT, $defined_vars['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        $res = curl_exec($ch); 

        if (curl_errno($ch)<>0) 
        {
            $this->LastErrMsg = "Error Number:-00006, Error Description: GET_RESULT_ERROR（获取结果失败:".curl_errno($ch);
            curl_close ($ch);
            return false;
        }
        else
        {    
            curl_close ($ch);
            if(strlen($res)==0)
            {
                $this->LastErrMsg = "Error Number:-00001, Error Description: RETURN_BLANK（远程服务器返回空页面）";
                return false;
            }
            else
            {
                if(substr_count($res,"\n")>0)
                {
                    $this->LastResult=$res;
                    return true;
                }
                else
                {
                    $this->LastErrMsg=$res;
                    return false;
                }
            }
        }            
    }    
}
?>
