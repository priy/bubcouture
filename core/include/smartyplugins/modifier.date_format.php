<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_date_format( $string, $format = "%b %e, %Y", $default_date = null )
{
    if ( $string != "" )
    {
        return strftime( $format, tpl_make_timestamp( $string ) );
    }
    if ( isset( $default_date ) && $default_date != "" )
    {
        return strftime( $format, tpl_make_timestamp( $default_date ) );
    }
}

if ( !function_exists( "tpl_make_timestamp" ) )
{
    function tpl_make_timestamp( $string )
    {
        if ( empty( $string ) )
        {
            $string = "now";
        }
        $time = strtotime( $string );
        if ( is_numeric( $time ) && $time != -1 )
        {
            return $time;
        }
        if ( is_numeric( $string ) && strlen( $string ) == 14 )
        {
            $time = mktime( substr( $string, 8, 2 ), substr( $string, 10, 2 ), substr( $string, 12, 2 ), substr( $string, 4, 2 ), substr( $string, 6, 2 ), substr( $string, 0, 4 ) );
            return $time;
        }
        $time = ( integer )$string;
        if ( 0 < $time )
        {
            return $time;
        }
        return time( );
    }
}
?>
