<?php
if(!function_exists('file_put_contents')){
    define('FILE_APPEND', 1);
    function file_put_contents($n, $d, $flag = false) {
        $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'wb';
        $f = @fopen($n, $mode);
        if ($f === false) {
            return 0;
        } else {
            if (is_array($d)) $d = implode($d);
            flock($f, LOCK_EX);
            $bytes_written = fwrite($f, $d);
            flock($f, LOCK_UN);
            fclose($f);
            return $bytes_written;
        }
    }
}

function parse_int($str){
    return (int)preg_replace('/[^0-9\.]+/','',$str);
}

function check_file_name($file_name){
    $black_list = array('asp'=>1,'jsp'=>1,'php'=>1,'php5'=>1);
    if(!$black_list[$file_name]){
        return true;
    }
    return false;
}
function timezone_list(){
    return array(
        '-12'=>__('(标准时-12) 日界线西'),
        '-11'=>__('(标准时-11) 中途岛、萨摩亚群岛'),
        '-10'=>__('(标准时-10) 夏威夷'),
        '-9'=>__('(标准时-9) 阿拉斯加'),
        '-8'=>__('(标准时-8) 太平洋时间(美国和加拿大)'),
        '-7'=>__('(标准时-7) 山地时间(美国和加拿大)'),
        '-6'=>__('(标准时-6) 中部时间(美国和加拿大)、墨西哥城'),
        '-5'=>__('(标准时-5) 东部时间(美国和加拿大)、波哥大'),
        '-4'=>__('(标准时-4) 大西洋时间(加拿大)、加拉加斯'),
        '-3'=>__('(标准时-3) 巴西、布宜诺斯艾利斯、乔治敦'),
        '-2'=>__('(标准时-2) 中大西洋'),
        '-1'=>__('(标准时-1) 亚速尔群岛、佛得角群岛'),
        '0'=>__('(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡'),
        '1'=>__('(标准时+1) 中欧时间、安哥拉、利比亚'),
        '2'=>__('(标准时+2) 东欧时间、开罗，雅典'),
        '3'=>__('(标准时+3) 巴格达、科威特、莫斯科'),
        '4'=>__('(标准时+4) 阿布扎比、马斯喀特、巴库'),
        '5'=>__('(标准时+5) 叶卡捷琳堡、伊斯兰堡、卡拉奇'),
        '6'=>__('(标准时+6) 阿拉木图、 达卡、新亚伯利亚'),
        '7'=>__('(标准时+7) 曼谷、河内、雅加达'),
        '8'=>__('(北京时间) 北京、重庆、香港、新加坡'),
        '9'=>__('(标准时+9) 东京、汉城、大阪、雅库茨克'),
        '10'=>__('(标准时+10) 悉尼、关岛'),
        '11'=>__('(标准时+11) 马加丹、索罗门群岛'),
        '12'=>__('(标准时+12) 奥克兰、惠灵顿、堪察加半岛'),
        );
}

if(!function_exists('json_encode')){
    function json_encode($value) {
        switch(gettype($value)) {
        case 'double':
        case 'integer':
            return $value>0?$value:'"'.$value.'"';
        case 'boolean':
            return $value?'true':'false';
        case 'string':
            return '"'.str_replace(
                array("\n","\b","\t","\f","\r"),
                array('\n','\b','\t','\f','\r'),
                addslashes($value)
            ).'"';
        case 'NULL':
            return 'null';
        case 'object':
            return '"Object '.get_class($value).'"';
        case 'array':
            if (isVector($value)){
                if(!$value){
                    return $value;
                }
                foreach($value as $v){
                    $result[] = json_encode($v);
                }
                return '['.implode(',',$result).']';
            }else {
                $result = '{';
                foreach ($value as $k=>$v) {
                    if ($result != '{') $result .= ',';
                    $result .= json_encode($k).':'.json_encode($v);
                }
                return $result.'}';
            }
        default:
            return '"'.addslashes($value).'"';
        }
    }
}

if(!function_exists('json_decode')){
    function json_decode($json,$assoc){
        include_once(dirname(__FILE__).'/lib/json.php');
        $o = new Services_JSON();
        return $o->decode($json,$assoc);
    }
}

if (!function_exists('ftp_chmod')){
    function ftp_chmod($ftp_stream, $mode, $filename){
        return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
    }
}

function ping_url($url,$data=null){
    $url = parse_url($url);
    parse_str($url['query'],$out);
    $url['query'] = '?'.http_build_query(array_merge($out,$data));
    $fp = fsockopen($url['host'], isset($url['port'])?$url['port']:80, $errno, $errstr, 2);
    if (!$fp) {
        return false;
    } else {
        $out = "GET {$url['path']}{$url['query']} HTTP/1.1\r\n";
        $out .= "Host: {$url['host']}\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        while (!feof($fp)) {
            $content.=fgets($fp, 128);
        }
        return $content;
    }
}

function register_shutdown_function_once($func,$key){

    if(!$key){
        $key = is_array($func)?implode(':',$func):$func;
    }

    if(!isset($GLOBALS['_sd_func'][$key])){
        $GLOBALS['_sd_func'][$key] = true;
        if(is_array($func) && is_string($func[0]) && !class_exists($func[0])){
            $system = &$GLOBALS['system'];
            $func[0] = &$system->loadModel($func[0]);
        }
        register_shutdown_function($func);
    }
}


