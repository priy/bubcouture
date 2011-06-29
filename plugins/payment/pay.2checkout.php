<?php
require('paymentPlugin.php');
class pay_2checkout extends paymentPlugin{

    var $name = '2CHECKOUT';//2CHECKOUT
    var $logo = '2CHECKOUT';
    var $version = 20070902;
    var $charset = 'utf-8';
    var $submitUrl = 'https://www.2checkout.com/2co/buyer/purchase'; 
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("AUD"=>"AUD", "CAD"=>"CAD", "EUR"=>"EUR", "GBP"=>"GBP", "HKD"=>"HKD", "JPY"=>"JPY", "KRW"=>"KRW", "SGD"=>"SGD", "USD"=>"USD");
    var $supportArea =  array("AREA_AUD","AREA_CAD","AREA_EUR","AREA_GBP","AREA_HKD","AREA_JPY","AREA_KRW","AREA_SGD","AREA_USD");
    var $desc = '2Checkout.com';
    var $orderby = 40;
    var $cur_trading = true;    //支持真实的外币交易
    
    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        ##$tmp_url = $this->url."index.php";
        $tmp_url = $this->callbackUrl;

        $return["sid"]= $merId;
        $return["cart_order_id"]= $payment["M_OrderId"];//$order->M_OrderId;
        $return['quantity']= "1";
        $return['invoice_num']= $payment["M_OrderNO"];//$order->M_OrderNO;
        $return['total']= $payment["M_Amount"];//$order->M_Amount;
        $return['card_holder_name']= $payment["R_Email"];//$order>R_Email;
//        $return.= "<input type=HIDDEN name=\"c_prod\" value=\"".$this->orderid."\">";
//        $return.= "<input type=HIDDEN name=\"id_type\" value=\"2\">";
        $return['lang']= "en";
        $return['email']= $payment["R_Email"];//$order->R_Email;
        $return['return_url']= $tmp_url;
            
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){    
        //=========================== 把商家的相关信息返回去 =======================
        $orderid    = $in['order_number'];            //商店订单号
        $mer_id        = $in['card_holder_name'];        //商户号
        $credit_card_processed    = $in['credit_card_processed'];    //状态
        $amount        = $in['product_id'];                //支付金额
        $product_id    = $in['total'];                //支付金额
        $md5sig        = $in['key'];                //验证串

        $paymentId = $orderid;
        $money = $amount;

        //检查签名
        $key = $this->getConf($orderid, 'PrivateKey');

        //整合md5加密
        $text = $key.$mer_id.$orderid.$amount;
        $md5digest = strtoupper(md5($text));

        if ($md5digest == $md5sig){
            if ($Order->paycur == $currency && $Order->paymoney <= $amount){
                return PAY_SUCCESS;
            }else{
                $message = '更新数据库，支付失败。';
                return PAY_FAILED;
            }
        }else{
            $message = '支付信息不正确，可能被篡改。';
            return PAY_ERROR;
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
