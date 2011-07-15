<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function GetFileSize( $baseType, $filename )
{
    LogUtils::log_str( "GetFileSize Begin" );
    $size = -1;
    $file = ServerUtils::combinepath( $baseType, $filename );
    LogUtils::log_str( $file );
    if ( file_exists( $file ) )
    {
        clearstatcache( );
        $size = filesize( $file );
        $size = $size ? $size : 0;
    }
    LogUtils::log_str( "GetFileSize return:".$size );
    return $size;
}

function DownloadFile( $baseType, $filename )
{
    LogUtils::log_str( "DownloadFile Begin" );
    LogUtils::log_obj( func_get_args( ) );
    @set_time_limit( 0 );
    $file = ServerUtils::combinepath( $baseType, $filename );
    LogUtils::log_str( $file );
    if ( file_exists( $file ) )
    {
        LogUtils::log_str( "file exists" );
        $server =& $GLOBALS['GLOBALS']['as_server'];
        $server->addAttachment( file_get_contents( $file ) );
    }
    else
    {
        LogUtils::log_str( "file not found" );
    }
    LogUtils::log_str( "DownloadFile Return" );
}

function UploadFile( $baseType, $filename, $append = FALSE )
{
    LogUtils::log_str( "UploadFile Begin" );
    LogUtils::log_obj( func_get_args( ) );
    @set_time_limit( 0 );
    $server =& $GLOBALS['GLOBALS']['as_server'];
    $sys =& $GLOBALS['GLOBALS']['system'];
    $atts = $server->getAttachments( );
    if ( 0 < count( $atts ) )
    {
        LogUtils::log_str( "received atts" );
        $file = ServerUtils::combinepath( $baseType, $filename );
        LogUtils::log_str( $file );
        $att = NULL;
        foreach ( $atts as $attitem )
        {
            $att = $attitem;
            break;
        }
        if ( !is_dir( $dir = dirname( $file ) ) )
        {
            LogUtils::log_str( "create dir:".$dir );
            mkdir_p( $dir );
        }
        LogUtils::log_str( "save file length:".strlen( $att['data'] ) );
        $done = FALSE;
        if ( !$done )
        {
            LogUtils::log_str( "save file:".$file );
            if ( $append )
            {
                file_put_contents( $file, $att['data'], FILE_APPEND );
            }
            else
            {
                file_put_contents( $file, $att['data'] );
            }
            @chmod( $file, 420 );
        }
    }
    else
    {
        LogUtils::log_str( "no atts found" );
    }
    LogUtils::log_str( "UploadFile Return" );
}

function UploadGoodsImage( $goods_id, $gimage_ids )
{
    LogUtils::log_str( "UploadGoodsImage Begin" );
    LogUtils::log_obj( func_get_args( ) );
    @set_time_limit( 0 );
    $server =& $GLOBALS['GLOBALS']['as_server'];
    $sys =& $GLOBALS['GLOBALS']['system'];
    if ( is_array( $gimage_ids ) )
    {
        $o = $sys->loadModel( "goods/gimage" );
        if ( $o )
        {
            LogUtils::log_str( "gimage saveImage:{$goods_id},(".implode( ",", $gimage_ids ).")" );
            $newThumbnail = array( );
            $ret = $o->saveImage( $goods_id, "", $gimage_ids[0], $gimage_ids, FALSE, $newThumbnail );
            LogUtils::log_obj( $ret );
        }
    }
    else
    {
        LogUtils::log_str( "parm gimage_ids is not array" );
    }
    LogUtils::log_str( "UploadGoodsImage Return" );
}

class SyncFileService extends BaseService
{

    public function init( &$server )
    {
        parent::init( $server );
        $server->register( "GetFileSize", array( "baseType" => "xsd:int", "filename" => "xsd:string" ), array( "return" => "xsd:int" ), "urn:shopexapi", "urn:shopexapi#GetFileSize", "rpc", "encoded", "" );
        $server->register( "DownloadFile", array( "baseType" => "xsd:int", "filename" => "xsd:string" ), array( ), "urn:shopexapi", "urn:shopexapi#DownloadFile", "rpc", "encoded", "" );
        $server->register( "UploadFile", array( "baseType" => "xsd:int", "filename" => "xsd:string", "append" => "xsd:boolean" ), array( ), "urn:shopexapi", "urn:shopexapi#UploadFile", "rpc", "encoded", "" );
        $server->register( "UploadGoodsImage", array( "goods_id" => "xsd:int", "gimage_ids" => "tns:IntegerArray" ), array( ), "urn:shopexapi", "urn:shopexapi#UploadGoodsImage", "rpc", "encoded", "" );
    }

}

?>
