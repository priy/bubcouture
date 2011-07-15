<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class LogUtils
{

    public function _log( $msg )
    {
        $logfile = AS_LOG_DIR.date( "Y-m-d", time( ) )."/assis.log";
        if ( !is_dir( $dir = dirname( $logfile ) ) )
        {
            mkdir( $dir, 755 );
        }
        $msg = date( "H:i:s", time( ) )."\t".$msg."\r\n";
        error_log( $msg, 3, $logfile );
    }

    public function log_str( $msg )
    {
        if ( isset( $GLOBALS['as_debug'] ) && $GLOBALS['as_debug'] )
        {
            LogUtils::_log( $msg );
        }
    }

    public function log_obj( $obj )
    {
        if ( isset( $GLOBALS['as_debug'] ) && $GLOBALS['as_debug'] )
        {
            ob_start( );
            var_dump( $obj );
            $msg = ob_get_contents( );
            ob_end_clean( );
            LogUtils::_log( $msg );
        }
    }

    public function clear_log( )
    {
        $logfile = AS_LOG_DIR.date( "Y-m-d", time( ) )."/assis.log";
        if ( file_exists( $logfile ) )
        {
            unlink( $logfile );
        }
    }

}

?>
