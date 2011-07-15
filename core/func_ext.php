<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function parse_int( $str )
{
    return ( integer )preg_replace( "/[^0-9\\.]+/", "", $str );
}

function check_file_name( $file_name )
{
    $black_list = array( "asp" => 1, "jsp" => 1, "php" => 1, "php5" => 1 );
    if ( !$black_list[$file_name] )
    {
        return TRUE;
    }
    return FALSE;
}

function timezone_list( )
{
    return array(
        "-12" => __( "(标准时-12) 日界线西" ),
        "-11" => __( "(标准时-11) 中途岛、萨摩亚群岛" ),
        "-10" => __( "(标准时-10) 夏威夷" ),
        "-9" => __( "(标准时-9) 阿拉斯加" ),
        "-8" => __( "(标准时-8) 太平洋时间(美国和加拿大)" ),
        "-7" => __( "(标准时-7) 山地时间(美国和加拿大)" ),
        "-6" => __( "(标准时-6) 中部时间(美国和加拿大)、墨西哥城" ),
        "-5" => __( "(标准时-5) 东部时间(美国和加拿大)、波哥大" ),
        "-4" => __( "(标准时-4) 大西洋时间(加拿大)、加拉加斯" ),
        "-3" => __( "(标准时-3) 巴西、布宜诺斯艾利斯、乔治敦" ),
        "-2" => __( "(标准时-2) 中大西洋" ),
        "-1" => __( "(标准时-1) 亚速尔群岛、佛得角群岛" ),
        "0" => __( "(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡" ),
        "1" => __( "(标准时+1) 中欧时间、安哥拉、利比亚" ),
        "2" => __( "(标准时+2) 东欧时间、开罗，雅典" ),
        "3" => __( "(标准时+3) 巴格达、科威特、莫斯科" ),
        "4" => __( "(标准时+4) 阿布扎比、马斯喀特、巴库" ),
        "5" => __( "(标准时+5) 叶卡捷琳堡、伊斯兰堡、卡拉奇" ),
        "6" => __( "(标准时+6) 阿拉木图、 达卡、新亚伯利亚" ),
        "7" => __( "(标准时+7) 曼谷、河内、雅加达" ),
        "8" => __( "(北京时间) 北京、重庆、香港、新加坡" ),
        "9" => __( "(标准时+9) 东京、汉城、大阪、雅库茨克" ),
        "10" => __( "(标准时+10) 悉尼、关岛" ),
        "11" => __( "(标准时+11) 马加丹、索罗门群岛" ),
        "12" => __( "(标准时+12) 奥克兰、惠灵顿、堪察加半岛" )
    );
}

function ping_url( $url, $data = NULL )
{
    $url = parse_url( $url );
    parse_str( $url['query'], $out );
    $url['query'] = "?".http_build_query( array_merge( $out, $data ) );
    $fp = fsockopen( $url['host'], isset( $url['port'] ) ? $url['port'] : 80, $errno, $errstr, 2 );
    if ( !$fp )
    {
        return FALSE;
    }
    else
    {
        $out = "GET {$url['path']}{$url['query']} HTTP/1.1\r\n";
        $out .= "Host: {$url['host']}\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite( $fp, $out );
        while ( !feof( $fp ) )
        {
            $content .= fgets( $fp, 128 );
        }
        return $content;
    }
}

function register_shutdown_function_once( $func, $key )
{
    if ( !$key )
    {
        $key = is_array( $func ) ? implode( ":", $func ) : $func;
    }
    if ( !isset( $GLOBALS['_sd_func'][$key] ) )
    {
        $GLOBALS['GLOBALS']['_sd_func'][$key] = TRUE;
        if ( is_array( $func ) && is_string( $func[0] ) && !class_exists( $func[0] ) )
        {
            $system =& $GLOBALS['GLOBALS']['system'];
            $func[0] =& $system->loadModel( $func[0] );
        }
        register_shutdown_function( $func );
    }
}

