<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

define( "IN_ASSIS_SERVICE", TRUE );
ob_start( );
if ( require( "../config/config.php" ) )
{
    ob_end_clean( );
    require( CORE_DIR."/assistant/api.php" );
}
?>
