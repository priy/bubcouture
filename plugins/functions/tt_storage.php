<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class tt_storage
{

    public function tt_storage( )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        if ( constant( "IMG_MEMCACHE" ) && constant( "IMG_MEMCACHE_PORT" ) )
        {
            ( );
            $this->memcache = new Memcache( );
            $this->memcache->addServer( IMG_MEMCACHE, IMG_MEMCACHE_PORT );
            $this->memcache->pconnect( );
        }
    }

    public function save( $file, &$url, $type, $addons )
    {
        $id = $this->_get_ident( $file, $type, $addons, $url, $path );
        if ( $path && $this->memcache->set( $path, file_get_contents( $file ) ) )
        {
            return $id;
        }
        else
        {
            return FALSE;
        }
    }

    public function replace( $file, $id )
    {
        if ( $this->memcache->set( $id, file_get_contents( $file ) ) )
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
        if ( $path && $this->memcache->set( $path, file_get_contents( $file ) ) )
        {
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
        $dir = ( $type ? $type."/" : "" ).date( "Ymd" )."/";
        $uri = $dir.substr( md5( ( $addons ? $addons : $file ).microtime( ) ), 0, 16 ).ext_name( basename( $addons ? $addons : $file ) );
        $path = $this->_ident( $uri );
        $url = "http://".S_NAME.$path;
        return $uri;
    }

    public function remove( $id )
    {
        if ( $id )
        {
            return $this->memcache->delete( $this->_ident( $id ), 10 );
        }
        else
        {
            return TRUE;
        }
    }

    public function _ident( $id )
    {
        return "/".str_pad( HOST_ID, 10, "0", STR_PAD_LEFT )."/".$id;
    }

    public function getFile( $id )
    {
        $tmpfile = tempnam( );
        if ( $id && file_put_contents( $tmpfile, $this->memcache->get( $id ) ) )
        {
            return $tmpfile;
        }
        else
        {
            return TRUE;
        }
    }

}

?>
