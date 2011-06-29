<?php
define('IN_INSTALLER',true);
require(CORE_DIR.'/kernel.php');
require(CORE_DIR.'/func_ext.php');
class installCore extends kernel{

    function __construct(){

        define('CORE_INCLUDE_DIR',CORE_DIR.
            ((file_exists(CORE_DIR.'/include_v5') && version_compare(PHP_VERSION,'5.0','>='))?'/include_v5':'/include'));

        if(isset( $_POST['dbname'] , $_POST['dbuser'] , $_POST['dbhost'] , $_POST['data'])){
            error_reporting(E_ALL ^ E_NOTICE);
            $this->install_mode='service';
            if(file_exists('../config/install.lock')){
                $this->service_install_res('false','Access denied by install.lock');
            }
            $_POST['uname']=$_POST['dbuser'];
            $_POST['pwd']=$_POST['dbpass'];
            $_POST['prefix']=$_POST['prefix']?$_POST['prefix']:'sdb_';
            $_POST['use_demo']=($_POST['data']=='true')?true:false;
            $_POST['stimezone']=$_POST['stimezone']?$_POST['stimezone']:8;
            parent::kernel();
            $this->service_install_mode();
        }

        if(file_exists('../config/install.lock') && (!isset($_GET['step']) || $_GET['step']!='complete')){
            $this->responseCode('403');
            echo 'Access denied by install.lock';
            exit();
        }else{
            parent::kernel();
            $this->model_dir =  'model';
            $this->checkPerm();
            $this->run();
        }
    }


    function service_install_res($res='false',$rsp=null){
        if($this->install_mode=='service'){
            $return['res']=$res;
            $return['rsp']=$rsp;
            //$this->_header();
            echo json_encode($return);
            exit();
        }else{
            echo $rsp;
        }
    }

    function service_install_mode(){
        if(!($link = @mysql_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']))){
            $this->service_install_res('false','mysql connect error');
        }

        $this->step_setdb();
        if($this->install_mode=='service'){
            $this->loadConfig();
        }
        $this->do_install(false);
        $this->service_install_res('true','install succ');
        exit();
    }

    function run(){

        $_GET['step'] = isset($_GET['step'])?$_GET['step']:1;
        $method = 'step_'.$_GET['step'];
        if(method_exists($this,$method)){
            call_user_func(array(&$this,$method));
        }else{
            header("HTTP/1.0 404 Not Found",true,404);
            echo '<h1>404 Not Found</h1><hr />';
        }
    }

    function loadConfig(){
        ob_start();
        if(include('../config/config.php')){
            $this->model_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=') && file_exists(CORE_DIR.'/model_v5')?'model_v5':'model');
            if(defined('DB_NAME') && defined('DB_USER') && defined('DB_PASSWORD') && defined('DB_HOST')){
                ob_end_clean();
                return true;
            }
        }
        ob_end_clean();
        return false;
    }

    function installCore(){
        $this->__construct();
    }

    function step_checkdb(){
        if(!($link = mysql_connect($_POST['dbhost'], $_POST['uname'], $_POST['pwd']))){
            header("Cache-Control: no-cache,no-store, must-revalidate",true,503);
            exit();
        }
        $database = array();
        if($result = mysql_query('show databases')){
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                if($row['Database']!='mysql' && $row['Database']!='information_schema')
                    $database[] = $row['Database'];
            }
        }
        mysql_free_result($result);
        mysql_close($link);
        if(count($database)>0){
            $html = '<select id="db_name" name="dbname">';
            foreach($database as $db){
                $html .= '<option value="'.htmlspecialchars($db).'">'.$db.'</option>';
            }
            echo $html .= '</select>';
        }
    }

    function step_1(){
        $this->output('step_1.html');
    }

    function step_2(){
        if(is_file(BASE_DIR.'/config/config.php')){
            require_once(BASE_DIR.'/config/config.php');
            $this->pagedata['database_username'] = DB_USER;
            $this->pagedata['database_password'] = DB_PASSWORD;
            $this->pagedata['database_name'] = DB_NAME;
            $this->pagedata['host'] = DB_HOST;
            $this->pagedata['database_prefix'] = DB_PREFIX;
        }
        $this->pagedata['timezone'] = timezone_list();
        $this->pagedata['default_timezone'] = 8;

        $this->output('step_2.html');
    }

