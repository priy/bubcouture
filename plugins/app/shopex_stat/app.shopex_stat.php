<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class app_shopex_stat extends app
{

    public $ver = 1.1;
    public $name = "营销统计工具";
    public $website = "http://www.shopex.cn";
    public $author = "shopex";
    public $help = "http://www.shopex.cn/bbs/thread.php?fid-164.html";

    public function ctl_mapper( )
    {
        return array( "shop:product:index" => "lisproduct:get_goodsinfo", "admin:member/member:addMemByAdmin" => "lismember:get_adminaddmen" );
    }

    public function listener( )
    {
        return array( "trading/order:create" => "listener:get_orderinfo", "trading/order:payed" => "listener:get_payinfo", "trading/order:shipping" => "listener:get_deliveryinfo", "member/account:register" => "listener:get_memberinfo", "member/account:login" => "listener:get_logmember", "member/advance:changeadvance" => "listener:get_money" );
    }

    public function output_modifiers( )
    {
        return array( "shop:*" => "modifiers:print_footer" );
    }

    public function getMenu( &$menu )
    {
        $menu['analytics']['items'][] = array_unshift( $menu['analytics']['items'], array(
            "type" => "group",
            "label" => "营销统计工具",
            "items" => array(
                array(
                    "type" => "menu",
                    "label" => __( "查看分析报表" ),
                    "link" => "index.php?ctl=plugins/stat_ctl&act=index&redirect=1"
                )
            )
        ) );
    }

    public function install( )
    {
        parent::install( );
        return TRUE;
    }

    public function uninstall( )
    {
        return TRUE;
    }

}

?>
