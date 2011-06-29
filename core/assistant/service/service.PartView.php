<?php

class PartViewService extends BaseService
{
    function init(&$server)
    {
        parent::init($server);

        $server->register('GetPartView',
            array('ctl' => 'xsd:string', 'act' => 'xsd:string', 'arg' => 'xsd:string'),
            array('return' => 'xsd:string'),
            'urn:shopexapi',
            'urn:shopexapi#GetPartView',
            'rpc',
            'encoded',
            '');

        $server->register('EvalModel',
            array('modelName' => 'xsd:string', 'methodName' => 'xsd:string', 'args' => 'tns:StringArray', 'args_desc' => 'tns:StringArray'),
            array('return' => 'xsd:string'),
            'urn:shopexapi',
            'urn:shopexapi#EvalModel',
            'rpc',
            'encoded',
            '');
    }
}

class partviewCore extends assisCore
{
    function run() {}

    function partviewCore(){
        require_once(CORE_INCLUDE_DIR.'/defined.php');
        require_once(CORE_INCLUDE_DIR.'/cachemgr.php');
        $this->cache = new nocache;

        require_once(CORE_INCLUDE_DIR.'/setmgr.php');
        $this->__setting = new setmgr;
        $this->set_timezone(SERVER_TIMEZONE);
    }

    function base_url(){
        if($url = $this->getConf('store.shop_url')){
            $url = substr($url,-1,1)=='/'?$url:$url.'/';
        }else{
            $curdir = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/'));
            $url = 'http://'.$_SERVER['HTTP_HOST'].substr($curdir, 0, strrpos($curdir,'/')+1);
        }
        $len = strlen($url);
        if ($len > 10 && strtolower(substr($url, $len - 10, 10)) == 'shopadmin/'){
            $url = substr($url, 0, $len - 10);
        }
        return $url;
    }

    function parseRequest($query = null){
        return array(
            'base_url'=>$this->base_url(),
            'member_lv'=>-1,
            'query'=>$query?$query:'index.html',
            'cur'=>null,
            'lang'=>null,
            'money'=>-1
        );
    }

    function make_query($ctl,$act,$args){
        $url = parent::mkUrl($ctl,$act,$args);
        return (strpos($url,'?') !== FALSE) ? substr($url, strpos($url,'?')+1) : $url;
    }

    function set_request($request){
        $this->request = $request;
        unset($this->_base_link);
    }

    function &getController($mod,$args=null){
        $pctl = parent::getController($mod,$args);
        if(!is_object($pctl)) return false;
        $pctl_name = get_class($pctl);
        $sctl_name = 'pv_'.$pctl_name;
        if (!class_exists($sctl_name))
        {
            $pvtmpl = 'default_partview.html';
            $pvtmpl_content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                <base>
                                <{header}>
                                <link rel="stylesheet" type="text/css" href="images/css.css" />
                                </head>
                                <body>
                                <{main}>
                                <script type="text/javascript" src="'.$this->base_url().(constant('GZIP_JS')?'statics/foot.jgz':'statics/foot.js').'"></script>
                                </body>
                                </html>';
            $nctl_desc = 'class '.$sctl_name.' extends '.$pctl_name.'{
                        function _get_view($theme,$ctl,$act="index"){
                            //$v = parent::_get_view($theme,$ctl,$act);
                            $pvtmpl_file = THEME_DIR.\'/\'.$theme.\'/\'.\''.$pvtmpl.'\';
                            file_put_contents($pvtmpl_file,\''.$pvtmpl_content.'\');
                            //if ($v == "default.html")
                            $v = "'.$pvtmpl.'";
                            return $v;
                        }
                        }';
            eval($nctl_desc);
        }
        return new $sctl_name($args);
    }

    function get_partview_html($request){
        $page = parent::_frontend($request);
        $html = isset($page['body']) ? $page['body'] : '';
        $baseurl = $this->base_url();
        $html = preg_replace('/<base>/i', '<base href="'.$baseurl.'">', $html);
        //$html = preg_replace('/href="\?/i', 'href="'.$this->base_url().'?', $html);
        $path = str_replace('/','\/',substr($baseurl, strpos($baseurl,'/',7)));
        $html = preg_replace('/(var\s+Shop\s*=\s*{"set":{"path":").*?"/i', '$1'.$path.'"', $html);
        return $html;
    }

    function error($code){
        $err_msg = '';
        if($code==404){
            $err_msg = $this->getConf('errorpage.p404');
        }else{
            $err_msg = $this->getConf('errorpage.p500');
        }
        $GLOBALS['as_server']->fault($code, $err_msg);
    }
}

function GetPartView($ctl,$act,$arg)
{
    LogUtils::log_str('GetPartView Begin');
    LogUtils::log_obj(func_get_args());
    parse_str($arg, $args);
    $args = array_values($args);
    $pvCore = new partviewCore();
    LogUtils::log_str('GetPartView created');
    $GLOBALS['system'] = &$pvCore;
    $query = $pvCore->make_query($ctl,$act,$args);
    LogUtils::log_str($query);
    $request = $pvCore->parseRequest($query);
    $pvCore->set_request($request);
    LogUtils::log_obj($request);
    $html = $pvCore->get_partview_html($request);

    LogUtils::log_str('GetPartView Return (string length:'.strlen($html).')');
    return $html;
}

function EvalModel($modelName, $methodName, $args, $args_desc)
{
    LogUtils::log_str('EvalModel Begin');
    LogUtils::log_obj(func_get_args());
    $sys = &$GLOBALS['system'];

    $call_args = array();
    for ($i = 0; $i < count($args); $i++){
        $desc = isset($args_desc[$i]) ? strtolower($args_desc[$i]) : 'string';
        if ($desc == 'string'){
            $call_args[] = $args[$i];
        }else if ($desc == 'int'){
            $call_args[] = intval($args[$i]);
        }else if ($desc == 'float'){
            $call_args[] = floatval($args[$i]);
        }else if ($desc == 'bool'){
            $call_args[] = strtolower($args[$i]) == 'true';
        }else if ($desc == 'array'){
            parse_str($args[$i], $arr);
            $call_args[] = $arr;
        }else if ($desc == 'array2'){
            parse_str($args[$i], $arr);
            $arr2 = array();
            foreach ($arr as $k=>$v) {
                $arr2[$k] = split(':',$v);
            }
            $call_args[] = $arr2;
        }
    }
    $result = '';
    $model = $sys->loadModel($modelName);
    if (is_object($model) && method_exists($model,$methodName)){
        LogUtils::log_str('model and method found');
        LogUtils::log_obj($call_args);
        $result = call_user_func_array(array(&$model,$methodName),$call_args);
    }
    LogUtils::log_str('EvalModel Return:');
    LogUtils::log_obj($result);
    return serialize($result);
}