<?php
define('IN_ASSIS_SERVICE', true);
ob_start();
if(@require('../config/config.php')){
    ob_end_clean();
    require(CORE_DIR.'/assistant/api.php');
}
?>