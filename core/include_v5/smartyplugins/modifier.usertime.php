<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_usertime( $timestamp )
{
    if ( !$site_timeformat )
    {
        $system =& $system;
        if ( !$GLOBALS['GLOBALS']['site_timeformat'] = $system->getConf( "site.timeFormat" ) )
        {
            $GLOBALS['GLOBALS']['site_timeformat'] = "Y-m-d H:i:s";
        }
    }
    return mydate( $site_timeformat, $timestamp );
}

?>
