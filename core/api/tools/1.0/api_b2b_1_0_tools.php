<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class api_b2b_1_0_tools
{

    public function get_http( $host, $port, $uri, $post_data = "", $timeout = 8 )
    {
        $fp = fsockopen( $host, $port, $errno, $errstr, $timeout );
        if ( !$fp )
        {
            trigger_error( "[Info::Info] Can't connect to server: ".$host."! {$errstr} ({$errno})", E_USER_WARNING );
            return FALSE;
        }
        $send = "";
        if ( $post_data == "" )
        {
            $send .= "GET ".$uri." HTTP/1.1\r\n";
            $send .= "Host: ".$host.":".$port."\r\n";
            $send .= "Connection: Close\r\n\r\n";
        }
        else
        {
            $send .= "POST ".$uri." HTTP/1.1\r\n";
            $send .= "Host: ".$host.":".$port."\r\n";
            $send .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $send .= "Content-Length: ".strlen( $post_data )."\r\n";
            $send .= "Connection: Close\r\n\r\n";
            $send .= $post_data;
        }
        fwrite( $fp, $send );
        $status = "OK";
        $clen = 0;
        $header_checked = FALSE;
        while ( !feof( $fp ) )
        {
            $buf = trim( fgets( $fp, 1024 ) );
            if ( !$header_checked && !preg_match( "/HTTP+[\\s\\S]+200/", $buf ) )
            {
                return "";
            }
            else
            {
                $header_checked = TRUE;
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
            while ( !feof( $fp ) )
            {
                $return_data .= trim( fgets( $fp, 1024 ) );
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
                    $clen = trim( fgets( $fp, 128 ) );
                    if ( !$clen )
                    {
                        break;
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
                            $tmp_data .= @fread( $fp, $need_len );
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
                    $return_data .= @fread( $fp, $need_len );
                    if ( 10000 <= $loop_times2++ )
                    {
                        break;
                    }
                } while ( 1 );
            }
        }
        fclose( $fp );
        return $return_data;
    }

    public function get_http_var( $url )
    {
        $arr_http = array( );
        $url = eregi_replace( "^http://", "", $url );
        $temp = explode( "/", $url );
        $host = array_shift( $temp );
        $path = "/".implode( "/", $temp );
        $temp = explode( ":", $host );
        $host = $temp[0];
        $port = isset( $temp[1] ) ? $temp[1] : 80;
        $arr_http['host'] = $host;
        $arr_http['port'] = $port;
        $arr_http['path'] = $path;
        return $arr_http;
    }

    public function addslashes_array( $value )
    {
        if ( empty( $value ) )
        {
            return $value;
        }
        else if ( is_array( $value ) )
        {
            foreach ( $value as $k => $v )
            {
                if ( is_array( $v ) )
                {
                    $value[$k] = addslashes_array( $v );
                }
                else
                {
                    $value[$k] = addslashes( $v );
                }
            }
            return $value;
        }
        else
        {
            return addslashes( $value );
        }
    }

    public function stripslashes_array( $value )
    {
        if ( empty( $value ) )
        {
            return $value;
        }
        else if ( is_array( $value ) )
        {
            foreach ( $value as $k => $v )
            {
                if ( is_array( $v ) )
                {
                    $value[$k] = stripslashes_array( $v );
                }
                else
                {
                    $value[$k] = stripslashes( $v );
                }
            }
            return $value;
        }
        else
        {
            return stripslashes( $value );
        }
    }

}

?>
