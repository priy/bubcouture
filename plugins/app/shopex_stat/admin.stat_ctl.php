<?php
$mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'include_v5':'include');
require_once(CORE_DIR.'/'.$mode_dir.'/adminPage.php');

class admin_stat_ctl extends adminPage{

    function admin_stat_ctl(){
         parent::adminPage();
    }

    function index(){
        $certificate = $this->system->loadModel("service/certificate");
        $certi_id = $certificate->getCerti();
        $token = $certificate->getToken();
        $sign = md5($certi_id.$token);
        $shoex_stat_webUrl = "http://stats.shopex.cn/?site_id=".$certi_id."&sign=".$sign;
        $this->pagedata['shoex_stat_webUrl'] = $shoex_stat_webUrl;
        $this->display('file:'.$this->template_dir.'view/index.html');
    }



}

















?>
