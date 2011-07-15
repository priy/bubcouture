<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_im( &$setting, &$system )
{
    if ( $setting['align'] == "1" )
    {
        $setting['plug'] = "<br>";
    }
    return $setting['im'];
}

?>
