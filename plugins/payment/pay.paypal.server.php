<?php
require('paymentPlugin.php');
class pay_paypal extends paymentPlugin{
    function pay_paypal_callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $req = 'cmd=_notify-validate';
        foreach ($in as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $errcode = "";
        // post back to PayPal system to validate
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

        $item_name = $in['item_name'];
        $payid = $in['item_number'];
        $mydate = substr($item_name,0,8);
        $payment_status = $in['payment_status'];
        $money = $payment_amount = $in['mc_gross'];
        $payment_currency = $in['mc_currency'];
        $txn_id = $in['txn_id'];
        $receiver_email = $in['receiver_email'];
        $payer_email = $in['payer_email'];
        $paymentId = $payid;
        $succ = "N";
        $retstr ="";
        if(!$fp){
           $succ = "N";
           $errcode = "1";
        }else{
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
            fclose ($fp);
        }
        //验证
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

    function pay_PAYPAL_relay($status){
        switch ($status){
            case PAY_SUCCESS:
            break;
            case PAY_ERROR:
                echo '支付失败,请立即与商店管理员联系';
            break;
        }
    }
}
?>