function file_rename($source,$dest){
    if(PHP_OS=='WINNT'){
        @copy($source,$dest);
        @unlink($source);
        if(file_exists($dest)) return true;
        else return false;
    }else{
        return rename($source,$dest);
    }
}

function ext_name($file){
    return substr($file,strrpos($file,'.'));
}

//ext_valid($filename,$type)  检查上传源文件名是否合法
function ext_valid($filename,$type)
{
    $extarr = array();
    $filename = strtolower($filename);
    $extarr[0]= array(".gif",".jpg",".jpeg",".png");
    if(!isset($extarr[$type])) return false;
    if($ext = strrchr($filename,"."))
    {
        if(in_array($ext,$extarr[$type]))
        {
            return true;
        }else return false;
    }
    else return false;
}

function base_url($with_file=false){

    if(defined('BASE_URL') && strlen(BASE_URL)>1){
        return BASE_URL;
    }

    if(isset($_SERVER['HTTPS']) && strpos('on',$_SERVER['HTTPS'])){
        $baseurl = 'https://'.$_SERVER['HTTP_HOST'];
        if($_SERVER['SERVER_PORT']!=443)$baseurl.=':'.$_SERVER['SERVER_PORT'];
    }else{
        $baseurl = 'http://'.$_SERVER['HTTP_HOST'];
        if($_SERVER['SERVER_PORT']!=80)$baseurl.=':'.$_SERVER['SERVER_PORT'];
    }
    if($with_file)
        $baseurl.=$_SERVER['SCRIPT_NAME'];
    else{
        $baseDir = dirname($_SERVER['SCRIPT_NAME']);
        $baseurl.=($baseDir == '\\' ? '' : $baseDir).'/';
    }
    return $baseurl;
}

function dateFormat($time){
    return date($GLOBALS['system']->getconf('admin.dateFormat','Y-m-d'),$time);
}
function timeFormat($time){
    return date($GLOBALS['system']->getconf('admin.timeFormat','Y-m-d H:i:s'),$time);
}

function array_merge2($paArray1, $paArray2){
    foreach ($paArray1 AS $sKey1 => $sValue1){
        $newArray[$sKey1] = $sValue1;
    }
    foreach ($paArray2 AS $sKey2 => $sValue2){
        $newArray[$sKey2] = $sValue2;
    }
    return $newArray;
}

function array_item($arr, $item) {
    if (is_array($arr)){
        if (empty($arr)||!is_string($item)) {
            return false;
        }
        $res = array();
        foreach($arr as $k=>$v){
            if ($v[$item]) {
                array_push($res, $v[$item]);
            }
        }
        return $res;
    }else{
        return false;
    }

    $container = array();
    if (is_array($arr) && !empty($arr)) {
    }
}

function steprange($start,$end,$step){
    if($end-$start){
        if($step<2)$step=2;
        $s = ($end - $start)/$step;
        $r=array(floor($start)-1);

        for($i=1;$i<$step;$i++){
            $n = $start+$i*$s;
            $f=pow(10,floor(log10($n-$r[$i-1])));
            $r[$i] = round($n/$f)*$f;
            $q[$i] = array($r[$i-1]+1,$r[$i]);
        }
        $q[$i] = array($r[$step-1]+1,ceil($end));
        return $q;
    }else{
        if(!$end)$end = $start;
        return array(array($start,$end));
    }
}

function find($dir,$ext=null,$path=null){
    $return=array();
    $sub = array();
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if($file{0}!='.'){
                    if(is_dir($dir.'/'.$file)){
                        $sub = array_merge($sub,find($dir.'/'.$file,$ext,$path.'/'.$file));
                    }elseif(!$ext || (($p = strrpos($file,'.')) && substr($file,$p+1)==$ext)){
                        $return[] = $path.'/'.$file;
                    }
                }
            }
            closedir($dh);
        }
    }
    sort($return);
    return array_merge($return,$sub);
}

function buildTag($params,$tag,$finish=true){
    foreach($params as $k=>$v){
        if(!is_null($v) && !is_array($v)){
            if($k=='value'){
                $v=htmlspecialchars($v);
            }
            $ret[]=$k.'="'.$v.'"';
        }
    }
    return '<'.$tag.' '.implode(' ',$ret).($finish?' /':'').'>';
}

function formatBytes($val, $digits = 3, $mode = "SI", $bB = "B"){ //$mode == "SI"|"IEC", $bB == "b"|"B"
    $si = array("", "K", "M", "G", "T", "P", "E", "Z", "Y");
    $iec = array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
    switch(strtoupper($mode)) {
    case "SI" : $factor = 1000; $symbols = $si; break;
    case "IEC" : $factor = 1024; $symbols = $iec; break;
    default : $factor = 1000; $symbols = $si; break;
    }
    switch($bB) {
    case "b" : $val *= 8; break;
    default : $bB = "B"; break;
    }
    for($i=0;$i<count($symbols)-1 && $val>=$factor;$i++)
        $val /= $factor;
    $p = strpos($val, ".");
    if($p !== false && $p > $digits) $val = round($val);
    elseif($p !== false) $val = round($val, $digits-$p);
    return round($val, $digits) . $symbols[$i] . $bB;
}

