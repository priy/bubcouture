<?php
define('UC_VERSION', '1.0.0');        //UCenter 版本标识
define('API_DELETEUSER', 1);        //用户删除 API 接口开关
define('API_RENAMEUSER', 1);        //用户改名 API 接口开关
define('API_UPDATEPW', 1);        //用户改密码 API 接口开关
define('API_GETTAG', 1);        //获取标签 API 接口开关
define('API_SYNLOGIN', 1);        //同步登录 API 接口开关
define('API_SYNLOGOUT', 1);        //同步登出 API 接口开关
define('API_UPDATEBADWORDS', 0);    //更新关键字列表 开关
define('API_UPDATEHOSTS', 0);        //更新域名解析缓存 开关
define('API_UPDATEAPPS', 0);        //更新应用列表 开关
define('API_UPDATECLIENT', 1);        //更新客户端缓存 开关
define('API_UPDATECREDIT', 1);        //更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);    //向 UCenter 提供积分设置 开关
define('API_UPDATECREDITSETTINGS', 1);    //更新应用积分设置 开关
define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');
ob_start();
define('PHP_SELF',dirname($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
if(include(dirname(__FILE__).'/../config/config.php')){
    ob_end_clean();

    if(!defined('CORE_INCLUDE_DIR')){
        define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
    }

    require(CORE_DIR.'/kernel.php');
    require(CORE_INCLUDE_DIR.'/shopCore.php');
    require_once(CORE_DIR.'/func_ext.php');
    require(CORE_DIR.'/lib/uc_client/lib/xml.class.php');
    class ucCore extends shopCore{

        function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

            $ckey_length = 4;

            $key = md5($key ? $key : UC_KEY);
            $keya = md5(substr($key, 0, 16));
            $keyb = md5(substr($key, 16, 16));
            $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

            $cryptkey = $keya.md5($keya.$keyc);
            $key_length = strlen($cryptkey);

            $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
            $string_length = strlen($string);

            $result = '';
            $box = range(0, 255);

            $rndkey = array();
            for($i = 0; $i <= 255; $i++) {
                $rndkey[$i] = ord($cryptkey[$i % $key_length]);
            }

            for($j = $i = 0; $i < 256; $i++) {
                $j = ($j + $box[$i] + $rndkey[$i]) % 256;
                $tmp = $box[$i];
                $box[$i] = $box[$j];
                $box[$j] = $tmp;
            }

            for($a = $j = $i = 0; $i < $string_length; $i++) {
                $a = ($a + 1) % 256;
                $j = ($j + $box[$a]) % 256;
                $tmp = $box[$a];
                $box[$a] = $box[$j];
                $box[$j] = $tmp;
                $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
            }

            if($operation == 'DECODE') {
                if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                    return substr($result, 26);
                } else {
                    return '';
                }
            } else {
                return $keyc.str_replace('=', '', base64_encode($result));
            }

        }

        function dsetcookie($var, $value, $life = 0, $prefix = 1) {
            global $cookiedomain, $cookiepath, $timestamp, $_SERVER;
            setcookie($var, $value,
                $life ? $timestamp + $life : 0, $cookiepath,
                $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
        }

        function dstripslashes($string) {
            if(is_array($string)) {
                foreach($string as $key => $val) {
                    $string[$key] = $this->dstripslashes($val);
                }
            } else {
                $string = stripslashes($string);
            }
            return $string;
        }

        function uc_serialize($arr, $htmlon = 0) {
            return xml_serialize($arr, $htmlon);
        }

//    function uc_unserialize($s) {
//      include_once UC_CLIENT_ROOT.'./lib/xml.class.php';
//      return xml_unserialize($s);
//    }

        function run(){
            $this->definevar();
            require_once(CORE_DIR.'/lib/uc_client/client.php');
            $code = $_GET['code'];
            parse_str($this->authcode($code, 'DECODE', UC_KEY), $get);
            if(MAGIC_QUOTES_GPC) {
                $get = $this->dstripslashes($get);
            }
            if(time() - $get['time'] > 3600) {
                exit('Authracation has expiried');
            }
            if(empty($get)) {
                exit('Invalid Request');
            } 
            $action = $get['action'];
            $timestamp = time();
            $method = 'action_'.$action;
            if(method_exists($this,$method)){
                $this->$method($get);
            }else{
                exit(API_RETURN_FAILED);
            }
        }


        function action_test(){
            exit(API_RETURN_SUCCEED); 
        }

        function action_deleteuser($get=''){ 

            !API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

            //用户删除 API 接口
            $account = $this->loadModel('member/account');
            $account->PlugUserDelete($get['ids']);
            exit(API_RETURN_SUCCEED);

        }

        function action_renameuser() {

            !API_RENAMEUSER && exit(API_RETURN_FORBIDDEN);

            //用户改名 API 接口
            $uid = $get['uid'];
            $usernamenew = $get['newusername'];

            $db->query("UPDATE {$tablepre}members SET username='$usernamenew' WHERE uid='$uid'");

            exit(API_RETURN_SUCCEED);

        }

        function action_updatepw($get='') {

            !API_UPDATEPW && exit(API_RETURN_FORBIDDEN);
            //更改用户密码
            exit(API_RETURN_SUCCEED);

        }

        function action_gettag() {

            !API_GETTAG && exit(API_RETURN_FORBIDDEN);

            //获取标签 API 接口
            $return = array($name, array());
            echo $this->uc_serialize($return, 1);

        }

        function action_synlogin($get='') {
            if(time() - $get['time']<=3600){
                !API_SYNLOGIN && exit(API_RETURN_FORBIDDEN); 
                $account = $this->loadModel('member/account');
                $o=$this->loadModel('utility/charset');
                if (strtoupper(UC_DBCHARSET)<>"UTF8")
                    $get['username'] = $o->local2utf($get['username'],'zh');
                if ($data=uc_get_user($get['username'])){
                    list($uid, $uname, $email) = $data;
                }
                $account->PlugUserRegist('',$get['uid'],$get['username'],$get['password'],$email);
                
            }else{
                exit(API_RETURN_FAILED);
            }

        }
        function action_synlogout() {
            !API_SYNLOGOUT && exit(API_RETURN_FORBIDDEN);
            $account = $this->loadModel('member/account');
            $account->PlugUserExit();
        }
        function action_updatebadwords() {

            !API_UPDATEBADWORDS && exit(API_RETURN_FORBIDDEN);

            //更新关键字列表
            exit(API_RETURN_SUCCEED);

        }

        function action_updatehosts() {

            !API_UPDATEHOSTS && exit(API_RETURN_FORBIDDEN);

            //更新HOST文件
            exit(API_RETURN_SUCCEED);

        }

        function action_updateapps() {

            !API_UPDATEAPPS && exit(API_RETURN_FORBIDDEN);

            //更新应用列表
            exit(API_RETURN_SUCCEED);

        }

        function action_updateclient() {
            !API_UPDATECLIENT && exit(API_RETURN_FORBIDDEN);
            $post = xml_unserialize(file_get_contents('php://input'));
            $cachefile = CORE_DIR . '/lib/uc_client/data/cache/settings.php';
            $fp = fopen($cachefile, 'w');
            $s = "<?php\r\n";
            $s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
            fwrite($fp, $s);
            fclose($fp);
            //更新客户端缓存
            exit(API_RETURN_SUCCEED);

        }

        function action_updatecredit() {

            !UPDATECREDIT && exit(API_RETURN_FORBIDDEN);

            //更新用户积分
            exit(API_RETURN_SUCCEED);

        }

        function action_getcreditsettings() {

            !GETCREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

            //向 UCenter 提供积分设置
            echo $this->uc_serialize($credits);

        }

        function action_updatecreditsettings() {

            !API_UPDATECREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

            //更新应用积分设置
            exit(API_RETURN_SUCCEED);

        }
        function definevar(){
            $passport = $this->loadModel('member/passport');
            $data = $passport->getOptions('ucenter');
            define('UC_CONNECT', 'mysql');
            define('UC_DBHOST', $data['ucserver']['value']);
            define('UC_DBUSER', $data['ucdbuser']['value']);
            define('UC_DBPW', $data['ucdbpass']['value']);
            define('UC_DBNAME', $data['ucdbname']['value']);
            define('UC_DBCHARSET', $data['ucdbcharset']['value']);
            define('UC_DBTABLEPRE', '`'.$data['ucdbname']['value'].'`.'.$data['ucprefix']['value']);
            define('UC_DBCONNECT', 0);
            define('UC_KEY', $data['uckey']['value']);
            define('UC_API', $data['ucapi']['value']);
            define('UC_CHARSET', $data['encoding']['value']);
            $tmp=parse_url($data['ucapi']['value']);
            if (preg_match('/([0-9]{1,3}\.){3}/',$tmp['host'])){
                define('UC_IP', $data['ucserver']['value']);
            }
            else{
                define('UC_IP', gethostbyname($data['ucserver']['value']));
            }
            define('UC_APPID', $data['ucserver']['value']);
            define('UC_PPP', $data['ucserver']['value']);
        }

    }
    $system = new ucCore(array());
    $system->run();
}else{
    header('HTTP/1.1 503 Service Unavailable',true,503);
    die('<h1>Service Unavailable</h1>');
}
?>
