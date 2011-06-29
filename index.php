<?php
define('PERPAGE',10); //rewiew: 检查是否去掉
define('RUN_IN','FRONT_END');
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING)); 
ob_start();
if(file_exists('config/config.php')){
    require('config/config.php');
    ob_end_clean();

    define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));

    if(isset($_GET['cron']) && $_GET['cron']){
        require(CORE_INCLUDE_DIR.'/crontab.php');
        $_GET['action'] = $_GET['cron'];
        new crontab();
    }else{
        require(CORE_INCLUDE_DIR.'/shopCore.php');
        new shopCore();
    }
    
}else header('Location: install/');
?>