function timeLength($time){
    if($day = floor($time/(24*3600))){
        $length .= $day.'天';
    }
    if($hour = floor($time % (24*3600)/3600)){
        $length .= $hour.'小时';
    }
    if($day==0 && $hour==0){
        $length = floor($time/60).'分';
    }
    return $length;
}

function getRefer(&$data){
    include_once(dirname(__FILE__).'/lib/json.php');
    $o = new Services_JSON();
    if(isset($_COOKIE['FIRST_REFER'])||isset($_COOKIE['NOW_REFER'])){
        $firstR = $o->decode($_COOKIE['FIRST_REFER'],true);
        $nowR = $o->decode($_COOKIE['NOW_REFER'],true);
        $data['refer_id'] = urldecode($firstR['ID']);
        $data['refer_url'] = $firstR['REFER'];
        $data['refer_time'] = $firstR['DATE']/1000;
        $data['c_refer_id'] = urldecode($nowR['ID']);
        $data['c_refer_url'] = $nowR['REFER'];
        $data['c_refer_time'] = $nowR['DATE']/1000;

    }
}

function day($time=null){
    if(!isset($GLOBALS['_day'][$time])){
        return $GLOBALS['_day'][$time] = floor($time/86400);
    }else{
        return $GLOBALS['_day'][$time];
    }
}

function array_slice_preserve_keys($array, $offset, $length = null)
{
    if (version_compare(phpversion(), '5.0.2', ">=")) {
        return(array_slice($array, $offset, $length, true));
    } else {
        $result = array();
        $i = 0;
        if($offset < 0)
            $offset = count($array) + $offset;
        if($length > 0)
            $endOffset = $offset + $length;
        else if($length < 0)
            $endOffset = count($array) + $length;
        else
            $endOffset = count($array);

        // collect elements
        foreach($array as $key=>$value)
        {
            if($i >= $offset && $i < $endOffset)
                $result[$key] = $value;
            $i++;
        }
        return($result);
    }
}

function safeHtml($var){
    return preg_replace('/<(\s*)(script|object|iframe|embed)(.*?)>/is','&lt;$1$2$3&gt;',$var);
}

function mydate($f,$d=null){
    global $_dateCache;
    if(!$d)$d=time();
    if(!isset($_dateCache[$d][$f])){
        $_dateCache[$d][$f] = date($f,$d);
    }
    return $_dateCache[$d][$f];
}

function remote_addr(){
    if(!isset($GLOBALS['_REMOTE_ADDR_'])){
        $addrs = array();

        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            foreach( array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) as $x_f )
            {
                $x_f = trim($x_f);

                if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f ) )
                {
                    $addrs[] = $x_f;
                }
            }
        }

        $GLOBALS['_REMOTE_ADDR_'] = isset($addrs[0])?$addrs[0]:$_SERVER['REMOTE_ADDR'];
    }
    return $GLOBALS['_REMOTE_ADDR_'];
}

function hostname(){
    $addrs = array();
    if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])){
        $addrs = array_reverse( explode( ',',  $_SERVER['HTTP_X_FORWARDED_HOST'] ) );
    }
    return isset($addrs[0])?trim($addrs[0]):$_SERVER['HTTP_HOST'];
}

/**
 * Compare an IP address to network(s)
 *
 * The network(s) argument may be a string or an array. A negative network
 * match must start with a "!". Depending on the 3rd parameter, it will
 * return true or false on the first match, or any negative rule will have
 * absolute priority (default).
 *
 * Samples:
 * match_network ("192.168.1.0/24", "192.168.1.1") -> true
 *
 * match_network (array ("192.168.1.0/24",  "!192.168.1.1"), "192.168.1.1")      -> false
 * match_network (array ("192.168.1.0/24",  "!192.168.1.1"), "192.168.1.1", true) -> true
 * match_network (array ("!192.168.1.0/24", "192.168.1.1"),  "192.168.1.1")      -> false
 * match_network (array ("!192.168.1.0/24", "192.168.1.1"),  "192.168.1.1", true) -> false
 *
 * @param mixed  Network to match
 * @param string IP address
 * @param bool  true: first match will return / false: priority to negative rules (default)
 * @see http://php.benscom.com/manual/en/function.ip2long.php#56373
 */
function match_network ($nets, $ip, $first=false) {
    $return = false;
    if (!is_array ($nets)) $nets = array ($nets);

    foreach ($nets as $net) {
        $rev = (preg_match ("/^\!/", $net)) ? true : false;
        $net = preg_replace ("/^\!/", "", $net);

        $ip_arr  = explode('/', $net);
        $net_long = ip2long($ip_arr[0]);
        $x        = ip2long($ip_arr[1]);
        $mask    = long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
        $ip_long  = ip2long($ip);

        if ($rev) {
            if (($ip_long & $mask) == ($net_long & $mask)) return false;
        } else {
            if (($ip_long & $mask) == ($net_long & $mask)) $return = true;
            if ($first && $return) return true;
        }
    }
    return $return;
}

define('TIME_SHORT',0);
define('TIME_LANG',2);

function time_format($timestamp,$format = TIME_SHORT){
    switch($format){
    case TIME_SHORT:
        return date('n/j H:m',$timestamp);
    case TIME_LANG:
        return date('D M j',$timestamp);
    default:
        return date('j/n/Y',$timestamp);
    }
}

