<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function core_time_auth( &$system )
{
    if ( $_POST['api_url'] == "time_auth" )
    {
        header( "Content-type:text/html;charset=utf-8" );
        $shopex_auth =& $system->loadmodel( "service/certificate" );
        if ( $shopex_auth->check_api( ) )
        {
            echo json_encode( $shopex_auth->show_pack_data( ) );
            exit( );
        }
    }
}

?>
