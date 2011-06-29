<?php
    require_once(CORE_DIR.'/admin/controller/order/ctl.order.php');
class admin_order_ctl extends ctl_order{
    var $filter = array('order_refer'=>'taobao');
    var $object = 'plugins/tb_order_ctl/tborder';
    var $detail_title = '';
    var $help = 'http://www.shopex.cn/help/ShopEx48/help_shopex48-1264414823-12130.html';
    function admin_order_ctl(){
        parent::ctl_order();
        $this->finder_action_tpl = dirname(__FILE__).'/view/order/finder_action.html';
        $appmgr = $this->system->loadModel('system/appmgr');
        $tb_api = &$appmgr->load('tb_order_ctl');
        $this->tb  = &$tb_api;
    }

    function export(){
        $this->template_dir = CORE_DIR.'/admin/view/';
        parent::export();
    }

    function index(){
        if(!$this->system->getConf("app.tb_order_ctl.nick")){
            $this->display("view/set_nick.html");
        }else{
            if($_GET['top_appkey']){
                $this->save_sess($_GET);
            }
            

            if($_GET['nick']){
                echo "<script>window.location.href='".$this->system->base_url()."shopadmin/index.php#ctl=plugins/order_ctl&act=index&redirect=1'</script>";
                exit;
            }
            $this->template_dir = CORE_DIR.'/admin/view/';
            $this->order_sync_yb();
            parent::index();
        }
    }

