<?php
require('paymentPlugin.php');
class pay_udpay extends paymentPlugin{

    var $name = '网汇通在线支付';//网汇通在线支付
    var $logo = 'UDPAY';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://www.udpay.com.cn/gateway/transForward.jsp';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"156");
    var $supportArea =  array("AREA_CNY");
    var $desc = '网汇通在线支付';
    var $orderby = 19;
    var $head_charset = 'gb2312';
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $keyfile = $this->getConf($payment['M_OrderId'], 'udpay');
        $charset = $this->system->loadModel('utility/charset');
        $ordAmount = floor($payment["M_Amount"] * 100);
        $msg = "txCode=TP001&merchantId=".$merId."&transDate=".date("Ymd",$payment["M_Time"])."&transFlow=".$payment["M_OrderNo"]."&orderId=".$payment["M_OrderId"]."&curCode=156&amount=".$ordAmount."&orderInfo=".$this->getConf('system.shopname')."&comment=&merURL=".$this->callbackUrl."&interfaceType=5";
        if (file_exists(dirname(__FILE__)."/../../home/upload/udpay/".$keyfile))
            $arr_key = file(dirname(__FILE__)."/../../home/upload/udpay/".$keyfile);
        elseif (file_exists(dirname(__FILE__)."/../../cert/udpay/".$keyfile))
            $arr_key = file(dirname(__FILE__)."/../../cert/udpay/".$keyfile);
        else{
            echo '<br><br>read key file error!';
            $this->_succ=true;
            exit;
        }
        preg_match("/=([^=]*)/",trim($arr_key[2]),$private);
        $privateModulus = $private[1];
        preg_match("/=([^=]*)/",trim($arr_key[3]),$private);
        $privateExponent = $private[1];
        $testRsaDecrypt = generateSigature($msg, $privateExponent, $privateModulus);
        $return["txCode"] = "TP001";
        $return["merchantId"] = $merId;
        $return["transDate"] = date("Ymd",$payment["M_Time"]);//date("Ymd",$order->M_Time);
        $return["transFlow"] = $payment["M_OrderNo"];//$order->M_OrderNO;
        $return["orderId"] = $payment["M_OrderId"];//$order->M_OrderId;
        $return["curCode"] = "156";
        $return["amount"] = $ordAmount;
        $return["orderInfo"] = $this->getConf('system.shopname');
        $return["comment"] = "";
        $return["interfaceType"] = "5";
        $return["sign"] = $testRsaDecrypt;
        $return["merURL"] = $this->callbackUrl;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){    
        $txCode = trim($in['txCode']);    //商家ID
        $merchantId = trim($in['merchantId']);    //商家ID
        $transDate = trim($in['transDate']);                    //交易日期
        $transFlow = trim($in['transFlow']);            //交易号
        $orderId = trim($in['orderId']);            //交易号
        $curCode = trim($in['curCode']);                //交易金额
        $amount = trim($in['amount']);                //交易金额
        $orderInfo = trim($in['orderInfo']);                //交易金额
        $whtFlow = trim($in['whtFlow']);                //交易金额
        $success = trim($in['success']);            //交易结果，"Y"表示成功，"N"表示失败
        $errorType = trim($in['errorType']);            //交易结果，"Y"表示成功，"N"表示失败
        $comment = trim($in['comment']);
        $sign = trim($in['sign']);
        $paymentId = $orderId;
        $money = $amount/100;
        $tradeno = $in['whtFlow'];
        $msg = "txCode=".$txCode."&merchantId=".$merchantId."&transDate=".$transDate
                ."&transFlow=".$transFlow."&orderId=".$orderId."&curCode=".$curCode."&amount=".$amount
                ."&orderInfo=".$orderInfo."&comment=".$comment."&whtFlow=".$whtFlow."&success=".$success."&errorType=".$errorType;
        if (!$keyfile = $this->getConf($orderId, 'udpay')){
            $message = "read key file error!";
            return PAY_FAILED;
        }
        if (file_exists(dirname(__FILE__)."/../../home/upload/udpay/".$keyfile))
            $arr_key = file(dirname(__FILE__)."/../../home/upload/udpay/".$keyfile);
        elseif (file_exists(dirname(__FILE__)."/../../cert/udpay/".$keyfile))
            $arr_key = file(dirname(__FILE__)."/../../cert/udpay/".$keyfile);
        preg_match("/=([^=]*)/",trim($arr_key[2]),$private);
        $privateModulus = $private[1];
        preg_match("/=([^=]*)/",trim($arr_key[3]),$private);
        $privateExponent = $private[1];
        preg_match("/=([^=]*)/",trim($arr_key[5]),$public);
        $publicModulus  = $public[1];
        preg_match("/=([^=]*)/",trim($arr_key[6]),$public);
        $publicExponent = $public[1];
        $verifySigature = verifySigature($msg, $sign, $publicExponent, $publicModulus);
        if ($verifySigature){
            switch ($success){
                //成功支付
                case "Y":
                    $message = "支付成功！";
                    return PAY_SUCCESS;
                    break;
                //支付失败
                case "N":
                    $message = '支付失败,请立即与商店管理员联系';
                    return PAY_FAILED;
                    break;
            }
        }else{
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
                'udpay'=>array(
                        'label'=>'私钥文件',
                        'type'=>'file'
                )
            );
    }
}
if (!function_exists('generateSigature')){
    function generateSigature($message, $exponent, $modulus){          
         $md5Message = md5($message);   
         $fillStr = "01ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff003020300c06082a864886f70d020505000410";                 
         $md5Message = $fillStr.$md5Message;
         $intMessage = bin2int(hex2bin($md5Message));
         $intE = bin2int(hex2bin($exponent));
         $intM = bin2int(hex2bin($modulus));
         $intResult = powmod($intMessage, $intE, $intM);
         $hexResult = bin2hex(int2bin($intResult));
         return $hexResult;
    }                                                                               
}
if (!function_exists('verifySigature')){
    function verifySigature($message, $sign, $exponent, $modulus){   
          $intSign = bin2int(hex2bin($sign));
          $intExponent = bin2int(hex2bin($exponent));
          $intModulus = bin2int(hex2bin($modulus));
        $intResult = powmod($intSign, $intExponent, $intModulus);                       
        $hexResult = bin2hex(int2bin($intResult));       
        $md5Message = md5($message);        
        if ($md5Message == substr($hexResult, -32)) {
          return "1";                                     
        } else return "0";
    }                                                                               
}
if (!function_exists('hex2bin')){
    function hex2bin($hexdata){    
       for ($i=0;$i<strlen($hexdata);$i+=2) { 
         $bindata=chr(hexdec(substr($hexdata,$i,2))).$bindata; 
       } 
       return $bindata; 
    }
}
if (!function_exists('bin2int')){
    function bin2int($str){
        $result = '0';
        $n = strlen($str);
        do {
            $result = bcadd(bcmul($result, '256'), ord($str{--$n}));
        } while ($n > 0);
        return $result;
    }
}
if (!function_exists('int2bin')){
    function int2bin($num){
        $result = '';
        do {
            $result= chr(bcmod($num, '256')).$result;
            $num = bcdiv($num, '256');
        } while (bccomp($num, '0'));
        return $result;
    }
}
if (!function_exists('powmod')){
    function powmod($num, $pow, $mod){
      $result = '1';
      do {
          if (!bccomp(bcmod($pow, '2'), '1')) {
              $result = bcmod(bcmul($result, $num), $mod);
          }
          $num = bcmod(bcpow($num, '2'), $mod);
          $pow = bcdiv($pow, '2');
      } while (bccomp($pow, '0'));
      return $result;
    }
}
?>