//将日期格式年-月-日转成时间戳
function dateToTimestamp($date=''){
    if($date == '') return time();
    $aDate = explode("-", $date);
    return mktime(0, 0, 0, $aDate[1], $aDate[2], $aDate[0]);
}

//取毫秒
function getMicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function mkdir_p($dir,$dirmode=0755){
    $path = explode('/',str_replace('\\','/',$dir));
    $depth = count($path);
    for($i=$depth;$i>0;$i--){
        if(file_exists(implode('/',array_slice($path,0,$i)))){
            break;
        }
    }
    for($i;$i<$depth;$i++){
        if($d= implode('/',array_slice($path,0,$i+1))){
            mkdir($d,$dirmode);
        }
    }
    return is_dir($dir);
}

function __($str){
    return $str;
    if(!isset($GLOBALS['_lang_tools'])){
        $system = &$GLOBALS['system'];
        $GLOBALS['_lang_tools'] = &$system->loadModel('utility/language');
    }
    $lang_tools = &$GLOBALS['_lang_tools'];
    return $lang_tools->translate($str);
}

//配送公式验算function
function cal_fee($exp,$weight,$totalmoney,$defPrice=0){
    if($str=trim($exp)){
        $dprice = 0;
        $weight = $weight + 0;
        $totalmoney = $totalmoney + 0;
        $str = str_replace("[", "getceil(", $str);
        $str = str_replace("]", ")", $str);
        $str = str_replace("{", "getval(", $str);
        $str = str_replace("}", ")", $str);

        $str = str_replace("w", $weight, $str);
        $str = str_replace("W", $weight, $str);
        $str = str_replace("p", $totalmoney, $str);
        $str = str_replace("P", $totalmoney, $str);
        eval("\$dprice = $str;");
        if($dprice === 'failed'){
            return $defPrice;
        }else{
            return $dprice;
        }
    }else{
        return $defPrice;
    }
}
function getval($expval){
    $expval = trim($expval);
    if($expval !== ''){
    eval("\$expval = $expval;");
    if ($expval > 0){
        return 1;
    }else if ($expval == 0){
        return 1/2;
    }else{
        return 0;
    }
    }else{
        return 0;
    }
}
function getceil($expval){
    if($expval = trim($expval)){
    eval("\$expval = $expval;");
    if ($expval > 0){
        return ceil($expval);
    }else{
        return 0;
    }
    }else{
        return 0;
    }
}

