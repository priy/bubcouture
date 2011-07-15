<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

define( "BASE_DIR", "../" );
define( "CORE_DIR", "../core" );
define( "RANDOM_HOME", FALSE );
error_reporting( E_ALL & ~( E_STRICT | E_NOTICE | E_WARNING ) );
require( "install.core.php" );
( );
$system = new installCore( );
?>
