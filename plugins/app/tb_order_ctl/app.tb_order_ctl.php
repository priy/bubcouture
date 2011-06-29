<?php
class app_tb_order_ctl extends app{
    var $ver = 1.6;
    var $name='同步处理淘宝订单';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = 'http://www.shopex.cn/help/ShopEx48/help_shopex48-1264414823-12130.html';


    function ctl_mapper(){
        return array(
               
            );
    }

    function install(){
        parent::install();
        $sqlContent = file_get_contents(dirname(__FILE__)."/dbdata/taobao_region.sql");
        foreach($this->db->splitSql($sqlContent) as $sql){
            if(!$this->db->exec($sql,true)){
                echo '<h3>Sql Error</h3><textarea style="width:500px;height:300px">'.htmlspecialchars($sql).'</textarea><br />';
                echo $this->db->errorInfo();
                exit();
            }
        }
        return true;
    }

    function uninstall(){
        $order_id = array();
        $orderdata = $this->db->select("SELECT order_id FROM sdb_orders WHERE order_refer ='taobao';");
        foreach($orderdata as $key => $value){
            $order_id[] = $value['order_id'];    
        };
        $this->db->exec("DELETE FROM sdb_orders WHERE order_refer ='taobao';");
        if($order_id){
            $this->db->exec("DELETE FROM sdb_order_items WHERE order_id IN(".implode(",",$order_id).");");
        }
        $this->db->exec("DELETE FROM sdb_status WHERE status_key ='TB_SESS' or status_key ='LAST_SYNCHRONIZATION';");
        return    parent::uninstall();
    }
    

    function getMenu(&$menu){
        $menu['order']['items'][0]['items'][]= array('type'=>'menu','label'=>'淘宝订单','link'=>'index.php?ctl=plugins/order_ctl&act=index&redirect=1');
    }
     
    function getContents($params,$session=false,$method='get',$no_red=false){
        $center = $this->system->loadModel('plugins/tb_order_ctl/center_send');
        $tb_api_mess = $center->getTbAppInfo();
        $params = array_merge($params,$tb_api_mess['result_msg']);
        return $this->system->call('tb_mess_send',$params,$session,$method,$no_red);
    }

    function getTbloginurl($url){
        $center = $this->system->loadModel('plugins/tb_order_ctl/center_send');
        if($center_msg =$center->getTbAppInfo()){
            $appkey = $center_msg['result_msg']['app_key'];
        };
        $tbs_params['api_key'] = $appkey;
        $tbs_params['ext_shop_title'] = "shopex网店";
        $tbs_params['ext_shop_domain'] = $this->system->base_url();
        $tbs_params['action'] = 'logon';
        $tbs_params['callback_url'] = $url?$url:$this->system->base_url().'shopadmin/index.php?ctl=plugins/order_ctl&act=save_sess';
        $login_url = "http://container.api.taobao.com/container/exShop";
        foreach($tbs_params as $key=>$value){
            $ps_s[]= $key.'='.$value;
        }
        $tb_url = $login_url.'?'.implode("&",$ps_s);
        return  $tb_url;
    }


    function timeout(){
        echo 'fail';
        exit;
    }

    function setting_load(){
        $center = $this->system->loadModel('plugins/tb_order_ctl/center_send');
        $mess = $center->get_tb_nick();
        if($mess){
            $nick = $mess['result_msg'];
            $this->system->setConf('app.tb_order_ctl.nick',$nick,true);
        }
    }

    function setting_save(){
        $center = $this->system->loadModel('plugins/tb_order_ctl/center_send');
        $setting = $_POST['setting'];
        if(!$center->open_servies()){
            echo '服务开通失败';
            exit;
        }
        if($center->set_tb_nick($setting['app.tb_order_ctl.nick'])){
            foreach($setting as $key=>$val){
                $this->system->setConf($key,$val);
            }    
        }else{
            echo '无法绑定该用户';
        }
    }


    function ajaxfunc(){
        return array('order_ctl:order_sync_yb:30000');
    }
}
