<?php
require('paymentPlugin.php');
class pay_iepay extends paymentPlugin{

    var $name = 'IEPAY';//IEPAY 
    var $logo = 'IEPAY';
    var $version = 20070902;
    var $charset = 'gb2312';
    var $submitUrl = 'https://www.epay.cc/creditcard/cardfinance.php'; //  
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("TWD"=>"TWD");
    var $supportArea =  array("AREA_TWD");
    var $desc = '';
    var $orderby = 25;
        
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        $msign = md5($ikey.":".$this->M_Amount.",".$merId.$this->orderid.",".$merId.",".$card.",".$scard.",".$actioncode.",".$actionParameter.",".$ver);// 
        $msign = strtolower($msign);

        //storeid:授權商店代碼 相当于商户号
        //password:密碼 相当于商户密钥
        //orderid:訂單編號(12碼以內)
        //account:金額
        //remark:訂單註解
        //storename:顯示的商店名稱
        $return['storeid'] = $merId;
        $return['password'] = $ikey;
        $return['account'] = $payment["M_Amount"];//$order->M_Amount;
        $return['remark'] =  $payment["M_Remark"];//$order->M_Remark;
        $return['orderid'] =  $payment["M_OrderId"];//$order->M_OrderId;
        $return['storename'] = $this->getConf('system.shopname');
        
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        //IEPAY的返回地址需要到商户后台去设置。无法在支付的时候自己指定返回地址。
        $v_oid = trim($in['orderid']);
        $v_amount = trim($in['account']);
        $v_date = trim($in['authdate']);
        $v_status = trim($in['status']);

        //orderid:訂單編號
        //status:授權狀態  0:成功  其他:失敗 
        //account:金額
        //authdate:授權時間
        $paymentId = $v_oid;
        $money = $v_amount;
        $ikey = $this->getConf($v_oid, 'PrivateKey');
        if($v_status == "0"){
            return PAY_SUCCESS;
        }else{
            $message = '交易失败';
            return PAY_FAILED;
        }

        #mt_srand((double)microtime()*1000000);
        #$randval = mt_rand(10000000,99999999);
        #$signstr = substr(hexdec(md5($tmp_orderno.$state.mktime().$randval)), 0, 10);
        #$Order->updateOrderSign($tmp_orderno, $signstr);
    }

    function getfields(){    //EGOLD没有商户私钥
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
