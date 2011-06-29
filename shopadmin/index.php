<?php
define('RUN_IN','BACK_END');
ob_start();
if(!include('../config/config.php')){
    header('Location: ../install/');
    exit();
}
ob_end_clean();

define('CORE_INCLUDE_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/include_v5':'/include'));

require(CORE_INCLUDE_DIR.'/adminCore.php');
new adminCore();
?>