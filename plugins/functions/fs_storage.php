<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class fs_storage
{

    public function save( $file, &$url, $type, $addons )
    {
        $id = $this->_get_ident( $file, $type, $addons, $url, $path );
        if ( $path && copy( $file, $path ) )
        {
            @unlink( $file );
            @chmod( $path, 420 );
            return $id;
        }
        else
        {
            return FALSE;
        }
    }

    public function replace( $file, $id )
    {
        $path = MEDIA_DIR.$id;
        $dir = dirname( $path );
        if ( file_exists( $path ) )
        {
            if ( !unlink( $path ) )
            {
                return FALSE;
            }
        }
        else if ( !is_dir( $dir ) && !mkdir_p( $dir ) )
        {
            return FALSE;
        }
        if ( $path && rename( $file, $path ) )
        {
            return $id;
        }
        else
        {
            return FALSE;
        }
    }

    public function save_upload( $file, &$url, $type, $addons )
    {
        $id = $this->_get_ident( $file, $type, $addons, $url, $path );
        if ( $path && move_uploaded_file( $file, $path ) )
        {
            @chmod( $path, 420 );
            return $id;
        }
        else
        {
            return FALSE;
        }
    }

    public function _get_ident( $file, $type, $addons, &$url, &$path )
    {
        $addons = implode( "-", $addons );
        $dir = "/".$type."/".date( "Ymd" )."/";
        $uri = $dir.substr( md5( ( $addons ? $addons : $file ).microtime( ) ), 0, 16 ).ext_name( basename( $addons ? $addons : $file ) );
        $path = MEDIA_DIR.$uri;
        $url = "images".$uri;
        if ( file_exists( $path ) && !unlink( $path ) )
        {
            return FALSE;
        }
        $dir = dirname( $path );
        if ( !is_dir( $dir ) && !mkdir_p( $dir ) )
        {
            return FALSE;
        }
        return $uri;
    }

    public function remove( $id )
    {
        if ( $id && file_exists( MEDIA_DIR."/".$id ) )
        {
            return unlink( MEDIA_DIR."/".$id );
        }
        else
        {
            return TRUE;
        }
    }

    public function getFile( $id )
    {
        if ( $id && file_exists( MEDIA_DIR."/".$id ) )
        {
            return MEDIA_DIR."/".$id;
        }
        else
        {
            return TRUE;
        }
    }

}

?>
