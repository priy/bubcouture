<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_cfg_treelist( $system )
{
    $o = $system->loadModel( "content/sitemap" );
    return $o->getList( );
}

?>
