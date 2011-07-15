<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class phpftp
{

    var $passiveMode = TRUE;
    var $lastLines = array( );
    var $lastLine = "";
    var $controlSocket = NULL;
    var $newResult = FALSE;
    var $lastResult = -1;
    var $pasvAddr = NULL;
    var $error_no = NULL;
    var $error_msg = NULL;

    function phpftp( )
    {
    }

    function connect( $host, $port = 21, $timeout = FTP_TIMEOUT )
    {
        $this->_reseterror( );
        $err_no = 0;
        $err_msg = "";
        if ( !( $this->controlSocket = @fsockopen( $host, $port, $err_no, $err_msg, $timeout ) ) )
        {
            $this->_seterror( -1, "fsockopen failed" );
        }
        if ( $err_no != 0 )
        {
            $this->seterror( $err_no, $err_msg );
        }
        if ( $this->_iserror( ) )
        {
            return false;
        }
        if ( !@socket_set_timeout( $this->controlSocket, $timeout ) )
        {
            $this->_seterror( -1, "socket_set_timeout failed" );
        }
        if ( $this->_iserror( ) )
        {
            return false;
        }
        $this->_waitforresult( );
        if ( $this->_iserror( ) )
        {
            return false;
        }
        return $this->getlastresult( ) == FTP_SERVICE_READY;
    }

    function isconnected( )
    {
        return $this->controlSocket != NULL;
    }

    function disconnect( )
    {
        if ( !$this->isconnected( ) )
        {
            return;
        }
        @fclose( $this->controlSocket );
    }

    function close( )
    {
        $this->disconnect( );
    }

    function login( $user, $pass )
    {
        $this->_reseterror( );
        $this->_printcommand( "USER ".$user );
        if ( $this->_iserror( ) )
        {
            return false;
        }
        $this->_waitforresult( );
        if ( $this->_iserror( ) )
        {
            return false;
        }
        if ( $this->getlastresult( ) == FTP_PASSWORD_NEEDED )
        {
            $this->_printcommand( "PASS ".$pass );
            if ( $this->_iserror( ) )
            {
                return FALSE;
            }
            $this->_waitforresult( );
            if ( $this->_iserror( ) )
            {
                return FALSE;
            }
        }
        $result = $this->getlastresult( ) == FTP_USER_LOGGED_IN;
        return $result;
    }

    function cdup( )
    {
        $this->_reseterror( );
        $this->_printcommand( "CDUP" );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return $lr == FTP_COMMAND_OK;
    }

    function cwd( $path )
    {
        $this->_reseterror( );
        $this->_printcommand( "CWD ".$path );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return $lr == FTP_COMMAND_OK;
    }

    function cd( $path )
    {
        return $this->cwd( $path );
    }

    function chdir( $path )
    {
        return $this->cwd( $path );
    }

    function chmod( $mode, $filename )
    {
        return $this->site( "CHMOD ".$mode." {$filename}" );
    }

    function delete( $filename )
    {
        $this->_reseterror( );
        $this->_printcommand( "DELE ".$filename );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return $lr == FTP_COMMAND_OK;
    }

    function exec( $cmd )
    {
        return $this->site( "EXEC ".$cmd );
    }

    function fget( $fp, $remote, $mode = FTP_BINARY, $resumepos = 0 )
    {
        $this->_reseterror( );
        $type = "I";
        if ( $mode == FTP_ASCII )
        {
            $type = "A";
        }
        $this->_printcommand( "TYPE ".$type );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        $result = $this->_download( "RETR ".$remote );
        if ( $result )
        {
            fwrite( $fp, $result );
        }
        return $result;
    }

    function fput( $remote, $resource, $mode = FTP_BINARY, $startpos = 0 )
    {
        $this->_reseterror( );
        $type = "I";
        if ( $mode == FTP_ASCII )
        {
            $type = "A";
        }
        $this->_printcommand( "TYPE ".$type );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        if ( 0 < $startpos )
        {
            fseek( $resource, $startpos );
        }
        $result = $this->_uploadresource( "STOR ".$remote, $resource );
        return $result;
    }

    function get_option( $option )
    {
        $this->_reseterror( );
        switch ( $option )
        {
        case "FTP_TIMEOUT_SEC" :
            return FTP_TIMEOUT;
        case "PHP_FTP_OPT_AUTOSEEK" :
            return FALSE;
        }
        seterror( -1, "Unknown option: ".$option );
        return false;
    }

    function get( $locale, $remote, $mode = FTP_BINARY, $resumepos = 0 )
    {
        if ( !( $fp = @fopen( $locale, "wb" ) ) )
        {
            return FALSE;
        }
        $result = $this->fget( $fp, $remote, $mode, $resumepos );
        @fclose( $fp );
        if ( !$result )
        {
            @unlink( $locale );
        }
        return $result;
    }

    function mdtm( $name )
    {
        $this->_reseterror( );
        $this->_printcommand( "MDTM ".$name );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        if ( $lr != FTP_FILE_STATUS )
        {
            return FALSE;
        }
        $subject = trim( substr( $this->lastLine, 4 ) );
        $lucifer = array( );
        if ( preg_match( "/([0-9][0-9][0-9][0-9])([0-9][0-9])([0-9][0-9])([0-9][0-9])([0-9][0-9])([0-9][0-9])/", $subject, $lucifer ) )
        {
            return mktime( $lucifer[4], $lucifer[5], $lucifer[6], $lucifer[2], $lucifer[3], $lucifer[1], 0 );
        }
        return FALSE;
    }

    function mkdir( $name )
    {
        $this->_reseterror( );
        $this->_printcommand( "MKD ".$name );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return $lr == FTP_COMMAND_OK;
    }

    function nb_continue( )
    {
        $this->_reseterror( );
    }

    function nb_fget( )
    {
        $this->_reseterror( );
    }

    function nb_fput( )
    {
        $this->_reseterror( );
    }

    function nb_get( )
    {
        $this->_reseterror( );
    }

    function nb_put( )
    {
        $this->_reseterror( );
    }

    function nlist( $remote_filespec = "" )
    {
        $this->_reseterror( );
        $result = $this->_download( trim( "NLST ".$remote_filespec ) );
        if ( $result !== FALSE )
        {
            return explode( "\n", str_replace( "\r", "", trim( $result ) ) );
        }
        return $result;
    }

    function pasv( $pasv )
    {
        if ( !$pasv )
        {
            $this->_seterror( "Active (PORT) mode is not supported" );
            return false;
        }
        return true;
    }

    function put( $remote, $local, $mode = FTP_BINARY, $startpos = 0 )
    {
        if ( !( $fp = @fopen( $local, "rb" ) ) )
        {
            return FALSE;
        }
        $result = $this->fput( $remote, $fp, $mode, $startpos );
        @fclose( $fp );
        return $result;
    }

    function pwd( )
    {
        $this->_reseterror( );
        $this->_printcommand( "PWD" );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        if ( $lr != FTP_PATHNAME )
        {
            return FALSE;
        }
        $subject = trim( substr( $this->lastLine, 4 ) );
        $lucifer = array( );
        if ( preg_match( "/\"(.*)\"/", $subject, $lucifer ) )
        {
            return $lucifer[1];
        }
        return FALSE;
    }

    function quit( )
    {
        $this->close( );
    }

    function raw( $cmd )
    {
        $this->_reseterror( );
        $this->_printcommand( $cmd );
        $this->_waitforresult( );
        $this->getlastresult( );
        return array(
            $this->lastLine
        );
    }

    function rawlist( $remote_filespec = "" )
    {
        $this->_reseterror( );
        $result = $this->_download( trim( "LIST ".$remote_filespec ) );
        if ( $result !== FALSE )
        {
            return explode( "\n", str_replace( "\r", "", trim( $result ) ) );
        }
        return $result;
    }

    function ls( $remote_filespec = "" )
    {
        $a = $this->rawlist( $remote_filespec );
        if ( !$a )
        {
            return $a;
        }
        $systype = $this->systype( );
        $is_windows = stristr( $systype, "WIN" ) !== FALSE;
        $b = array( );
        while ( list( $i, $line ) = each( $a ) )
        {
            if ( $is_windows && preg_match( "/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)/", $line, $lucifer ) )
            {
                $b[$i] = array( );
                if ( $lucifer[3] < 70 )
                {
                    $lucifer[3] += 2000;
                }
                else
                {
                    $lucifer[3] += 1900;
                }
                $b[$i]['isdir'] = $lucifer[7] == "<DIR>";
                list( , , , , $b[$i]['hour'], $b[$i]['hour'], $b[$i]['hour'], $b[$i]['hour'], , , , , , $b[$i]['hour'] ) = $lucifer;
                $b[$i]['minute'] = $lucifer[5];
                $b[$i]['time'] = @mktime( $lucifer[4] + ( strcasecmp( $lucifer[6], "PM" ) == 0 ? 12 : 0 ), $lucifer[5], 0, $lucifer[1], $lucifer[2], $lucifer[3] );
                $b[$i]['am/pm'] = $lucifer[6];
                $b[$i]['name'] = $lucifer[8];
            }
            else if ( $is_windows || !( $lucifer = preg_split( "/[ ]/", $line, 9, PREG_SPLIT_NO_EMPTY ) ) )
            {
                echo $line."\n";
                $lcount = count( $lucifer );
                if ( !( $lcount < 8 ) )
                {
                    $b[$i] = array( );
                    $b[$i]['isdir'] = $lucifer[0][0] === "d";
                    $b[$i]['islink'] = $lucifer[0][0] === "l";
                    $b[$i]['perms'] = $lucifer[0];
                    $b[$i]['number'] = $lucifer[1];
                    $b[$i]['owner'] = $lucifer[2];
                    $b[$i]['group'] = $lucifer[3];
                    $b[$i]['size'] = $lucifer[4];
                    if ( $lcount == 8 )
                    {
                        sscanf( $lucifer[5], "%d-%d-%d", $b[$i]['year'], $b[$i]['month'], $b[$i]['day'] );
                        sscanf( $lucifer[6], "%d:%d", $b[$i]['hour'], $b[$i]['minute'] );
                        $b[$i]['time'] = @mktime( $b[$i]['hour'], $b[$i]['minute'], 0, $b[$i]['month'], $b[$i]['day'], $b[$i]['year'] );
                        $b[$i]['name'] = $lucifer[7];
                    }
                    else
                    {
                        $b[$i]['month'] = $lucifer[5];
                        $b[$i]['day'] = $lucifer[6];
                        if ( preg_match( "/([0-9]{2}):([0-9]{2})/", $lucifer[7], $l2 ) )
                        {
                            $b[$i]['year'] = date( "Y" );
                            $b[$i]['hour'] = $l2[1];
                            $b[$i]['minute'] = $l2[2];
                        }
                        else
                        {
                            $b[$i]['year'] = $lucifer[7];
                            $b[$i]['hour'] = 0;
                            $b[$i]['minute'] = 0;
                        }
                        $b[$i]['time'] = strtotime( sprintf( "%d %s %d %02d:%02d", $b[$i]['day'], $b[$i]['month'], $b[$i]['year'], $b[$i]['hour'], $b[$i]['minute'] ) );
                        $b[$i]['name'] = $lucifer[8];
                    }
                }
            }
        }
        return $b;
    }

    function rename( $from, $to )
    {
        $this->_reseterror( );
        $this->_printcommand( "RNFR ".$from );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        $this->_printcommand( "RNTO ".$to );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return $lr == FTP_COMMAND_OK;
    }

    function rmdir( $name )
    {
        $this->_reseterror( );
        $this->_printcommand( "RMD ".$name );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return $lr == FTP_COMMAND_OK;
    }

    function set_option( )
    {
        $this->_reseterror( );
        $this->_seterror( -1, "set_option not supported" );
        return false;
    }

    function site( $cmd )
    {
        $this->_reseterror( );
        $this->_printcommand( "SITE ".$cmd );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        return true;
    }

    function size( $name )
    {
        $this->_reseterror( );
        $this->_printcommand( "SIZE ".$name );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        if ( $lr == FTP_FILE_STATUS )
        {
            return trim( substr( $this->lastLine, 4 ) );
        }
        return FALSE;
    }

    function ssl_connect( )
    {
        $this->_reseterror( );
        $this->_seterror( -1, "ssl_connect not supported" );
        return false;
    }

    function systype( )
    {
        $this->_reseterror( );
        $this->_printcommand( "SYST" );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        if ( $lr == FTP_NAME_SYSTEM_TYPE )
        {
            return trim( substr( $this->lastLine, 4 ) );
        }
        return FALSE;
    }

    function getlastresult( )
    {
        $this->newResult = FALSE;
        return $this->lastResult;
    }

    function _hasnewresult( )
    {
        return $this->newResult;
    }

    function _waitforresult( )
    {
        do
        {
        } while ( !$this->_hasnewresult( ) || $this->_readln( ) !== FALSE && !$this->_iserror( ) );
    }

    function _readln( )
    {
        $line = fgets( $this->controlSocket );
        if ( $line === FALSE )
        {
            $this->_seterror( -1, "fgets failed in _readln" );
            return FALSE;
        }
        if ( strlen( $line ) == 0 )
        {
            return $line;
        }
        $lucifer = array( );
        if ( preg_match( "/^[0-9][0-9][0-9] /", $line, $lucifer ) )
        {
            $this->lastResult = intval( $lucifer[0] );
            $this->newResult = TRUE;
            if ( substr( $lucifer[0], 0, 1 ) == "5" )
            {
                $this->_seterror( $this->lastResult, trim( substr( $line, 4 ) ) );
            }
        }
        $this->lastLine = trim( $line );
        $this->lastLines[] = "< ".trim( $line );
        return $line;
    }

    function _printcommand( $line )
    {
        $this->lastLines[] = "> ".$line;
        fwrite( $this->controlSocket, $line."\r\n" );
        fflush( $this->controlSocket );
    }

    function _pasv( )
    {
        $this->_reseterror( );
        $this->_printcommand( "PASV" );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( $this->_iserror( ) )
        {
            return FALSE;
        }
        if ( $lr != FTP_PASSIVE_MODE )
        {
            return FALSE;
        }
        $subject = trim( substr( $this->lastLine, 4 ) );
        $lucifer = array( );
        if ( preg_match( "/\\((\\d{1,3}),(\\d{1,3}),(\\d{1,3}),(\\d{1,3}),(\\d{1,3}),(\\d{1,3})\\)/", $subject, $lucifer ) )
        {
            $this->pasvAddr = $lucifer;
            $host = sprintf( "%d.%d.%d.%d", $lucifer[1], $lucifer[2], $lucifer[3], $lucifer[4] );
            $port = $lucifer[5] * 256 + $lucifer[6];
            $err_no = 0;
            $err_msg = "";
            $passiveConnection = fsockopen( $host, $port, $err_no, $err_msg, FTP_TIMEOUT );
            if ( $err_no != 0 )
            {
                $this->_seterror( $err_no, $err_msg );
                return FALSE;
            }
            return $passiveConnection;
        }
        return FALSE;
    }

    function _download( $cmd )
    {
        if ( !( $passiveConnection = $this->_pasv( ) ) )
        {
            return FALSE;
        }
        $this->_printcommand( $cmd );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( !$this->_iserror( ) )
        {
            $result = "";
            while ( !feof( $passiveConnection ) )
            {
                $result .= fgets( $passiveConnection );
            }
            fclose( $passiveConnection );
            $this->_waitforresult( );
            $lr = $this->getlastresult( );
            if ( $lr == FTP_FILE_TRANSFER_OK || $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK )
            {
                return $result;
            }
            return FALSE;
        }
        fclose( $passiveConnection );
        return FALSE;
    }

    function _uploadresource( $cmd, $resource )
    {
        if ( !( $passiveConnection = $this->_pasv( ) ) )
        {
            return FALSE;
        }
        $this->_printcommand( $cmd );
        $this->_waitforresult( );
        $lr = $this->getlastresult( );
        if ( !$this->_iserror( ) )
        {
            $result = "";
            while ( !feof( $resource ) )
            {
                $buf = fread( $resource, 1024 );
                fwrite( $passiveConnection, $buf );
            }
            fclose( $passiveConnection );
            $this->_waitforresult( );
            $lr = $this->getlastresult( );
            if ( $lr == FTP_FILE_TRANSFER_OK || $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK )
            {
                return $result;
            }
            return FALSE;
        }
        fclose( $passiveConnection );
        return FALSE;
    }

    function _reseterror( )
    {
        $this->error_no = NULL;
        $this->error_msg = NULL;
    }

    function _seterror( $no, $msg )
    {
        if ( is_array( $this->error_no ) )
        {
            $this->error_no[] = $no;
            $this->error_msg[] = $msg;
        }
        else if ( $this->error_no != NULL )
        {
            $this->error_no = array(
                $this->error_no,
                $no
            );
            $this->error_msg = array(
                $this->error_msg,
                $msg
            );
        }
        else
        {
            $this->error_no = $no;
            $this->error_msg = $msg;
        }
    }

    function _iserror( )
    {
        return $this->error_no !== 0;
    }

}

define( "FTP_TIMEOUT", 90 );
define( "FTP_COMMAND_OK", 200 );
define( "FTP_FILE_ACTION_OK", 250 );
define( "FTP_FILE_TRANSFER_OK", 226 );
define( "FTP_COMMAND_NOT_IMPLEMENTED", 502 );
define( "FTP_FILE_STATUS", 213 );
define( "FTP_NAME_SYSTEM_TYPE", 215 );
define( "FTP_PASSIVE_MODE", 227 );
define( "FTP_PATHNAME", 257 );
define( "FTP_SERVICE_READY", 220 );
define( "FTP_USER_LOGGED_IN", 230 );
define( "FTP_PASSWORD_NEEDED", 331 );
define( "FTP_USER_NOT_LOGGED_IN", 530 );
if ( !defined( "FTP_ASCII" ) )
{
    define( "FTP_ASCII", 0 );
}
if ( !defined( "FTP_BINARY" ) )
{
    define( "FTP_BINARY", 1 );
}
?>