    function step_setdb(){
        define('DB_NAME', $_POST['dbname']);  // The name of the database
        define('DB_USER', $_POST['uname']);  // Your MySQL username
        define('DB_PASSWORD', $_POST['pwd']); // ...and password
        define('DB_HOST', $_POST['dbhost']);
        define('DB_PREFIX', $_POST['prefix']);
        define('SERVER_TIMEZONE', $_POST['stimezone']);

        if(php_sapi_name()=='isapi'){
            $constant['WITHOUT_FLOCK'] = true;
        }

        if(!defined('STORE_KEY') || strlen(trim(STORE_KEY))==0){
            $constant['STORE_KEY'] = md5(implode(',',microtime()).implode(',',$_SERVER));
        }

        $this->db = &$this->database();

        if(!$this->db->_rw_conn(true)){
            if($this->install_mode=='service'){
                $this->service_install_res('false','db connect error,db has not exists');
            }
            $this->output('wrong_db.html');
        }else{
            if(file_exists(BASE_DIR.'/config/config.php')){
                $sample = file_get_contents(BASE_DIR.'/config/config.php');
            }else{
                $sample = file_get_contents(BASE_DIR.'/config/config.sample.php');
            }
            $s = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST','STORE_KEY','DB_PREFIX','SERVER_TIMEZONE');

            foreach($s as $v){
                if(defined($v) || isset($constant[$v])){
                    $arr['#(define\\s*\\(\\s*[\'"]'.$v.'[\'"]\\s*,\\s*)[^;]+;#i'] = '\\1\''.str_replace('\'','\\\'',isset($constant[$v])?$constant[$v]:constant($v)).'\');';
                }else{
                    echo 'error: undefined constant: '.$v;
                    exit();
                }
            }
            if(RANDOM_HOME) $arr['#(define\\(\\s*[\'"]HOME_DIR[\'"]\\s*,\\s*)[^;]+;#i'] = '\\1dirname(__FILE__).\'/home'.'_'.substr(md5(microtime()),3,6).'\');';

            if(file_put_contents(BASE_DIR.'/config/config.php',preg_replace(array_keys($arr),array_values($arr),$sample))){
                //header('Location: index.php?step=ready');
            }else{
                $this->service_install_res('false','error: configure file write error! ');
                exit();
            }
        }
    }

    function step_ready(){
        $this->step_setdb();
        if($this->loadConfig()) {

            include('svinfo.php');
            $tester = new mdl_serverinfo();
            $this->pagedata['svinfo'] = $tester->run();
            $this->pagedata['status'] = 0;
            $this->db = &$this->database();
            if(!$this->db->_rw_conn()){
                echo 'error: can\'t connect MySql Server! '.mysql_error();
                exit();
            }
            if(!defined('DB_PREFIX')){
                define('DB_PREFIX','sdb_');
            }
            $this->pagedata['db_pre'] = DB_PREFIX;
            $this->set_timezone(SERVER_TIMEZONE);
            $tz = timezone_list();

            $timelist = array();
            foreach($tz as $z=>$t){
                $timelist[$z] = date('H:i',time()+($z-SERVER_TIMEZONE)*3600).'-'.$t;
            }

            $this->pagedata['defaultHour'] = SERVER_TIMEZONE;
            $this->pagedata['timelist'] = &$timelist;
            $this->pagedata['stimezone'] = $tz[SERVER_TIMEZONE];

            $this->output('step_ready.html');
        }else header('Location: index.php?step=1');

    }

