<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_links( &$setting, &$system )
{
    $link = $o = $system->loadModel( "content/frendlink" );
    $results = $link->getList( NULL, array( "disabled" => "false" ), 0, $setting['limit'] ? $setting['limit'] : 10, $c );
    return $results;
}

?>
