<?php
class ctl_certificate extends adminPage{
    var $workground ='setting';
    function ctl_certificate(){
        parent::adminPage();
        $this->lang = 'zh-cn';
        $this->base_url = 'http://service.shopex.cn/info.php';
        $this->sess_id = $this->system->session->sess_id;
        $this->license_url="index.php?ctl=service/certificate&act=download";
    }

    function showIndex(){
        if(constant('SAAS_MODE')){
            exit;
        }
        $this->path[] = array('text'=>__('ShopEx证书'));
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $this->Certi = $this->certi_model->getCerti();
        $this->Token = $this->certi_model->getToken();
        if(empty($this->Certi) ||empty($this->Token)){
            $this->pagedata['license']=false;
        }else{
            $this->pagedata['license']=true;
            $this->pagedata['license_url']=$this->license_url;
        }
        $this->pagedata['certi_id']=$this->Certi;
        $this->pagedata['debug']=false;
        $this->page('service/index.html');
    }

    function upLicense(){
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $result1=$this->certi_model->checkFile($_FILES['license']['tmp_name']);
        if(!$result1){
            $this->splash('failed','index.php?ctl=service/certificate&act=showIndex',__('重置证书失败，请先上传文件'));
            exit;
        }
        $result = $this->certi_model->upload($_FILES['license']['tmp_name']);
        if(!$result){
            $this->splash('failed','index.php?ctl=service/certificate&act=showIndex',__('证书重置失败,请先上传文件'));
            exit;
        }else{
            $this->splash('success','index.php?ctl=service/certificate&act=showIndex',__('证书重置成功'));
            exit;
        }
    }
    function inputto(){
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $this->certi_model->inputto();
    }
    function download(){
        header("Content-type:application/octet-stream;charset=utf-8");
        header("Content-Type: application/force-download");
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $charset = &$this->system->loadModel('utility/charset');
        $this->fileName = $charset->utf2local($this->certi_model->getName().'CERTIFICATE.CER','zh');
        header("Content-Disposition:filename=".$this->fileName);
        $this->Certi = $this->certi_model->getCerti();
        $this->Token = $this->certi_model->getToken();
        echo $this->Certi;
        echo '|||';
        echo $this->Token;
    }
    function del(){
        $this->certi_model = &$this->system->loadModel('service/certificate');
        $this->certi_model->checkPass($_POST);
        if(!$this->certi_model->checkPass($_POST)){
            $this->splash('failed','index.php?ctl=service/certificate&act=checkPass',__('请输入正确的用户名和密码'));
            exit;
        }
        $this->certi_model->delLicense();
        $this->splash('success','index.php?ctl=service/certificate&act=showIndex',__('证书清空成功'));
    }
    function checkPass(){
        $this->page('service/checkp.html');
    }

}
?>
