<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_styleset( $style )
{
    switch ( $style )
    {
    case 1 :
        return "font-weight: bold;";
    case 2 :
        return "font-style: italic;";
    case 3 :
        return "text-decoration: line-through;";
    }
}

?>
