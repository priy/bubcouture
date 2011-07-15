<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_search( &$setting, &$system )
{
    $setting['search'] = $GLOBALS['search'];
    $data = $system->getConf( "search.show.range" );
    return $data;
}

?>