    function order_sync_yb(){
        $css_url= str_replace("\\","/",$this->system->base_url().substr(dirname(__FILE__),strpos(dirname(__FILE__),'plugins')));
        echo "<script>new Request({update:'sssss',onRequest:function(){document.getElementById('top-tab-order').getElement('span').getElement('span').innerHTML = '订&nbsp;&nbsp;&nbsp;单 <i class=\"ico-tb-sync\" title=\"正在更新淘宝订单状态和新订单…\">同步中</i>'},onComplete:function(txt){if(txt=='fail'){new Dialog('index.php?ctl=plugins/order_ctl&act=sess_timeout',{width:550,height:200,title:'淘宝登陆',onShow:function(e){
        this.dialog_body.id='dialogContent';}})}else{txt=JSON.decode(txt);
        var tpgo = '订&nbsp;&nbsp;&nbsp;单';
        if(txt['success_count']!=0){
            tpgo += txt['success_count'];
        }
            \$('top-tab-order').getElement('span').getElement('span').innerHTML =  tpgo ;}}}).post('index.php?ctl=plugins/order_ctl&act=order_sync');
        if(!\$E('link#tb-ico')) new Asset.css('$css_url/images/tb_ico.css', {id:'tb-ico'});
        
        </script>";
        
    }



    function colsetting(){
        $this->template_dir = CORE_DIR.'/admin/view/';
        parent::colsetting();
    }

    function order_sync(){
        $this->system->call("get_order_info",$this->tb);
    }


    
    function sess_timeout(){
        $this->pagedata['tblogin_url'] =  $this->tb->getTbloginurl();
        $this->display("view/sess_timeout.html");
    }

    function save_sess($params){
        $center = $this->system->loadModel('plugins/tb_order_ctl/center_send');
        if($center_msg =$center->getTbAppInfo()){
            $app_secret = $center_msg['result_msg']['app_secret'];
        };
        if($center_msg =$center->get_tb_nick()){
            $nick = $center_msg['result_msg'];
        };
        if($params['nick']!=$nick){
            echo '<script>alert("您登录的淘宝帐号和此功能对应的应用配置中的淘宝帐号不一致，请使用此功能相关应用中配置的淘宝帐号进行登录。");</script>';
        }else{
            $sign = base64_encode($this->md5bin(md5($params['top_appkey'].$params['top_parameters'].$params['top_session'].$app_secret)));
            if($params['top_sign']==$sign){
                $status = $this->system->loadModel("system/status");
                $status->set('tb_sess',$params['top_session']);
                $mess= $center->save_sess($params['top_session']);
            }
        }
    }


    function _detail(){
        return array(
            'detail_info'=>array('label'=>__('基本信息'),'tpl'=>dirname(__FILE__).'/view/order/order_detail.html'),
            );
    }

    function detail($object_id,$func=null){
          $this->template_dir = CORE_DIR.'/admin/view/';
          parent::detail($object_id,$func=null);
    }


    function toPrint(){
        if($_POST['order_id']){
            $aInput = $_POST['order_id'];
        }elseif($orderid){
            $aInput = array($orderid);
        }else{
            $this->begin('index.php?ctl=order/order&act=index');
            $this->end(false, __('打印失败：订单参数传递出错'));
            exit();
        }

        $oCur = &$this->system->loadModel('system/cur');
        $aCur = $oCur->getSysCur();

        $dbTmpl = &$this->system->loadModel('content/systmpl');
        foreach($aInput as $orderid){
            $aData=array();
            $objOrder = &$this->system->loadModel('trading/order');
            $tbjOrder = &$this->system->loadModel('plugins/tb_order_ctl/tborder');
            $aData = $tbjOrder->gettborder_info($orderid);
            $aData['shipping'] = $this->getBytbship($aData['shipping_type']);
            $aData['currency'] = $aCur[$aData['currency']];
            $objMember = &$this->system->loadModel('member/member');
            $aMember = $objMember->getFieldById($aData['member_id'], array('uname','name','tel','mobile','email','zip','addr'));
            $aData['member'] = $aMember;

            $payment = &$this->system->loadModel('trading/payment');
            $aPayment = $payment->getPaymentById($aData['payment']);
            $aData['payment'] = $aPayment['custom_name'];

            $aData['shopname'] = $this->system->getConf('store.company_name');
            $aData['shopaddress'] = $this->system->getConf('store.address');
            $aData['shoptelphone'] = $this->system->getConf('store.telephone');
            $aData['shopzip'] = $this->system->getConf('store.zip_code');

            $aItems = $objOrder->getItemList($orderid);
            foreach($aItems as $k => $rows){
                $aItems[$k]['addon'] = unserialize($rows['addon']);
                if($rows['minfo'] && unserialize($rows['minfo'])){
                    $aItems[$k]['minfo'] = unserialize($rows['minfo']);
                }else{
                    $aItems[$k]['minfo'] = array();
                }
                if($aItems[$k]['addon']['adjname']) $aItems[$k]['name'] .= __('<br>配件：').$aItems[$k]['addon']['adjname'];
                $aItems[$k]['catname'] = $objOrder->getCatByPid($rows['product_id']);
            }
            $aData['goodsItems'] = $aItems;
            $aData['giftItems'] = $objOrder->getGiftItemList($orderid);
            $this->pagedata['pages'][] = $this->_fetch_compile_include( dirname(__FILE__).'/view/order/orderprint.html',array('order'=>$aData));
        }
        $this->pagedata['shopname'] = $aData['shopname'];
        $this->template_dir = CORE_DIR.'/admin/view/';
        $this->display('print.html');
    }


    function detail_info($order_id){
        parent::detail_info($order_id);
        $this->detail_tbdelivery($order_id);
        include_once("mdl.tborder.php");
        $tborder = new mdl_tborder();
        $aOrder = $tborder->gettborder_info($order_id);
        $aOrder['shipping']  = $this->getBytbship($aOrder['shipping_type']);
        if($aOrder['status'] == 'dead'){
            $order_status = 5;                
        }else if($aOrder['status']=='finish'){
            $order_status = 6;
        }else if($aOrder['pay_status']==0){
            $order_status = 0;
        }else if($aOrder['pay_status']==1 && $aOrder['ship_status']==0){
            $order_status = 1;
        }else if($aOrder['pay_status']==1 && $aOrder['ship_status']==1){
            $order_status = 2;
            /*if($tborder->get_delay_items($order_id)){
                $this->pagedata['can_delay'] = true;    
            };*/
        }
        foreach($this->pagedata['goodsItems'] as $k=>$v){
            $this->pagedata['goodsItems'][$k] = array_merge($this->pagedata['goodsItems'][$k],$tborder->getExtendItems($v['item_id']));
            if($this->pagedata['goodsItems'][$k]['refund_status']){
                $this->pagedata['goodsItems'][$k]['refund_msg'] = $this->get_order_refund_status($this->pagedata['goodsItems'][$k]['refund_status']);
                $this->pagedata['has_refund'] = true;
            }
        };
        $aOrder['discount'] = 0-$aOrder['discount'];
        $this->pagedata['order_id'] =  $order_id;
        $tb_pay_time = (7*24*3600+$aOrder['createtime'])-time();
        $this->pagedata['order_status'] =  $order_status;
        $this->pagedata['paytb_tiem'] = $this->get_m_d_y($tb_pay_time);
        $this->pagedata['confirm_time'] = $this->get_m_d_y((10*24*3600+$aOrder['delivery_time'])-time());
        $this->pagedata['css_url'] = str_replace("\\","/",$this->system->base_url().substr(dirname(__FILE__),strpos(dirname(__FILE__),'plugins')));
        $this->pagedata['order'] = $aOrder;
        $this->display("view/order/order_detail.html");
    }


    function detail_mark($orderid){    //订单备注
        parent::detail_mark($orderid);
        $this->template_dir = CORE_DIR.'/admin/view/';
        $this->display("order/od_mark.html");
    }

    function detail_msg($orderid){
        parent::detail_msg($orderid);
        $this->template_dir = CORE_DIR.'/admin/view/';
        $this->display("order/od_msg.html");
    }

    function detail_logs($orderid, $page=1){
        parent::detail_logs($orderid,$page);
        $this->template_dir = CORE_DIR.'/admin/view/';
        $this->display("order/od_logs.html");
    }

    function detail_bills($orderid){
        parent::detail_bills($orderid);
        $this->template_dir = CORE_DIR.'/admin/view/';
        $this->display("order/od_bill.html");
    }




    function get_m_d_y($tb_pay_time){
        $day =  floor($tb_pay_time/(24*3600));
        $tmp_hour  = $tb_pay_time%(24*3600);
        $hour = floor($tmp_hour/3600);
        $tmp_minute = floor($tmp_hour%3600);
        $minute =      floor($tmp_minute/60);
        $second =     floor($tmp_minute%60);
        return $day."天".$hour."小时".$minute."分".$second."秒";
    }

    function set_order_price($order_id){
        $url=$this->system->base_url().'shopadmin/index.php?ctl=plugins/tb_notify&act=notify&action=set_order_price';
        $param['method']='taobao.trade.price.update.page';
        $param['biz_order_id']=$order_id;
        $param['nick']='c13701638315';//todo 正式用户名
        $param['biz_type']=620;
        $param['callback_url']=urlencode($url);
        $param['height']=400;
        $url=$this->tb->getFrameUrl($param,'html');
        $this->pagedata['frame']=$url;
        $this->display('view/order/set_order_price.html');
    }



    function close_order($order_id){
        $url=$this->system->base_url().'shopadmin/index.php?ctl=plugins/tb_notify&act=notify&action=close_order';
        $param['method']='taobao.trade.order.cancel.page';
        $param['biz_order_id']=$order_id;
        $param['biz_type']=620;
        $param['nick']='c13701638315';
        $param['callback_url']=urlencode($url);
        $url=$this->tb->getFrameUrl($param,'html');
        $this->pagedata['frame']=$url;
        $this->display('view/order/taobao_frame.html');
    }
            
    function delay_delivery_time($order_id){
        $url=$this->system->base_url().'shopadmin/index.php?ctl=plugins/tb_notify&act=notify&action=delay_delivery_time';
        $param['method']='taobao.trade.receivetime.delay.page';
        $param['biz_order_id']=$order_id;
        //$param['nick']='tbtest1621';
        $param['biz_type']=620;
        $param['nick']='c13701638315';
        $param['callback_url']=urlencode($url);
        $url=$this->tb->getFrameUrl($param,'html');
        $this->pagedata['frame']=$url;
        $this->display('view/order/taobao_frame.html');
    }


    function showConsignFlow($order_id){
        require_once('mdl.tborder.php');
        $tb_model = new mdl_tborder();
        $deliver_address = $tb_model->getdeliaddress();
        if(!$deliver_address){
            echo __('发货错误：缺少发货地址，请添加后重试');
            return false;
        }else{
            foreach($deliver_address as $dk =>$dv){
                $tmp_deliv = explode(':',$dv['region']);
                $tmp_dd = explode('/',$tmp_deliv[1]);
                $deliver_address[$dk]['region_id'] = $tb_model->gettbareaid($tmp_dd[2]);
                $deliver_address[$dk]['region'] = str_replace("/"," ",$tmp_deliv[1]);
            }
            $this->pagedata['deli_address'] =  $deliver_address;
        }
        parent::showConsignFlow($order_id);
        include_once("delivercorp.php");
        $this->pagedata['corplist'] = getdeliverycorplist();
        $this->pagedata['corplist'][] = array('corp_id'=>'other','name'=>'其他');
        $this->display('view/order/orderconsign.html');
    }


    function toDelivery($order_id){
        include_once("delivercorp.php");
        $params['method'] = 'taobao.delivery.send';
        $company_code = getCompany_code($_POST['logi_id']-1);
        if($_POST['logi_id'] == 'other'){
            $company_code ='other';
        }
        if($company_code == 'virtual_goods'){
            $params['orderType'] = 'virtual_goods';
            $params['out_sid'] = 888888;
        }else{
            $params['orderType'] = 'delivery_needed';
            $params['out_sid'] = $_POST['logi_no'];
        }
        $params['company_code'] = $company_code;
        $params['seller_name'] = $_POST['seller_name'];
        $params['seller_area_id'] = $_POST['region_id'];
        $params['seller_address'] = $_POST['seller_address'];
        $params['seller_zip'] = $_POST['seller_zip'];
        $params['seller_phone'] = $_POST['seller_phone'];
        $params['seller_mobile'] = $_POST['seller_tel'];
        $params['memo'] = $_POST['memo'];
        $params['tid'] = $_POST['order_id'];
        $return_tb_msg = $this->tb->getContents($params);
        if(isset($return_tb_msg['error_response']['sub_msg'])){
            $this->template_dir = CORE_DIR.'/admin/view/';            $this->splash('failed','index.php?ctl=plugins/order_ctl&act=showConsignFlow&p[0]='.$order_id,$return_tb_msg['error_response']['sub_msg']);
            exit;
        }
        $date  = getdeliverycorplist();
        $aCorp = $date[$_POST['logi_id']-1];
        $_POST['logi_name'] = $aCorp['name'];
        $db = $this->system->database();
        $db->exec("UPDATE sdb_orders SET ship_status = 1 WHERE order_id =".$order_id,true);
        $db->exec("UPDATE sdb_tb_order_ctl_orders SET delivery_time = ".time()." WHERE order_id =".$order_id,true);
        echo '发货成功';
        parent::toDelivery($order_id);
    }

    function addtradenote($order_id){
        require_once('mdl.tborder.php');
        $order = new mdl_tborder();
        $this->pagedata['tid'] = $order_id;
        $this->pagedata['items'] = $order->getrateitems($order_id);
        $this->display("view/order/addtradenote.html");
    }


    function detail_tbdelivery($order_id){
        $db = $this->system->database();
        $data = $db->select("select * from sdb_delivery where order_id = '".$order_id."'");
        if(count($data)==0){
            $data = $db->select("select * from sdb_orders where order_id = '".$order_id."'");
        }
        $tmp_area = explode(":",$data[0]['ship_area']);
        $data[0]['ship_area'] = str_replace("/","-",$tmp_area[1]);
        $this->pagedata['delivery'] = $data[0];        
    }


     function toaddtradenote(){
        $this->template_dir = CORE_DIR.'/admin/view/';
        require_once('mdl.tborder.php');
        $order = new mdl_tborder();
        $return = true;
        if(!isset($_POST['order_id'])){
            $msg = __("参数错误，无法提交");
            $this->splash('failed','index.php?ctl=plugins/order_ctl&act=addtradenote&p[0]='.$_POST['tid'],$msg);
            exit;
        }
        foreach($_POST['order_id'] as $key=>$value){
            if(isset($_POST['result'][$key])){
                $params['method'] = 'taobao.traderate.add';
                $params['tid'] = $_POST['tid'];
                $params['order_id'] = $value;
                $params['result'] = $_POST['result'][$key];
                $params['role'] = 'seller';
                if($_POST['content'][$key]){
                    $params['content'] = $_POST['content'][$key];
                }
                $returnmsg = $this->tb->getContents($params,'','post');
                if(isset($returnmsg['error_response']['sub_msg'])){                        $this->splash('failed','index.php?ctl=plugins/order_ctl&act=addtradenote&p[0]='.$_POST['tid'],$returnmsg['error_response']['sub_msg']);
                        exit;
                }
                if($value){
                    $order->settradenote($value);
                };
            }            
        }
    }

    function get_order_refund_status($refund_id){
        $list = array(0=>'未申请退款',1=>'买家收到货物，需要退货',2=>'买家未收到货物，不需要退货',6=>'买家收到货物，不需要退货',7=>'退款成功',5=>'买家已退货，等待卖家确认收货',3=>'卖家不同意退款',8=>'卖家同意退款，等待买家退货');
        if($list[$refund_id]){
            return $list[$refund_id];
        }else{
            return "等待买家处理退款";
        }
    }


    function getBytbship($type){
        $list = array('free'=>'卖家承担运费','post'=>'平邮','express'=>'快递','ems'=>'EMS','virtual'=>'虚拟物品');
        if(isset($list[$type])){
            return $list[$type];
        }else{
            return false;
        }
    }


    function md5bin($md5str){
        $ret = '';
        for($i=0;$i<32;$i+=2){
            $ret.=chr(hexdec($md5str{$i}.$md5str{$i+1}));
        }
        return $ret;
    }


    function save_tb_nick(){
        $this->tb->setting_save();
        $this->index();
    }



    
    
}

















?>