function space_split($var){
    return preg_split('/\\s*[["\']([^"\']+)["\']|\s]*/',$var,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
}

function sendfile($file){
    $handle = fopen($file, "r");
    while($buffer = fread($handle,102400)){
        echo $buffer;
        flush();
    }
    fclose($handle);
}

function download($fname='data',$data=null,$mimeType='application/force-download'){

    if(headers_sent($file,$line)){
        echo 'Header already sent @ '.$file.':'.$line;
        exit();
    }

    //header('Cache-Control: no-cache;must-revalidate'); //fix ie download bug
    header('Pragma: no-cache, no-store');
    header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");

    if(strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')){
        $fname = urlencode($fname);
        header('Content-type: '.$mimeType);
    }else{
        header('Content-type: '.$mimeType.';charset=utf-8');
    }
    header("Content-Disposition: attachment; filename=\"".$fname.'"');
    //header( "Content-Description: File Transfer");

    if($data){
        header('Content-Length: '.strlen($data));
        echo $data;
        exit();
    }
}
function remove_floder($path){
    if(($handle = opendir($path))){
        while (false !==($file = readdir($handle))){
            if($file!='.' && $file!='..'){
                if(is_dir($file)){
                    remove_floder($path.'/'.$file);
                }else{
                    @unlink($path.'/'.$file);
                }
            }
        }
        closedir($handle);
        @rmdir($path);
    }
    return true;
}
function array_key_filter(&$array,$keys){
    $return = array();
    foreach(explode(',',$keys) as $k){
        if(isset($array[$k])){
            $return[$k] = &$array[$k];
        }
    }
    return $array = $return;
}

function has_unsafeword($str){
    return preg_match('/~!@#\\$%\\^&\\*\\(\\)\\+=\\|\\}]{\\[":><\\?;\'\/\\.,/', $str);
}

function gc(&$value){
    $value = null;
    unset($value);
}

/**
 * 上传文件后，将目标文件的权限设置为0644，避免有些服务器丢失读去权限
 */
function move_chmod_uploaded_file($filename,$destination,$mod=0644){
    if(move_uploaded_file($filename,$destination)){
        chmod($destination,$mod);
        return true;
    }else{
        return false;
    }
}

function isVector (&$array) {
    $next = 0;
    foreach ($array as $k=>$v) {
        if ($k !== $next)
            return false;
        $next++;
    }
    return true;
}

/**
 * 对数组的元素添加转义
 *
 * @param array $array
 * @return array
 */
function addslashes_array($value){
    if(empty($value)){
        return $value;
    }else{
        if(is_array($value)){
            foreach($value as $k=>$v){
                if(is_array($v)){
                    $value[$k] = addslashes_array($v);
                }else{
                    $value[$k] = addslashes($v);
                }
            }
            return $value;
        }else{
            return addslashes($value);
        }
    }
}

function stripslashes_array($value){
    if(empty($value)){
        return $value;
    }else{
        if(is_array($value)){
            $tmp = $value;
            foreach($tmp as $k=>$v){
                $k = stripslashes($k);
                $value[$k] = $v;

                if(is_array($v)){
                    $value[$k] = stripslashes_array($v);
                }else{
                    $value[$k] = stripslashes($v);
                }
            }
            return $value;
        }else{
            return stripslashes($value);
        }
    }
}

function get_http( $host, $port, $uri, $post_data = "" ,$timeout = 30){
    $fp = fsockopen( $host, $port, $errno, $errstr, $timeout );
    if( !$fp )
    {
        trigger_error( "[Info::Info] Can't connect to server: ".$host."! $errstr ($errno)", E_USER_WARNING );
        return false;
    }

    $send = "";
    if( $post_data == "" )
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
        $send .= "Content-Length: ".strlen($post_data)."\r\n";
        $send .= "Connection: Close\r\n\r\n";
        $send .= $post_data;
    }

    fwrite( $fp, $send );
    $status = "OK";
    $clen = 0;
    $header_checked = false;

    while( !feof($fp) )
    {
//HTTP/1.1 200 OK HTTP/1.1 302 Moved Temporarily
        $buf = trim( fgets( $fp, 1024 ) );

        if( !$header_checked && !preg_match('/HTTP+[\s\S]+200/',$buf)){
            return '';
        }else{
            $header_checked=true;
        }

        if( $buf == "")
        {
            break;
        }
        $tmp_buf_key_value = explode( ": ", $buf );

        if( $tmp_buf_key_value[0] == "Content-Length" ) // ?
        {
            $clen = $tmp_buf_key_value[1];
        }
        else if( $tmp_buf_key_value[0] == "Transfer-Encoding" && $tmp_buf_key_value[1] == "chunked" )
        {
            $clen = -1;
        }
        else if($clen == 0)
        {
            $clen = "NO_SEARCH_LEN";
        }
    }

    $return_data = "";
    if( $clen == "NO_SEARCH_LEN" )
    {
        while( !feof($fp) )
        {
            $return_data .= trim( fgets( $fp, 1024 ) );
        }
    }
    else if( $clen == 0 )
    {
    }
    else if( $clen <= -1 )
    {
        $loop_times1 = 0;
        while( 1 )
        {
            $clen = trim( fgets($fp, 128) );
            if( !$clen )
            {
                break;
            }
            $clen = base_convert($clen, 16, 10);
            if( $clen > 0 )
            {
                $tmp_data = "";
                $loop_times2 = 0;
                while( 1 )
                {
                    $need_len = $clen - strlen( $tmp_data );
                    if( $need_len <= 0 )
                    {
                        break;
                    }
                    $tmp_data .= @fread( $fp, $need_len );
                    if( $loop_times2++ >= 10000 )
                    {
                        break;
                    }
                }
                $return_data .= $tmp_data;
            }
            if( $loop_times1++ >= 1000 )
            {
                break;
            }
        }
    }
    else
    {
        $return_data = "";
        $loop_times2 = 0;
        while( 1 )
        {
            $need_len = $clen - strlen( $return_data );
            if( $need_len <= 0 )
            {
                break;
            }
            $return_data .= @fread( $fp, $need_len );
            if( $loop_times2++ >= 10000 )
            {
                break;
            }
        }
    }

    fclose($fp);

    return $return_data;
}

function strlenChinaese( $title ){
    $realnum = 0;
    for($i=0;$i<strlen($title);$i++)
    {
        $ctype = 0;
        $cstep = 0;

        $cur = substr($title,$i,1);
        if($cur == "&")
        {
            if(substr($title,$i,4) == "&lt;")
            {
                $i += 3;
                $realnum ++;
            }
            else if(substr($title,$i,4) == "&gt;")
            {
                $i += 3;
                $realnum ++;
            }
            else if(substr($title,$i,5) == "&amp;")
            {
                $i += 4;
                $realnum ++;
            }
            else if(substr($title,$i,6) == "&quot;")
            {
                $i += 5;
                $realnum ++;
            }
            else if(preg_match("/&#(\d+);?/i",substr($title,$i,8),$match))
            {
                $i += strlen($match[0])-1;
                $realnum ++;
            }
        }else
        {
            if(ord($cur)>=252)
            {
                $i += 5;
                $realnum ++;
                if($magic)
                {
                    $blen ++;
                    $ctype = 1;
                }
            }
            else if(ord($cur)>=248)
            {
                $i += 4;
                $realnum ++;
            }
            else if(ord($cur)>=240)
            {
                $i += 3;
                $realnum ++;
            }
            else if(ord($cur)>=224)
            {
                $i += 2;
                $realnum ++;
            }
            else if(ord($cur)>=192)
            {
                $i += 1;
                $realnum ++;
            }
            else
            {
                $realnum ++;
            }
        }
    }
    return $realnum;
}

if(!function_exists('array_diff_key')){
    function array_diff_key()
    {
        $arrs = func_get_args();
        $result = array_shift($arrs);
        $aTmp = $result;
        foreach ($arrs as $array){
            foreach ($aTmp as $key => $v){
                if (array_key_exists($key, $array)){
                    unset($result[$key]);
                }
            }
        }
        unset($aTmp);
        return $result;
    }
}

