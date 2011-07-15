<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_cfg_goods_show( $system )
{
    $o = $system->loadModel( "goods/products" );
    return $o->orderBy( );
}

?>
