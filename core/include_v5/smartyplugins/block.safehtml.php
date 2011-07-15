<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_block_safehtml( $params, $content, &$template_object )
{
    if ( null !== $content )
    {
        return preg_replace( "/<(\\s*)(script|object|iframe|embed)(.*?)>/is", "&lt;\$1\$2\$3&gt;", $content );
    }
}

?>
