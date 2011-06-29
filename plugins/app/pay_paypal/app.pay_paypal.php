<?php
class app_pay_paypal extends app{
    var $ver = 1.1;
    var $name='PayPal';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = '';
    var $type = 'paypal';
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