if(!function_exists('http_build_query')){
    function http_build_query($data,$prefix=null,$sep='',$key='')
    {
        $ret = array();
        foreach((array)$data as $k => $v){
            $k = urlencode($k);
            if(is_int($k) && $prefix != null){
                $k = $prefix.$k;
            }
            if(!empty($key)){
                $k = $key."[".$k."]";
            }

            if(is_array($v) || is_object($v)){
                array_push($ret,http_build_query($v,"",$sep,$k));
            }else{
                array_push($ret,$k."=".urlencode($v));
            }
        }

        if(empty($sep)){
            $sep = ini_get("arg_separator.output");
        }

        return implode($sep, $ret);
    }
}

function array_change_key($items, $key){
    $_flag=false;
    if (is_array($items)){
        if (empty($arr)||!is_string($key)) {
            foreach($items as $_k => $_item){
                if($_flag || $_item[$key]){
                    $result[$_item[$key]] = $_item;
                }else{
                    return false;
                }
            }
            return $result;
        }
    }
    return false;
}


/*该函数用于生成bmp*/
function imagebmp($img, $file = "", $RLE = 0) {

$ColorCount = imagecolorstotal($img);

$Transparent = imagecolortransparent($img);
$IsTransparent = $Transparent != -1;

if ($IsTransparent)
   $ColorCount--;

if ($ColorCount == 0) {
   $ColorCount = 0;
   $BitCount = 24;
};
if (($ColorCount > 0) and ($ColorCount <= 2)) {
   $ColorCount = 2;
   $BitCount = 1;
};
if (($ColorCount > 2) and ($ColorCount <= 16)) {
   $ColorCount = 16;
   $BitCount = 4;
};
if (($ColorCount > 16) and ($ColorCount <= 256)) {
   $ColorCount = 0;
   $BitCount = 8;
};

$Width = imagesx($img);
$Height = imagesy($img);

$Zbytek = (4 - ($Width / (8 / $BitCount)) % 4) % 4;
$palsize = 0; // cid added
if ($BitCount < 24)
   $palsize = pow(2, $BitCount) * 4;

$size = (floor($Width / (8 / $BitCount)) + $Zbytek) * $Height +54;
$size += $palsize;
$offset = 54 + $palsize;

// Bitmap File Header
$ret = 'BM'; // header (2b)
$ret .= int_to_dword($size); // size of file (4b)
$ret .= int_to_dword(0); // reserved (4b)
$ret .= int_to_dword($offset); // byte location in the file which is first byte of IMAGE (4b)
// Bitmap Info Header
$ret .= int_to_dword(40); // Size of BITMAPINFOHEADER (4b)
$ret .= int_to_dword($Width); // width of bitmap (4b)
$ret .= int_to_dword($Height); // height of bitmap (4b)
$ret .= int_to_word(1); // biPlanes = 1 (2b)
$ret .= int_to_word($BitCount); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
$ret .= int_to_dword($RLE); // RLE COMPRESSION (4b)
$ret .= int_to_dword(0); // width x height (4b)
$ret .= int_to_dword(0); // biXPelsPerMeter (4b)
$ret .= int_to_dword(0); // biYPelsPerMeter (4b)
$ret .= int_to_dword(0); // Number of palettes used (4b)
$ret .= int_to_dword(0); // Number of important colour (4b)
// image data

$CC = $ColorCount;
$sl1 = strlen($ret);
if ($CC == 0)
   $CC = 256;
if ($BitCount < 24) {
   $ColorTotal = imagecolorstotal($img);
   if ($IsTransparent)
    $ColorTotal--;

   for ($p = 0; $p < $ColorTotal; $p++) {
    $color = imagecolorsforindex($img, $p);
    $ret .= inttobyte($color["blue"]);
    $ret .= inttobyte($color["green"]);
    $ret .= inttobyte($color["red"]);
    $ret .= inttobyte(0); //RESERVED
   };

   $CT = $ColorTotal;
   for ($p = $ColorTotal; $p < $CC; $p++) {
    $ret .= inttobyte(0);
    $ret .= inttobyte(0);
    $ret .= inttobyte(0);
    $ret .= inttobyte(0); //RESERVED
   };
};

$retd = ''; // cid added
if ($BitCount <= 8) {

   for ($y = $Height -1; $y >= 0; $y--) {
    $bWrite = "";
    for ($x = 0; $x < $Width; $x++) {
     $color = imagecolorat($img, $x, $y);
     $bWrite .= decbinx($color, $BitCount);
     if (strlen($bWrite) == 8) {
      $retd .= inttobyte(bindec($bWrite));
      $bWrite = "";
     };
    };

    if ((strlen($bWrite) < 8) and (strlen($bWrite) != 0)) {
     $sl = strlen($bWrite);
     for ($t = 0; $t < 8 - $sl; $t++)
      $sl .= "0";
     $retd .= inttobyte(bindec($bWrite));
    };
    for ($z = 0; $z < $Zbytek; $z++)
     $retd .= inttobyte(0);
   };
};

if (($RLE == 1) and ($BitCount == 8)) {
   for ($t = 0; $t < strlen($retd); $t += 4) {
    if ($t != 0)
     if (($t) % $Width == 0)
      $ret .= chr(0) .
      chr(0);

    if (($t +5) % $Width == 0) {
     $ret .= chr(0) . chr(5) . substr($retd, $t, 5) . chr(0);
     $t += 1;
    }
    if (($t +6) % $Width == 0) {
     $ret .= chr(0) . chr(6) . substr($retd, $t, 6);
     $t += 2;
    } else {
     $ret .= chr(0) . chr(4) . substr($retd, $t, 4);
    };
   };
   $ret .= chr(0) . chr(1);
} else {
   $ret .= $retd;
};

if ($BitCount == 24) {
   $Dopl = ''; // cid added
   for ($z = 0; $z < $Zbytek; $z++)
    $Dopl .= chr(0);

   for ($y = $Height -1; $y >= 0; $y--) {
    for ($x = 0; $x < $Width; $x++) {
     $color = imagecolorsforindex($img, ImageColorAt($img, $x, $y));
     $ret .= chr($color["blue"]) . chr($color["green"]) . chr($color["red"]);
    }
    $ret .= $Dopl;
   };

};

if ($file != "") {
   $r = ($f = fopen($file, "w"));
   $r = $r and fwrite($f, $ret);
   $r = $r and fclose($f);
   return $r;
} else {
   echo $ret;
};
};

