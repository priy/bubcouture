<?php
require('paymentPlugin.php');
class pay_haipay extends paymentPlugin{
    function pay_haipay_callback($in,&$paymentId,&$money,&$message,&$tradeno){
    
       	$merId       = $in['MerId'];
        $enctype     = $in['EncType'];
        $data        = $in['Data'];
        $signature   = $in['Sign'];

        $data = base64_decode($data);
	$datas = explode('|', $data); 
        $OrderId = $datas[0];
        $MerOrderId = $datas[1];
        $MerDate = $datas[2];
        $Amount = $datas[3];
        $CurType = $datas[4];
        $abc = $datas[5];
        $ISSUCC = $datas[6];
        $CommitionName = $datas[7];
        $CommitionNum = $datas[8];
        $Remark = base64_decode($datas[9]);
        
        $paymentId = $MerOrderId;
		$money=$Amount/100;
		$tradeno = $OrderId;
		$payment = $this->system->loadModel('trading/payment');
		$prow=$payment->getById($paymentId);
		$cur = $this->system->loadModel('system/cur');
		$money = $cur->get_cur_money($money,$prow['currency']);
		
		#在该字段中放置商户登陆merchant.ips.com.cn的网站中的证书#
		$cert = $this->getConf($paymentId, 'PrivateKey');
        $src = $merId.'|'.'01'.'|'.$data.'|'.$cert;
         //Md5摘要认证
		if (strtolower($signature) == md5($src))
		{
			echo "[Succeed]";
			return PAY_SUCCESS;
		}
		else
		{
			return PAY_FAILED;
		}
        
    }

    function pay_haipay_relay($status){
        switch ($status){
            case PAY_FAILED:
                $aTemp = 'failed';
                break;
            case PAY_TIMEOUT:
                $aTemp = 'timeout';
                break;
            case PAY_SUCCESS:
                $aTemp = 'succ';
                break;
            case PAY_CANCEL:
                $aTemp = 'cancel';
                break;
            case PAY_ERROR:
                $aTemp = 'status';
                break;
            case PAY_PROGRESS:
                $aTemp = 'progress';
                break;
        }
    }
}
?>
