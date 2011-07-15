<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( CORE_DIR."/func_ext.php" );
require_once( "cachemgr.php" );
class cachedir extends cachemgr
{

    public $name = "独立文件缓存";
    public $desc = "一个个的小文件";

    public function cachedir( )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        $this->db =& $system->database( );
        $this->totalBytes = 15728640;
        $sql = "create table if not exists sdb_cachedir\n(\n   cache_file                     varchar(32)                    not null,\n   cache_size                     mediumint unsigned             not null,\n   last_update                    int unsigned                   not null,\n   primary key (cache_file)\n)type = MyISAM";
        $cachedir_sql = $this->db->exec( $sql, 1, 1 );
        if ( TRUE === $cachedir_sql )
        {
            $row = $this->db->selectrow( "select sum(cache_size) as size from sdb_cachedir" );
            $this->curBytes = $row['size'];
            if ( $this->totalBytes < $row['size'] )
            {
                $this->_free( 10, $row['size'] - $this->totalBytes );
            }
        }
        parent::cachemgr( );
    }

    public function _free( $step, $to_free )
    {
        $free = $i = 0;
        while ( $free < $to_free && $i++ < 10 )
        {
            $deleted = array( );
            foreach ( $this->db->select( "SELECT cache_file,cache_size FROM sdb_cachedir order by last_update limit 0,".$step ) as $cache_item )
            {
                if ( $size = $this->_remove_file( $cache_item ) )
                {
                    $free += $size;
                    $deleted[] = $cache_item['cache_file'];
                }
            }
            $this->db->exec( "delete from sdb_cachedir where cache_file in (\"".implode( "\",\"", $deleted )."\")", 1, 1 );
        }
    }

    public function _path( $key, $mkdir = FALSE )
    {
        $dir = HOME_DIR."/cache/".$key[0].$key[1];
        if ( $mkdir && !mkdir_p( $dir ) )
        {
            return FALSE;
        }
        return $dir."/".substr( $key, 2 );
    }

    public function store( $key, &$value )
    {
        $path = $this->_path( $key, TRUE );
        $data = serialize( $value );
        $this->db->exec( "replace into sdb_cachedir (cache_file,cache_size,last_update)VALUES(\"".$key."\",".strlen( $data ).",".time( ).")", 1, 1 );
        return $path && file_put_contents( $path, $data );
    }

    public function fetch( $key, &$data )
    {
        $file = $this->_path( $key );
        if ( file_exists( $file ) )
        {
            if ( filemtime( $file ) < filemtime( HOME_DIR."/cache/cache.stat" ) )
            {
                return FALSE;
            }
            $data = unserialize( file_get_contents( $file ) );
            $this->db->exec( "update sdb_cachedir set last_update=".time( )." where cache_file=\"".$key."\"", 1, 1 );
            return $data !== FALSE;
        }
        else
        {
            return FALSE;
        }
    }

    public function _remove_file( $cache_item )
    {
        $f = $this->_path( $cache_item['cache_file'] );
        if ( !file_exists( $f ) || unlink( $f ) )
        {
            return $cache_item['cache_size'];
        }
        else
        {
            error_log( "Can't delete ".$f, 3, HOME_DIR."/log/cachedir.log"."\n" );
            return FALSE;
        }
    }

    public function clear( )
    {
        set_time_limit( 2 );
        $now = time( );
        $rows = $this->db->select( "SELECT cache_file,cache_size FROM sdb_cachedir where last_update<".$now." limit 0,10" );
        while ( 0 < count( $rows ) )
        {
            $deleted = array( );
            foreach ( $rows as $cache_item )
            {
                if ( $this->_remove_file( $cache_item ) )
                {
                    $deleted[] = $cache_item['cache_file'];
                }
            }
            $this->db->exec( "delete from sdb_cachedir where cache_file in (\"".implode( "\",\"", $deleted )."\")", 1, 1 );
            $rows = $this->db->select( "SELECT cache_file,cache_size FROM sdb_cachedir where last_update<".$now." limit 0,10" );
        }
        return TRUE;
    }

    public function status( &$curBytes, &$totalBytes )
    {
        $curBytes = $this->curBytes;
        $totalBytes = $this->totalBytes;
        $row = $this->db->selectrow( "select count(*) as count from sdb_cachedir" );
        return array(
            array(
                "name" => "总缓存对象数量",
                "value" => $row['count']
            )
        );
    }

}

?>
