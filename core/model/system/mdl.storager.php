<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_storager
{

    function mdl_storager( )
    {
        $this->system = $system;
        $this->base_url = $this->system->base_url( );
        $this->class_name = defined( "WITH_STORAGER" ) ? constant( "WITH_STORAGER" ) : "fs_storage";
        require_once( PLUGIN_DIR."/functions/".$this->class_name.".php" );
        $this->worker = new $this->class_name( );
        if ( defined( "HOST_MIRRORS" ) )
        {
            $host_mirrors = preg_split( "/[,\\s]+/", constant( "HOST_MIRRORS" ) );
            if ( is_array( $host_mirrors ) && isset( $host_mirrors[0] ) )
            {
                $this->host_mirrors =& $host_mirrors;
                $this->host_mirrors_count = count( $host_mirrors ) - 1;
            }
        }
    }

    function &parse( $ident )
    {
        $ret = array( );
        if ( !$ident )
        {
            return false;
        }
        if ( list( $ret['url'], $ret['url'], $ret['url'] ) = explode( "|", $ident ) )
        {
            return $ret;
        }
        $ret['url'] =& $ident;
        return $ret;
    }

    function save( $file, $type = null, $addons = "" )
    {
        if ( $addons )
        {
            if ( !is_array( $addons ) )
            {
                $addons = array(
                    $addons
                );
            }
        }
        else
        {
            $addons = array( );
        }
        if ( $id = $this->worker->save( $file, $url, $type, $addons ) )
        {
            return $url."|".$id."|".$this->class_name;
        }
        return false;
    }

    function __check_upload( $file )
    {
        switch ( $file['error'] )
        {
        case 1 :
            return __( "上传的文件大小超出了服务器的空间大小" );
        case 2 :
            return __( "上传的文件大小超出浏览器限制" );
        case 3 :
            return __( "文件仅部分被上传" );
        case 4 :
            $msg = __( "没有找到要上传的文件" );
            break;
        case 5 :
            return __( "服务器临时文件夹丢失" );
        case 6 :
            return __( "文件写入到临时文件夹出错" );
        }
        return false;
    }

    function get_pic_upload_max( )
    {
        $limited = $this->system->getconf( "system.upload.limit" );
        switch ( $limited )
        {
        case "0" :
            $limited = array( "size" => 512000, "desc" => "500KB" );
            return $limited;
        case "1" :
            $limited = array( "size" => 1024000, "desc" => "1M" );
            return $limited;
        case "2" :
            $limited = array( "size" => 2048000, "desc" => "2M" );
            return $limited;
        case "3" :
            $limited = array( "size" => 3072000, "desc" => "3M" );
            return $limited;
        case "4" :
            $limited = array( "size" => 5120000, "desc" => "5M" );
            return $limited;
        case "5" :
            $limited = array(
                "size" => 5.12e+009,
                "desc" => __( "无限制大小" )
            );
            return $limited;
        }
        $limited = array( "size" => 512000, "desc" => "500KB" );
        return $limited;
    }

    function save_upload( $file, $type = null, $addons = "", &$msg )
    {
        $file['name'] = strtolower( $file['name'] );
        if ( $file['error'] )
        {
            $msg = $this->__check_upload( $file );
            trigger_error( $msg, E_USER_ERROR );
            return false;
        }
        $limited = $this->get_pic_upload_max( );
        if ( $limited['size'] < $file['size'] )
        {
            $msg = __( "上传的文件大小不能超过" ).$limited['desc'].__( "，请处理后重新上传！" );
            trigger_error( __( "上传的文件大小不能超过" ).$limited['desc'].__( "，请处理后重新上传！" ), E_USER_ERROR );
            return false;
        }
        $allow_upload = array( ".gif" => 1, ".jpg" => 1, ".jpeg" => 1, ".png" => 1, ".bmp" => 1, ".swf" => 1 );
        if ( !isset( $allow_upload[ext_name( $file['name'] )] ) )
        {
            $msg = __( "上传文件类型错误。" );
            trigger_error( __( "上传文件类型错误。" ), E_USER_ERROR );
            return false;
        }
        if ( $addons )
        {
            if ( is_array( $addons ) )
            {
                $addons[] = $file['name'];
            }
            else
            {
                $addons = array(
                    $addons,
                    $file['name']
                );
            }
        }
        else
        {
            $addons = array(
                $file['name']
            );
        }
        if ( method_exists( $this->worker, "save_upload" ) )
        {
            if ( $id = $this->worker->save_upload( $file['tmp_name'], $url, $type, $addons ) )
            {
                return $url."|".$id."|".$this->class_name;
            }
            return false;
        }
        if ( $id = $this->worker->save( $file['tmp_name'], $url, $type, $addons ) )
        {
            return $url."|".$id."|".$this->class_name;
        }
        return false;
    }

    function replace( $file, $ident, $type = null, $addons = "" )
    {
        if ( method_exists( $this->worker, "replace" ) && $ident )
        {
            $data = $this->parse( $ident );
            if ( $this->worker->replace( $file, $data['id'] ) )
            {
                return $ident;
            }
            return false;
        }
        if ( $ident )
        {
            $this->remove( $ident );
        }
        return $this->save( $file, $type, $addons );
    }

    function remove( $ident )
    {
        $data = $this->parse( $ident );
        return $this->worker->remove( $data['id'] );
    }

    function getfile( $ident )
    {
        if ( $data = $this->parse( $ident ) )
        {
            return $this->worker->getfile( $data['id'] );
        }
        return false;
    }

    function geturl( $ident )
    {
        if ( $ident )
        {
            $libs = array( "http://" => 1, "https:/" => 1 );
            $data =& $this->parse( $ident );
            if ( isset( $libs[strtolower( substr( $data['url'], 0, 7 ) )] ) )
            {
                return $data['url'];
            }
            if ( $this->host_mirrors )
            {
                return $this->host_mirrors[rand( 0, $this->host_mirrors_count )]."/".$data['url'];
            }
            return $this->base_url.$data['url'];
        }
        return false;
    }

}

?>
