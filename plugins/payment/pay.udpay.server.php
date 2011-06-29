<?php
require('paymentPlugin.php');
class pay_udpay extends paymentPlugin{
    function pay_udpay_callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $txCode = trim($in['txCode']);    //商家ID
        $merchantId = trim($in['merchantId']);    //商家ID
        $transDate = trim($in['transDate']);                    //交易日期
        $tradeno = $transFlow = trim($in['transFlow']);            //交易号
        $orderId = trim($in['orderId']);            //交易号
        $curCode = trim($in['curCode']);                //交易金额
        $money = $amount = trim($in['amount']);                //交易金额
        $orderInfo = trim($in['orderInfo']);                //交易金额
        $whtFlow = trim($in['whtFlow']);                //交易金额
        $success = trim($in['success']);            //交易结果，"Y"表示成功，"N"表示失败
        $errorType = trim($in['errorType']);            //交易结果，"Y"表示成功，"N"表示失败
        $sign = trim($in['sign']);
        $comment = trim($in['comment']);
        $money = $amount/100;
        $paymentId = $orderId;
        $tradeno = $in['whtFlow'];
        $key = $this->getConf($orderId, 'PrivateKey');

        $msg = "txCode=".$txCode."&merchantId=".$merchantId."&transDate=".$transDate
                ."&transFlow=".$transFlow."&orderId=".$orderId."&curCode=".$curCode."&amount=".$amount
                ."&orderInfo=".$orderInfo."&comment=".$comment."&whtFlow=".$whtFlow."&success=".$success."&errorType=".$errorType;

        if (!$this->getConf($orderId, 'udpay')){
            echo("read key file error!");
            exit;
        }
        if (file_exists(dirname(__FILE__)."/../../home/upload/udpay/".$keyfile))
            $arr_key = file(dirname(__FILE__)."/../../home/upload/udpay/".$keyfile);
        elseif (file_exists(dirname(__FILE__)."/../../cert/udpay/".$keyfile))
            $arr_key = file(dirname(__FILE__)."/../../cert/udpay/".$keyfile);
        $privateModulus = substr(trim($arr_key[2]), 23);
        $privateExponent = substr(trim($arr_key[3]), 24);
        $publicModulus  = substr(trim($arr_key[5]), 17);
        $publicExponent = substr(trim($arr_key[6]), 18);
        $verifySigature = verifySigature($msg, $sign, $privateExponent, $privateModulus);
        if ($sign==$testRsaDecrypt && $verifySigature){
            switch ($success){
                //成功支付
                case "Y":
                    echo "chenggong";
                    return PAY_SUCCESS;
                    break;
                //支付失败
                case "N":
                    echo "shibai";
                    return PAY_ERROR;
                    break;
            }
        }else{
            echo "Error";
            return PAY_FAILED;
        }

    }
}
function pay_UDPAY_relay($status){
    switch ($status){
        case PAY_FAILED:
            echo "fail";
            break;
        case PAY_SUCCESS:
            echo "received";
            break;
        case PAY_ERROR:
            echo "fail";
            break;
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
if (!function_exists('powmods')){
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
