<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !class_exists( "http_base" ) )
{
    require( CORE_INCLUDE_DIR."/http_base.php" );
}
if ( !class_exists( "mdl_http_client" ) )
{
    require( dirname( __FILE__ )."/mdl.http_client.php" );
}
class mdl_http_client extends mdl_http_client
{

}

?>
