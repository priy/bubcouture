<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compile_modifier_gimage( $ident )
{
    list( $ident ) = explode( ",", $ident );
    return "substr(".$ident.",0,strpos({$ident},'|'))";
}

?>
