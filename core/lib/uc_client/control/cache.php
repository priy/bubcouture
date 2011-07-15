<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
class cachecontrol extends base
{

    public function cachecontrol( )
    {
        $this->base( );
    }

    public function onupdate( $arr )
    {
        $this->load( "cache" );
        $_ENV['cache']->updatedata( );
    }

}

?>
