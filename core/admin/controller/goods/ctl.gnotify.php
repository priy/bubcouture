<?php
include_once('objectPage.php');
class ctl_gnotify extends objectPage{

    var $workground = 'goods';
    var $finder_action_tpl = 'product/gnotify/finder_action.html';
    var $object = 'goods/goodsNotify';
    var $filterUnable = true;

    function index($operate){
        if($operate=='admin'){
            $this->system->set_op_conf('notifytime',time());
        }
        parent::index();
    }

    function toNotify(){
        if($_POST['gnotify_id']){
            $notify = &$this->system->loadModel('goods/goodsNotify');
            $aRet = $notify->toNofity($_POST['gnotify_id']);
            if($aRet['success'])
                echo __("邮件发送成功，").$aRet['success'].__("条邮件已发出！");
            if($aRet['failed'])
                echo __("邮件发送失败，").$aRet['failed'].__("条邮件未发送！");
        }else{
            echo __("请先从列表中选择需要发送的记录！");
        }
    }
}
?>
