<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_goodsmenu( $params, &$smarty )
{
    if ( $runtime['member_lv'] < 0 )
    {
        $params['login'] = "nologin";
    }
    echo $smarty->_fetch_compile_include( "shop:product/menu.html", $params );
}

?>
