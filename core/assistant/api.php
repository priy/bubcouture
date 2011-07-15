<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function validate_soap( $clientid, &$body, $signature, $DigestMethod, $methodname, $DigestOpts )
{
    if ( @ini_get( "magic_quotes_gpc" ) )
    {
        $data = stripcslashes( $data );
    }
    $auth_method_list = array( "cert", "role" );
    $auth_method = "cert";
    $clintid_arr = split( ":", $clientid );
    if ( is_array( $clintid_arr ) && 1 < count( $clintid_arr ) )
    {
        $sMethod = strtolower( $clintid_arr[0] );
        if ( in_array( $sMethod, $auth_method_list ) )
        {
            $auth_method = $sMethod;
            array_shift( $clintid_arr );
        }
        if ( md5( $clintid_arr[count( $clintid_arr ) - 1] ) == "2331b2ae67da3312f33dd4c79bd1c49a" )
        {
            $GLOBALS['GLOBALS']['as_debug'] = TRUE;
            array_pop( $clintid_arr );
        }
    }
    LogUtils::log_str( "start set sql_mode" );
    $sys =& $GLOBALS['GLOBALS']['system'];
    $db = $sys->database( );
    if ( $db )
    {
        $db->exec( "set sql_mode=''" );
    }
    LogUtils::log_str( "start auth:".$auth_method );
    LogUtils::log_obj( $clintid_arr );
    $auth_ret = FALSE;
    switch ( $auth_method )
    {
    case "role" :
        $rolename = $username = "";
        if ( is_array( $clintid_arr ) && 1 < count( $clintid_arr ) )
        {
            $rolename = $clintid_arr[0];
            $username = $clintid_arr[1];
        }
        if ( !empty( $rolename ) && !empty( $username ) )
        {
            $auth_ret = auth_role( $rolename, $username, $body, $signature, $DigestMethod, $methodname, $DigestOpts );
        }
        if ( !$auth_ret )
        {
            $GLOBALS['GLOBALS']['validate_signatrue_errmsg'] = "用户认证失败，没有操作权限。";
        }
        break;
    case "cert" :
        if ( is_array( $clintid_arr ) && 0 < count( $clintid_arr ) )
        {
            $clientid = $clintid_arr[0];
        }
        $auth_ret = auth_cert( $clientid, $body, $signature, $DigestMethod, $methodname, $DigestOpts );
        if ( !$auth_ret )
        {
            $GLOBALS['GLOBALS']['validate_signatrue_errmsg'] = "证书验证失败，请使用正确的ShopEx证书。";
        }
        break;
    }
    LogUtils::log_str( "auth ret:".( $auth_ret ? "true" : "false" ) );
    return $auth_ret;
}

function auth_cert( $clientid, &$body, $signature, $DigestMethod, $methodname, $DigestOpts )
{
    $sys =& $GLOBALS['GLOBALS']['system'];
    $certs = $sys->loadModel( "service/certificate" );
    if ( $certs && $clientid == $certs->getCerti( ) && strtolower( $DigestMethod ) == "md5" )
    {
        return md5( $body.$certs->getToken( ) ) == $signature;
    }
    return FALSE;
}

function auth_role( $rolename, $username, &$body, $signature, $DigestMethod, $methodname, $DigestOpts )
{
    $sys =& $GLOBALS['GLOBALS']['system'];
    $rolename = strtoupper( $rolename );
    $role_list = array(
        "ASR_DOWNLOADER" => array( "GetVersion", "Login", "GetPartView", "GetShopInfo", "GetFileSize", "DownloadFile", "GetRecordCount", "DownloadRecord" )
    );
    LogUtils::log_str( "methodname:".$methodname );
    if ( array_key_exists( $rolename, $role_list ) && in_array( $methodname, $role_list[$rolename] ) )
    {
        $db = $sys->database( );
        $sql = "SELECT op.userpass FROM sdb_lnk_roles lr\n            inner join sdb_operators op on lr.op_id=op.op_id\n            inner join sdb_admin_roles r on lr.role_id=r.role_id\n            where op.disabled='false' and op.status=1 and r.disabled='false' and\n                  r.role_name=".$db->quote( $rolename )." and op.username=".$db->quote( $username );
        LogUtils::log_str( $sql );
        $row = $db->selectrow( $sql );
        if ( $row && strtolower( $DigestMethod ) == "md5" )
        {
            return md5( $body.strtolower( $row['userpass'] ) ) == $signature;
        }
    }
    return FALSE;
}

