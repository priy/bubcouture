<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_cfg_virtualcat( $system )
{
    $objCat = $system->loadModel( "goods/virtualcat" );
    return $objCat->getMapTree( 0, "" );
}

?>
