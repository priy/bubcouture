<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_paddingleft( $vol, $empty, $fill )
{
    return str_repeat( $fill, $empty ).$vol;
}

?>
