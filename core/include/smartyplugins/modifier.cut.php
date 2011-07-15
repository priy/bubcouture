<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_cut( $string, $length = 80, $etc = "...", $break_words = false, $middle = false )
{
    if ( $length == 0 )
    {
        return "";
    }
    if ( isset( $string[$length + 1] ) )
    {
        $length -= min( $length, strlen( $etc ) );
        if ( !$break_words || !$middle )
        {
            $string = preg_replace( "/\\s+?(\\S+)?\$/", "", utftrim( substr( $string, 0, $length + 1 ) ) );
        }
        if ( !$middle )
        {
            return utftrim( substr( $string, 0, $length ) ).$etc;
        }
        return utftrim( substr( $string, 0, $length / 2 ) ).$etc.utftrim( substr( $string, 0 - $length / 2 ) );
    }
    return $string;
}

function utftrim( $str )
{
    $found = false;
    $i = 0;
    for ( ; $i < 4 && $i < strlen( $str ); ++$i )
    {
        $ord = ord( substr( $str, strlen( $str ) - $i - 1, 1 ) );
        if ( !( 192 < $ord ) )
        {
            continue;
        }
        $found = true;
        break;
    }
    if ( $found )
    {
        if ( 240 < $ord )
        {
            if ( $i == 3 )
            {
                return $str;
            }
            return substr( $str, 0, strlen( $str ) - $i - 1 );
        }
        if ( 224 < $ord )
        {
            if ( $i == 2 )
            {
                return $str;
            }
            return substr( $str, 0, strlen( $str ) - $i - 1 );
        }
        if ( $i == 1 )
        {
            return $str;
        }
        return substr( $str, 0, strlen( $str ) - $i - 1 );
    }
    return $str;
}

?>
