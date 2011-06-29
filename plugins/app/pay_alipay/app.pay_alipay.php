<?php
class app_pay_alipay extends app{
    var $ver = 1.3;
    var $name='支付宝[即时到帐]';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = '';
    var $type = 'alipay';
    function install(){
        parent::install();
        return true;
    }

    function uninstall(){
        $this->db->exec('delete from sdb_payment_cfg where pay_type ="'.$this->type.'"');
        return parent::uninstall();
    }

    function ctl_mapper(){
        return array(

        );
    }

}
?>