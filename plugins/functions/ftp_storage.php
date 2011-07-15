<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ftp_storage
{

    public function save( $path = "", $url, $id )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        $fp = fopen( BASE_DIR.$path, "rb" );
        $id = $path;
        $url = "http://".__FTP_SERVER__."/".$path;
        $ftp_path = __FTP_DIR__."/".$path;
        $file = $ftp_path;
        $conn_id = ftp_connect( __FTP_SERVER__ );
        $login_result = ftp_login( $conn_id, __FTP_UNAME__, __FTP_PASSWD__ );
        $d = split( "/", $ftp_path );
        $ftp_path = "";
        if ( substr( __FTP_DIR__, 0, 1 ) == "/" )
        {
            $i = 1;
        }
        else
        {
            $i = 0;
        }
        for ( ; $i < count( $d ) - 1; ++$i )
        {
            $ftp_path .= "/".$d[$i];
            if ( !ftp_chdir( $conn_id, $ftp_path ) )
            {
                @ftp_chdir( $conn_id, "/" );
                if ( !ftp_mkdir( $conn_id, $ftp_path ) )
                {
                    return FALSE;
                }
            }
        }
        ftp_fput( $conn_id, $file, $fp, FTP_BINARY );
        $o = $system->loadModel( "goods/gimage" );
        foreach ( $o->defaultImages as $tag )
        {
            $ext = $o->getImageExt( BASE_DIR.$path );
            $otherimage = substr( BASE_DIR.$path, 0, strlen( BASE_DIR.$path ) - strlen( $ext ) )."_".$tag.$ext;
            if ( file_exists( $otherimage ) )
            {
                $fp = fopen( $otherimage, "rb" );
                ftp_fput( $conn_id, $ftp_path."/".basename( $otherimage ), $fp, FTP_BINARY );
                fclose( $fp );
                @unlink( $otherimage );
            }
        }
        @unlink( BASE_DIR.$path );
        ftp_close( $conn_id );
        fclose( $fp );
        return TRUE;
    }

    public function remove( $ident )
    {
        $conn_id = ftp_connect( __FTP_SERVER__ );
        $login_result = ftp_login( $conn_id, __FTP_UNAME__, __FTP_PASSWD__ );
        $ret = ftp_delete( $conn_id, __FTP_DIR__."/".$ident );
        ftp_close( $conn_id );
        return $ret;
    }

}

define( __FTP_SERVER__, "192.168.0.114" );
define( __FTP_UNAME__, "hjx" );
define( __FTP_PASSWD__, "hjxisking" );
define( __FTP_DIR__, "/shopex/" );
?>
