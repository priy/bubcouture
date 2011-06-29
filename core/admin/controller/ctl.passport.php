<?php
class ctl_passport extends adminPage{
    var $login_times_error=3;
    function login(){
        $this->pagedata['message'] = $_SESSION['loginmsg'];
        unset($_SESSION['loginmsg']);
        $this->pagedata['show_varycode']=$this->checkVeryCode();
        $this->system->__session_close();
        if($_COOKIE["SHOPEX_LOGIN_NAME"]){
            $this->pagedata['username']=$_COOKIE["SHOPEX_LOGIN_NAME"];
            $this->pagedata['save_login_name']=true;
        }
        $auth_type=$this->system->getConf('certificate.auth_type');
        if ($auth_type){
            $this->pagedata['authtype'] = $auth_type;
            $certificate = $this->system->loadModel('service/certificate');
            if ($auth_type=="free"){
                $this->pagedata['authstate'] = $certificate->getUrl($this->system->getConf('certificate.auth_strname'),1);
            }elseif($auth_type=="commercial"){
                $this->pagedata['authstate'] = $certificate->getUrl($this->system->getConf('certificate.auth_strname'),1);
            }
        }
        if($_GET['return']) $this->pagedata['return'] = $_GET['return'];
        $this->display('login.html');
    }
    function checkVeryCode()
    {
        if($this->system->getConf('system.admin_verycode') || ($this->system->getConf('system.admin_error_login_times')>$this->login_times_error && intval($this->system->getConf('system.admin_error_login_time')+3600)>time())){
            return true;
        }else{
            return false;
        }
    }
    function dologin(){
        if($this->system->getConf('system.admin_verycode') || $this->system->getConf('system.admin_error_login_times')>$this->login_times_error){
            if(strtolower($_POST["verifycode"]) !== strtolower($_SESSION["RANDOM_CODE"]))
            {
                $_SESSION['loginmsg'] = __("验证码输入错误!");
                header('Location: index.php?ctl=passport&act=login');
                exit;
            }
        }
        $oOpt = &$this->system->loadModel('admin/operator');
        $aResult = $oOpt->tryLogin($_POST);

        if ($aResult){
            require('magicvars_sys.php');

            $magic = &$this->system->loadModel('system/magicvars');
            $now_magic_data = $magic->getList('var_name','',0,-1);
            $tmp_magic_data = array();
            foreach($now_magic_data as $m_key =>$m_value){
                $tmp_magic_data[$m_value['var_name']]  = 1;
            }
            $import_data = array_diff_key($magicvars,$tmp_magic_data);
            if($import_data){
                foreach($import_data as $me=>$i_data){
                        $magic->insert($i_data);
                }
            }
            if($_POST['save_login_name']){
                setcookie("SHOPEX_LOGIN_NAME",$_POST['usrname'],(time()+86400*10));
            }else{
                setcookie("SHOPEX_LOGIN_NAME","");
            }

            $status = &$this->system->loadModel('system/status');
            $_SESSION['SHOPEX_LG_KEY'] = md5(remote_addr().$aResult['op_id']);
            setcookie('SHOPEX_LG_KEY',md5(remote_addr().$aResult['op_id']));
            $status->update(1);
            $this->system->op_id = $aResult['op_id'];
            $data['lastlogin']=time();
            $data['logincount'] = $aResult['logincount'] + 1;
            $oOpt->setLogInfo($data,$aResult['op_id']);
            $this->system->setConf('system.admin_error_login_times',0);
            if($_REQUEST['return']){
                header("Location: index.php#".$_REQUEST['return']);
            }else{
                header("Location: index.php");
            }

        }else{
            if(intval($this->system->getConf('system.admin_error_login_time')+3600)>time()){
                $this->system->setConf('system.admin_error_login_times',$this->system->getConf('system.admin_error_login_times')+1);
            }else{

                $this->system->setConf('system.admin_error_login_times',1);
            }
            $this->system->setConf('system.admin_error_login_time',time());
            $_SESSION['loginmsg'] = __('用户名或密码错误!');
            header('Location: index.php?ctl=passport&act=login');
            exit;
        }
    }

    function check_login(){
        $oOpt = &$this->system->loadModel('admin/operator');
        $aResult = $oOpt->doLogin($_POST);
        if($aResult){
            echo 'true';
            exit;
        }else{
            echo 'false';
            exit;
        }
    }

    function logout(){
        $this->system->op_id = 0;
        $_SESSION = array();
        header('Location: index.php?ctl=passport&act=login');
    }

    function verifycode(){
        header("Cache-Control: no-cache, no-store, must-revalidate"); // 强制更新
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Pragma: no-cache");
        $oVerifyCode = &$this->system->loadmodel('utility/vcode');
        $_SESSION["RANDOM_CODE"] = $oVerifyCode->init(4);
        $this->system->__session_close(1);
        $oVerifyCode->output();
    }
    function certi_validate(){
        $cert = $this->system->loadModel('service/certificate');
        $sess_id = $_POST['session_id'];

        $return = array();
        if($sess_id == $cert->get_sess()){
            $return = array(
                'res' => 'succ',
                'msg' => '',
                'info' => ''
            );

            echo json_encode($return);
        }else{
            $return = array(
                'res' => 'fail',
                'msg' => '000001',
                'info' => 'You have the different session!'
            );

            echo json_encode($return);
        }
    }
}
?>
