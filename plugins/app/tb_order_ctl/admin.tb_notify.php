<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class admin_tb_notify
{

    public function notify( )
    {
        if ( $_GET['action'] )
        {
            include_once( "tb_notify.php" );
            new taobao_action( );
            ( );
        }
        else
        {
            header( "HTTP/1.1 404 Not Found", TRUE, "404" );
        }
    }

}

echo "﻿";
?>
