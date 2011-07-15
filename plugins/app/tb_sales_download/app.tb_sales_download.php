<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class app_tb_sales_download extends app
{

    public $ver = 1.1;
    public $name = "下载淘宝销售记录";
    public $website = "http://www.shopex.cn";
    public $author = "shopex";
    public $depend = array
    (
        "taobao_goods" => "同步管理淘宝商品",
        "tb_order_ctl" => "同步处理淘宝订单"
    );
    public $help = "http://www.shopex.cn/help/ShopEx48/help_shopex48-1264415044-12132.html";

    public function ctl_mapper( )
    {
        return array( );
    }

    public function getMenu( &$menu )
    {
        $menu['tools']['items']['tb_sell'] = array(
            "type" => "group",
            "label" => __( "下载淘宝销售记录" ),
            "items" => array(
                array(
                    "type" => "menu",
                    "label" => __( "下载记录和评价" ),
                    "link" => "index.php?ctl=plugins/sales_ctl&act=dotaobaorate"
                )
            )
        );
    }

    public function getContents( $params, $session = FALSE, $method = "get", $no_red = FALSE )
    {
        $center = $this->system->loadModel( "plugins/tb_sales_download/center_send" );
        $tb_api_mess = $center->getTbAppInfo( );
        $params = array_merge( $params, $tb_api_mess['result_msg'] );
        return $this->system->call( "tb_mess_send", $params, $session, $method, $no_red );
    }

    public function getTbloginurl( $url )
    {
        $center = $this->system->loadModel( "plugins/tb_sales_download/center_send" );
        $tb_api_mess = $center->getTbAppInfo( );
        if ( $tb_api_mess )
        {
            $appkey = $tb_api_mess['result_msg']['app_key'];
        }
        $tbs_params['api_key'] = $appkey;
        $tbs_params['ext_shop_title'] = "shopex网店";
        $tbs_params['ext_shop_domain'] = $this->system->base_url( );
        $tbs_params['action'] = "logon";
        $tbs_params['callback_url'] = $url;
        $login_url = "http://container.api.taobao.com/container/exShop";
        foreach ( $tbs_params as $key => $value )
        {
            $ps_s[] = $key."=".$value;
        }
        $tb_url = $login_url."?".implode( "&", $ps_s );
        return $tb_url;
    }

    public function timeout( )
    {
        echo "fail";
        exit( );
    }

    public function setting_load( )
    {
        $center = $this->system->loadModel( "plugins/tb_sales_download/center_send" );
        $mess = $center->get_tb_nick( );
        if ( $mess )
        {
            $nick = $mess['result_msg'];
            $this->system->setConf( "app.tb_sales_download.nick", $nick, TRUE );
        }
    }

    public function setting_save( )
    {
        $center = $this->system->loadModel( "plugins/tb_sales_download/center_send" );
        $setting = $_POST['setting'];
        if ( !$center->open_servies( ) )
        {
            echo "服务开通失败";
            exit( );
        }
        if ( $center->set_tb_nick( $setting['app.tb_sales_download.nick'] ) )
        {
            foreach ( $setting as $key => $val )
            {
                $this->system->setConf( $key, $val );
            }
        }
        else
        {
            echo "无法绑定该用户";
        }
    }

}

?>
