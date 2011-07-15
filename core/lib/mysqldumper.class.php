<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class Mysqldumper
{

    public $_host = NULL;
    public $_dbuser = NULL;
    public $_dbpassword = NULL;
    public $_dbname = NULL;
    public $_isDroptables = NULL;
    public $tableid = NULL;
    public $startid = NULL;

    public function Mysqldumper( $host = "localhost", $dbuser = "", $dbpassword = "", $dbname = "" )
    {
        $this->setHost( $host );
        $this->setDBuser( $dbuser );
        $this->setDBpassword( $dbpassword );
        $this->setDBname( $dbname );
        $this->setDroptables( FALSE );
    }

    public function setHost( $host )
    {
        $this->_host = $host;
    }

    public function getHost( )
    {
        return $this->_host;
    }

    public function setDBname( $dbname )
    {
        $this->_dbname = $dbname;
    }

    public function getDBname( )
    {
        return $this->_dbname;
    }

    public function getDBuser( )
    {
        return $this->_dbuser;
    }

    public function setDBpassword( $dbpassword )
    {
        $this->_dbpassword = $dbpassword;
    }

    public function getDBpassword( )
    {
        return $this->_dbpassword;
    }

    public function setDBuser( $dbuser )
    {
        $this->_dbuser = $dbuser;
    }

    public function setDroptables( $state )
    {
        $this->_isDroptables = $state;
    }

    public function isDroptables( )
    {
        return $this->_isDroptables;
    }

    public function multiDump( $filename, $fileid, $sizelimit, $backdir, $ignoreList = NULL )
    {
        $ret = TRUE;
        $lf = "\r\n";
        $lencount = 0;
        $bakfile = ( $backdir."/multibak_".$filename."_".( $fileid + 1 ) ).".sql";
        if ( $ignoreList )
        {
            $ignoreList = array_flip( $ignoreList );
        }
        $fw = @fopen( $bakfile, "wb" );
        if ( !$fw )
        {
            exit( "备份目录{$backdir}不可写" );
        }
        $resource = mysql_connect( $this->getHost( ), $this->getDBuser( ), $this->getDBpassword( ), TRUE );
        mysql_select_db( $this->getDbname( ), $resource );
        if ( !constant( "DB_OLDVERSION" ) )
        {
            mysql_query( "SET NAMES '".MYSQL_CHARSET_NAME."'", $resource );
        }
        $result = mysql_query( "SHOW TABLES" );
        $tables = $this->result2Array( 0, $result );
        foreach ( $tables as $tblval )
        {
            if ( substr( $tblval, 0, strlen( DB_PREFIX ) ) == DB_PREFIX )
            {
                $tablearr[] = $tblval;
            }
        }
        fwrite( $fw, "#".$lf );
        fwrite( $fw, ( "# SHOPEX SQL MultiVolumn Dump ID:".( $fileid + 1 ) ).$lf );
        fwrite( $fw, "# Version ".$GLOBALS['SHOPEX_THIS_VERSION'].$lf );
        fwrite( $fw, "# ".$lf );
        fwrite( $fw, "# Host: ".$this->getHost( ).$lf );
        fwrite( $fw, "# Server version: ".mysql_get_server_info( ).$lf );
        fwrite( $fw, "# PHP Version: ".phpversion( ).$lf );
        fwrite( $fw, "# Database : `".$this->getDBname( )."`".$lf );
        fwrite( $fw, "#" );
        $i = 0;
        $j = $this->tableid;
        for ( ; $j < count( $tablearr ); ++$j )
        {
            $tblval = $tablearr[$j];
            $table_prefix = constant( "DB_PREFIX" );
            $subname = substr( $tblval, strlen( $table_prefix ) );
            $written_tbname = "{shopexdump_table_prefix}".$subname;
            if ( $this->startid == -1 )
            {
                fwrite( $fw, $lf.$lf."# --------------------------------------------------------".$lf.$lf );
                $lencount += strlen( $lf.$lf."# --------------------------------------------------------".$lf.$lf );
                fwrite( $fw, "#".$lf."# Table structure for table `{$tblval}`".$lf );
                $lencount += strlen( "#".$lf."# Table structure for table `{$tblval}`".$lf );
                fwrite( $fw, "#".$lf.$lf );
                $lencount += strlen( "#".$lf."# Table structure for table `{$tblval}`".$lf );
                mysql_query( "ALTER TABLE `{$tblval}` comment ''" );
                if ( $this->isDroptables( ) )
                {
                    fwrite( $fw, "DROP TABLE IF EXISTS `{$written_tbname}`;".$lf );
                    $lencount += strlen( "DROP TABLE IF EXISTS `{$written_tbname}`;".$lf );
                }
                $result = mysql_query( "SHOW CREATE TABLE `{$tblval}`" );
                $createtable = $this->result2Array( 1, $result );
                $tmp_value = str_replace( "\n", "", $this->formatcreate( $createtable[0] ) );
                $pos = strpos( $tmp_value, $tblval );
                $tmp_value = substr( $tmp_value, 0, $pos ).$written_tbname.substr( $tmp_value, $pos + strlen( $tblval ) );
                fwrite( $fw, $tmp_value.$lf.$lf );
                $lencount += strlen( $tmp_value.$lf.$lf );
                $this->startid = 0;
            }
            if ( $sizelimit * 1000 < $lencount )
            {
                $this->tableid = $j;
                $this->startid = 0;
                $ret = FALSE;
                break;
            }
            if ( isset( $ignoreList["sdb_".$subname] ) )
            {
                $this->startid = -1;
                continue;
            }
            fwrite( $fw, "#".$lf."# Dumping data for table `{$tblval}`".$lf."#".$lf );
            $lencount += strlen( "#".$lf."# Dumping data for table `{$tblval}`".$lf."#".$lf );
            $result = mysql_query( "SELECT * FROM `{$tblval}`" );
            if ( !mysql_data_seek( $result, $this->startid ) )
            {
                $this->startid = -1;
                continue;
            }
            while ( $row = mysql_fetch_object( $result ) )
            {
                $insertdump = $lf;
                $insertdump .= "INSERT INTO `{$written_tbname}` VALUES (";
                $arr = $this->object2Array( $row );
                foreach ( $arr as $key => $value )
                {
                    if ( !is_null( $value ) )
                    {
                        $value = $this->utftrim( mysql_escape_string( $value ) );
                        $insertdump .= "'{$value}',";
                    }
                    else
                    {
                        $insertdump .= "NULL,";
                    }
                }
                $insertline = rtrim( $insertdump, "," ).");";
                fwrite( $fw, $insertline );
                $lencount += strlen( $insertline );
                $this->startid++;
                if ( $sizelimit * 1000 < $lencount )
                {
                    $ret = FALSE;
                    $this->tableid = $j;
                    break;
                }
            }
            $this->startid = -1;
            ++$i;
        }
        mysql_close( $resource );
        fclose( $fw );
        chmod( $bakfile, 438 );
        return $ret;
    }

    public function object2Array( $obj )
    {
        $array = NULL;
        if ( is_object( $obj ) )
        {
            $array = array( );
            foreach ( get_object_vars( $obj ) as $key => $value )
            {
                if ( is_object( $value ) )
                {
                    $array[$key] = $this->object2Array( $value );
                }
                else
                {
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }

    public function loadObjectList( $key = "", $resource )
    {
        $array = array( );
        while ( $row = mysql_fetch_object( $resource ) )
        {
            if ( $key )
            {
                $array[$row->$key] = $row;
            }
            else
            {
                $array[] = $row;
            }
        }
        mysql_free_result( $resource );
        return $array;
    }

    public function result2Array( $numinarray = 0, $resource )
    {
        $array = array( );
        while ( $row = mysql_fetch_row( $resource ) )
        {
            $array[] = $row[$numinarray];
        }
        mysql_free_result( $resource );
        return $array;
    }

    public function formatcreate( $str )
    {
        $body = substr( $str, 0, strrpos( $str, ")" ) + 1 );
        $tail = strtolower( substr( $str, strrpos( $str, ")" ) - strlen( $str ) ) );
        if ( strstr( $tail, "memory" ) || strstr( $tail, "heap" ) )
        {
            return $body." TYPE=HEAP{shopexdump_create_specification};";
        }
        else
        {
            return $body." TYPE=MyISAM{shopexdump_create_specification};";
        }
    }

    public function utftrim( $str )
    {
        $found = FALSE;
        $i = 0;
        for ( ; $i < 4 && $i < strlen( $str ); ++$i )
        {
            $ord = ord( substr( $str, strlen( $str ) - $i - 1, 1 ) );
            if ( 192 < $ord )
            {
                $found = TRUE;
                break;
            }
            if ( $i == 0 && $ord < 128 )
            {
                break;
            }
        }
        if ( $found )
        {
            if ( 240 < $ord )
            {
                if ( $i == 3 )
                {
                    return $str;
                }
                else
                {
                    return substr( $str, 0, strlen( $str ) - $i - 1 );
                }
            }
            else if ( 224 < $ord )
            {
                if ( 2 <= $i )
                {
                    return $str;
                }
                else
                {
                    return substr( $str, 0, strlen( $str ) - $i - 1 );
                }
            }
            else if ( 1 <= $i )
            {
                return $str;
            }
            else
            {
                return substr( $str, 0, strlen( $str ) - $i - 1 );
            }
        }
        else
        {
            return $str;
        }
    }

}

define( "MYSQL_CHARSET_NAME", "utf8" );
?>
