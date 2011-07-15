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

    public $worker = NULL;

    public function mdl_language( )
    {
        return;
        $this->worker = new nocache( );
    }

    public function init( $lang_name )
    {
        return;
        $this->lang_name = $lang_name;
        $this->is_base = $lang_name == BASE_LANG;
        $this->worker = new simplehash( );
        $this->worker->workat( CORE_DIR."/lang/".$lang_name.".dat" );
    }

    public function translate( $string )
    {
        return $string;
        if ( $this->worker->get( $string, $return ) )
        {
            return $return;
        }
        else
        {
            return $string;
        }
    }

    public function getLangs( )
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

    public function po2dat( $po_file, $dat_file )
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
