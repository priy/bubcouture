<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_amount( $money, $currency = null, $basicFormat = false, $chgval = true, $is_order = false )
{
    $system =& $system;
    $cur =& $system->loadModel( "system/cur" );
    return $cur->amount( $money, $currency, $basicFormat, $chgval, $is_order );
}

?>
