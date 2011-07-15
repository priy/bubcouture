<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_block_area( $params, $content, &$ctl )
{
    if ( NULL !== $content )
    {
        $ctl->_update_areas[$params['inject']] .= $content;
        return "";
    }
}

?>
