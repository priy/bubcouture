<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function GetShopInfo( )
{
    LogUtils::log_str( "GetShopInfo Begin" );
    $server =& $GLOBALS['GLOBALS']['as_server'];
    $sys =& $GLOBALS['GLOBALS']['system'];
    $db = $sys->database( );
    $info = array(
        "timezone" => defined( "SERVER_TIMEZONE" ) ? SERVER_TIMEZONE : 8
    );
    LogUtils::log_str( "GetShopInfo Return:" );
    LogUtils::log_obj( $info );
    return $info;
}

class ShopInfoService extends BaseService
{

    public function init( &$server )
    {
        parent::init( $server );
        $server->wsdl->addComplexType( "ShopInfo", "complexType", "struct", "all", "", array(
            "timezone" => array( "name" => "timezone", "type" => "xsd:int" )
        ) );
        $server->register( "GetShopInfo", array( ), array( "return" => "tns:ShopInfo" ), "urn:shopexapi", "urn:shopexapi#GetShopInfo", "rpc", "encoded", "" );
    }

}

?>
