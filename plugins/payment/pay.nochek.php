<?php
require('paymentPlugin.php');
class pay_nochek extends paymentPlugin{

    var $name = 'NOCHEX在线支付';//NOCHEX在线支付
    var $logo = 'NOCHEK';
    var $version = 20070902;
    var $charset = 'GB2312';
    var $submitUrl = 'https://www.nochex.com/nochex.dll/checkout'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("GBP"=>"GBP");//英镑
    var $supportArea =  array("AREA_GBP");
    var $desc = 'NOCHEX在线支付';
    var $orderby = 45;
    var $cur_trading = true;    //支持真实的外币交易
    
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        //$ikey = $this->getConf('PrivateKey');

        $return['email'] = $merId;
        $return['ordernumber'] = $payment["M_OrderId"];//$order->M_OrderId;
        $return['amount'] = $payment["M_Amount"];//$order->M_Amount;
        $return['responderurl'] = $this->callbackUrl;
        $return['description'] = $payment["M_Remark"];//$order->M_Remark;
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        // read the post from PayPal system and add 'cmd'
        $req = '';
        foreach ($in as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $req = ltrim($req,'&');
        $header .= "POST /nochex.dll/apc/apc HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
        $fp = fsockopen ('www.nochex.com', 80, $errno, $errstr, 10);
        if (!$fp){
echo "succ0=N";
            $succ = "N";
        }else{
echo "<br />succ1=Y";
            fputs ($fp, $header . $req);
            $res = '';
            $headerdone = false;
            while (!feof($fp))    {
                $line = fgets ($fp, 1024);
                if (strcmp($line, "\r\n") == 0)        {
                    $headerdone = true;
                }elseif($headerdone){
                    $res .= $line;
                }
            }
echo "<br />res=".$res;
            $lines = explode("\n", $res);
            $keyarray = array();
echo "<br />lines0=".$lines[0];
            if (strcmp ($lines[0], "AUTHORISED") == 0){
                for ($i=1; $i<count($lines);$i++){
                    list($key,$val) = explode("=", $lines[$i]);
                    $keyarray[urldecode($key)] = urldecode($val);
                }
echo "<br />to_email=".$to_email = $keyarray['to_email'];
echo "<br />from_email=".$from_email = $keyarray['from_email'];
echo "<br />transaction_id=".$transaction_id = $keyarray['transaction_id'];
echo "<br />amount=".$amount = $keyarray['amount'];
echo "<br />transaction_date=".$mydate = $keyarray['transaction_date'];
echo "<br />order_id=".$payid = $keyarray['order_id'];
echo "<br />security_key=".$security_key = $keyarray['security_key'];
echo "<br />status=".$status = $keyarray['status'];
                $succ = "Y";
            }elseif(strcmp ($lines[0], "DECLINED") == 0){
                $succ = "N";
                // log for manual investigation
            }
echo "succ2=".$succ;
        }
        fclose ($fp);
        //验证
        switch ($succ){
            //成功支付
            case "Y":
                $paymentId = $keyarray['order_id'];
                $money = $keyarray['amount'];
                return PAY_SUCCESS;
                break;
            //支付失败
            case "N":
                $message = '支付失败,请立即与商店管理员联系';
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
}
?>
