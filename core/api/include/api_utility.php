<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class api_utility
{

    public $host = NULL;
    public $file = NULL;
    public $port = 80;
    public $tolken = NULL;
    public $error = NULL;
    public $error_no = NULL;
    public $debug = NULL;
    public $gzip = FALSE;
    public $send_type = "json";
    public $application_error = NULL;
    public $call_func = NULL;
    public $call_params = array( );

    public function api_utility( $host, $file, $port, $tolken )
    {
        if ( !$this->system )
        {
            $this->system =& $GLOBALS['GLOBALS']['system'];
        }
        $host = strtolower( $host );
        error_reporting( E_USER_ERROR | E_ERROR | E_USER_WARNING );
        $api_error = set_error_handler( array(
            $this,
            "apiErrorHandle"
        ) );
        if ( substr( 0, 4 ) != "http" )
        {
            $host = "http://".$host;
        }
        $host_info = parse_url( $host );
        if ( $host_info['path'] == "/" || !$host_info['path'] )
        {
            $this->host = $host_info['host'];
            $this->file = $file;
        }
        else
        {
            $this->host = $host_info['host'];
            if ( $host_info['path'][strlen( $host_info['path'] ) - 1] != "/" )
            {
                $this->file = $host_info['path'].$file;
            }
            else
            {
                $this->file = substr( $host_info['path'], 0, -1 ).$file;
            }
        }
        $this->tolken = $tolken;
        restore_error_handler( );
    }

    public function encode( $vars )
    {
        if ( constant( "DEBUG_API" ) )
        {
            $vars['api_debug'] = 1;
        }
        ksort( $vars );
        $verify = "";
        foreach ( $vars as $key => $v )
        {
            $verify .= $v;
        }
        $vars['ac'] = md5( $verify.$this->tolken );
        return $vars;
    }

    public function apiErrorHandle( $errno, $errstr, $errfile, $errline )
    {
        switch ( $errno )
        {
        case E_USER_ERROR :
            $this->error = "user error:".$errstr;
            break;
        case E_USER_WARNING :
            $this->error = "user warning:".$errstr;
            break;
        case E_ERROR :
            $this->error = "error:".$errstr;
            break;
        default :
            $this->error = "system error".$errstr;
            break;
        }
        return FALSE;
    }

    public function send( $vars, $method = "post" )
    {
        if ( $vars['return_data'] )
        {
            $this->send_type = $vars['return_data'];
        }
        $vars = $this->data_encode( $vars );
        $vars = $this->encode( $vars );
        $sender_vars = "";
        foreach ( $vars as $key => $value )
        {
            $sender_vars .= $key."=".rawurlencode( $value )."&";
        }
        $sender_vars = substr( $sender_vars, 0, -1 );
        if ( $method == "post" )
        {
            return $this->data_resume( $this->api_post_send( $sender_vars ), $vars['return_data'] );
        }
        if ( $method == "get" )
        {
            return $this->data_resume( $this->api_get_send( $sender_vars ), $vars['return_data'] );
        }
    }

    public function api_post_send( $vars )
    {
        $process = fsockopen( $this->host, $this->port, $errno, $errstr, 10 );
        if ( !$process )
        {
            return FALSE;
        }
        $post .= "POST ".$this->file." HTTP/1.1\r\n";
        $post .= "Host: ".$this->host.":".$this->port."\r\n";
        $post .= "Content-Type: application/x-www-form-urlencoded\r\n";
        if ( function_exists( "gzuncompress" ) )
        {
            $post .= "Content-Encoding: gzip \r\n";
        }
        $post .= "Content-Length: ".strlen( $vars )."\r\n";
        $post .= "Connection: Close\r\n\r\n";
        $post .= $vars;
        if ( constant( "DEBUG_API" ) )
        {
            error_log( $vars."\n", 3, HOME_DIR."/logs/api.log" );
        }
        fwrite( $process, $post );
        while ( !feof( $process ) )
        {
            $buf = trim( fgets( $process, 1024 ) );
            if ( !$header_checked && !preg_match( "/HTTP+[\\s\\S]+200/", $buf ) )
            {
                $this->error = "HTTP status is not 200;".$buf;
                return FALSE;
            }
            else
            {
                $header_checked = TRUE;
            }
            if ( !$this->gzip && preg_match( "/gzip/", $buf ) )
            {
                $this->gzip = TRUE;
            }
            if ( $buf == "" )
            {
                break;
            }
            $tmp_buf_key_value = explode( ": ", $buf );
            if ( $tmp_buf_key_value[0] == "Content-Length" )
            {
                $clen = $tmp_buf_key_value[1];
            }
            else if ( $tmp_buf_key_value[0] == "Transfer-Encoding" && $tmp_buf_key_value[1] == "chunked" )
            {
                $clen = -1;
            }
            else if ( $clen == 0 )
            {
                $clen = "NO_SEARCH_LEN";
            }
        }
        $return_data = "";
        if ( $clen == "NO_SEARCH_LEN" )
        {
            while ( !feof( $process ) )
            {
                $return_data .= trim( fgets( $process, 1024 ) );
            }
        }
        else
        {
            if ( $clen == 0 )
            {
            }
            else if ( $clen <= -1 )
            {
                $loop_times1 = 0;
                do
                {
                    $clen = trim( fgets( $process, 128 ) );
                    if ( !$clen )
                    {
                        $clen = trim( fgets( $process, 128 ) );
                        if ( !$clen )
                        {
                            break;
                        }
                    }
                    $clen = base_convert( $clen, 16, 10 );
                    if ( 0 < $clen )
                    {
                        $tmp_data = "";
                        $loop_times2 = 0;
                        do
                        {
                            $need_len = $clen - strlen( $tmp_data );
                            if ( $need_len <= 0 )
                            {
                                break;
                            }
                            $tmp_data .= @fread( $process, $need_len );
                            if ( 10000 <= $loop_times2++ )
                            {
                                break;
                            }
                        } while ( 1 );
                        $return_data .= $tmp_data;
                    }
                    if ( 1000 <= $loop_times1++ )
                    {
                        break;
                    }
                } while ( 1 );
            }
            else
            {
                $return_data = "";
                $loop_times2 = 0;
                do
                {
                    $need_len = $clen - strlen( $return_data );
                    if ( $need_len <= 0 )
                    {
                        break;
                    }
                    $return_data .= @fread( $process, $need_len );
                    if ( 10000 <= $loop_times2++ )
                    {
                        break;
                    }
                } while ( 1 );
            }
        }
        fclose( $process );
        if ( $this->gzip && function_exists( "gzuncompress" ) )
        {
            return gzuncompress( $return_data );
        }
        else
        {
            return $return_data;
        }
    }

    public function api_get_send( $vars )
    {
        $process = fsockopen( $host, 80, $errno, $errstr, 10 );
        if ( !$process )
        {
            return FALSE;
        }
        $get = "GET ".$this->file." HTTP/1.1\r\n";
        $get .= "Host: ".$this->host.":".$this->port."\r\n";
        $get .= "Connection: Close\r\n\r\n";
        fwrite( $process, $get );
        while ( !feof( $process ) )
        {
            $result = fread( $process, 1024 );
        }
        fclose( $process );
        return $result;
    }

    public function data_resume( $data, $code = "json" )
    {
        switch ( $code )
        {
        case "raw" :
            return $data;
            break;
        case "json" :
            $result = json_decode( $data, TRUE );
            break;
        case "soap" :
            break;
        default :
            $xml = $this->system->loadModel( "utility/xml" );
            $result = $xml->xml2array( $data, "shopex" );
            break;
        }
        $this->error_no = $result['msg'];
        if ( constant( "DEBUG_API" ) )
        {
            if ( $result['request_info'] )
            {
                $this->debug = $result['request_info'];
            }
            error_log( print_r( $result, TRUE )."\n\n", 3, HOME_DIR."/logs/api.log" );
        }
        $this->application_error_handle( $result );
        if ( $result['result'] == "success" )
        {
            return $result['info'];
        }
        else if ( $result['result'] == "fail" )
        {
            $this->error = $result['info'];
        }
        else if ( !is_array( $result ) )
        {
            $this->error = "data invalid";
            $this->error_no = "0x017";
        }
        $this->trigger_all_errors( $this->call_func, $this->call_params );
        return FALSE;
    }

    public function _empty_varfy( &$data )
    {
        foreach ( array(
            $data
        ) as $key => $value )
        {
            if ( is_array( $value ) )
            {
                $this->_empty_varfy( $value );
            }
            else if ( is_null( $data[$key] ) )
            {
                $data[$key] = "";
            }
        }
        return $data;
    }

    public function application_error_handle( $result )
    {
        if ( is_array( $result['application_error'] ) )
        {
            foreach ( $result['application_error'] as $key => $value )
            {
                $this->application_error[] = $result['application_error'][$key];
                if ( $result['application_error'][$key]['level'] == "error" )
                {
                    $error = TRUE;
                }
            }
            if ( $error )
            {
                $this->error = "data error";
                return FALSE;
            }
        }
        return TRUE;
    }

    public function data_encode( $data )
    {
        $this->en_prase_data( $data );
        $xml = $this->system->loadModel( "utility/xml" );
        foreach ( $data as $key => $value )
        {
            if ( is_array( $value ) )
            {
                if ( $this->send_type == "json" )
                {
                    $data[$key] = json_encode( $value );
                }
                if ( $this->send_type == "xml" )
                {
                    $result = $xml->array2xml( $value, "shopex" );
                    $data[$key] = $result;
                }
            }
        }
        return $data;
    }

    public function en_prase_data( &$data )
    {
        foreach ( ( array )$data as $key => $value )
        {
            if ( preg_match( "/^[-ÿ]/", $key ) )
            {
                $data['shop_prase_name'] = $key;
                $data['shop_prase_content'] = $value;
            }
            if ( is_array( $value ) )
            {
                $this->en_prase_data( $value );
            }
        }
        return $data;
    }

    public function de_prase_data( &$data )
    {
        foreach ( ( array )$data as $key => $value )
        {
            if ( $key == "shop_prase_name" )
            {
                $data[$data['shop_prase_name']] = $data[$data['shop_prase_content']];
            }
            if ( is_array( $value ) )
            {
                $this->de_prase_data( $value );
            }
        }
    }

    public function data_decode( &$data )
    {
        foreach ( $data as $key => $value )
        {
            if ( $value[0] == "{" )
            {
                $data[$key] = json_decode( $value );
            }
        }
        $this->de_prase_data( $data );
    }

    public function _getApiData( $act, $api_version, $send = array( ), $license = TRUE, $return_data = "json", $send_data = "json" )
    {
        if ( $license )
        {
            $license = $this->system->getConf( "certificate.id" );
            if ( $license )
            {
                $send = array_merge( array(
                    "certificate_id" => $license
                ), ( array )$send );
            }
        }
        $send = array_merge( array(
            "act" => $act,
            "api_version" => $api_version,
            "send_data" => $send_data,
            "return_data" => $return_data
        ), ( array )$send );
        return $this->send( $send );
    }

    public function getApiData( $api_name, $api_version, $params, $license = TRUE, $cache = FALSE, $return_data = "json", $send_data = "json" )
    {
        if ( $this->system->getConf( "site.api.maintenance.is_maintenance" ) && 2 <= floatval( API_VERSION ) )
        {
            $this->error_no = "0x019";
            $this->error = $this->system->getConf( "site.api.maintenance.notify_msg" );
            trigger_error( $this->error, E_USER_ERROR );
            return FALSE;
        }
        $this->error_no = NULL;
        $this->error = NULL;
        if ( $cache )
        {
            $key = md5( $api_name.$api_version.json_encode( $params ) );
            if ( isset( $this->api_data[$key] ) )
            {
                return $this->api_data[$key];
            }
            else
            {
                $this->api_data[$key] = $this->_getApiData( $api_name, $api_version, $params, $license, $return_data, $send_data );
                return $this->api_data[$key];
            }
        }
        else
        {
            return $this->_getApiData( $api_name, $api_version, $params, $license, $return_data );
        }
    }

    public function trigger_all_errors( $call_func = NULL, $call_params = array( ) )
    {
        $errorStr = "";
        $callback = FALSE;
        $error_type = E_USER_ERROR;
        if ( $this->error_no == "0x003" )
        {
            $errorStr = $this->error."::";
            $callback = TRUE;
        }
        else if ( $this->error_no == "0x015" )
        {
            $errorStr = $this->error_no;
            $callback = TRUE;
        }
        else if ( $this->error_no == "0x016" )
        {
            $error_type = E_USER_WARNING;
        }
        else if ( $this->error_no != NULL )
        {
            $errorStr = "sys:".$this->error;
            $callback = TRUE;
        }
        if ( $this->application_error )
        {
            foreach ( $this->application_error as $array_error )
            {
                if ( $array_error['level'] == "error" )
                {
                    $errorStr = "sys:".$array_error['desc'];
                    $callback = TRUE;
                    break;
                }
            }
        }
        if ( $call_func && $errorStr && $callback === TRUE )
        {
            @call_user_func_array( $call_func, $call_params );
            $error_type = E_USER_WARNING;
        }
        if ( $errorStr )
        {
            trigger_error( $errorStr, $error_type );
        }
    }

}

?>
