<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_replace( $string, $search, $replace )
{
    return str_replace( $search, $replace, $string );
}

?>
