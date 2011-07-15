<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_brand( $setting, &$system )
{
    $oGoods = $system->loadModel( "goods/brand" );
    return $oGoods->getAll( );
}

?>
