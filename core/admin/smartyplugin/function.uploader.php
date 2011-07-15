<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_uploader( $params, &$smarty )
{
    echo $smarty->_fetch_compile_include( "system/tools/uploader.html", $params );
}

?>
