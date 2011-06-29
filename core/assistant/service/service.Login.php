<?php

class LoginService extends BaseService
{
    function init(&$server)
    {
        parent::init($server);

        $server->wsdl->addComplexType(
            'Version',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'shopver' => array('name'=>'shopver','type'=>'xsd:string'),
                'assisver' => array('name'=>'assisver','type'=>'xsd:string')
            )
        );

        $server->register('GetVersion',
            array(),
            array('return' => 'tns:Version'),
            'urn:shopexapi',
            'urn:shopexapi#GetVersion',
            'rpc',
            'encoded',
            '');
        $server->register('Login',
            array('user' => 'xsd:string', 'pass' => 'xsd:string', 'loginas' => 'xsd:int'),
            array('return' => 'xsd:boolean'),
            'urn:shopexapi',
            'urn:shopexapi#Login',
            'rpc',
            'encoded',
            '');
         $server->register('GetRedirectToken',
            array('user' => 'xsd:string', 'pass' => 'xsd:string', 'loginas' => 'xsd:int'),
            array('return' => 'xsd:string'),
            'urn:shopexapi',
            'urn:shopexapi#GetRedirectToken',
            'rpc',
            'encoded',
            '');
    }
}

function GetVersion()
{
    $sys = &$GLOBALS['system'];
    $ver = $sys->version();
    $vstr = '';
    foreach ($ver as $k => $v) $vstr .= " $k:$v";

    LogUtils::log_str('GetVersion Begin');
    $ver = array('shopver' => trim($vstr), 'assisver' => '3.0.3');
    LogUtils::log_str('GetVersion Return:');
    LogUtils::log_obj($ver);
    return $ver;
}

function Login($user, $pass, $loginas)
{
    LogUtils::clear_log();
    LogUtils::log_str('Login Begin');
    LogUtils::log_obj(func_get_args());

    $logret = false;
    $sys = &$GLOBALS['system'];
    $db = $sys->database();

    $sql = "select userpass,super,status from sdb_operators where disabled='false' and username=".$db->quote($user);
    LogUtils::log_str($sql);
    $row = $db->selectrow($sql);
    LogUtils::log_obj($row);
    if ($row && strtolower($row['userpass']) == strtolower($pass))
    {
        $logret = true;
        if (isset($row['super']) && $row['super'] != $loginas)
            $logret = false;        
        if (isset($row['status']) && $row['status'] != 1)
            $logret = false;        
    }
    LogUtils::log_str('Login Return:'. ($logret ? 'true' : 'false'));

    return $logret;
}

function GetRedirectToken($user,$pass,$loginas)
{
     $token = '';
     if (Login($user,$pass,$loginas))
     {
             $token_file = AS_TMP_DIR . 'astoken.php';
             if (file_exists($token_file)){
                  include($token_file);
             }
             if (!isset($redirect_tokes) || !is_array($redirect_tokes))
                 $redirect_tokes = array();
             $now = time();
             $str = '<?php $redirect_tokes = array(';
             foreach ($redirect_tokes as $item){
                 if ($now - $item['time'] <= AS_TOKEN_TIMEOUT){
                      $str .= "\r\narray('token'=>'{$item['token']}','user'=>'{$item['user']}','time'=>{$item['time']}),";
                 }
             }
             $token = md5($user.$pass.time());
             $str .= "\r\narray('token'=>'".$token."','user'=>'{$user}','time'=>".time().")\r\n); ?>";
             file_put_contents($token_file, $str);
     }

     LogUtils::log_str('GetRedirectToken Return:'.$token);

     return $token;
}
?>