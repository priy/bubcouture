<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$mode_dir = ( !defined( "SHOP_DEVELOPER" ) || !constant( "SHOP_DEVELOPER" ) ) && version_compare( PHP_VERSION, "5.0", ">=" ) ? "model_v5" : "model";
require_once( CORE_DIR."/".$mode_dir."/service/mdl.apiclient.php" );
class mdl_shopex_stat extends mdl_apiclient
{

    public function mdl_shopex_stat( )
    {
        $this->key = "371e6dceb2c34cdfb489b8537477ee1c";
        $this->url = "http://esb.shopex.cn/api.php";
        parent::mdl_apiclient( );
        $certificate = $this->system->loadModel( "service/certificate" );
        $this->cert_id = $certificate->getCerti( );
    }

    public function get_certi_token( )
    {
        $result = $this->native_svc( "service.get_certi_token", array(
            "certificate_id" => $this->cert_id
        ) );
        if ( $result['result'] == "fail" )
        {
            exit( "参数错误" );
        }
        return array(
            "token" => $result['result_msg']['token'],
            "stat_domain" => $result['result_msg']['shopex_stat_domain']
        );
    }

}

?>
