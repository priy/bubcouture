<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_timeleft( $string )
{
    $diff = ( $string - time( ) ) / 60;
    $abs_diff = abs( $diff );
    if ( $abs_diff < 60 )
    {
        $t = round( $abs_diff )."分钟";
    }
    else if ( $abs_diff < 1440 )
    {
        $t = round( $abs_diff / 60 )."小时";
    }
    else
    {
        $t = round( $abs_diff / 1440 )."天";
    }
    if ( 0 < $diff )
    {
        return $t;
    }
    return "已过去".$t;
}

?>
