<?php
if(!class_exists('pageFactory')){
    require(CORE_DIR.'/include/pageFactory.php');
}

class shopex_stat_listener extends pageFactory{

    function shopex_stat_listener(){
        $this->system = &$GLOBALS['system'];
    }


    function get_orderinfo($event_type,$order_data){
        if ($order_data){
             $url = $this->system->base_url();

           $str = $order_data['shipping'];
           if(strstr($str,"货到付款")){
             $shipment = 'cash';
           }
           else {
               $shipment='nomal';
               }
             $info_v = array('order_id'=>$order_data['order_id'],'shipment'=>$shipment,'total'=>$order_data['total_amount'],'member_id'=>$order_data['member_id'],'member'=>$order_data['uname'],'num'=>$order_data['itemnum'],'goods_id'=>$order_data['products']['0']['goods_id'],'price'=>$order_data['totalPrice'],'goods_url'=>$url.'?product-'.$order_data['products']['0']['goods_id'],'thumbnail_pic'=>$order_data['products']['0']['thumbnail_pic'],'goods_name'=>$order_data['tostr']);
             if (!$_COOKIE["SHOPEX_LOGIN_NAME"]){
             $info_v['from'] = 'font';
             $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", serialize($info_v),0,"/");
             }
             else {
                 $info_v['from'] = 'admin';
                 $this->system->setConf('site.orderinfo',serialize($info_v));
             }
       }
    }


     //支付状态(后台)
    function get_payinfo($event_type,$order_data){
        switch($order_data['pay_status']){
            case 0:$pay_status = 'nopay';break;
            case 1:$pay_status = 'pay';break;
            case 2:$pay_status = 'deal';break;
            case 3:$pay_status = 'Partial_payments';break;
            case 4:$pay_status = 'Partial_refund';break;
            case 5:$pay_status = 'Full_refund';break;
        }
        if ($order_data){
           $info_v = array('order_id'=>$order_data['order_id'],'status'=>$pay_status);
           $this->system->setConf('site.payinfo',serialize($info_v));
         }
    }


      //发货退货
    function get_deliveryinfo($event_type,$order_data){
       //error_log(print_r($order_data,true), 3, "d:/log18.txt");
        if ($order_data['ship_status'] == '1'){
            $ship_status = 'send';
          }
        if ($order_data['ship_status']=='4'){
             $ship_status = 'reship';
          }

        if ($order_data){
            $info_v = array('order_id'=>$order_data['order_id'],'ship_status'=>$ship_status);
            //$result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", serialize($info_v),0,"/");
            $this->system->setConf('site.goods_status',serialize($info_v));

          }
    }

     //会员注册
     function get_memberinfo($event_type,$member_data){
         if ($member_data){
           $firstR = json_decode($_COOKIE['FIRST_REFER'],true);
           $info_m = array('uname'=>$member_data['uname'],'member_id'=>$member_data['member_id'],'ip'=>$member_data['reg_ip'],'u_make'=>'font','refer_url'=>$firstR);
           $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", serialize($info_m),0,"/");
        }
    }


     //会员登陆
      function get_logmember($event_type,$log_member){
         if($log_member){
           $info_log = array('uid'=>$log_member['member_id'],'uname'=>$log_member['uname']);
           $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", serialize($info_log),0,"/");
         }
      }


     //admin预存款
       function get_money($event_type,$money_data){
          if ($money_data){
           $money='doadd';
            }
           else {$money='undo';}

           $info_money = array('uid'=>$money_data['member_id'],'money'=>$money);

           $this->system->setconf('addmoney',$info_money);
        }
  }

?>