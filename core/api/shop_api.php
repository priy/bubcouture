<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( CORE_DIR."/kernel.php" );
require_once( CORE_DIR."/func_ext.php" );
class shop_api extends kernel
{

    public $return_data_type = 0;
    public $version = NULL;

    public function shop_api( )
    {
        error_reporting( E_USER_ERROR | E_ERROR | E_USER_WARNING );
        $api_error = set_error_handler( array(
            $this,
            "apiErrorHandle"
        ) );
        parent::kernel( );
        $this->magic = get_magic_quotes_gpc( );
        $method = include( CORE_DIR."/api/include/api_link.php" );
        $apiversion = $_POST['api_version'];
        if ( $method[$_POST['act']][$_POST['api_version']] )
        {
            $callmethod = $method[$_POST['act']][$_POST['api_version']];
        }
        else if ( $method[$_POST['act']] && ( $apiversion = $this->api_version_compare( $method[$_POST['act']], $_POST['api_version'] ) ) )
        {
            $callmethod = $method[$_POST['act']][$apiversion];
        }
        if ( !$method[$_POST['act']] )
        {
            $this->error_handle( "missing method" );
        }
        else if ( !$method[$_POST['act']][$apiversion]['n_varify'] && !$this->verfy( $_POST ) )
        {
            $this->error_handle( "veriy fail" );
        }
        else if ( $ctl = $callmethod['ctl'] )
        {
            include( CORE_DIR."/".dirname( $ctl )."/".$apiversion."/".basename( $ctl ).".php" );
            $ctl = basename( $ctl );
            ( );
            $action = new $ctl( );
            $callmethod = $method[$_POST['act']][$apiversion];
            if ( $_POST['return_data'] )
            {
                $action->data_format = strtolower( $_POST['return_data'] );
            }
            if ( strpos( " ".$_SERVER['HTTP_ACCEPT_ENCODING'], "gzip" ) )
            {
                $action->gzip = TRUE;
            }
            $action->verify_data( $_POST, $callmethod );
            $action->$callmethod['act']( $_POST );
        }
        else
        {
            $this->error_handle( "service error", "serice not this method" );
        }
        echo "t";
        exit( );
        restore_error_handler( );
    }

    public function api_version_compare( $data, $version )
    {
        foreach ( $result = array_reverse( $data ) as $key => $value )
        {
            if ( version_compare( $version, $key, ">" ) )
            {
                return $key;
            }
        }
        return FALSE;
    }

    public function apiErrorHandle( $errno, $errstr, $errfile, $errline )
    {
        switch ( $errno )
        {
        case E_USER_ERROR :
            $this->error_handle( "system error", "user error:".$errstr );
            break;
        case E_USER_WARNING :
            $this->error_handle( "system error", "user warning:".$errstr );
            break;
        case E_ERROR :
            $this->error_handle( "system error", "error:".$errstr );
            break;
        default :
            break;
        }
    }

    public function verfy( &$data )
    {
        if ( !$data['api_version'] )
        {
            $this->responseCode( "404" );
        }
        $token = $this->getConf( "certificate.token" );
        if ( !$token )
        {
            $this->error_handle( "shop error", "shop no token" );
        }
        $verfy = strtolower( trim( $data['ac'] ) );
        unset( $data['ac'] );
        ksort( $data );
        $tmp_verfy = "";
        foreach ( $data as $key => $value )
        {
            $data[$key] = stripslashes( $value );
            $tmp_verfy .= $data[$key];
        }
        if ( $verfy && $verfy == strtolower( md5( trim( $tmp_verfy.$token ) ) ) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function _header( $content = "text/html", $charset = "utf-8" )
    {
        header( "Content-type: ".$content.";charset=".$charset );
        header( "Cache-Control: no-cache,no-store , must-revalidate" );
        $expires = gmdate( "D, d M Y H:i:s", time( ) + 20 );
        header( "Expires: ".$expires." GMT" );
    }

    public function error_handle( $code, $error_info = NULL )
    {
        if ( !$this->error )
        {
            $this->error = include( "include/api_error_handle.php" );
        }
        $result['msg'] = $code;
        $result['result'] = "fail";
        $result['info'] = $error_info ? $error_info : $this->error[$code]['code'];
        switch ( $_POST['return_data'] )
        {
        case "json" :
            $this->_header( );
            echo json_encode( $result );
            break;
        default :
            $this->system =& $GLOBALS['GLOBALS']['system'];
            $xml =& $this->system->loadModel( "utility/xml" );
            echo $xml->array2xml( $result, "shopex" );
            exit( );
            break;
        }
        exit( );
    }

    public function mkUrl( $ctl, $act = "index", $args = NULL, $extName = "html" )
    {
        return $this->realUrl( $ctl, $act, $args, $extName, $this->request['base_url'] );
    }

}

?>
