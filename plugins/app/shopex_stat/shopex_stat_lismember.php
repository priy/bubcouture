<?php
if(!class_exists('ctl_member')){
    require(CORE_DIR.'/admin/controller/member/ctl.member.php');
}

class shopex_stat_lismember extends ctl_member{

    function shopex_stat_lismember(){
        parent::ctl_member();
        $this->system = &$GLOBALS['system'];
    }

     function get_adminaddmen(){
        $oMem = &$this->system->loadModel("member/member");
        $id = $oMem->addMemberByAdmin($_POST,$message);
        $name = $_POST['uname'];
        $stuats = 'back';
        $info_mem = array('aid'=>$id,'aname'=>$name,'style'=>$stuats);
        $status =  &$this->system->loadModel("system/status");
        $status->set('site.addmenbyadmin',serialize($info_mem));

    }
}
?>