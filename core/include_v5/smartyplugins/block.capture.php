<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_block_capture( $params, $content, &$tpl )
{
    if ( null !== $content )
    {
        $tpl->_env_vars['capture'][isset( $params['name'] ) ? $params['name'] : "default"] =& $content;
        if ( isset( $params['assign'] ) )
        {
            $tpl->_vars[$params['assign']] =& $content;
        }
    }
    return null;
}

?>
