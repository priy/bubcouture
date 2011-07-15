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
class domaincontrol extends base
{

    public function domaincontrol( )
    {
        $this->base( );
        $this->load( "domain" );
    }

    public function onls( )
    {
        return $_ENV['domain']->get_list( 1, 9999, 9999 );
    }

}

?>
