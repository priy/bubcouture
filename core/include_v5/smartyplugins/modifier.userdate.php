<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_userdate( $timestamp )
{
    if ( !$site_dateformat )
    {
        $system =& $system;
        if ( !$GLOBALS['GLOBALS']['site_dateformat'] = $system->getConf( "site.dateFormat" ) )
        {
            $GLOBALS['GLOBALS']['site_dateformat'] = "Y-m-d";
        }
    }
    return mydate( $site_dateformat, $timestamp );
}

?>
