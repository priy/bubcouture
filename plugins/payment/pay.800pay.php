<?php
require('paymentPlugin.php');
class pay_800pay extends paymentPlugin{

    var $name = '八佰付在线支付';//八佰付在线支付
    var $logo = '800PAY';
    var $version = 20070615;
    var $charset = 'utf-8';
    var $submitUrl = 'https://www.800-pay.com/PayAction/ReceivePayOrder.aspx';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif';
    var $supportCurrency =  array("CNY"=>"RMB","USD"=>"USD","KRW"=>"KRW");
    var $supportArea = array('AREA_CNY','AREA_USD','AREA_KRW');
    var $status;
    var $desc = '';
    var $orderby = 28;
    var $cur_trading = true;    //支持真实的外币交易

    function toSubmit($payment){

        switch($order->M_Language){
            case 'zh_CN':
                $payment["M_Language"] = 'cn';//$order->M_Language = 'cn';
            break;
            case 'en_US':
                $payment["M_Language"] = 'en';//$order->M_Language = 'en';
            break;
            case 'zh_TW':
                $payment["M_Language"] = 'tw';//$order->M_Language = 'tw';
            break;
        }

        $info = array(
            'M_id'=>$this->getConf($payment["M_OrderId"], 'member_id'),    //    商家号    您在800pay中注册分配的商家ＩＤ代号。如果本字段有误，您将不能通过验证。    必填域    demo@800-pay.com    MAX(50)
            //订单号 消费者选择支付后商户网站产生的一个唯一的定单号，该订单号应该不重复。800PAY通过商家号+订单号来唯一确认一笔订单的重复性，该订单号不能超过30位。    必填域    2.00701E+11    MAX(30)
            'M_OrderID'=>$payment["M_OrderId"],//$order->M_OrderId,            
            'M_OAmount'=>$payment["M_Amount"],//$order->M_Amount,            //    订单金额    消费者支付订单的总金额，一笔订单一个，以元为单位。订单金额，格式：元.角分    必填域    0.01    MAX(15)
            'M_OCurrency'=>$this->supportCurrency[0],    //    支付币种    用来区分一笔支付的币种。目前暂时只支持人民币（RMB）支付。定义如下：    必填域    RMB    3
            //返回路径    商家根据“返回路径”进行接收消费者所支付订单信息，更新消费者更付状态。    必填域    https://www.800-pay.com/PayDemo/MerReceiverPay.aspx    MAX(100)
            'M_URL'=>$this->callbackUrl,
            'M_Language'=>$payment["M_Language"],//$order->M_Language,        //    语言选择    表示商家使用的页面语言，800PAY将会返回相应语言的支付结果通知，定义如下：    必填域    cn    MAX(10)
            'T_TradeName'=>$payment["T_TradeName"],//$order->T_TradeName,        //    商品名称    进行订单支付的消费者所选购商品名称    必填域    苹果    MAX(50)
            'T_Unit'=>$payment["T_Unit"],//$order->T_Unit,                //    商品单位    进行订单支付的消费者所选购商品单位    必填域    件    MAX(20)
            'T_UnitPrice'=>$payment["T_UnitPrice"],//$order->T_UnitPrice,        //    商品单价    进行订单支付的消费者所选购商品单价    必填域    1.5    MAX(20)
            'T_quantity'=>$payment["T_quantity"],//$order->T_quantity,        //    商品数量    进行订单支付的消费者所选购商品数量    必填域    2    MAX(20)
            'T_carriage'=>$payment["T_carriage"],//$order->T_carriage,        //    商品运费    进行订单支付的消费者所选购商品运费    必填域    0    MAX(20)
            'S_Name'=>'',                //    消费者姓名    支付时消费者的姓名    必填域    姜圣    
            'S_Address'=>'',            //    消费者住址    进行订单支付的消费者的住址    必填域    大连    
            'S_PostCode'=>'',        //    邮政编码    进行订单支付的消费者住址的邮政编码    必填域    116600    
            'S_Telephone'=>'',        //    消费者联系电话    进行订单支付的消费者的联系电话    必填域    0411-83684021    
            'S_Email'=>'',                //    消费者电子邮件地址    进行订单支付的消费者的电子邮件地址    必填域    admin@800-PAY.com    
            'R_Name'=>'',                //    收货人姓名    订单支付成功后货品收货人的姓名    必填域    姜大圣    
            'R_Address'=>'',            //    收货人住址    订单支付成功后货品收货人的住址    必填域    大连    
            'R_PostCode'=>'',        //    收货人邮政编码    订单支付成功后货品收货人的住址所在地的邮政编码    必填域    116623    
            'R_Telephone'=>'',        //    收货人联系电话    订单支付成功后货品收货人的联系电话    必填域    0411-83684021    
            'R_Email'=>'',                //    收货人电子邮件地址    订单支付成功后货品收货人的邮件地址    必填域    admin@800PAY.com    
            'M_OComment'=>'',            //    备注    订单来源，如From ECShop order info@800-mall.com    必填域    From ECShop order info@800-mall.com    无限制
            'State'=>0,                                //    支付状态    该项是支付返回的认证    必填域    0    1
            'M_ODate'=>date('Y-m-d h:i:s'),            //    交易日期    当天订单交易日期    必填域    2007-5-10 12:08    8
            );
        
        $return=array();
        $return['OrderMessage'] = implode('|',$info);
        $return['digest'] = strtoupper(MD5($return['OrderMessage'].$this->getConf($payment["M_OrderId"], 'PrivateKey')));
        $return['M_ID'] = $this->getConf($payment["M_OrderId"], 'member_id');

        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){
        $digest = trim(md5($in['OrderMessage']));
        $info = explode('|',$in['OrderMessage']);

        $paymentId = $info[1];
        $money = $info[2];

        if($in['Digest'] == $digest){
            switch($info[22]){
                case 0:
                    $message = '未支付';
                    return PAY_CANCEL;
                break;
                case 2:
                    return PAY_SUCCESS;
                break;
                case 3:
                    $message = '交易失败';
                    return PAY_FAILED;
                break;
                default:
                    $message = '交易出现错误';
                    return PAY_ERROR;
                break;
            }
        }else{
            $message = '交易出现错误';
            return PAY_ERROR;
        }
    }

    function getfields(){
        return array(
                    'member_id'=>array(
                            'label'=>'800pay用户ID',
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