function imagecreatefrombmp($file) {
global $CurrentBit, $echoMode;

$f = fopen($file, "r");
$Header = fread($f, 2);

if ($Header == "BM") {
   $Size = freaddword($f);
   $Reserved1 = freadword($f);
   $Reserved2 = freadword($f);
   $FirstByteOfImage = freaddword($f);

   $SizeBITMAPINFOHEADER = freaddword($f);
   $Width = freaddword($f);
   $Height = freaddword($f);
   $biPlanes = freadword($f);
   $biBitCount = freadword($f);
   $RLECompression = freaddword($f);
   $WidthxHeight = freaddword($f);
   $biXPelsPerMeter = freaddword($f);
   $biYPelsPerMeter = freaddword($f);
   $NumberOfPalettesUsed = freaddword($f);
   $NumberOfImportantColors = freaddword($f);

   if ($biBitCount < 24) {
    $img = imagecreate($Width, $Height);
    $Colors = pow(2, $biBitCount);
    for ($p = 0; $p < $Colors; $p++) {
     $B = freadbyte($f);
     $G = freadbyte($f);
     $R = freadbyte($f);
     $Reserved = freadbyte($f);
     $Palette[] = imagecolorallocate($img, $R, $G, $B);
    };

    if ($RLECompression == 0) {
     $Zbytek = (4 - ceil(($Width / (8 / $biBitCount))) % 4) % 4;

     for ($y = $Height -1; $y >= 0; $y--) {
      $CurrentBit = 0;
      for ($x = 0; $x < $Width; $x++) {
       $C = freadbits($f, $biBitCount);
       imagesetpixel($img, $x, $y, $Palette[$C]);
      };
      if ($CurrentBit != 0) {
       freadbyte($f);
      };
      for ($g = 0; $g < $Zbytek; $g++)
       freadbyte($f);
     };

    };
   };

   if ($RLECompression == 1) //$BI_RLE8
    {
    $y = $Height;

    $pocetb = 0;

    while (true) {
     $y--;
     $prefix = freadbyte($f);
     $suffix = freadbyte($f);
     $pocetb += 2;

     $echoit = false;

     if ($echoit)
      echo "Prefix: $prefix Suffix: $suffix<BR>";
     if (($prefix == 0) and ($suffix == 1))
      break;
     if (feof($f))
      break;

     while (!(($prefix == 0) and ($suffix == 0))) {
      if ($prefix == 0) {
       $pocet = $suffix;
       $Data .= fread($f, $pocet);
       $pocetb += $pocet;
       if ($pocetb % 2 == 1) {
        freadbyte($f);
        $pocetb++;
       };
      };
      if ($prefix > 0) {
       $pocet = $prefix;
       for ($r = 0; $r < $pocet; $r++)
        $Data .= chr($suffix);
      };
      $prefix = freadbyte($f);
      $suffix = freadbyte($f);
      $pocetb += 2;
      if ($echoit)
       echo "Prefix: $prefix Suffix: $suffix<BR>";
     };

     for ($x = 0; $x < strlen($Data); $x++) {
      imagesetpixel($img, $x, $y, $Palette[ord($Data[$x])]);
     };
     $Data = "";

    };

   };

   if ($RLECompression == 2) //$BI_RLE4
    {
    $y = $Height;
    $pocetb = 0;

    /*while(!feof($f))
    echo freadbyte($f)."_".freadbyte($f)."<BR>";*/
    while (true) {
     //break;
     $y--;
     $prefix = freadbyte($f);
     $suffix = freadbyte($f);
     $pocetb += 2;

     $echoit = false;

     if ($echoit)
      echo "Prefix: $prefix Suffix: $suffix<BR>";
     if (($prefix == 0) and ($suffix == 1))
      break;
     if (feof($f))
      break;

     while (!(($prefix == 0) and ($suffix == 0))) {
      if ($prefix == 0) {
       $pocet = $suffix;

       $CurrentBit = 0;
       for ($h = 0; $h < $pocet; $h++)
        $Data .= chr(freadbits($f, 4));
       if ($CurrentBit != 0)
        freadbits($f, 4);
       $pocetb += ceil(($pocet / 2));
       if ($pocetb % 2 == 1) {
        freadbyte($f);
        $pocetb++;
       };
      };
      if ($prefix > 0) {
       $pocet = $prefix;
       $i = 0;
       for ($r = 0; $r < $pocet; $r++) {
        if ($i % 2 == 0) {
         $Data .= chr($suffix % 16);
        } else {
         $Data .= chr(floor($suffix / 16));
        };
        $i++;
       };
      };
      $prefix = freadbyte($f);
      $suffix = freadbyte($f);
      $pocetb += 2;
      if ($echoit)
       echo "Prefix: $prefix Suffix: $suffix<BR>";
     };

     for ($x = 0; $x < strlen($Data); $x++) {
      imagesetpixel($img, $x, $y, $Palette[ord($Data[$x])]);
     };
     $Data = "";

    };

   };

   if ($biBitCount == 24) {
    $img = imagecreatetruecolor($Width, $Height);
    $Zbytek = $Width % 4;

    for ($y = $Height -1; $y >= 0; $y--) {
     for ($x = 0; $x < $Width; $x++) {
      $B = freadbyte($f);
      $G = freadbyte($f);
      $R = freadbyte($f);
      $color = imagecolorexact($img, $R, $G, $B);
      if ($color == -1)
       $color = imagecolorallocate($img, $R, $G, $B);
      imagesetpixel($img, $x, $y, $color);
     }
     for ($z = 0; $z < $Zbytek; $z++)
      freadbyte($f);
    };
   };
   return $img;

};

fclose($f);

};

