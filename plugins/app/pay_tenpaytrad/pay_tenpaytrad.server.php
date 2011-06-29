<?PHP
require_once('paymentPlugin.php');
class pay_tenpaytrad extends paymentPlugin{
    function pay_tenpaytrad_callback($in,&$paymentId,&$money,&$message,&$tradeno){
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
        $ikey = $this->getConf($smch_vno,"PrivateKey");
        $buffer = $this->AddParameter($buffer, "attach",                 $attach);
        $buffer = $this->AddParameter($buffer, "buyer_id",             $buyer_id);
        $buffer = $this->AddParameter($buffer, "cft_tid",                 $cft_tid);
        $buffer = $this->AddParameter($buffer, "chnid",                 $chnid);
        $buffer = $this->AddParameter($buffer, "cmdno",                 $cmdno);
        $buffer = $this->AddParameter($buffer, "mch_vno",                 $smch_vno);
        $buffer = $this->AddParameter($buffer, "retcode",                 $retcode);
        $buffer = $this->AddParameter($buffer, "seller",                 $seller);
        $buffer = $this->AddParameter($buffer, "status",                 $status);
        $buffer = $this->AddParameter($buffer, "total_fee",             $total_fee);
        $buffer = $this->AddParameter($buffer, "trade_price",             $trade_price);
        $buffer = $this->AddParameter($buffer, "transport_fee",         $transport_fee);
        $buffer = $this->AddParameter($buffer, "version",                 $version);
        $strLocalSign = strtoupper(md5($buffer."&key=".$ikey));
        $tradeno = $in['cft_tid'];
        $paymentId=$attach;
        $money=$total_fee/100;
        if ($strLocalSign  == $sign )
        {
            //验证MD5签名成功
            if ($retcode == "0")
            {
                //支付成功，在这里处理业务逻辑注意判断订单是否重复的逻辑，注意订单金额为分
                echo "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">";
                switch(($status)){
                case 1:
                    //交易创建
                    break;
                case 2:
                    //收获地址填写完毕
                    break;
                case 3:
                    //买家付款成功
                    return PAY_PROGRESS;
                    break;
                case 4:
                    //卖家发货成功
                    break;
                case 5:
                    //买家收货确认，交易成功
                    return PAY_SUCCESS;
                    break;
                case 6:
                    //交易关闭，未完成超时关闭
                    break;
                case 7:
                    //修改交易价格成功
                    break;
                case 8:
                    //买家发起退款
                    break;
                case 9:
                    //退款成功
                    return PAY_REFUND_SUCCESS;
                    break;
                case 10:
                    //退款关闭
                    break;
                default:
                    //error
                    return PAY_ERROR;
                    break;
                }
                //echo $retcode;
            }
            else {
                //支付失败，请根据retcode进行错误逻辑处理
                $message = $retcode;
                return PAY_FAIL;
            }
        }
        else{
            $message = "qianming";
            return PAY_ERROR;
            //签名失败，请进行非法操作的逻辑处理
        }
    }

    function AddParameter($buffer,$parameterName,$parameterValue)
    {
        if ($parameterValue=="")
            return $buffer;
        if (empty($buffer))
            $buffer = $parameterName . "=". $parameterValue;
        else
            $buffer = $buffer . "&" . $parameterName . "=" .$parameterValue;
        return $buffer;
    }
}
?>
