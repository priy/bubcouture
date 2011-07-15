<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$mode_dir = ( !defined( "SHOP_DEVELOPER" ) || !constant( "SHOP_DEVELOPER" ) ) && version_compare( PHP_VERSION, "5.0", ">=" ) ? "include_v5" : "include";
require_once( CORE_DIR."/".$mode_dir."/adminPage.php" );
class admin_stat_ctl extends adminPage
{

    public function admin_stat_ctl( )
    {
        parent::adminpage( );
    }

    public function index( )
    {
        $certificate = $this->system->loadModel( "service/certificate" );
        $certi_id = $certificate->getCerti( );
        $mdl = $this->system->loadModel( "plugins/shopex_stat/shopex_stat" );
        $token_array = $mdl->get_certi_token( );
        if ( !$token_array )
        {
            header( "Content-Type: text/html;charset=utf-8" );
            exit( "参数错误" );
        }
        $sign = md5( $certi_id.$token_array['token'] );
        $shoex_stat_webUrl = $token_array['stat_domain']."?site_id=".$certi_id."&sign=".$sign;
        $this->pagedata['shoex_stat_webUrl'] = $shoex_stat_webUrl;
        $this->display( "file:".$this->template_dir."view/index.html" );
    }

}

?>
