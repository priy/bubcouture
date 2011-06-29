<?php
define('IN_ASSIS_SERVICE', true);
ob_start();
if(@require('../config/config.php')){
    ob_end_clean();
    
    define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));
            
    require(CORE_DIR.'/assistant/api.php');
}
?>