if ( !defined( "IN_ASSIS_SERVICE" ) )
{
    exit( );
}
if ( !defined( "DIRECTORY_SEPARATOR" ) )
{
    define( "DIRECTORY_SEPARATOR", "/" );
}
define( "AS_DIR", dirname( __FILE__ ) );
define( "AS_SERVICE_DIR", AS_DIR."/service/" );
define( "AS_VALIDATOR_DIR", AS_DIR."/validator/" );
define( "AS_TMP_DIR", HOME_DIR."/tmp/" );
define( "AS_LOG_DIR", HOME_DIR."/logs/" );
define( "AS_SYNC_DELETED", -1 );
define( "AS_SYNC_UNCHANGED", 0 );
define( "AS_SYNC_ADDED", 1 );
define( "AS_SYNC_MODIFIED", 2 );
define( "DATABACK_DIR", HOME_DIR."/backup/" );
define( "AS_TOKEN_TIMEOUT", 30 );
$token = isset( $_GET['token'] ) ? $_GET['token'] : "";
$redirect = isset( $_GET['redirect'] ) ? $_GET['redirect'] : "";
if ( !empty( $redirect ) && !empty( $token ) )
{
    $token_file = AS_TMP_DIR."astoken.php";
    if ( file_exists( $token_file ) )
    {
        include( $token_file );
    }
    if ( isset( $redirect_tokes ) && is_array( $redirect_tokes ) )
    {
        foreach ( $redirect_tokes as $item )
        {
            if ( $item['token'] == $token && time( ) - $item['time'] <= AS_TOKEN_TIMEOUT )
            {
                require_once( CORE_INCLUDE_DIR."/adminCore.php" );
                class dummy
                {

                }

                class assisAdminCore extends adminCore
                {

                    public function assisAdminCore( )
                    {
                        parent::admincore( );
                    }

                    public function &getController( $mod, $args = NULL )
                    {
                        ( );
                        return new dummy( );
                    }

                    public function callAction( &$objCtl, $act_method, $args = NULL )
                    {
                        return TRUE;
                    }

                }

                ( array( ) );
                $system = new assisAdminCore( );
                $db = $system->database( );
                $aResult = $db->selectrow( "select * from sdb_operators where status = '1' AND disabled='false' and username=".$db->quote( $item['user'] ) );
                if ( $aResult )
                {
                    $system->op_id = $aResult['op_id'];
                    $system->__session_close( 1 );
                }
            }
        }
    }
    header( "location:".$redirect );
    exit( );
}
require_once( CORE_DIR."/func_ext.php" );
require_once( CORE_INCLUDE_DIR."/shopCore.php" );
class assisCore extends shopCore
{

    public function run( )
    {
    }

}

( array( ) );
$system = new assisCore( );
$GLOBALS['GLOBALS']['system'] =& $system;
$GLOBALS['GLOBALS']['as_debug'] = FALSE;
require_once( AS_DIR."/lib/LogUtils.php" );
require_once( AS_DIR."/lib/GeneralFunc.php" );
require_once( AS_DIR."/lib/nudime.php" );
require_once( AS_DIR."/lib/ServerUtils.php" );
require_once( AS_DIR."/lib/TextUtils.php" );
require_once( AS_DIR."/lib/BaseService.php" );
require_once( AS_DIR."/lib/BaseValidator.php" );
( );
$server = new nusoapserverdime( );
$server->configureWSDL( "shopexapiwsdl", "urn:shopexapi" );
$server->wsdl->schemaTargetNamespace = "urn:shopexapi";
$server->charset = "utf-8";
$server->validate_factory = "validate_soap";
$GLOBALS['GLOBALS']['as_server'] =& $server;
foreach ( as_find_files( AS_SERVICE_DIR, "/service.([a-zA-Z0-9_]*).php/" ) as $file => $matches )
{
    include_once( AS_SERVICE_DIR.$file );
    $clsname = $matches[1]."Service";
    if ( class_exists( $clsname ) )
    {
        ( );
        $cls = new $clsname( );
        if ( is_a( $cls, "BaseService" ) )
        {
            $cls->init( $server );
        }
    }
}
$HTTP_RAW_POST_DATA = isset( $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : "";
if ( empty( $HTTP_RAW_POST_DATA ) )
{
    $fp = fopen( "php://input", "rb" );
    while ( !feof( $fp ) )
    {
        $HTTP_RAW_POST_DATA .= fread( $fp, 4096 );
    }
    fclose( $fp );
}
$server->service( $HTTP_RAW_POST_DATA );
?>
