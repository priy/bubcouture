<?PHP
    include_once('config/config.php');
    if(!defined('CORE_INCLUDE_DIR')){
        define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
    }
    include_once(CORE_DIR.'/kernel.php');
    require_once(CORE_DIR.'/func_ext.php');
    include_once(CORE_INCLUDE_DIR.'/shopCore.php');
    class phpwindCore extends kernel{
        function clientAction(){
            $passport=$this->loadModel('member/passport');
            $obj=$passport->function_judge('ClientUserAction');
            $this->InitGP(array('action','userdb','forward','verify'));
            if ($obj){
                $clientsign=md5($GLOBALS['action'].$GLOBALS['userdb'].$GLOBALS['forward'].$obj->_config['PrivateKey']);
                if ($clientsign==$GLOBALS['verify']){
                     $obj->ClientUserAction($GLOBALS['action'],$GLOBALS['userdb'],$GLOBALS['forward']);
                }
                else{
                    echo "安全检验失败，请检查通行证设置是否正确！";
                }
            }
            else{
                echo "请查看PhpWind论坛V6.3.2整合是否开启！！";
            }
        }
        function setCookie($name,$value,$expire=false,$path=null){
            if(!$this->_cookiePath){
                $cookieLife = $this->getConf('system.cookie.life');
                $this->_cookiePath = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
                $this->_cookieLife = $cookieLife;
            }
            $this->_cookieLife = ($this->_cookieLife>0)?$this->_cookieLife:315360000;
            setCookie(COOKIE_PFIX.'['.$name.']',$value,($expire===false)?(time()+$this->_cookieLife):$expire,$this->_cookiePath);
            $_COOKIE[$name] = $value;
        } 
        function getConf($key){
            $this->checkExpries(DB_PREFIX.'SETTINGS');
            return parent::getConf($key);
        }
        function InitGP($keys,$method=null,$cv=null){
           !is_array($keys) && $keys = array($keys);
           foreach ($keys as $k) {
               if ($method!='P' && isset($_GET[$k])) {
                   $GLOBALS[$k] = $_GET[$k];
               } elseif ($method!='G' && isset($_POST[$k])) {
                   $GLOBALS[$k] = $_POST[$k];
               }
               isset($GLOBALS[$k]) && !empty($cv) && $GLOBALS[$k] = $this->value_cv($GLOBALS[$k],$cv);
           }
        }
        function value_cv($value,$cv=null){
            if (empty($cv)) {
                return $value;
            } elseif ($cv=='int') {
                return (int)$value;
            } elseif ($cv=='array') {
                return is_array($value) ? $value : '';
            }
            return $this->Char_cv($value);
        }
        function Char_cv($msg,$isurl=null){
            $msg = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','',$msg);
            $msg = str_replace(array("\0","%00","\r"),'',$msg);
            empty($isurl) && $msg = preg_replace("/&(?!(#[0-9]+|[a-z]+);)/si",'&amp;',$msg);
            $msg = str_replace(array("%3C",'<'),'&lt;',$msg);
            $msg = str_replace(array("%3E",'>'),'&gt;',$msg);
            $msg = str_replace(array('"',"'","\t",'  '),array('&quot;','&#39;','    ','&nbsp;&nbsp;'),$msg);
            return $msg;
        }
        /*
        function &_frontend(){
        } */
    }
    $pw = new phpwindCore();
    $pw->clientAction();
?>