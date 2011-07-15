<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class UpgradeScript extends Upgrade
{

    public $left_domain = "shopex.cn";
    public $workground = "setting";
    public $max_runtime = 5;
    public $safebytes = 10;
    public $set = NULL;
    public $noticeMsg = array( );

    public function upgrade_checkdb( )
    {
        $payment =& $this->system->loadModel( "trading/payment" );
        $sql = "SELECT pay_type FROM sdb_payment_cfg";
        if ( ( $rs = $this->db->select( $sql ) ) && is_array( $rs ) )
        {
            foreach ( $rs as $key => $val )
            {
                if ( $val['pay_type'] == "wangyin" )
                {
                    $val['pay_type'] = "wangjin";
                    $this->db->exec( "update sdb_payment_cfg set pay_type ='wangjin' where pay_type = 'wangyin'" );
                }
                $type = $val['pay_type'];
                $payType = "pay_".$val['pay_type'];
                $this->install_online( $payType, "", 1 );
            }
        }
    }

    public function install_online( $ident, $url, $is_update = FALSE )
    {
        if ( !$url )
        {
            $url = "http://sds.ecos.shopex.cn/payments/apps/".$ident.".tar";
        }
        $GLOBALS['_POST'] = array(
            "download_list" => array(
                $url
            ),
            "succ_url" => "http://".$_SERVER['HTTP_HOST'].dirname( $_SERVER['PHP_SELF'] )."/index.php?ctl=trading/payment&act=do_install_online"
        );
        $this->start( );
        if ( $is_update )
        {
            $ident = date( "Ymd" ).substr( md5( time( ).rand( 0, 9999 ) ), 0, 5 );
            $this->run( $this->ident, 0 );
            $GLOBALS['_GET']['download'] = $this->ident;
            $this->do_install_online( );
        }
    }

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
                $GLOBALS['_GET']['download'] = $this->ident;
                $this->do_install_online( );
            }
        }
    }

    public function do_install_online( )
    {
        $task = HOME_DIR."/tmp/".$_GET['download'];
        $temp_mess = file_get_contents( $task."/task.php" );
        $down_data = unserialize( $temp_mess );
        if ( $url = $down_data['download_list'][0] )
        {
            $filename = substr( $url, strrpos( $url, "/" ) + 1 );
            $file_path = $task."/".$filename;
            $dir_name = substr( $filename, 0, strrpos( $filename, "." ) );
            if ( file_exists( $file_path ) )
            {
                $appmgr = $this->system->loadModel( "system/appmgr" );
                $appmgr->instal_ol_app( $file_path, $dir_name, $msg, TRUE );
                $this->install_app( $dir_name );
            }
        }
    }

    public function install_app( $ident )
    {
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $refesh =& $this->system->loadModel( "system/addons" );
        if ( $appmgr->install( $ident, "1" ) )
        {
        }
        else
        {
            $this->end( FALSE, "安装失败" );
        }
    }

    public function singlepage( $view )
    {
        $this->pagedata['_PAGE_'] = $view;
        $this->pagedata['statusId'] = $this->system->getConf( "shopex.wss.enable" );
        $this->pagedata['session_id'] = $this->system->sess_id;
        $this->pagedata['shopadmin_dir'] = dirname( $_SERVER['PHP_SELF'] )."/";
        $this->pagedata['shop_base'] = $this->system->base_url( );
        $output = $this->fetch( "singlepage.html" );
        $re = "/<script([^>]*)>(.*?)<\\/script>/is";
        $this->__scripts = "";
        echo preg_replace_callback( $re, array(
            $this,
            "_singlepage_prepare"
        ), $output );
        echo "<script type=\"text/plain\" id=\"__eval_scripts__\" >";
        echo $this->__scripts;
        echo "</script></body></html>";
    }

    public function &fetch( $file, $display = FALSE )
    {
        if ( defined( "CUSTOM_CORE_DIR" ) )
        {
            if ( $pos = strpos( $file, "#" ) )
            {
            }
            else
            {
                $pos = strlen( $file );
            }
            if ( !file_exists( CUSTOM_CORE_DIR."/".__ADMIN__."/view/".substr( $file, 0, $pos ) ) )
            {
                $this->template_dir = CORE_DIR."/admin/view/";
            }
            else
            {
                $this->template_dir = CUSTOM_CORE_DIR."/admin/view/";
            }
        }
        $content = $this->pfetch( $file );
        if ( $this->_update_areas )
        {
            foreach ( $this->_update_areas as $k => $area )
            {
                $content .= "<!-----".$k."-----".$area."-----".$k."----->";
            }
            $this->_update_areas = array( );
        }
        $this->system->apply_modifiers( $content, "admin" );
        if ( $display )
        {
            echo $content;
        }
        else
        {
            return $content;
        }
    }

    public function &pfetch( $file, $display = FALSE )
    {
        $this->_files = array(
            $file
        );
        $output =& $this->_fetch_compile( $this->_get_resource( $file ) );
        array_shift( $this->_files );
        if ( $display )
        {
            echo $output;
        }
        else
        {
            return $output;
        }
    }

    public function &_fetch_compile( $file )
    {
        $this->_current_file = $file;
        $name = md5( ( $this->_resource_type == 1 ? $this->template_dir.$file : $this->_resource_type."_".$file ).$this->lang.$this->__ident );
        if ( $this->force_compile || !$this->__run_compiled( $name, $this->_resource_time, $output ) )
        {
            $file_contents = "";
            if ( $this->_resource_type == 1 || $this->_resource_type == "file" )
            {
                if ( file_exists( $this->template_dir.$file ) )
                {
                    $file_contents = file_get_contents( $this->template_dir.$file );
                }
            }
            else
            {
                call_user_func_array( $this->_plugins['resource'][$this->_resource_type][0], array(
                    $file,
                    $file_contents,
                    $this
                ) );
            }
            if ( file_exists( $file ) )
            {
                $file_contents = file_get_contents( $file );
            }
            $this->_file = $file;
            if ( !is_object( $this->_compile_obj ) )
            {
                $this->_compile_obj =& $this->system->loadModel( "system/tramsy" );
                $this->_compile_obj->_parent =& $this;
                $this->_compile_obj->enable_strip_whitespace =& $this->enable_strip_whitespace;
            }
            $this->_compile_obj->_require_stack = array( );
            $this->_compile_obj->_plugins =& $this->_plugins;
            $this->_compile_obj->left_delimiter =& $this->left_delimiter;
            $this->_compile_obj->right_delimiter =& $this->right_delimiter;
            $output =& $this->_compile_obj->_compile_file( $file_contents, FALSE );
            $this->_compile_obj->post_compile( $output );
            $this->_set_compile( $name, $output );
            ob_start( );
            eval( " ?>".$output );
            $output = ob_get_contents( );
            ob_end_clean( );
        }
        if ( 0 < count( $this->_plugins['outputfilter'] ) )
        {
            foreach ( $this->_plugins['outputfilter'] as $filter_func )
            {
                $output = $filter_func( $output );
            }
        }
        return $output;
    }

    public function _get_resource( $file )
    {
        $this->__ident = "";
        $_resource_name = explode( ":", trim( $file ) );
        if ( $this->default_resource_type != "file" && count( $_resource_name ) == 1 )
        {
            $this->_resource_type = $this->default_resource_type;
            $exists = isset( $this->_plugins['resource'][$this->_resource_type] ) && call_user_func_array( $this->_plugins['resource'][$this->_resource_type][1], array(
                $file,
                $resource_timestamp,
                $this
            ) );
            if ( !$exists )
            {
                return FALSE;
                $this->trigger_error( "file '{$file}' does not exist", E_USER_ERROR );
            }
            $this->_resource_time = $resource_timestamp;
        }
        else if ( count( $_resource_name ) == 1 || $_resource_name[0] == "file" )
        {
            if ( $_resource_name[0] == "file" )
            {
                $file = substr( $file, 5 );
            }
            if ( $p = strpos( $file, "#" ) )
            {
                $this->__ident = substr( $file, $p );
                $file = substr( $file, 0, $p );
            }
            $exists = $this->template_exists( $file );
            if ( !$exists )
            {
                return FALSE;
                $this->trigger_error( "file '{$file}' does not exist", E_USER_ERROR );
            }
        }
        else
        {
            $this->_resource_type = $_resource_name[0];
            $file = substr( $file, strlen( $this->_resource_type ) + 1 );
            $exists = isset( $this->_plugins['resource'][$this->_resource_type] ) && call_user_func_array( $this->_plugins['resource'][$this->_resource_type][1], array(
                $file,
                $resource_timestamp,
                $this
            ) );
            if ( !$exists )
            {
                if ( file_exists( $file ) )
                {
                    return $file;
                }
                return FALSE;
                $this->trigger_error( "file '{$file}' does not exist", E_USER_ERROR );
            }
            $this->_resource_time = $resource_timestamp;
        }
        return $file;
    }

    public function __run_compiled( $key, $resource_time, &$output )
    {
        $file = $this->compile_dir.$key.".php";
        if ( file_exists( $file ) && !( filemtime( $file ) < max( $resource_time, $this->versionTimeStamp ) ) )
        {
            ob_start( );
            include( $file );
            $output = ob_get_contents( );
            ob_end_clean( );
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function _set_compile( $key, &$content )
    {
        return file_put_contents( $this->compile_dir.$key.".php", $content );
    }

}

?>