/*
* Helping functions:
*-------------------------
*
* freadbyte($file) - reads 1 byte from $file
* freadword($file) - reads 2 bytes (1 word) from $file
* freaddword($file) - reads 4 bytes (1 dword) from $file
* freadlngint($file) - same as freaddword($file)
* decbin8($d) - returns binary string of d zero filled to 8
* RetBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
* freadbits($file,$count) - reads next $count bits from $file
* RGBToHex($R,$G,$B) - convert $R, $G, $B to hex
* int_to_dword($n) - returns 4 byte representation of $n
* int_to_word($n) - returns 2 byte representation of $n
*/

function freadbyte($f) {
return ord(fread($f, 1));
};

function freadword($f) {
$b1 = freadbyte($f);
$b2 = freadbyte($f);
return $b2 * 256 + $b1;
};

function freadlngint($f) {
return freaddword($f);
};

function freaddword($f) {
$b1 = freadword($f);
$b2 = freadword($f);
return $b2 * 65536 + $b1;
};

function RetBits($byte, $start, $len) {
$bin = decbin8($byte);
$r = bindec(substr($bin, $start, $len));
return $r;

};

$CurrentBit = 0;
function freadbits($f, $count) {
global $CurrentBit, $SMode;
$Byte = freadbyte($f);
$LastCBit = $CurrentBit;
$CurrentBit += $count;
if ($CurrentBit == 8) {
   $CurrentBit = 0;
} else {
   fseek($f, ftell($f) - 1);
};
return RetBits($Byte, $LastCBit, $count);
};

function RGBToHex($Red, $Green, $Blue) {
$hRed = dechex($Red);
if (strlen($hRed) == 1)
   $hRed = "0$hRed";
$hGreen = dechex($Green);
if (strlen($hGreen) == 1)
   $hGreen = "0$hGreen";
$hBlue = dechex($Blue);
if (strlen($hBlue) == 1)
   $hBlue = "0$hBlue";
return ($hRed . $hGreen . $hBlue);
};

function int_to_dword($n) {
return chr($n & 255) . chr(($n >> 8) & 255) . chr(($n >> 16) & 255) . chr(($n >> 24) & 255);
}
function int_to_word($n) {
return chr($n & 255) . chr(($n >> 8) & 255);
}

function decbin8($d) {
return decbinx($d, 8);
};

function decbinx($d, $n) {
$bin = decbin($d);
$sbin = strlen($bin);
for ($j = 0; $j < $n - $sbin; $j++)
   $bin = "0$bin";
return $bin;
};

function inttobyte($n) {
return chr($n);
};

function removeBom($data){
    if(is_array($data)){
        foreach($data as $k=>$v){
            $charset[1] = substr($v, 0, 1);
            $charset[2] = substr($v, 1, 1);
            $charset[3] = substr($v, 2, 1);
            if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
                $data[$k] = substr($v, 3);
            }
        }
    }
    else{
        $charset[1] = substr($data, 0, 1);
        $charset[2] = substr($data, 1, 1);
        $charset[3] = substr($data, 2, 1);
        if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
            $data = substr($data, 3);
        }
    }
    return $data;
}

function deleteDir($dir){
    $handle = @opendir($dir);
    if(!$handle){
        die("目录不存在");
    }
    while (false !== ($file = readdir($handle))) {
        if($file != "." && $file != ".."){
            $file = $dir . DIRECTORY_SEPARATOR .$file;
            if (is_dir($file)){
                deleteDir($file);
            } else {
                @unlink($file);
            }
        }
    }
    closedir( $handle );
    @rmdir($dir);
}