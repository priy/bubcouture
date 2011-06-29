<?php
require('paymentPlugin.php');
class pay_paypal extends paymentPlugin{

    var $name = 'PayPal';//PayPal 外贸必选（标准版）
    var $logo = 'PAYPAL';
    var $version = 20070902;
    var $charset = 'UTF-8';
    var $applyUrl = 'https://www.paypal.com/row/mrb/pal=XE8XBENY4W9RY';
    var $submitUrl = 'https://www.paypal.com/cgi-bin/webscr'; //
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("USD"=>"USD", "CAD"=>"CAD", "EUR"=>"EUR", "GBP"=>"GBP", "JPY"=>"JPY", "AUD"=>"AUD","NZD"=>"NZD","CHF"=>"CHF","HKD"=>"HKD","SGD"=>"SGD","SEK"=>"SEK","DKK"=>"DKK","PLZ"=>"PLZ","NOK"=>"NOK","HUF"=>"HUF","CSK"=>"CSK");
    var $supportArea =  array("AREA_USD", "AREA_CAD", "AREA_EUR", "AREA_GBP", "AREA_JPY", "AREA_AUD","AREA_NZD","AREA_CHF","AREA_HKD","AREA_SGD","AREA_SEK","AREA_DKK","AREA_PLZ","AREA_NOK","AREA_HUF","AREA_CSK");
    var $desc = "PayPal 是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准，在全球 103个国家和地区支持多达 16种外币，并拥有 1亿 3千万的客户资源，支持流行的国际信用卡支付。外贸网站首选。<br><a href='https://www.paypal.com/row/mrb/pal=J7QXH9YWP2YV4' target='_blank'><img src='images/apply-imm.gif' border='0' align=right></a> ";
    var $intro = "PayPal 是全球最大的在线支付平台，同时也是目前全球贸易网上支付标准，在全球 103个国家和地区支持多达 16种外币，并拥有 1亿 3千万的客户资源，支持流行的国际信用卡支付。外贸网站首选。<br><font color='red'>本接口需点击【立即申请PAYPAL】链接进行在线签约后方可使用。</font> ";
    var $applyProp = array("postmethod"=>"POST");//代理注册参数组
    var $orderby = 20;
    var $cur_trading = true;    //支持真实的外币交易
    var $head_charset='utf-8';
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');
        $return['cmd'] = "_xclick";
        $return['business'] = $merId;
        $return['item_name'] = "Payment:".$payment["M_OrderNO"];//$order->M_OrderNO;
        $return['item_number'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['currency_code'] = $payment["M_Currency"];//$order->M_Currency;
        $return['return'] = $this->callbackUrl;
        $return['notify_url'] = $this->serverCallbackUrl;
        $return['lc'] = "US";
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){
        /*
        $req = 'cmd=_notify-synch';
        $tx_token = $in['tx'];
        $auth_token = 'CHANGE-TO-YOUR-TOKEN';
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
            $res = '';
            $headerdone = false;
            while (!feof($fp)){
                $line = fgets ($fp, 1024);
                if (strcmp($line, "\r\n") == 0){
                    $headerdone = true;
                }else if ($headerdone){
                    $res .= $line;
                }
            }
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

                $auth_token = trim($this->getConf($payid, 'PrivateKey'));
                if(strlen($auth_token)==0){
                    $message = "Invalid Auth_token";
                    return PAY_FAILED;
                }

                $payment_status = $keyarray['payment_status'];
                if($payment_status == "Completed"){
                    $succ = "Y";
                }else{
                    $succ = "N";
                    $errcode = "2";
                }
            }else if (strcmp ($lines[0], "FAIL") == 0){
                $succ = "N";
                $errcode = "3".$res;
            }
        }
        fclose ($fp);*/
        $paymentId = $in['item_number'];
        $money = $in['amt'];
        if ($in['st']=="Pending")
            $succ="Y";
        else
            $succ="N";
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
    function applyForm($agentfield){
      $tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即申请PAYPAL</a>';
      $tmp_form.="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($agentfield as $key => $val){
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }
      $tmp_form.="</form>";
      return $tmp_form;
   }
}
?>
