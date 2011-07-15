<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_widgets( $params, &$smarty )
{
    $system =& $system;
    $widgets_mdl =& $system->loadmodel( "content/widgets" );
    $frontend =& $system->loadmodel( "system/frontend" );
    $frontend->theme = $smarty->theme;
    $solt = intval( $smarty->_wgbar[$smarty->_files[0]]++ );
    if ( substr( $smarty->_files[0], 0, 5 ) == "page:" )
    {
        $smarty->_files[0] = substr( $smarty->_files[0], 5 );
    }
    return $widgets_mdl->load( $smarty->_files[0], $solt, isset( $params['id'] ) ? $params['id'] : null );
}

?>
