<?php
define('BASE_DIR', '../');
define('CORE_DIR','../core');
define('RANDOM_HOME',false);
//define('HOME_DIR','../home/random');
//define('SHOP_DEVELOPER',true);
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING)); 
require('install.core.php');

$system = new installCore();
?>