<?php
class app_pay_offline extends app{
    var $ver = 1.0;
    var $name='线下支付';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = '';
    var $type = 'offline';
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