    function step_cluster(){
        if($this->loadConfig()) {
            $this->output('step_cluster.html');
        }else header('Location: index.php?step=1');
    }
    function do_install($full_install=true){
        if(!$full_install){
            $this->install_basic_db();
        }
        //$sqlContent = file_get_contents('dbscripts/mysql.sql');
        $sqlContent.= file_get_contents('dbscripts/init.sql');
        if($_POST['use_demo']){
            $sqlContent .= file_get_contents('dbscripts/demo.sql');
        }
        $this->db->exec('SET NAMES utf8');
        //$this->install_basic_db();
        foreach($this->db->splitSql($sqlContent) as $sql){
            if(!$this->db->exec($sql,true)){
                $this->service_install_res('false','<h3>Sql Error</h3><textarea style="width:500px;height:300px">'.htmlspecialchars($sql).'</textarea><br />');
                //echo '<h3>Sql Error</h3><textarea style="width:500px;height:300px">'.htmlspecialchars($sql).'</textarea><br />';
                echo $this->db->errorInfo();
                exit();
            }
        }

        if($this->install_mode=='service'){
            $password = $_POST['password'];
        }else{
            $password = md5($_POST['password']);
        }
        if($this->install_mode=='usual'){
            $uname = $_POST['uname']?$_POST['uname']:'admin';
        }else{
            $uname = 'admin';
        }
        $rs = $this->db->exec('select * from sdb_operators where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'username'=>$uname,
            'userpass'=>$password,
            'status'=>1,
            'super'=>1
        ));
        if(!$this->db->exec($sql,true)){
            $this->service_install_res('false','error: cannot create administrator account.');
            exit();
        }
        if(RANDOM_HOME){
            if(!is_dir(HOME_DIR)){
                if(!mkdir(HOME_DIR,0777)){
                    $this->service_install_res('false','error: can\' mk home dir');
                    exit();
                }
            }

            if(
                !mkdir_p(HOME_DIR.'/cache/data') ||
                !mkdir_p(HOME_DIR.'/cache/front_tmpl') ||
                !mkdir_p(HOME_DIR.'/cache/admin_tmpl') ||
                !mkdir_p(HOME_DIR.'/download') ||
                !mkdir_p(HOME_DIR.'/fonts') ||
                !mkdir_p(HOME_DIR.'/logs') ||
                !mkdir_p(HOME_DIR.'/template') ||
                !mkdir_p(HOME_DIR.'/upload')
            ){
                $this->service_install_res('false','error: can\' mk home dir');
                exit();
            }
        }

        $this->setConf('system.timezone.default',$_POST['timezone']);
        $this->init();

        $this->pagedata['password'] = $_POST['password'];
        $this->pagedata['uname'] = $uname;

        file_put_contents(BASE_DIR.'config/install.lock','If you want to reinstall system, delete this file!');
        $this->cache->clear();
        $data['article'] = $this->db->selectrow("SELECT max(article_id) as max FROM sdb_articles");
        $data['goods'] = $this->db->selectrow("SELECT max(goods_id) as max FROM sdb_goods");
        $data['goods_cat'] = $this->db->selectrow("SELECT max(cat_id) as max FROM sdb_goods_cat");
        $data['goods_memo'] = $this->db->selectrow("SELECT max(goods_id) as max FROM sdb_goods_memo");
        $data['goods_type'] = $this->db->selectrow("SELECT max(type_id) as max FROM sdb_goods_type");
        $data['package_product'] = $this->db->selectrow("SELECT max(product_id) as max FROM sdb_package_product");
        $data['products'] = $this->db->selectrow("SELECT max(product_id) as max FROM sdb_products");
        $data['spec_values'] = $this->db->selectrow("SELECT max(spec_value_id) as max FROM sdb_spec_values");
        $data['specification'] = $this->db->selectrow("SELECT max(spec_id) as max FROM sdb_specification");
        $data['type_brand'] = $this->db->selectrow("SELECT max(type_id) as max FROM sdb_type_brand");
        $data['brand'] = $this->db->selectrow("SELECT max(brand_id) as max FROM sdb_brand");
        $data['goods_virtual_cat'] = $this->db->selectrow("SELECT max(virtual_cat_id) as max FROM sdb_goods_virtual_cat");
        $data['gimages'] = $this->db->selectrow("SELECT max(gimage_id) as max FROM sdb_gimages");
        $data['status'] = $this->db->selectrow("SELECT max(last_update) as max FROM sdb_status");

