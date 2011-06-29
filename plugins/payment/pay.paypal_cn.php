<?php
require('paymentPlugin.php');
class pay_paypal_cn extends paymentPlugin{

    var $name = 'PayPal 贝宝- 人民币支付';//PayPal 贝宝- 人民币支付
    var $logo = 'PAYPAL_CN';
    var $version = 20070902;
    var $charset = 'GB2312';
    var $applyUrl = '';
    var $submitUrl = 'https://www.paypal.com/cn/cgi-bin/webscr'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"CNY");
    var $supportArea =  array("AREA_CNY");
    var $desc = "PayPal贝宝 是 PayPal 支付平台的人民币业务品牌，客户通过 PayPal 贝宝可简单快捷地进行支付，款项实时到帐，推荐使用。<br><a href='http://www.paypal.com/cn/' target='_blank'><img src='images/apply-imm.gif' border='0' align=right></a> ";
    var $intro = "PayPal贝宝 是 PayPal 支付平台的人民币业务品牌，客户通过 PayPal 贝宝可简单快捷地进行支付，款项实时到帐，推荐使用。<br><a href='http://www.paypal.com/cn/' target='_blank'><img src='images/apply-imm.gif' border='0' align=right></a> ";    
    var $orderby = 36;
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        $return['cmd'] = "_xclick";
        $return['business'] = $merId;
        $return['item_name'] = $payment["M_OrderNO"];
        $return['item_number'] = $payment["M_OrderId"];
        $return['amount'] = $payment["M_Amount"];
        $return['currency_code'] = $payment["M_Currency"];
        $return['bn'] = "shopex";
        $return['return'] = $this->callbackUrl;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){
        /*
        $req = 'cmd=_notify-synch';
        $tx_token = $in['tx'];
        $req .= "&tx=$tx_token&at=$auth_token";
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
        if (!$fp){
            $succ = "N";
            $errcode = "1";
        }else{
            fputs ($fp, $header . $req);
            // read the body data 
            $res = '';
            $headerdone = false;
            while (!feof($fp)){
                $line = fgets ($fp, 1024);
                if (strcmp($line, "\r\n") == 0){
                    // read the header
                    $headerdone = true;
                }elseif($headerdone){
                    // header has been read. now read the contents
                    $res .= $line;
                }
            }
            // parse the data
            $lines = explode("\n", $res);
            $keyarray = array();
            if (strcmp ($lines[0], "SUCCESS") == 0){
                for ($i=1; $i<count($lines);$i++){
                    list($key,$val) = explode("=", $lines[$i]);
                    $keyarray[urldecode($key)] = urldecode($val);
                }
                $firstname = $keyarray['first_name'];
                $lastname = $keyarray['last_name'];
                $itemname = $keyarray['item_name'];
                $amount = $keyarray['mc_gross'];
                
                $payid = $keyarray['item_number'];
                $mydate = substr($itemname,0,8);
                $currency = $keyarray['mc_currency'];

                $paymentId = $keyarray['item_number'];
                $money = $keyarray['mc_gross'];

                $payment_status = $keyarray['payment_status'];
                //取得PAYPAL私钥,该私钥在PAYPAL后台开启了Payment Data Transfer后,会以identity token方式显示在该页上
                $auth_token = trim($this->getConf($payid, 'PrivateKey'));
                if(strlen($auth_token)==0){
                    $message = "Invalid Auth_token";
                    return PAY_FAILED;
                }
                if($payment_status == "Completed"){
                    $succ = "Y";
                }else{
                    $succ = "N";
                    $errcode = "2";
                }
            }elseif(strcmp ($lines[0], "FAIL") == 0){
                $succ = "N";
                $errcode = "3".$res;
                // log for manual investigation
            }
        }
        fclose ($fp);
        //验证
        switch ($succ){
            //成功支付
            case "Y":
                return PAY_SUCCESS;
            break;
            //支付失败
            case "N":
                $message = '支付失败,请立即与商店管理员联系'."(".$errcode.")";
                return PAY_FAILED;
                break;
        } */
        $req = 'cmd=_notify-validate';
        foreach ($in as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length:" . strlen($req) ."\r\n\r\n";
        $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
        $item_name = $in['item_name'];
        $item_number = $in['item_number'];
        $payment_status = $in['payment_status'];
        $payment_amount = $in['mc_gross'];
        $payment_currency = $in['mc_currency'];
        $txn_id = $in['txn_id'];
        $receiver_email = $in['receiver_email'];
        $payer_email = $in['payer_email'];
        $paymentId = $item_number;
        $money = $in['mc_gross'];
        if (!$fp) {
            $succ = "N";
            $errcode = "1";
        }
        else{
            fputs ($fp, $header . $req."\r\n\r\n");
            while (!feof($fp)){
                $res = fgets ($fp, 1024);
                $retstr .= ",".$res;
                if (strcmp (trim($res), "VERIFIED") == 0){
                    if(trim($payment_status)=="Completed"){
                        $succ="Y";
                    }else{
                        $succ = "N";
                        $errcode = "2";
                    }
                }elseif(strcmp ($res, "INVALID") == 0){
                    $succ = "N";
                    $errcode = "3";
                }
            }
        } 
        fclose ($fp);
        switch ($succ){
            //成功支付
            case "Y":
                return PAY_SUCCESS;
                break;
            //支付失败
            case "N":
                return PAY_ERROR;
                break;
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
}
?>
