<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class phpFtp
{

    public $passiveMode = TRUE;
    public $lastLines = array( );
    public $lastLine = "";
    public $controlSocket = NULL;
    public $newResult = FALSE;
    public $lastResult = -1;
    public $pasvAddr = NULL;
    public $error_no = NULL;
    public $error_msg = NULL;

    public function phpFtp( )
    {
    }

    public function connect( $host, $port = 21, $timeout = FTP_TIMEOUT )
    {
        $this->_resetError( );
        $err_no = 0;
        $err_msg = "";
        if ( !( $this->controlSocket = fsockopen( $host, $port, $err_no, $err_msg, $timeout ) ) )
        {
            $this->_setError( -1, "fsockopen failed" );
        }
        if ( $err_no != 0 )
        {
            $this->setError( $err_no, $err_msg );
        }
        if ( $this->_isError( ) )
        {
            return false;
        }
        if ( !@socket_set_timeout( $this->controlSocket, $timeout ) )
        {
            $this->_setError( -1, "socket_set_timeout failed" );
        }
        if ( $this->_isError( ) )
        {
            return false;
        }
        $this->_waitForResult( );
        if ( $this->_isError( ) )
        {
            return false;
        }
        return $this->getLastResult( ) == FTP_SERVICE_READY;
    }

    public function isConnected( )
    {
        return $this->controlSocket != NULL;
    }

    public function disconnect( )
    {
        if ( !$this->isConnected( ) )
        {
            return;
        }
        @fclose( $this->controlSocket );
    }

    public function close( )
    {
        $this->disconnect( );
    }

    public function login( $user, $pass )
    {
        $this->_resetError( );
        $this->_printCommand( "USER {$user}" );
        if ( $this->_isError( ) )
        {
            return false;
        }
        $this->_waitForResult( );
        if ( $this->_isError( ) )
        {
            return false;
        }
        if ( $this->getLastResult( ) == FTP_PASSWORD_NEEDED )
        {
            $this->_printCommand( "PASS {$pass}" );
            if ( $this->_isError( ) )
            {
                return FALSE;
            }
            $this->_waitForResult( );
            if ( $this->_isError( ) )
            {
                return FALSE;
            }
        }
        $result = $this->getLastResult( ) == FTP_USER_LOGGED_IN;
        return $result;
    }

    public function cdup( )
    {
        $this->_resetError( );
        $this->_printCommand( "CDUP" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK;
    }

    public function cwd( $path )
    {
        $this->_resetError( );
        $this->_printCommand( "CWD {$path}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK;
    }

    public function cd( $path )
    {
        return $this->cwd( $path );
    }

    public function chdir( $path )
    {
        return $this->cwd( $path );
    }

    public function chmod( $mode, $filename )
    {
        return $this->site( "CHMOD {$mode} {$filename}" );
    }

    public function delete( $filename )
    {
        $this->_resetError( );
        $this->_printCommand( "DELE {$filename}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK;
    }

    public function exec( $cmd )
    {
        return $this->site( "EXEC {$cmd}" );
    }

    public function fget( $fp, $remote, $mode = FTP_BINARY, $resumepos = 0 )
    {
        $this->_resetError( );
        $type = "I";
        if ( $mode == FTP_ASCII )
        {
            $type = "A";
        }
        $this->_printCommand( "TYPE {$type}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        $result = $this->_download( "RETR {$remote}" );
        if ( $result )
        {
            fwrite( $fp, $result );
        }
        return $result;
    }

    public function fput( $remote, $resource, $mode = FTP_BINARY, $startpos = 0 )
    {
        $this->_resetError( );
        $type = "I";
        if ( $mode == FTP_ASCII )
        {
            $type = "A";
        }
        $this->_printCommand( "TYPE {$type}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        if ( 0 < $startpos )
        {
            fseek( $resource, $startpos );
        }
        $result = $this->_uploadResource( "STOR {$remote}", $resource );
        return $result;
    }

    public function get_option( $option )
    {
        $this->_resetError( );
        switch ( $option )
        {
        case "FTP_TIMEOUT_SEC" :
            return FTP_TIMEOUT;
        case "PHP_FTP_OPT_AUTOSEEK" :
            return FALSE;
        }
        seterror( -1, "Unknown option: {$option}" );
        return false;
    }

    public function get( $locale, $remote, $mode = FTP_BINARY, $resumepos = 0 )
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

    public function mdtm( $name )
    {
        $this->_resetError( );
        $this->_printCommand( "MDTM {$name}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
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

    public function mkdir( $name )
    {
        $this->_resetError( );
        $this->_printCommand( "MKD {$name}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_PATHNAME || $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK;
    }

    public function nb_continue( )
    {
        $this->_resetError( );
    }

    public function nb_fget( )
    {
        $this->_resetError( );
    }

    public function nb_fput( )
    {
        $this->_resetError( );
    }

    public function nb_get( )
    {
        $this->_resetError( );
    }

    public function nb_put( )
    {
        $this->_resetError( );
    }

    public function nlist( $remote_filespec = "" )
    {
        $this->_resetError( );
        $result = $this->_download( trim( "NLST {$remote_filespec}" ) );
        return $result !== FALSE ? explode( "\n", str_replace( "\r", "", trim( $result ) ) ) : $result;
    }

    public function pasv( $pasv )
    {
        if ( !$pasv )
        {
            $this->_setError( "Active (PORT) mode is not supported" );
            return false;
        }
        return true;
    }

    public function put( $remote, $local, $mode = FTP_BINARY, $startpos = 0 )
    {
        if ( !( $fp = @fopen( $local, "rb" ) ) )
        {
            return FALSE;
        }
        $result = $this->fput( $remote, $fp, $mode, $startpos );
        @fclose( $fp );
        return $result;
    }

    public function pwd( )
    {
        $this->_resetError( );
        $this->_printCommand( "PWD" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
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

    public function quit( )
    {
        $this->close( );
    }

    public function raw( $cmd )
    {
        $this->_resetError( );
        $this->_printCommand( $cmd );
        $this->_waitForResult( );
        $this->getLastResult( );
        return array(
            $this->lastLine
        );
    }

    public function rawlist( $remote_filespec = "" )
    {
        $this->_resetError( );
        $result = $this->_download( trim( "LIST {$remote_filespec}" ) );
        return $result !== FALSE ? explode( "\n", str_replace( "\r", "", trim( $result ) ) ) : $result;
    }

    public function ls( $remote_filespec = "" )
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
                $b[$i]['size'] = $lucifer[7];
                $b[$i]['month'] = $lucifer[1];
                $b[$i]['day'] = $lucifer[2];
                $b[$i]['year'] = $lucifer[3];
                $b[$i]['hour'] = $lucifer[4];
                $b[$i]['minute'] = $lucifer[5];
                $b[$i]['time'] = mktime( $lucifer[4] + ( strcasecmp( $lucifer[6], "PM" ) == 0 ? 12 : 0 ), $lucifer[5], 0, $lucifer[1], $lucifer[2], $lucifer[3] );
                $b[$i]['am/pm'] = $lucifer[6];
                $b[$i]['name'] = $lucifer[8];
            }
            else if ( !$is_windows && ( $lucifer = preg_split( "/[ ]/", $line, 9, PREG_SPLIT_NO_EMPTY ) ) )
            {
                echo $line."\n";
                $lcount = count( $lucifer );
                if ( $lcount < 8 )
                {
                    continue;
                }
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
                    $b[$i]['time'] = mktime( $b[$i]['hour'], $b[$i]['minute'], 0, $b[$i]['month'], $b[$i]['day'], $b[$i]['year'] );
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
        return $b;
    }

    public function rename( $from, $to )
    {
        $this->_resetError( );
        $this->_printCommand( "RNFR {$from}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        $this->_printCommand( "RNTO {$to}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK;
    }

    public function rmdir( $name )
    {
        $this->_resetError( );
        $this->_printCommand( "RMD {$name}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK;
    }

    public function set_option( )
    {
        $this->_resetError( );
        $this->_setError( -1, "set_option not supported" );
        return false;
    }

    public function site( $cmd )
    {
        $this->_resetError( );
        $this->_printCommand( "SITE {$cmd}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return true;
    }

    public function size( $name )
    {
        $this->_resetError( );
        $this->_printCommand( "SIZE {$name}" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_FILE_STATUS ? trim( substr( $this->lastLine, 4 ) ) : FALSE;
    }

    public function ssl_connect( )
    {
        $this->_resetError( );
        $this->_setError( -1, "ssl_connect not supported" );
        return false;
    }

    public function systype( )
    {
        $this->_resetError( );
        $this->_printCommand( "SYST" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
        {
            return FALSE;
        }
        return $lr == FTP_NAME_SYSTEM_TYPE ? trim( substr( $this->lastLine, 4 ) ) : FALSE;
    }

    public function getLastResult( )
    {
        $this->newResult = FALSE;
        return $this->lastResult;
    }

    public function _hasNewResult( )
    {
        return $this->newResult;
    }

    public function _waitForResult( )
    {
        do
        {
            if ( !$this->_hasNewResult( ) && $this->_readln( ) !== FALSE && !$this->_isError( ) )
            {
                break;
            }
        } while ( 1 );
    }

    public function _readln( )
    {
        $line = fgets( $this->controlSocket );
        if ( $line === FALSE )
        {
            $this->_setError( -1, "fgets failed in _readln" );
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
                $this->_setError( $this->lastResult, trim( substr( $line, 4 ) ) );
            }
        }
        $this->lastLine = trim( $line );
        $this->lastLines[] = "< ".trim( $line );
        return $line;
    }

    public function _printCommand( $line )
    {
        $this->lastLines[] = "> ".$line;
        fwrite( $this->controlSocket, $line."\r\n" );
        fflush( $this->controlSocket );
    }

    public function _pasv( )
    {
        $this->_resetError( );
        $this->_printCommand( "PASV" );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( $this->_isError( ) )
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
                $this->_setError( $err_no, $err_msg );
                return FALSE;
            }
            return $passiveConnection;
        }
        return FALSE;
    }

    public function _download( $cmd )
    {
        if ( !( $passiveConnection = $this->_pasv( ) ) )
        {
            return FALSE;
        }
        $this->_printCommand( $cmd );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( !$this->_isError( ) )
        {
            $result = "";
            while ( !feof( $passiveConnection ) )
            {
                $result .= fgets( $passiveConnection );
            }
            fclose( $passiveConnection );
            $this->_waitForResult( );
            $lr = $this->getLastResult( );
            return $lr == FTP_FILE_TRANSFER_OK || $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK ? $result : FALSE;
        }
        else
        {
            fclose( $passiveConnection );
            return FALSE;
        }
    }

    public function _uploadResource( $cmd, $resource )
    {
        if ( !( $passiveConnection = $this->_pasv( ) ) )
        {
            return FALSE;
        }
        $this->_printCommand( $cmd );
        $this->_waitForResult( );
        $lr = $this->getLastResult( );
        if ( !$this->_isError( ) )
        {
            $result = "";
            while ( !feof( $resource ) )
            {
                $buf = fread( $resource, 1024 );
                fwrite( $passiveConnection, $buf );
            }
            fclose( $passiveConnection );
            $this->_waitForResult( );
            $lr = $this->getLastResult( );
            return $lr == FTP_FILE_TRANSFER_OK || $lr == FTP_FILE_ACTION_OK || $lr == FTP_COMMAND_OK ? $result : FALSE;
        }
        else
        {
            fclose( $passiveConnection );
            return FALSE;
        }
    }

    public function _resetError( )
    {
        $this->error_no = NULL;
        $this->error_msg = NULL;
    }

    public function _setError( $no, $msg )
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

    public function _isError( )
    {
        return $this->error_no != NULL && $this->error_no !== 0;
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
