<?php
class app_shopex_stat extends app{
    var $ver = 1.1;
    var $name='营销统计工具';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = 'http://www.shopex.cn/bbs/thread.php?fid-164.html';



   function ctl_mapper(){
        return array(
          'shop:product:index' => 'lisproduct:get_goodsinfo',
          'admin:member/member:addMemByAdmin' => 'lismember:get_adminaddmen'
       );
    }


    function listener(){
        return array(
            'trading/order:create' =>'listener:get_orderinfo',
            'trading/order:payed'=>'listener:get_payinfo',
            'trading/order:shipping'=>'listener:get_deliveryinfo',
            'member/account:register'=>'listener:get_memberinfo',
            'member/account:login'=>'listener:get_logmember',
            'member/advance:changeadvance'=>'listener:get_money',
            //'member/member:addMemberByAdmin'=>'listener:get_backmember'

        );
    }

    function output_modifiers(){
        return array(
       'shop:*'=>'modifiers:print_footer'
            );

    }


    function getMenu(&$menu){
        $menu['analytics']['items'][]= array_unshift($menu['analytics']['items'],array('type'=>'group','label'=>'营销统计工具','items'=>array(array("type"=>'menu','label'=>__('查看分析报表'),'link'=>'index.php?ctl=plugins/stat_ctl&act=index&redirect=1'))) );
    }

    function install(){
        parent::install();
        return true;
    }

    function uninstall(){
        return true;
    }

}
