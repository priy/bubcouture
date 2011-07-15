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
class appcontrol extends base
{

    public function appcontrol( )
    {
        $this->base( );
        $this->load( "app" );
    }

    public function onls( )
    {
        $applist = $_ENV['app']->get_apps( "appid, type, name, url, tagtemplates" );
        $applist2 = array( );
        foreach ( $applist as $key => $app )
        {
            $app['tagtemplates'] = uc_unserialize( $app['tagtemplates'] );
            $applist2[$app['appid']] = $app;
        }
        return $applist2;
    }

}

?>
