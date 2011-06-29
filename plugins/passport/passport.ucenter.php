<?PHP
    class passport_ucenter extends modelFactory{
         var $passport_name = "UCenter 1.0/1.5";
         var $passport_memo = "Ucenter1.0/1.5整合";
         var $_config = null;
         var $tmpl = "passport_ucenter.html";//加载对应的模板文件
         var $forward=0;
         var $charset;
         var $name = "ucenter";
         function setconfig($config){
            $this->_config = $config;
         }
         function verifylogin($login,$passwd){

         }
         function decode($responseData){
            
         }
         function getoptions(){
            return array(
                "ucapi"=>array('label'=>'UCenter URL：','type'=>'input','required'=>'true'),
                "uckey"=>array('label'=>'UCenter 通信密钥：','type'=>'input'),
                "ucappid"=>array('label'=>'UCenter 应用ID：','type'=>'input'),
                "ucserver"=>array('label'=>'UCenter 数据库服务器：(不带http://前缀)','type'=>'input'),
                "ucdbuser"=>array('label'=>'UCenter 数据库用户名：','type'=>'input','required'=>'true'),
                "ucdbpass"=>array('label'=>'UCenter 数据库密码：','type'=>'input'),
                "ucdbname"=>array('label'=>'UCenter 数据库名：','type'=>'input','required'=>'true'),
                "ucprefix"=>array('label'=>'UCenter 表名前缀：','type'=>'input'),
                "encoding"=>array('label'=>'UCenter系统编码：','type'=>'select','options'=>array('utf8' => '国际化编码(utf-8)','gbk' => '简体中文','big5' => '繁体中文','en' => '英文')),
                "ucdbcharset"=>array('label'=>'UCenter数据库编码：','type'=>'select','options'=>array('utf8'=>'UTF8','gbk'=>'GBK'))
            );
         }
         /*
         function createConfig($api='',$pwd=''){
             //------获取UCenter的配置信息
             $config=$this->getUcInfo($api,$pwd);
             if (strstr($config,"|")){
                list($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset, $ucapi, $ucip) = explode('|', $config);
                $cinfo = $appid;
             }
             elseif (intval($config)==-1){
                 $cinfo = '创始人密码有误！';
             }
             elseif (intval($config)==-2){
                $cinfo = 'UCenter的URL有误！';
             }
             return $cinfo;
         }
         function getUcInfo($api='',$pwd=''){
            $app_type   = 'ShopEx48';
            $app_name   = 'ShopEx48 网店';
            $app_url    = substr($this->system->base_url(),0,-1);
            $app_charset = "utf-8";
            $app_dbcharset = "utf8";
            $ucapi = !empty($api)?trim($api):'';
            $ucpwd = !empty($pwd)?trim($pwd):'';
            if ($ucapi){
                $temp=@parse_url($ucapi);
                $ucip=gethostbyname($temp['host']);
                if (ip2long($ucip)==-1||ip2long($ucip)===FALSE){
                    return -3;
                }        
            }
            $postdata="m=app&a=add&ucfounder=&ucfounderpw=".urlencode($ucpwd)."&apptype=".urlencode($app_type)."&appname=".urlencode($app_name)."&appurl=".urlencode($app_url)."&appip=&appcharset=".$app_charset.
        '&appdbcharset='.$app_dbcharset;
            $config = $this->getUcConfig($ucapi."/index.php",500,$postdata,$ucip,50);
            if (!empty($config)){
                $config=$config."|$ucapi|$ucip";
            }
            return $config;
         } 
         function getUcConfig($url,$limit=0,$post='',$ip='',$timeout=15){
            $matches=@parse_url($url);
            $host=$matches['host'];
            $path = $matches['path'] ? $matches['path'].'?'.$matches['query'].($matches['fragment'] ? '#'.$matches['fragment'] : '') : '/';
            $port = !empty($matches['port']) ? $matches['port'] : 80;
            if ($post){
                $out  = "POST $path HTTP/1.1\r\n";
                $out .=    "Accept: **\r\n";        
                $out .= "Accept-Language: zh-cn\r\n";
                $out .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
                $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
                $out .= "Host: $HOST\r\n";
                $out .= "Content-Length: ".strlen($post)."\r\n"; 
                $out .= "Connection: Close\r\n\r\n";
                $out .= $post;
            }
            $fp=@fsockopen(($ip ? $ip : $host),$port,$errorno,$errorstr,$timeout);
            if ($fp){
                stream_set_timeout($fp,$timeout);
                @fwrite($fp,$out);
                $status = stream_get_meta_data($fp);
                if (!$status['time_out']){
                    $i=0;
                    while(!feof($fp)){
                        $header=fgets($fp);
                        if(!$i)
                           $tophead=$header;
                        if (($header)&&($header == "\r\n" ||$header == "\n")){
                            break;
                        }
                        $i++;
                    } 
                    if (strstr(strtoupper($tophead),"NOT FOUND")){
                        return -2;
                    }
                    $stop=false;
                    while(!feof($fp) && !$stop){
                        $data = @fread($fp,($limit==0||$limit>892 ? 892 : $limit));
                        $return .=$data;
                        if ($limit){
                            $limit -= strlen($data);
                            $stop = $limit<=0;
                        }
                    }
                }  
                @fclose($fp);
                return $return;
            }
         } */
         function checkuser($username){
             $this->getDefineVar();
             @include_once(CORE_DIR.'/lib/uc_client/client.php');
             if (is_object($this->charset)){
                 $username = $this->charset->utf2local($username,"zh");
             }
             $ucc=uc_user_checkname($username);
             return $ucc; 
         }
         function regist_user($username,$password,$email){
              $this->getDefineVar();
              @include_once(CORE_DIR.'/lib/uc_client/client.php');
              if (is_object($this->charset)){
                 $username = $this->charset->utf2local($username,"zh");
                 $password = $this->charset->utf2local($password,"zh");
              }
              $urg=uc_user_register($username,$password,$email);
              return $urg;
         }
         function regist($userId,$rurl){
            return true;
         }
         function logout($userId,$url){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            $logoutinfo=uc_user_synlogout($userId);
            return $logoutinfo;
         }
         function check_login($username,$password){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            if (is_object($this->charset))
                $username = $this->charset->utf2local($username,"zh");
            $logres=uc_user_login($username,$password);
            return $logres;
         }
         function login($userId,$url){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            $loginfo = uc_user_synlogin($userId);
            return $loginfo;
         }
         function get_user($username){
            $this->getDefineVar();
            @include_once(CORE_DIR.'/lib/uc_client/client.php');
            if (is_object($this->charset))
                $username = $this->charset->utf2local($username,"zh");
            $userinfo=uc_get_user($username);
            return $userinfo;
         }
         function getDefineVar(){
            $pobj = $this->system->loadmodel('member/passport');
            $data = $pobj->getOptions('ucenter');              
            define('UC_CONNECT', 'mysql');                               
            define('UC_DBHOST', $data['ucserver']['value']);
            define('UC_DBUSER', $data['ucdbuser']['value']);
            define('UC_DBPW',   $data['ucdbpass']['value']);
            define('UC_DBNAME', $data['ucdbname']['value']);
            define('UC_DBCHARSET', $data['ucdbcharset']['value']);
            define('UC_DBTABLEPRE', '`'.$data['ucdbname']['value']."`.".$data['ucprefix']['value']);
            define('UC_DBCONNECT', 0);
            define('UC_KEY', $data['uckey']['value']);
            define('UC_API', $data['ucapi']['value']);
            define('UC_CHARSET', $data['encoding']['value']);
            $tmp=parse_url($data['ucapi']['value']); 
            if (preg_match('/([0-9]{1,3}\.){3}/',$tmp['host'])){
                define('UC_IP', $tmp['host']);
            }
            else{
                define('UC_IP', gethostbyname($tmp['host']));
            }
            define('UC_APPID', $data['ucappid']['value']);//$data['ucserver']['value']);
            define('UC_PPP', $data['ucserver']['value']);
            if (strtoupper(UC_DBCHARSET)<>"UTF8"){
                $this->charset=$this->system->loadModel('utility/charset'); 
            }
         }
         function implodeUserToUC(){
             $this->getDefineVar();
             @include_once(CORE_DIR.'/lib/uc_client/client.php');
             $mem = $this->system->loadModel('member/member');
             $this->charset = $this->system->loadModel('utility/charset');
             $data=$mem->getUserForBBS();
             if (is_array($data)){
                 if (UC_DBCHARSET=="gbk"){
                     foreach($data as $key => $val){
                        $data[$key]['uname'] =  $this->charset->utf2local($val['uname'],"zh");
                     }
                 }
                 uc_user_allmerge($data,$uidGroup);
                 if(is_array($uidGroup)){
                    $account=$this->system->loadModel('member/account');
                    $account->UpdateForeignId($uidGroup);
                 }
             }
         }
         function edituser($uname,$oldpass,$newpass,$email,$ignore=0){
             $this->getDefineVar();
             @include_once(CORE_DIR.'/lib/uc_client/client.php');
             if (is_object($this->charset))
                $uname = $this->charset->utf2local($uname,"zh");
             return uc_user_edit($uname,$oldpass,$newpass,'',$ignore);
         }
         function checkuserregister($uname,$passwd,$email,&$uid,&$message){
             $isuser=$this->checkuser($uname);
             if ($isuser=='-3'){
                $message=__('您开启了UCenter整合，且UCenter中存在该用户名');
             }
             else{
                $uid = $this->regist_user($uname,$passwd,$email);
                switch ($uid){
                    case -1:
                        $message=__('无效的用户名');
                        break;
                    case -2:
                        $message=__('用户名不允许注册');
                        break;
                    case -3:
                        $message=__('已经存在一个相同的用户名');
                        break;
                    case -4:
                        $message=__('无效的email地址');
                        break;
                    case -5:
                        $message=__('邮件不允许');
                        break;
                    case -6:
                        $message=__('该邮件地址已经存在');
                        break;
                    default:
                        break;
                }
             }
             return true;
         }
         function checkusername($uname='',$passwd='',$forward=''){
            $logres = $this->check_login($uname,$passwd);
            //list($uid, $uname, $passwd, $email, $repeat) = $logres;
            return $logres;
         }
    }
?>