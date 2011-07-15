<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_tag( $params, &$smarty )
{
    echo $smarty->_fetch_compile_include( "finder/finder-tag.html", $params );
}

?>
