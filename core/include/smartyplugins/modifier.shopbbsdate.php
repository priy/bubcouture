<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_shopbbsdate( $timestamp )
{
    return mydate( "y-m-d h:i", $timestamp );
}

?>
