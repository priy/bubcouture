<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$include_dir = ( !defined( "SHOP_DEVELOPER" ) || !constant( "SHOP_DEVELOPER" ) ) && version_compare( PHP_VERSION, "5.0", ">=" ) ? "include_v5" : "include";
require_once( CORE_DIR."/".$include_dir."/adminPage.php" );
class admin_sales_ctl extends adminPage
{

    public function admin_sales_ctl( )
    {
        parent::adminpage( );
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $tb_api =& $appmgr->load( "tb_sales_download" );
        $this->tb =& $tb_api;
    }

    public function dotaobaorate( )
    {
        if ( !$this->system->getConf( "app.tb_sales_download.nick" ) )
        {
            $center = $this->system->loadModel( "plugins/tb_sales_download/center_send" );
            if ( $nick = $center->get_tb_nick( ) )
            {
                $this->system->setConf( "app.tb_sales_download.nick", $nick['result_msg'], TRUE );
                $this->pagedata['css_url'] = str_replace( "\\", "/", $this->system->base_url( ).substr( dirname( __FILE__ ), strpos( dirname( __FILE__ ), "plugins" ) ) );
                $this->display( "view/sales/dotaobao_rate.html" );
            }
            else
            {
                $this->display( "view/set_nick.html" );
            }
        }
        else
        {
            $this->pagedata['css_url'] = str_replace( "\\", "/", $this->system->base_url( ).substr( dirname( __FILE__ ), strpos( dirname( __FILE__ ), "plugins" ) ) );
            $this->display( "view/sales/dotaobao_rate.html" );
        }
    }

    public function index( )
    {
        $this->save_sess( $_GET );
        echo "<script>window.location.href='".$this->system->base_url( )."shopadmin/index.php#ctl=plugins/sales_ctl&act=dotaobaorate'</script>";
    }

    public function traderate_syn( $do_output = FALSE, $page = 1 )
    {
        $this->system->call( "traderate_info_get", $do_output, $page, $this->tb );
    }

    public function sess_timeout( )
    {
        $url = $this->system->base_url( )."shopadmin/index.php?ctl=plugins/sales_ctl&act=save_sess";
        $this->pagedata['tblogin_url'] = $this->tb->getTbloginurl( $url );
        $this->display( "view/sess_timeout.html" );
    }

    public function save_sess( $params )
    {
        $center = $this->system->loadModel( "plugins/tb_sales_download/center_send" );
        if ( $center_msg = $center->getTbAppInfo( ) )
        {
            $app_secret = $center_msg['result_msg']['app_secret'];
        }
        if ( $center_msg = $center->get_tb_nick( ) )
        {
            $nick = $center_msg['result_msg'];
        }
        if ( $params['nick'] != $nick )
        {
            echo "<script>alert(\"您登录的淘宝帐号和此功能对应的应用配置中的淘宝帐号不一致，请使用此功能相关应用中配置的淘宝帐号进行登录。\");</script>";
        }
        else
        {
            $sign = base64_encode( $this->md5bin( md5( $params['top_appkey'].$params['top_parameters'].$params['top_session'].$app_secret ) ) );
            if ( $params['top_sign'] == $sign )
            {
                $status = $this->system->loadModel( "system/status" );
                $status->set( "tb_sess", $params['top_session'] );
                $mess = $center->save_sess( $params['top_session'] );
            }
        }
    }

    public function md5bin( $md5str )
    {
        $ret = "";
        $i = 0;
        for ( ; $i < 32; $i += 2 )
        {
            $ret .= chr( hexdec( $md5str[$i].$md5str[$i + 1] ) );
        }
        return $ret;
    }

    public function save_tb_nick( )
    {
        $this->tb->setting_save( );
        $this->index( );
    }

}

?>
