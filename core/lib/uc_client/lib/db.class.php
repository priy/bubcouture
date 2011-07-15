<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class db
{

    public $querynum = 0;
    public $link = NULL;
    public $histories = NULL;
    public $time = NULL;
    public $tablepre = NULL;

    public function connect( $dbhost, $dbuser, $dbpw, $dbname = "", $dbcharset = "", $pconnect = 0, $tablepre = "", $time = 0 )
    {
        $this->time = $time;
        $this->tablepre = $tablepre;
        if ( $pconnect )
        {
            if ( !( $this->link = mysql_pconnect( $dbhost, $dbuser, $dbpw ) ) )
            {
                $this->halt( "Can not connect to foreign DB server" );
            }
        }
        else if ( !( $this->link = mysql_connect( $dbhost, $dbuser, $dbpw, 1 ) ) )
        {
            $this->halt( "Can not connect to foreign DB server" );
        }
        if ( "4.1" < $this->version( ) )
        {
            if ( $dbcharset )
            {
                mysql_query( "SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->link );
            }
            if ( "5.0.1" < $this->version( ) )
            {
                mysql_query( "SET sql_mode=''", $this->link );
            }
        }
        if ( $dbname )
        {
            mysql_select_db( $dbname, $this->link );
        }
    }

    public function fetch_array( $query, $result_type = MYSQL_ASSOC )
    {
        return mysql_fetch_array( $query, $result_type );
    }

    public function result_first( $sql )
    {
        $query = $this->query( $sql );
        return $this->result( $query, 0 );
    }

    public function fetch_first( $sql )
    {
        $query = $this->query( $sql );
        return $this->fetch_array( $query );
    }

    public function fetch_all( $sql )
    {
        $arr = array( );
        $query = $this->query( $sql );
        while ( $data = $this->fetch_array( $query ) )
        {
            $arr[] = $data;
        }
        return $arr;
    }

    public function cache_gc( )
    {
        $this->query( "DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<{$this->time}" );
    }

    public function query( $sql, $type = "", $cachetime = FALSE )
    {
        $func = $type == "UNBUFFERED" && @function_exists( "mysql_unbuffered_query" ) ? "mysql_unbuffered_query" : "mysql_query";
        if ( !( $query = $func( $sql, $this->link ) ) && $type != "SILENT" )
        {
            $this->halt( "MySQL Query Error", $sql );
        }
        $this->querynum++;
        $this->histories[] = $sql;
        return $query;
    }

    public function affected_rows( )
    {
        return mysql_affected_rows( $this->link );
    }

    public function error( )
    {
        return $this->link ? mysql_error( $this->link ) : mysql_error( );
    }

    public function errno( )
    {
        return intval( $this->link ? mysql_errno( $this->link ) : mysql_errno( ) );
    }

    public function result( $query, $row )
    {
        $query = @mysql_result( $query, $row );
        return $query;
    }

    public function num_rows( $query )
    {
        $query = mysql_num_rows( $query );
        return $query;
    }

    public function num_fields( $query )
    {
        return mysql_num_fields( $query );
    }

    public function free_result( $query )
    {
        return mysql_free_result( $query );
    }

    public function insert_id( )
    {
        return 0 <= ( $id = mysql_insert_id( $this->link ) ) ? $id : $this->result( $this->query( "SELECT last_insert_id()" ), 0 );
    }

    public function fetch_row( $query )
    {
        $query = mysql_fetch_row( $query );
        return $query;
    }

    public function fetch_fields( $query )
    {
        return mysql_fetch_field( $query );
    }

    public function version( )
    {
        return mysql_get_server_info( $this->link );
    }

    public function close( )
    {
        return mysql_close( $this->link );
    }

    public function halt( $message = "", $sql = "" )
    {
        exit( $message."<br /><br />".$sql."<br /> ".mysql_error( ) );
    }

}

?>