function file_rename( $source, $dest )
{
    if ( PHP_OS == "WINNT" )
    {
        @copy( $source, $dest );
        @unlink( $source );
        if ( file_exists( $dest ) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return rename( $source, $dest );
    }
}

function ext_name( $file )
{
    return substr( $file, strrpos( $file, "." ) );
}

function ext_valid( $filename, $type )
{
    $extarr = array( );
    $filename = strtolower( $filename );
    $extarr[0] = array( ".gif", ".jpg", ".jpeg", ".png" );
    if ( !isset( $extarr[$type] ) )
    {
        return FALSE;
    }
    if ( $ext = strrchr( $filename, "." ) )
    {
        if ( in_array( $ext, $extarr[$type] ) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return FALSE;
    }
}

function base_url( $with_file = FALSE )
{
    if ( defined( "BASE_URL" ) && 1 < strlen( BASE_URL ) )
    {
        return BASE_URL;
    }
    if ( isset( $_SERVER['HTTPS'] ) && strpos( "on", $_SERVER['HTTPS'] ) )
    {
        $baseurl = "https://".$_SERVER['HTTP_HOST'];
        if ( $_SERVER['SERVER_PORT'] != 443 )
        {
            $baseurl .= ":".$_SERVER['SERVER_PORT'];
        }
    }
    else
    {
        $baseurl = "http://".$_SERVER['HTTP_HOST'];
        if ( $_SERVER['SERVER_PORT'] != 80 )
        {
            $baseurl .= ":".$_SERVER['SERVER_PORT'];
        }
    }
    if ( $with_file )
    {
        $baseurl .= $_SERVER['SCRIPT_NAME'];
    }
    else
    {
        $baseDir = dirname( $_SERVER['SCRIPT_NAME'] );
        $baseurl .= ( $baseDir == "\\" ? "" : $baseDir )."/";
    }
    return $baseurl;
}

function dateFormat( $time )
{
    return date( $GLOBALS['system']->getconf( "admin.dateFormat", "Y-m-d" ), $time );
}

function timeFormat( $time )
{
    return date( $GLOBALS['system']->getconf( "admin.timeFormat", "Y-m-d H:i:s" ), $time );
}

function array_merge2( $paArray1, $paArray2 )
{
    foreach ( $paArray1 as $sKey1 => $sValue1 )
    {
        $newArray[$sKey1] = $sValue1;
    }
    foreach ( $paArray2 as $sKey2 => $sValue2 )
    {
        $newArray[$sKey2] = $sValue2;
    }
    return $newArray;
}

function array_item( $arr, $item )
{
    if ( is_array( $arr ) )
    {
        if ( empty( $arr ) || !is_string( $item ) )
        {
            return FALSE;
        }
        $res = array( );
        foreach ( $arr as $k => $v )
        {
            if ( $v[$item] )
            {
                array_push( $res, $v[$item] );
            }
        }
        return $res;
    }
    else
    {
        return FALSE;
    }
    $container = array( );
}

function steprange( $start, $end, $step )
{
    if ( $end - $start )
    {
        if ( $step < 2 )
        {
            $step = 2;
        }
        $s = ( $end - $start ) / $step;
        $r = array(
            floor( $start ) - 1
        );
        $i = 1;
        for ( ; $i < $step; ++$i )
        {
            $n = $start + $i * $s;
            $f = pow( 10, floor( log10( $n - $r[$i - 1] ) ) );
            $r[$i] = round( $n / $f ) * $f;
            $q[$i] = array(
                $r[$i - 1] + 1,
                $r[$i]
            );
        }
        $q[$i] = array(
            $r[$step - 1] + 1,
            ceil( $end )
        );
        return $q;
    }
    else
    {
        if ( !$end )
        {
            $end = $start;
        }
        return array(
            array(
                $start,
                $end
            )
        );
    }
}

function find( $dir, $ext = NULL, $path = NULL )
{
    $return = array( );
    $sub = array( );
    if ( is_dir( $dir ) && ( $dh = opendir( $dir ) ) )
    {
        while ( ( $file = readdir( $dh ) ) !== FALSE )
        {
            if ( $file[0] != "." )
            {
                if ( is_dir( $dir."/".$file ) )
                {
                    $sub = array_merge( $sub, find( $dir."/".$file, $ext, $path."/".$file ) );
                }
                else if ( !$ext || ( $p = strrpos( $file, "." ) ) && substr( $file, $p + 1 ) == $ext )
                {
                    $return[] = $path."/".$file;
                }
            }
        }
        closedir( $dh );
    }
    sort( $return );
    return array_merge( $return, $sub );
}

function buildTag( $params, $tag, $finish = TRUE )
{
    foreach ( $params as $k => $v )
    {
        if ( !is_null( $v ) && !is_array( $v ) )
        {
            if ( $k == "value" )
            {
                $v = htmlspecialchars( $v );
            }
            $ret[] = $k."=\"".$v."\"";
        }
    }
    return "<".$tag." ".implode( " ", $ret ).( $finish ? " /" : "" ).">";
}

function formatBytes( $val, $digits = 3, $mode = "SI", $bB = "B" )
{
    $si = array( "", "K", "M", "G", "T", "P", "E", "Z", "Y" );
    $iec = array( "", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi" );
    switch ( strtoupper( $mode ) )
    {
    case "SI" :
        $factor = 1000;
        $symbols = $si;
        break;
    case "IEC" :
        $factor = 1024;
        $symbols = $iec;
        break;
    default :
        $factor = 1000;
        $symbols = $si;
        break;
    }
    switch ( $bB )
    {
    case "b" :
        $val *= 8;
        break;
    default :
        $bB = "B";
        break;
    }
    $i = 0;
    for ( ; $i < count( $symbols ) - 1 && $factor <= $val; ++$i )
    {
        $val /= $factor;
    }
    $p = strpos( $val, "." );
    if ( $p !== FALSE && $digits < $p )
    {
        $val = round( $val );
    }
    else if ( $p !== FALSE )
    {
        $val = round( $val, $digits - $p );
    }
    return round( $val, $digits ).$symbols[$i].$bB;
}

function timeLength( $time )
{
    if ( $day = floor( $time / 86400 ) )
    {
        $length .= $day."天";
    }
    if ( $hour = floor( $time % 86400 / 3600 ) )
    {
        $length .= $hour."小时";
    }
    if ( $day == 0 && $hour == 0 )
    {
        $length = floor( $time / 60 )."分";
    }
    return $length;
}

function getRefer( &$data )
{
    include_once( dirname( __FILE__ )."/lib/json.php" );
    ( );
    $o = new Services_JSON( );
    if ( isset( $_COOKIE['FIRST_REFER'] ) || isset( $_COOKIE['NOW_REFER'] ) )
    {
        $firstR = $o->decode( $_COOKIE['FIRST_REFER'], TRUE );
        $nowR = $o->decode( $_COOKIE['NOW_REFER'], TRUE );
        $data['refer_id'] = urldecode( $firstR['ID'] );
        $data['refer_url'] = $firstR['REFER'];
        $data['refer_time'] = $firstR['DATE'] / 1000;
        $data['c_refer_id'] = urldecode( $nowR['ID'] );
        $data['c_refer_url'] = $nowR['REFER'];
        $data['c_refer_time'] = $nowR['DATE'] / 1000;
    }
}

function day( $time = NULL )
{
    if ( !isset( $GLOBALS['_day'][$time] ) )
    {
        return $GLOBALS['GLOBALS']['_day'][$time] = floor( $time / 86400 );
    }
    else
    {
        return $GLOBALS['_day'][$time];
    }
}

function array_slice_preserve_keys( $array, $offset, $length = NULL )
{
    if ( version_compare( phpversion( ), "5.0.2", ">=" ) )
    {
        return array_slice( $array, $offset, $length, TRUE );
    }
    else
    {
        $result = array( );
        $i = 0;
        if ( $offset < 0 )
        {
            $offset = count( $array ) + $offset;
        }
        if ( 0 < $length )
        {
            $endOffset = $offset + $length;
        }
        else if ( $length < 0 )
        {
            $endOffset = count( $array ) + $length;
        }
        else
        {
            $endOffset = count( $array );
        }
        foreach ( $array as $key => $value )
        {
            if ( $offset <= $i && $i < $endOffset )
            {
                $result[$key] = $value;
            }
            ++$i;
        }
        return $result;
    }
}

function safeHtml( $var )
{
    return preg_replace( "/<(\\s*)(script|object|iframe|embed)(.*?)>/is", "&lt;\$1\$2\$3&gt;", $var );
}

function mydate( $f, $d = NULL )
{
    global $_dateCache;
    if ( !$d )
    {
        $d = time( );
    }
    if ( !isset( $_dateCache[$d][$f] ) )
    {
        $_dateCache[$d][$f] = date( $f, $d );
    }
    return $_dateCache[$d][$f];
}

function remote_addr( )
{
    if ( !isset( $GLOBALS['_REMOTE_ADDR_'] ) )
    {
        $addrs = array( );
        if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
        {
            foreach ( array_reverse( explode( ",", $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $x_f )
            {
                $x_f = trim( $x_f );
                if ( preg_match( "/^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\$/", $x_f ) )
                {
                    $addrs[] = $x_f;
                }
            }
        }
        $GLOBALS['GLOBALS']['_REMOTE_ADDR_'] = isset( $addrs[0] ) ? $addrs[0] : $_SERVER['REMOTE_ADDR'];
    }
    return $GLOBALS['_REMOTE_ADDR_'];
}

function hostname( )
{
    $addrs = array( );
    if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) )
    {
        $addrs = array_reverse( explode( ",", $_SERVER['HTTP_X_FORWARDED_HOST'] ) );
    }
    return isset( $addrs[0] ) ? trim( $addrs[0] ) : $_SERVER['HTTP_HOST'];
}

function match_network( $nets, $ip, $first = FALSE )
{
    $return = FALSE;
    if ( !is_array( $nets ) )
    {
        $nets = array(
            $nets
        );
    }
    foreach ( $nets as $net )
    {
        $rev = preg_match( "/^\\!/", $net ) ? TRUE : FALSE;
        $net = preg_replace( "/^\\!/", "", $net );
        $ip_arr = explode( "/", $net );
        $net_long = ip2long( $ip_arr[0] );
        $x = ip2long( $ip_arr[1] );
        $mask = long2ip( $x ) == $ip_arr[1] ? $x : 4.29497e+009 << 32 - $ip_arr[1];
        $ip_long = ip2long( $ip );
        if ( $rev )
        {

[exception occured]

================================
Exception code[ C0000005 ]
Compiler[ 003C5FF0 ]
Executor[ 003C64F8 ]
OpArray[ 00CA2820 ]
File< C:\Documents and Settings\hebin\����\bubcouture\core\func_ext.php >
Class< main >
Function< match_network >
Stack[ 00146D30 ]
Step[ 7 ]
Offset[ 66 ]
LastOffset[ 88 ]
    66  IS_EQUAL                     [-]   0[0] $Tmp_33 - $Tmp_31 - $Tmp_32
================================
?>
