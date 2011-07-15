<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class secache_no_flock extends secache
{

    function secache_no_flock( )
    {
        secache::secache( );
        $this->__support_usleep = version_compare( PHP_VERSION, 5, ">=" ) ? 20 : 1;
    }

    function lock( $is_block, $whatever = false )
    {
        ignore_user_abort( 1 );
        $lockfile = $this->_file.".lck";
        if ( file_exists( $lockfile ) )
        {
            if ( 5 < time( ) - filemtime( $lockfile ) )
            {
                touch( $lockfile );
                return true;
            }
            if ( !$is_block )
            {
                return false;
            }
        }
        $lock_ex = @fopen( $lockfile, "x" );
        $i = 0;
        do
        {
            while ( $lock_ex === false )
            {
                if ( $whatever )
                {
                    break;
                }
                if ( $i < 20 )
                {
                    break;
                }
                ++$i;
            }
            clearstatcache( );
            if ( $this->__support_usleep == 1 )
            {
                usleep( rand( 9, 999 ) );
            }
            else
            {
                sleep( 1 );
            }
            $lock_ex = @fopen( $lockfile, "x" );
        } while ( 1 );
        return $lock_ex !== false;
    }

    function unlock( )
    {
        ignore_user_abort( 0 );
        return unlink( $this->_file.".lck" );
    }

}

?>
