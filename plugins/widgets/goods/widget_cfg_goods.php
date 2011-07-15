<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_cfg_goods( $system )
{
    $o = $system->loadModel( "goods/products" );
    return $o->orderBy( );
}

?>
