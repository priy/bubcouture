<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_link( $params, &$smarty )
{
    $args = isset( $params['args'] ) ? $params['args'] : array( );
    foreach ( $params as $key => $val )
    {
        if ( preg_match( "/^arg([0-9]+)\$/", $key, $matches ) )
        {
            $args[$matches[1]] = str_replace( "-", "@", $val );
        }
    }
    return $smarty->system->mkurl( $params['ctl'], $params['act'], $args, $params['extname'] ? $params['extname'] : "html" );
}

?>
