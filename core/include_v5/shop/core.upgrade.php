<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function core_upgrade( &$system )
{
    if ( $_GET['_ajax'] )
    {
        $url = "index.php";
        $output = "<script>\nvar href = top.location.href;\nvar pos = href.indexOf('#') + 1;\nwindow.location.href=\"{$url}\"+(pos ? ('&return='+encodeURIComponent(href.substr(pos))) : '');\n</script>";
        echo $output;
        exit( );
    }
    $upgrade =& $system->loadModel( "system/upgrade" );
    $upgrade->exec( $_GET['act'] );
}

?>
