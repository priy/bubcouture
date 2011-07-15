<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class cURL
{

    public $headers = NULL;
    public $user_agent = NULL;
    public $compression = NULL;
    public $cookie_file = NULL;
    public $proxy = NULL;

    public function cURL( $cookies = FALSE, $cookie = "cookies.txt", $compression = "gzip", $proxy = "" )
    {
        $this->headers[] = "Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg";
        $this->headers[] = "Connection: Keep-Alive";
        $this->headers[] = "Content-type: application/x-www-form-urlencoded;charset=UTF-8";
        $this->user_agent = "ShopEx Curl Client";
        $this->compression = $compression;
        $this->proxy = $proxy;
        $this->cookies = $cookies;
    }

    public function cookie( $cookie_file )
    {
        if ( file_exists( $cookie_file ) )
        {
            $this->cookie_file = $cookie_file;
        }
        else
        {
            if ( !fopen( $cookie_file, "w" ) )
            {
                $this->error( "The cookie file could not be opened. Make sure this directory has the correct permissions" );
            }
            $this->cookie_file = $cookie_file;
            fclose( $this->cookie_file );
        }
    }

    public function get( $url )
    {
        $process = curl_init( $url );
        curl_setopt( $process, CURLOPT_HTTPHEADER, $this->headers );
        curl_setopt( $process, CURLOPT_HEADER, 0 );
        curl_setopt( $process, CURLOPT_USERAGENT, $this->user_agent );
        if ( $this->cookies == TRUE )
        {
            curl_setopt( $process, CURLOPT_COOKIEFILE, $this->cookie_file );
        }
        if ( $this->cookies == TRUE )
        {
            curl_setopt( $process, CURLOPT_COOKIEJAR, $this->cookie_file );
        }
        curl_setopt( $process, CURLOPT_ENCODING, $this->compression );
        curl_setopt( $process, CURLOPT_TIMEOUT, 5 );
        if ( $this->proxy )
        {
            curl_setopt( $cUrl, CURLOPT_PROXY, "proxy_ip:proxy_port" );
        }
        curl_setopt( $process, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $process, CURLOPT_FOLLOWLOCATION, 1 );
        $return = curl_exec( $process );
        curl_close( $process );
        return $return;
    }

    public function post( $url, $data )
    {
        $process = curl_init( $url );
        curl_setopt( $process, CURLOPT_HTTPHEADER, $this->headers );
        curl_setopt( $process, CURLOPT_HEADER, 1 );
        curl_setopt( $process, CURLOPT_USERAGENT, $this->user_agent );
        if ( $this->cookies == TRUE )
        {
            curl_setopt( $process, CURLOPT_COOKIEFILE, $this->cookie_file );
        }
        if ( $this->cookies == TRUE )
        {
            curl_setopt( $process, CURLOPT_COOKIEJAR, $this->cookie_file );
        }
        curl_setopt( $process, CURLOPT_ENCODING, $this->compression );
        curl_setopt( $process, CURLOPT_TIMEOUT, 30 );
        if ( $this->proxy )
        {
            curl_setopt( $process, CURLOPT_PROXY, $this->proxy );
        }
        curl_setopt( $process, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $process, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $process, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $process, CURLOPT_POST, 1 );
        $return = curl_exec( $process );
        curl_close( $process );
        return $return;
    }

    public function error( $error )
    {
        echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>{$error}</div></center>";
        exit( );
    }

}

?>
