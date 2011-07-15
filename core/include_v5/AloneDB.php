<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class AloneDB
{

    public $_rw_lnk = null;
    public $_ro_lnk = null;
    public $prefix = "sdb_";

    public function AloneDB( &$system )
    {
        $this->system = $system;
    }

    public function insert( $table, $data )
    {
        $rs = $this->exec( "select * from ".$table." where 0=1" );
        if ( $this->exec( $this->getInsertSQL( $rs ) ) )
        {
            return $this->lastinsertid( );
        }
        else
        {
            return false;
        }
    }

    public function replace( $table, $data, $whereFilter )
    {
    }

    public function update( $table, $data, $whereFilter )
    {
    }

    public function &exec( $sql, $skipModifiedMark = false, $db_lnk = null )
    {
        if ( !$skipModifiedMark && preg_match( "/(?:(delete\\s+from)|(insert\\s+into)|(update))\\s+([]0-9a-z_:\"`.@[-]*)/is", $sql, $match ) )
        {
            $table = strtoupper( trim( str_replace( "`", "", str_replace( "\"", "", str_replace( "'", "", $match[4] ) ) ) ) );
            $this->_modified( $table );
            if ( $table == "SDB_GOODS" )
            {
                $whereClause = $this->_whereClause( $sql );
                if ( 0 < strlen( $whereClause ) )
                {
                    $modifi_sql = "UPDATE `sdb_goods` SET last_modify=".time( );
                    $modifi_sql .= " WHERE ".$whereClause;
                    $this->exec( $modifi_sql, 1 );
                }
            }
        }
        if ( !is_resource( $db_lnk ) )
        {
            if ( $this->_rw_lnk )
            {
                $db_lnk =& $this->_rw_lnk;
            }
            else
            {
                $db_lnk =& $this->_rw_conn( );
            }
        }
        if ( $this->prefix != "sdb_" )
        {
            $sql = preg_replace( "/([`\\s\\(,])(sdb_)([a-z\\_]+)([`\\s\\.]{0,1})/is", "\${1}".$this->prefix."\\3\\4", $sql );
        }
        if ( $rs = mysql_query( $sql, $db_lnk ) )
        {
            $db_result = array(
                "rs" => $rs,
                "sql" => $sql
            );
            return $db_result;
        }
        else
        {
            trigger_error( $sql.":".mysql_error( $db_lnk ), E_USER_WARNING );
            return false;
        }
    }

    public function &select( $sql )
    {
        if ( $this->_rw_lnk )
        {
            $db_lnk =& $this->_rw_lnk;
        }
        else if ( $this->_ro_lnk )
        {
            $db_lnk =& $this->_ro_lnk;
        }
        else
        {
            $db_lnk =& $this->_ro_conn( );
        }
        if ( 0 < $this->system->_co_depth && preg_match( "/FROM\\s+([]0-9a-z_:\"`.@[-]*)/is", $sql, $tableName ) )
        {
            $this->system->checkExpries( $tableName[1] );
        }
        $rs = $this->exec( $sql, true, $db_lnk );
        $data = array( );
        while ( $row = mysql_fetch_assoc( $rs['rs'] ) )
        {
            $data[] = $row;
        }
        mysql_free_result( $rs['rs'] );
        return $data;
    }

    public function &selectrow( $sql )
    {
        $row =& $this->selectlimit( $sql, 1, 0 );
        return $row[0];
    }

    public function &selectlimit( $sql, $limit = 10, $offset = 0 )
    {
        if ( 0 <= $offset || 0 <= $limit )
        {
            $offset = 0 <= $offset ? $offset."," : "";
            $limit = 0 <= $limit ? $limit : "18446744073709551615";
            $sql .= " LIMIT ".$offset." ".$limit;
        }
        $data =& $this->select( $sql );
        return $data;
    }

    public function &_ro_conn( )
    {
        if ( defined( "DB_SLAVE_HOST" ) )
        {
            $this->_ro_lnk =& $this->_connect( DB_SLAVE_HOST, DB_SLAVE_USER, DB_SLAVE_PASSWORD, DB_SLAVE_NAME );
        }
        else if ( $this->_rw_lnk )
        {
            $this->_ro_lnk =& $this->_rw_lnk;
        }
        else
        {
            $this->_ro_lnk =& $this->_rw_conn( );
        }
        return $this->_ro_lnk;
    }

    public function &getRows( $rs, $row = 10 )
    {
        $i = 0;
        $data = array( );
        while ( ( $row = mysql_fetch_assoc( $rs['rs'] ) ) && $i++ < $row )
        {
            $data[] = $row;
        }
        return $data;
    }

    public function &_rw_conn( )
    {
        $this->_rw_lnk =& $this->_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        return $this->_rw_lnk;
    }

    public function &_connect( $host, $user, $passwd, $dbname )
    {
        if ( constant( "DB_PCONNECT" ) )
        {
            $lnk = mysql_pconnect( $host, $user, $passwd );
        }
        else
        {
            $lnk = mysql_connect( $host, $user, $passwd );
        }
        if ( !$lnk )
        {
            trigger_error( __( "无法连接数据库:" ).mysql_error( ).E_USER_ERROR );
        }
        mysql_select_db( $dbname, $lnk );
        if ( preg_match( "/[0-9\\.]+/is", mysql_get_server_info( $lnk ), $match ) )
        {
            $dbver = $match[0];
            if ( version_compare( $dbver, "4.1.1", "<" ) )
            {
                define( "DB_OLDVERSION", 1 );
                $this->dbver = 3;
            }
            else
            {
                if ( constant( "DB_CHARSET" ) )
                {
                    mysql_query( "SET NAMES '".DB_CHARSET."'", $lnk );
                }
                if ( !version_compare( $dbver, "6", "<" ) )
                {
                    $this->dbver = 6;
                }
            }
        }
        return $lnk;
    }

    public function count( $sql )
    {
        $sql = preg_replace( array( "/(.*\\s)LIMIT .*/i", "/^select\\s+(.+?)\\bfrom\\b/is" ), array( "\\1", "select count(*) as c from" ), trim( $sql ) );
        $row = $this->select( $sql );
        return intval( $row[0]['c'] );
    }

    public function GetUpdateSQL( &$rs, $data, $InsertIfNoResult = false, $insertData = null, $ignore = false )
    {
        if ( !function_exists( "db_get_update_sql" ) )
        {
            require( "core/db.tools.php" );
        }
        return db_get_update_sql( $this, $rs, $data, $InsertIfNoResult, $insertData, $ignore );
    }

    public function GetInsertSQL( &$rs, $data, $autoup = false )
    {
        if ( !function_exists( "db_get_insert_sql" ) )
        {
            require( CORE_INCLUDE_DIR."/core/db.tools.php" );
        }
        return db_get_insert_sql( $this, $rs, $data, $autoup );
    }

    public function _modified( $table )
    {
        if ( substr( $table, -11 ) != "op_sessions" && $this->system->cache && substr( strtolower( trim( $table ) ), -8 ) != "cachemgr" )
        {
            $this->system->cache->setModified( $table );
        }
        if ( $table == "SDB_GOODS" || $table == "SDB_GTASK" )
        {
            register_shutdown_function_once( array( "trading/goods", "update_gtask" ) );
        }
    }

    public function _whereClause( $queryString )
    {
        preg_match( "/\\sWHERE\\s(.*)/is", $queryString, $whereClause );
        $discard = false;
        if ( $whereClause )
        {
            if ( preg_match( "/\\s(ORDER\\s.*)/is", $whereClause[1], $discard ) )
            {
            }
            else
            {
                if ( preg_match( "/\\s(LIMIT\\s.*)/is", $whereClause[1], $discard ) )
                {
                }
                else
                {
                    preg_match( "/\\s(FOR UPDATE.*)/is", $whereClause[1], $discard );
                }
            }
        }
        else
        {
            $whereClause = array(
                false,
                false
            );
        }
        if ( $discard )
        {
            $whereClause[1] = substr( $whereClause[1], 0, strlen( $whereClause[1] ) - strlen( $discard[1] ) );
        }
        return $whereClause[1];
    }

    public function quote( $string )
    {
        if ( !( $result = mysql_real_escape_string( $string ) ) )
        {
            $result = $string;
        }
        $string = addslashes( $string );
        return "'".$string."'";
    }

    public function lastinsertid( )
    {
        $sql = "SELECT LAST_INSERT_ID() AS lastinsertid";
        $rs = $this->exec( $sql, true, $this->_rw_lnk );
        $row = mysql_fetch_assoc( $rs['rs'] );
        mysql_free_result( $rs['rs'] );
        return $row['lastinsertid'];
    }

    public function &query( $sql, $skipModifiedMark = false, $db_lnk = null )
    {
        $rs = $this->exec( $sql, $skipModifiedMark, $db_lnk );
        return $rs;
    }

    public function affect_row( )
    {
        return mysql_affected_rows( );
    }

    public function errorinfo( )
    {
        return mysql_error( );
    }

    public function splitsql( $sql )
    {
        if ( !function_exists( "database_split_sql" ) )
        {
            require( CORE_INCLUDE_DIR."/core/db.split_sql.php" );
        }
        return database_split_sql( $sql, $this );
    }

    public function selectPager( $queryString, $pageStart = null, $pageLimit = null )
    {
        $_data['total'] = $this->count( $queryString );
        $_data['page'] = ceil( $_data['total'] / $pageLimit );
        if ( $pageLimit == null )
        {
            $_data =& $this->select( $queryString );
        }
        else
        {
            $_data['data'] = $this->selectLimit( $queryString, $pageLimit, $pageStart * $pageLimit );
        }
        return $_data;
    }

}

?>
