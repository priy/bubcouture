<?php
    if(!class_exists('mdl_apiclient')){
        $mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');
        require_once(CORE_DIR.'/'.$mode_dir.'/service/mdl.apiclient.php');
    }
    class mdl_center_send extends mdl_apiclient{
        function mdl_center_send(){ 
            $this->key = '371e6dceb2c34cdfb489b8537477ee1c';
            $this->url = 'http://esb.shopex.cn/api.php';
            parent::mdl_apiclient();
            $certificate = $this->system->loadModel("service/certificate");
            $this->cert_id = $certificate->getCerti();
        }

        function getTbAppInfo(){
            return $this->returncenterMess($this->native_svc("service.get_appkey",array('certificate_id'=>$this->cert_id)));            
        }

        
        function save_sess($sess){
             return $this->returncenterMess($this->native_svc("session.save_sess",array('certificate_id'=>$this->cert_id,'tb_session_id'=>$sess)));            
        }

        function open_servies(){
             return $this->returncenterMess($this->native_svc("service.open_services",array('certificate_id'=>$this->cert_id,'services'=>'taobao_order_synchronize'))); 
        }

        function service_valid(){
            $params = array('certificate_id'=>$this->cert_id,
                            'app_id'=>'shopex_esb',
                            'version'=>'1.0',
                            'service_id'=>'taobao_order_synchronize');
            return $this->returncenterMess($this->native_svc("service.valid",$params));
        }

        function get_tb_nick(){
             return $this->returncenterMess($this->native_svc("session.get_tb_nick",array('certificate_id'=>$this->cert_id)));            
        }

        function set_tb_nick($nick){
             return $this->returncenterMess($this->native_svc("session.save_tb_nick",array('certificate_id'=>$this->cert_id,'tb_nickname'=>$nick)));
        }

        function returncenterMess($mess){
            if($mess['result']=='succ'){
                return $mess;
            }else{
                return false;
            }
        }










    }



?>