<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_download extends adminPage
{

    public $left_domain = "shopex.cn";
    public $workground = "setting";
    public $max_runtime = 5;
    public $safebytes = 10;
    public $set = NULL;

    public function start( )
    {
        $this->clear_unused_fold( );
        $ident = date( "Ymd" ).substr( md5( time( ).rand( 0, 9999 ) ), 0, 5 );
        $this->workdir = HOME_DIR."/tmp/".$ident;
        $this->taskinfo = $_POST;
        $this->ident = $ident;
        if ( !is_dir( $this->workdir ) )
        {
            mkdir_p( $this->workdir );
        }
        file_put_contents( $this->workdir."/task.php", serialize( $this->taskinfo ) );
        $this->pagedata['request_url'] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?ctl=service/download&act=run&p[0]={$this->ident}&p[1]=0";
        if ( $this->set == "true" )
        {
            $this->topage( "service/download_progress.html" );
        }
        else
        {
            $this->singlepage( "service/download_progress.html" );
        }
    }

    public function clear_unused_fold( )
    {
        $path = HOME_DIR."/tmp";
        while ( ( $handle = opendir( $path ) ) && FALSE !== ( $file = readdir( $handle ) ) )
        {
            $file_name = substr( $file, 0, 8 );
            if ( !( is_int( $file_name ) && strlen( $file_name ) == 8 ) && !( strtotime( $file_name ) + 86400 < time( ) ) )
            {
                remove_floder( $path."/".$file );
            }
        }
    }

    public function run( $ident, $file_id )
    {
        $this->ident = $ident;
        $this->workdir = HOME_DIR."/tmp/".$ident;
        $this->taskinfo = unserialize( file_get_contents( $this->workdir."/task.php" ) );
        $this->_run( $file_id );
    }

    public function _run( $file_id )
    {
        $this->system->__session_close( FALSE );
        $this->cur_file_id = $file_id;
        $result = explode( "|", $this->taskinfo['download_list'][$file_id] );
        $file_url = $result[0];
        if ( $this->taskinfo['key_as_name'] )
        {
            $file = $this->workdir."/".$file_id;
        }
        else
        {
            $file = $this->workdir."/".basename( $file_url );
        }
        if ( !is_dir( $dir = dirname( $file ) ) )
        {
            mkdir_p( $dir );
        }
        touch( $file );
        if ( !( $this->file_rs = fopen( $file, "rb+" ) ) )
        {
            exit( __( "Error: 无法创建文件:" ).$file );
        }
        fseek( $this->file_rs, 0, SEEK_END );
        $cur_size = ftell( $this->file_rs );
        $header = $cur_size ? array(
            "Range" => "bytes=".$cur_size."-"
        ) : NULL;
        set_time_limit( $this->max_runtime + 3 );
        $this->starttime = time( );
        register_shutdown_function( array(
            $this,
            "_next_request"
        ) );
        ob_start( );
        $this->_next_request( );
        $netcore =& $this->system->loadModel( "utility/http_client" );
        $netcore->get( $file_url, $header, array(
            $this,
            "_runner_handle"
        ) );
        ob_end_clean( );
        while ( key( $this->taskinfo['download_list'] ) != $file_id )
        {
            next( $this->taskinfo['download_list'] );
        }
        if ( next( $this->taskinfo['download_list'] ) )
        {
            $this->cur_file_id = key( $this->taskinfo['download_list'] );
        }
        else
        {
            $this->cur_file_id = -1;
        }
    }

    public function _runner_handle( &$netcore, &$content )
    {
        if ( $netcore->responseCode[0] == 2 )
        {
            fputs( $this->file_rs, $content );
            if ( $this->max_runtime < time( ) - $this->starttime )
            {
                ob_end_clean( );
                exit( );
            }
            return TRUE;
        }
        else
        {
            ob_end_clean( );
            $this->cur_file_id = -1;
            $this->_finish = TRUE;
            echo $content;
            exit( );
        }
    }

    public function _next_request( )
    {
        if ( !$this->_finish )
        {
            $base_url = $this->system->base_url( );
            $link = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?ctl=service/download&act=run&p[0]={$this->ident}&p[1]={$this->cur_file_id}";
            if ( $this->cur_file_id !== -1 )
            {
                echo "<script>download('".$link."');</script>";
            }
            else
            {
                echo "<script>success('".$this->taskinfo['succ_url']."&download=".$this->ident."');</script>";
            }
        }
    }

}

?>