        $this->setConf('system.last_backup',time());
        $this->setConf('system.test.database',serialize($data));

    }
    function step_full_install(){

        set_time_limit(0);

        if($this->loadConfig()) {
            $this->db = &$this->database();

            if(!$this->db->_rw_conn(true)){
                $this->output('wrong_db.html');
                exit;
            }else{
                $this->do_install();

                $this->output('step_install.html');
                exit();
            }
        }
        header('Location: index.php?step=1');
    }

    function step_install_mysql_db(){
        $this->loadConfig();
        if(constant('DB_HOST')){
            $lnk = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
            if(!$lnk){
                $dbver = 0;
            }else{
                if(preg_match('/[0-9\.]+/is',mysql_get_server_info($lnk),$match)){
                    $dbver = $match[0];
                }
            }
        }else{
            $dbver = 0;
        }
        header("mysql:".$dbver);
        if($this->install_basic_db()){
            echo '数据库安装成功|index.php?step=install_init_db|系统内置数据';
        }else{
            echo '数据库安装失败!|fail';
        }
    }

    function step_install_init_db(){
       $this->loadConfig();
        $this->get_exec_sql('dbscripts/init.sql','安装系统内置数据失败!|fail');
        if($_POST['use_demo']=="true"){
            echo '安装系统内置数据成功!|index.php?step=install_demo_db|DEMO数据';
        }else{
            echo '安装系统内置数据成功!|index.php?step=install_plugins|安装插件';
        }
    }

    function db_connect(){
        if(!$this->loadConfig()){
            return false;
        }
        if(!$this->db){
              $this->db = &$this->database();
        }
        if(!defined('DB_PREFIX')){
             define('DB_PREFIX','sdb_');
        }
        if(!$this->db->_rw_conn(true)){
           return false;
        }
        return true;
    }

    function get_exec_sql($file,$errormsg='安装出错'){
       if($this->db_connect()) {
            $sqlContent= file_get_contents($file);
            foreach($this->db->splitSql($sqlContent) as $sql){
                        if(!$this->db->exec($sql,true)){
                            echo $errormsg.'|fail';
                            exit();
                        }
            }
       }else{
             echo $errormsg.'|fail';
             exit();
       }
    }

    function step_install_demo_db(){
        $this->loadConfig();
        $this->get_exec_sql('dbscripts/demo.sql','安装系统DEMO数据失败!|fail');
        echo '安装系统DEMO数据成功!|index.php?step=install_plugins|安装插件';
    }

    function step_install_plugins(){
        $this->loadConfig();
        $addons = &$this->loadModel('system/addons');
        $addons->refresh();
        $appmgr = &$this->loadModel('system/appmgr');
        $apps = $appmgr->getList('no_compare');
        foreach($apps as $k =>$v){
            if($v['plugin_ident']){
                if($v['plugin_ident']!="shopex_stat"){
                    $appmgr->install($v['plugin_ident']);
                }else{
                    if($_POST['install_stat']){
                        if($appmgr->load("shopex_stat")){
                            $appmgr->install($v['plugin_ident']);
                            $appmgr->enable($v['plugin_ident']);
                        }
                    }
                }
            }
            if(substr($item['plugin_ident'],0,4) == 'pay_'){
                $appmgr->enable($v['plugin_ident']);
            }
        }

        echo '插件安装成功!|index.php?step=install_success|初始化数据';
    }

    function step_install_success(){
        if(!$this->db_connect()){
            echo '安装初始化数据失败!|fail';
            exit();
        }
        $password = $_POST['password'];
        $uname = $_POST['uname']?$_POST['uname']:'admin';
        $rs = $this->db->exec('select * from sdb_operators where 0=1');
        $sql = $this->db->getInsertSQL($rs,array(
            'username'=>$uname,
            'userpass'=>md5($password),
            'status'=>1,
            'super'=>1
        ));
        if(!$this->db->exec($sql,true)){
            $this->service_install_res('false','error: cannot create administrator account.');
            exit();
        }
        if(RANDOM_HOME){
            if(!is_dir(HOME_DIR)){
                if(!mkdir(HOME_DIR,0777)){
                    $this->service_install_res('false','error: can\' mk home dir');
                    exit();
                }
            }

            if(
                !mkdir_p(HOME_DIR.'/cache/data') ||
                !mkdir_p(HOME_DIR.'/cache/front_tmpl') ||
                !mkdir_p(HOME_DIR.'/cache/admin_tmpl') ||
                !mkdir_p(HOME_DIR.'/download') ||
                !mkdir_p(HOME_DIR.'/fonts') ||
                !mkdir_p(HOME_DIR.'/logs') ||
                !mkdir_p(HOME_DIR.'/template') ||
                !mkdir_p(HOME_DIR.'/upload')
            ){
                echo 'error: can\' mk home dir';
                exit();
            }
        }

        $this->setConf('system.timezone.default',$_POST['timezone']);
        $this->setConf('system.last_backup',time());
        $this->init();
        $this->cache->clear();
        //file_put_contents(BASE_DIR.'config/install.lock','If you want to reinstall system, delete this file!');
        //$this->fetch('step_install.html',array('goodsItem'=>$goodsItem, 'giftsItem'=>$giftsItem));

        echo '初始化数据成功!|index.php?step=install_fake_html|设置伪静态';
    }

     function step_install_fake_html(){
        if(!$this->db_connect()){
            echo '安装初始化数据失败!|fail';
            exit();
        }
        $o = &$this->loadModel('utility/tools');
        if($o->test_fake_html(true,$msg)){
            echo '设置伪静态成功!|index.php?step=install_done|安装完成';
        }else{
            echo '设置伪静态失败'.$msg.'!|index.php?step=install_done|安装完成';
        }
     }
    function step_install_done(){
       file_put_contents(BASE_DIR.'config/install.lock','If you want to reinstall system, delete this file!');
       echo 'success';

    }
    function step_complete(){
        $this->pagedata['link_url'] = dirname(dirname($_SERVER['PHP_SELF'])).'/shopadmin/';
        $this->pagedata['password'] = $_POST['password'];
        $this->pagedata['uname'] = $_POST['uname']?$_POST['uname']:'admin';
        $this->output('step_install.html');
    }

    function install_basic_db(){
        $o = &$this->loadModel('utility/schemas');
        $aDb = $o->get_system_schemas();

        $aDb = array_merge( array( 'settings'=>$aDb['settings'], 'plugins'=>$aDb['plugins']), $aDb );
        foreach( $aDb as $name=>$schema){
            $db = &$this->database();
            $db->exec('drop table if exists '.$db->prefix.$name);
            $db->exec($o->get_insert_sql(CORE_DIR.'/schemas/'.$schema));
        }
        return true;
    }

    function init(){
        $ver = $this->version();
        $db = &$this->database();
        $db->exec('drop table if exists sdb_dbver');
        $ver['dbver'] = $ver['dbver']?$ver['dbver']:'dbver';
        $db->exec("create table sdb_dbver(`{$ver['dbver']}` varchar(255)) type = MYISAM");

        $o = &$this->loadModel('content/sitemap');
        $o->update();

        $o = &$this->loadModel('system/template');
        $o->initTheme('purple');
        $result=$o->setDefault('purple');
        $usedTpl = $o->getDefault();
        $o->getList();

        $o = &$this->loadModel('system/local');
        $rs = $o->use_package(defined('DEFAULT_LOCAL')?DEFAULT_LOCAL:'mainland');
    }

    function step_reinstall(){
        $this->step_install();
    }

    function output($file){
        header('Content-type: text/html;charset=utf-8');
        $this->pagedata['PAGE'] = $file;
        $this->pagedata['version'] = $this->version();
        $smarty = &$this->loadModel('system/frontend');
        $smarty->ctl = &$this;
        $smarty->template_dir = 'view/';
        $smarty->compile_dir = 'templates_c/';
        $smarty->left_delimiter='<{';
        $smarty->right_delimiter='}>';
        $smarty->_vars = &$this->pagedata;
        $smarty->display('main.html');
        exit();
    }

    function checkPerm(){
        if(!is_writable('templates_c')){
            echo '<strong>Error:</strong> <i>install/templates_c</i> is not writable!';
            exit();
        }
        $arr = array(
            array('path'=>'install/templates_c','is_writable'=>true)
        );

        chdir('../');
        $this->_checkDirPerm('config',$arr);
        $this->_checkDirPerm('themes',$arr);
        $this->_checkDirPerm('images',$arr);
        $this->_checkDirPerm('home',$arr,true);
        chdir('install');

        foreach($arr as $item){
            if(!$item['is_writable']){
                $this->pagedata['dirs'] = $arr;
                $this->output('permview.html');
                exit();
            }
        }
    }

    function _checkDirPerm($dir,&$arr,$r=false){

        $arr[] = array(
            'path'=>$dir,
            'is_writable'=>is_writable($dir),
        );

        if($r){
            if($handle = opendir($dir)){
                while(false != ($file = readdir($handle))){
                    if($file{0}!='.' && is_dir($dir.'/'.$file)){
                        $this->_checkDirPerm($dir.'/'.$file,$arr,$r);
                    }
                }
                closedir($handle);
            }
        }
    }

    function base_url(){
        return dirname(dirname($_SERVER["PHP_SELF"]));
    }

}
?>