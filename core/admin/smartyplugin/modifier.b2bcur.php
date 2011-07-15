<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_b2bcur( $money, $supplier_id, $currency = NULL )
{
    $system =& $GLOBALS['GLOBALS']['system'];
    $cur =& $system->loadModel( "purchase/b2bcur" );
    return $cur->getOrderDecimal( $money, $supplier_id, $currency );
}

?>
