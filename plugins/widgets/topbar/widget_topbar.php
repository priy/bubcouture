<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_topbar( &$setting, &$system )
{
    $o = $system->loadModel( "system/cur" );
    $appmgr = $system->loadModel( "system/appmgr" );
    $login_plugin = $appmgr->getloginplug( );
    foreach ( $login_plugin as $key => $value )
    {
        $object = $appmgr->instance_loginplug( $value );
        if ( method_exists( $object, "getWidgetsHtml" ) )
        {
            $data['login_content'][] = $object->getWidgetsHtml( );
        }
    }
    if ( $appmgr->openid_loglist( ) )
    {
        $data['open_id_open'] = TRUE;
    }
    $data['cur'] = json_encode( $o->curAll( ) );
    return $data;
}

?>
