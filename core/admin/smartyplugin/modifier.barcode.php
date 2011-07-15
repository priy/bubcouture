<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_barcode( $data )
{
    $system =& $GLOBALS['GLOBALS']['system'];
    $bcode =& $system->loadModel( "utility/barcode" );
    return $bcode->get( $data );
}

?>
