<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_strip( $string, $replace = " " )
{
    return preg_replace( "!\\s+!", $replace, $string );
}

?>
