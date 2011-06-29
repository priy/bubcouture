<?php
require('paymentPlugin.php');
class pay_offline extends paymentPlugin{

    var $name = '线下支付';    //线下支付
    var $logo = '';
    var $version = 200080519;
    var $charset = 'gb2312';
    var $supportCurrency = array("ALL"=>"1");
    var $supportArea =  array("AREA_CNY");
    var $desc = '线下支付';
    var $orderby = 6;
    
    function getfields(){
        return array();
    }
}
?>
