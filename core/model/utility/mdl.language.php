<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_language
{

    var $worker;

    function mdl_language( )
    {
    }

    function init( $lang_name )
    {
    }

    function translate( $string )
    {
        return $string;
    }

    function getlangs( )
    {
        return array(
            array(
                "val" => "zh",
                "label" => __( "中文（简体）" ),
                "cur" => "CNY"
            ),
            array(
                "val" => "big5",
                "label" => __( "中文（繁體）" )
            ),
            array( "val" => "en", "label" => "English (US)" )
        );
    }

    function po2dat( $po_file, $dat_file )
    {
        $hash = new simplehash( );
        if ( file_exists( $dat_file ) )
        {
            unlink( $dat_file );
        }
        $hash->workat( $dat_file );
        $handle = fopen( $po_file, "r" );
        if ( $handle )
        {
            while ( !feof( $handle ) )
            {
                $line = fgets( $handle, 4096 );
                if ( preg_match( "/(msgid|msgstr)\\s+\"(.*)\"/i", $line, $match ) )
                {
                    if ( $match[1] == "msgid" )
                    {
                        $now_word = $match[2];
                    }
                    else if ( $match[1] == "msgstr" )
                    {
                        $hash->set( md5( $now_word ), $match[2] );
                    }
                }
            }
            fclose( $handle );
        }
        $hash->close( );
        return true;
    }

}

require( "simplehash.php" );
?>
