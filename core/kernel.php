<?php
/**
 * kernel
 *
 * @package
 * @version $Id: kernel.php 1948 2008-04-25 09:36:32Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @license Commercial
 */
class kernel{

    var $__setting;
    var $_funcPkg;
    var $models;
    var $_app_version = '4.8.5';
    var $__call_libs;
    var $_co_depth=0;
    var $memcache = false;
    var $_base_link = null;
    var $model_dir = 'model';

    function kernel(){
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        $GLOBALS['system'] = &$this;

        if (get_magic_quotes_gpc()){
            unSafeVar($_GET);
            unSafeVar($_POST);
            unSafeVar($_COOKIE);
        }
        if(!defined('CORE_INCLUDE_DIR')){
            define('CORE_INCLUDE_DIR',CORE_DIR.
               ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
        }
        set_include_path(CORE_INCLUDE_DIR.PATH_SEPARATOR.'.'.PATH_SEPARATOR.CORE_DIR.'/lib/pear');
        require('defined.php');
        $this->model_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');

        if(constant('WITH_MEMCACHE')){
            $this->init_memcache(); //review: 错误信息
        }

        if(defined('IN_INSTALLER')){
            $this->cache = new nocache();
        }else{

            $this->__metadata = unserialize(file_get_contents(HOME_DIR.'/fdata.php'));
            if(isset($this->__metadata['GTASK_REMINDER']) && $this->__metadata['GTASK_REMINDER']>0 && time()>$this->__metadata['GTASK_REMINDER']){
                $goods = &$this->loadModel('trading/goods');
                $goods->flush_gtask();
            }

            if(constant('WITHOUT_CACHE')){
                $this->cache = new nocache();
            }else{
              
              require('cachemgr.php');
                if(constant('WITH_MEMCACHE')){
                    require(PLUGIN_DIR.'/functions/cache_memcache.php');
                    $this->cache = new cache_memcache;
                }elseif(defined('CACHE_METHOD')){
                    require(PLUGIN_DIR.'/functions/'.CACHE_METHOD.'.php');
                    $cache_method = CACHE_METHOD;
                    $this->cache = new $cache_method;
                }elseif(php_sapi_name()=='isapi'){
                    require('secache.php');
                    require('secache_no_flock.php');
                    $this->cache = new secache_no_flock;
                }else{
                    require('secache.php');
                    $this->cache = new secache;
                }
            }
        }

        require('setmgr.php');
        $this->__setting = new setmgr;
        $this->set_timezone(SERVER_TIMEZONE);
    }

    function apply_modifiers(&$output,$type){
        if(!constant('SAFE_MODE')){
            $output_modifiers = $this->getConf('system.output_modifiers');

            $modifiers = array_merge(
                (array)$output_modifiers['*']
                ,(array)$output_modifiers[$type.':*']
                , (array)$output_modifiers[$type.':'.strtolower($this->request['action']['controller']).':*']
                , (array)$output_modifiers[$type.':'.strtolower($this->request['action']['controller'].':'.$this->request['action']['method'])]);

            if($modifiers){
                $appmgr = &$this->loadModel('system/appmgr');
                foreach($modifiers as $modifier){
                    if($a_func = $appmgr->get_func($modifier)){
                        list($obj,$func) = $a_func;
                        $output = $obj->$func($output,$this->ctl);
                    }
                }
            }
        }
    }

    function call(){
        $args = func_get_args();
        $func = array_shift($args);
        if(!function_exists('dapi')){
            require_once('dapi.php');
        }
        return dapi_call($func,$args,$this);
    }

    function getmeta($key,&$ret){
        if(isset($this->__metadata[$key])){
            $ret = $this->__metadata[$key];
            return true;
        }else{
            return false;
        }
    }

    function savemeta($key,$value){
        $this->__metadata[$key] = $value;
        $this->__metachanged[$key] = &$this->__metadata[$key];
        register_shutdown_function_once(array(&$this,'_final_save_meta'),__FUNCTION__.__LINE__);
    }

    function _final_save_meta(){
        $data = unserialize(file_get_contents(HOME_DIR.'/fdata.php'));
        $data = array_merge((array)$data,$this->__metachanged);
        file_put_contents(HOME_DIR.'/fdata.php',serialize($data));
    }

    function log($message,$key='message'){
        error_log();
    }

    function init_memcache(){
        if(!$this->memcache){
            $this->memcache=new Memcache;
            $ports = explode(',',MEMCACHED_PORT);
            foreach(explode(',',MEMCACHED_HOST) as $i=>$h){
                $this->memcache->addServer($h,$ports[$i]);
            }
            $this->memcache->pconnect();
        }
    }

    function set_timezone($tz){
        if(function_exists('date_default_timezone_set')){
            $tz = 0-$tz;
            if($tz>12 || $tz<-12){
                $tz = 0;
            }
            date_default_timezone_set('Etc/GMT'.($tz>0?('+'.$tz):$tz));
        }
    }

    function base_url(){
        if(!isset($this->_base_url)){
                $this->_base_url='http://'.$_SERVER['HTTP_HOST'].substr(PHP_SELF, 0, strrpos(PHP_SELF, '/') + 1);
        }
        return $this->_base_url;
    }




    /**
    *
    * This is the short Description for the Function
    *
    * This is the long description for the Class
    *
    * @return    mixed     Description
    * @access    private
    * @see        ??
    */
    function &parse($query){
        
        if($pos = strpos($query,'.')){
            $type = substr($query,$pos+1);
            if($position = strpos($type,'&')){
                $type = substr($type,0,$position);
            }
            if($position = strpos($type,'?')){
                $type = substr($type,0,$position);
            }
            $query = substr($query,0,$pos);
        }
        $query=str_replace('~','-',$query);
        $args = explode('-',$query);
        $act = 'index';


        if(($ctl = array_shift($args)) && $ctl!='index'){
            $c = count($args);
            if($c>0 && !is_numeric($args[$c-1])){
                $act = array_pop($args);
            }
                }
        foreach($args as $k=>$v){
            $args[$k] = str_replace(array(';jh;',';dian;',';xie;'),array('-','.','/'),$v);
        }
        return array('controller'=>$ctl,'method'=>$act,'args'=>$args,'type'=>$type);

    }


    function &getLink($controller,$method,$args=null,$extname=null){
        if($controller=='index') return '';
        $array = array($controller);
        $use_arg = 0;
        if(is_array($args) && (isset($args[1]) || (isset($args[0]) && $args[0]))){
            $use_arg = 1;
            foreach($args as $k=>$v){
                $args[$k] = str_replace(array('-','.','/','%2F'),array(';jh;',';dian;',';xie;',';xie;'),$v);
            }
            $array = array_merge(array($controller),$args);
            }
        if($method!='index' || ($use_arg && !is_numeric(array_pop($args)))){
            $array[] = urlencode($method);
        }
        return implode('-',$array).'.'.($extname?$extname:$this->seoEmuFile);
    }


    function microtime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    function realUrl($ctl,$act='index',$args=null,$extName = 'html',$base_url=null){
        if(!$base_url){
            $base_url = $this->base_url();
        }
        if(!$extName){
            $extName='html';
        }
        if(!isset($this->__emu_static)){
            $this->__emu_static = (!$this->getConf('system.seo.emuStatic'));
            $this->__link_builder = $this->getConf('system.seo.mklink');
        }

        if($this->__emu_static){
            $base_url.=APP_ROOT_PHP.'?';
        }

        if($ctl=='page' && $act=='index'){
            return $base_url;
        }else{

            return $base_url.$this->getLink($ctl?$ctl:$this->request['action']['controller'],$act?$act:$this->request['action']['method'],$args,$extName);

        }
    }

    function shutdown(){ }

    function errorHandler($errno, $errstr, $errfile, $errline){
        return ($errno== ($this->_halt_err_level & $errno))?false:true;
    }

    function co_start(){
        $this->_co_depth++;
    }

    function co_end(){
        return array_keys($this->_cacheObjects[$this->_co_depth--]);
    }

    function checkExpries($cname){
        if(is_array($cname)){
            for($i=$this->_co_depth;$i>0;$i--){
                foreach($cname as $obj){
                    $this->_cacheObjects[$i][strtoupper($obj)]=1;
                }
            }
        }else{
            for($i=$this->_co_depth;$i>0;$i--){
                $this->_cacheObjects[$i][strtoupper($cname)]=1;
            }
        }
    }

    /**
     * &database
     *
     * @access public
     * @return void
     */
    function &database(){
        if(!isset($this->__db)){
            if(!class_exists('AloneDB')){
                require 'AloneDB.php';
            }
            $this->__db = new AloneDB($this);
            $this->__db->prefix = DB_PREFIX;
        }
        return $this->__db;
    }

    /**
     * error
     *
     * @param int $errcode
     * @access public
     * @return void
     */
    function error($errcode=404,$errmsg=null){
        if($errcode==404){
            $this->responseCode(404);
        }
        header('X-JSON: '.json_encode(array('code'=>$errcode,'id'=>time())));
        die('<h1>Error:'.$errcode.'</h1><p>'.$errmsg.'</p>');
    }

    function api_call($instance,$host,$file,$port=80,$tolken){
        require_once(API_DIR.'/include/api_utility.php');
        if(!$this->intance_api[$instance]){
            $this->intance_api[$instance]=new api_utility($host,$file,$port,$tolken);
        }
        return $this->intance_api[$instance];
    }

    /**
     * loadModel
     *
     * @param mixed $className
     * @param mixed $single
     * @access public
     * @return void
     */
    function &loadModel($modelName,$single=true){

        if($single && isset($this->models[strtolower($modelName)]) ){
            return $this->models[strtolower($modelName)];
        }

        $className='mdl_'.basename($modelName);


        class_exists('modelFactory') or require(CORE_INCLUDE_DIR.'/modelFactory.php');

        $plugin_path = explode("/",$modelName);
        if($plugin_path[0]=='plugins'){
            if(file_exists($modleFile=PLUGIN_DIR.'/app/'.$plugin_path[1].'/mdl.'.basename($modelName).'.php')){
                require($modleFile);
            }
        }


 
        if (!class_exists($className))
            if (file_exists($modelFile=CORE_DIR.'/'.$this->model_dir.'/'.dirname($modelName).'/mdl.'.basename($modelName).'.php'))
                require($modelFile);
        if (defined('CUSTOM_CORE_DIR')){
            $CusclassName='cmd_'.basename($modelName);
            if (!class_exists($CusclassName))
                if(file_exists($cusinc = CUSTOM_CORE_DIR.'/model/'.dirname($modelName).'/cmd.'.basename($modelName).'.php')){
                    require($cusinc);
                    $className=$CusclassName;
                }
        }
        $object= new $className();
        $object->modelName = $modelName;
        if($single){
            $this->models[strtolower($modelName)] = &$object;
        }
        return $object;
    }

    /**
     * callAction
     *
     * @param mixed $objMod
     * @param mixed $act_method
     * @access public
     * @return void
     */
    function callAction(&$objCtl,$act_method,$args=null){

        $protected = array_flip(get_class_methods('pagefactory'));

        $ctlmap = $this->getConf('system.ctlmap');
        if(!$this->request['action']['ident']){
            $this->request['action']['ident'] = strtolower('shop:'.
            $this->request['action']['controller'].
            ':'.$this->request['action']['method']);
        }

        if(!constant('SAFE_MODE') && isset($ctlmap[$this->request['action']['ident']])){
            $appmgr = $this->loadModel('system/appmgr');
            list($objCtl,$act_method) = $appmgr->get_func($ctlmap[$this->request['action']['ident']]);
        }
        $tmp_ident = $this->request['action']['ident'];
        foreach($ctlmap as $key=>$value){
            $appmgr = $this->loadModel('system/appmgr');
            if($key[strlen($key)-1]=='*'&&strstr($key,substr($tmp_ident,0,strrpos($tmp_ident,':')))){
                list($objCtl,$act_method) = $appmgr->get_func(substr($value,0,strrpos($value,':')).':'.$act_method);
            }
        }


        if(isset($objCtl->_call)){
            array_unshift($args,$act_method);
            $act_method = $objCtl->_call;
        }

        if($act_method{0}!=='_' && method_exists($objCtl,$act_method)){
            if(isset($args[0])){
                call_user_func_array(array(&$objCtl,$act_method),$args);
            }else{
                $objCtl->$act_method();
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * output
     *
     * @param mixed $content
     * @param int $expired_time
     * @param mixed $mime_type
     * @param mixed $headers
     * @param mixed $filename
     * @access public
     * @return void
     */
    function output($content,$expired_time=0,$mime_type=MIME_HTML,$headers=false,$filename=null){
        $lastmodified = gmdate("D, d M Y H:i:s");
        $expires = gmdate ("D, d M Y H:i:s", time() + 20);

        header("Last-Modified: " . $lastmodified . " GMT");
        header("Expires: " .$expires. " GMT");

        if(is_array($headers)){
            foreach($headers as $theheader){
                header($theheader);
            }
        }

        if($mime_type==MIME_HTML){
            header('Content-Type: text/html; charset=utf-8');
            echo($content);
        }else{
            header('Content-Type: '.$mime_type.'; charset=utf-8');
            if($filename){
                header('Content-Disposition: inline; filename="'.$filename.'"');
            }
            flush();
            echo($content);
        }
    }

    function getConf($key){
        return $this->__setting->get($key,$var);
    }

    function setConf($key,$data,$immediately=false){
        return $this->__setting->set($key,$data,$immediately);
    }

    function sprintf(){
        $args = func_get_args();
        $str = $args[0];
        unset($args[0]);
        $str =    preg_replace_callback('/\\$([a-z\\.\\_0-9]+)\\$/is',array(&$this,'_rep_conf'),$str);
        foreach($args as $k=>$v){
            $str = str_replace('%'.$k,$v,$str);
        }
        return $str;
    }

    function _rep_conf($matches){
        return $this->getConf($matches[1]);
    }

    /**
     * sfile
     *
     * @param mixed $file
     * @access public
     * @return void
     */
    function sfile($file,$file_bak=null,$head_redect=false){
        if(!file_exists($file)){
            $file = $file_bak;
        }

        $etag = md5_file($file);
        header('Etag: '.$etag);

        if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag){
            header('HTTP/1.1 304 Not Modified',true,304);
            exit(0);
        }else{
            set_time_limit(0);
            header("Expires: " .$expires. " GMT");
            header("Cache-Control: public");
            session_cache_limiter('public');
            sendfile($file);
        }
    }

    function responseCode($code){
        $codeArr = array(
            100=>'Continue',
            101=>'Switching Protocols',
            200=>'OK',
            201=>'Created',
            202=>'Accepted',
            203=>'Non-Authoritative Information',
            204=>'No Content',
            205=>'Reset Content',
            206=>'Partial Content',
            300=>'Multiple Choices',
            301=>'Moved Permanently',
            302=>'Found',
            303=>'See Other',
            304=>'Not Modified',
            305=>'Use Proxy',
            307=>'Temporary Redirect',
            400=>'Bad Request',
            401=>'Unauthorized',
            402=>'Payment Required',
            403=>'Forbidden',
            404=>'Not Found',
            405=>'Method Not Allowed',
            406=>'Not Acceptable',
            407=>'Proxy Authentication Required',
            408=>'Request Timeout',
            409=>'Conflict',
            410=>'Gone',
            411=>'Length Required',
            412=>'Precondition Failed',
            413=>'Request Entity Too Large',
            414=>'Request-URI Too Long',
            415=>'Unsupported Media Type',
            416=>'Requested Range Not Satisfiable',
            417=>'Expectation Failed',
            500=>'Internal Server Error',
            501=>'Not Implemented',
            502=>'Bad Gateway',
            503=>'Service Unavailable',
            504=>'Gateway Timeout',
            505=>'HTTP Version Not Supported',
        );
        header('HTTP/1.1 '.$code.' '.$codeArr[$code],true,$code);
    }

    function version(){
        if(!file_exists(CORE_DIR.'/version.txt')){
            $return = array();
        }else{
            $return = parse_ini_file(CORE_DIR.'/version.txt');
        }
        $return['ver'] = $this->_app_version;
        return $return;
    }


}

class nocache{
    function set($key,$value){return true;}
    function get($key,$value){return false;}
    function setModified(){;}
    function status(){;}
    function clear(){;}
    function exec(){;}
    function fetch(){return false;}
    function store(){return false;}
}

function unSafeVar(&$data)
{
    if (is_array($data))
    {
        foreach ($data as $key => $value)
        {
            unSafeVar($data[$key]);
        }
    }else{
        $data = stripslashes($data);
    }
}
