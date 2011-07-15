<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_check_import( $aData, $aFile, &$object )
{
    if ( $aData['type'] == "csv" )
    {
        if ( substr( $aFile['upload']['name'], -4 ) != ".csv" )
        {
            trigger_error( __( "文件格式有误" ), E_USER_ERROR );
            exit( );
        }
        $content = file_get_contents( $aFile['upload']['tmp_name'] );
        if ( substr( $content, 0, 3 ) == "﻿" )
        {
            $content = substr( $content, 3 );
            $handle = fopen( $aFile['upload']['tmp_name'], "wb" );
            fwrite( $handle, $content );
            fclose( $handle );
        }
        $handle = fopen( $aFile['upload']['tmp_name'], "r" );
    }
    else if ( substr( $aData['type'], 0, 4 ) == "site" )
    {
        $handle['url'] = $aData['url'];
        $handle['count'] = 0;
    }
    $addons = $object->system->loadmodel( "system/addons" );
    $exporter = $addons->load( $aData['type'], "io" );
    $g = $object->system->loadmodel( "goods/gtype" );
    while ( $data = $exporter->import_row( $handle ) )
    {
        $goMark = true;
        foreach ( $data as $v )
        {
            if ( !trim( $v ) )
            {
                continue;
            }
            $goMark = false;
            break;
        }
        if ( !$goMark )
        {
            if ( $data[0][0] == "*" )
            {
                $type_name = explode( ":", $data[0], 2 );
                if ( $gtype = $g->gettypebyalias( "*", $type_name[1] ) )
                {
                    $type_valid = true;
                    $type_id = $gtype['type_id'];
                    $gtype['props'] = unserialize( $gtype['props'] );
                    $gtype['params'] = unserialize( $gtype['params'] );
                    $title_array = $object->gettypeexporttitle( $gtype );
                    $title_array_flip = array_flip( $title_array );
                    unset( $proto );
                    unset( $rel );
                    $proto['type_id'] = $type_id;
                    foreach ( $data as $k => $v )
                    {
                        if ( strstr( $v, "props:" ) && !isset( $title_array_flip[$v] ) )
                        {
                            trigger_error( __( "商品类型“" ).$gtype['name'].__( "”中的“" ).$v.__( "”属性并不存在！" ), E_USER_ERROR );
                            exit( );
                        }
                        if ( strstr( $v, "params:" ) && !isset( $title_array_flip[$v] ) )
                        {
                            trigger_error( __( "商品类型“" ).$gtype['name'].__( "”中的“" ).$v.__( "”参数并不存在！" ), E_USER_ERROR );
                            exit( );
                        }
                        if ( !( $v != "" ) && !$title_array_flip[$v] )
                        {
                            $proto[$title_array_flip[$v]] =& $rel[$k];
                        }
                    }
                }
                else
                {
                    $type_valid = false;
                    $object->csvlog( "warning", __( "商品类型“" ).$type_name[1].__( "”在商店中并不存在，该类型下的商品数据不能导入！" ) );
                }
            }
            else
            {
                if ( $type_valid && $type_id )
                {
                    foreach ( $data as $k => $v )
                    {
                        $rel[$k] = trim( $v );
                    }
                    if ( $proto['i_bn'] == "" )
                    {
                        unset( $proto->'goods_pdt' );
                        unset( $proto->'goods_spec' );
                        if ( $last_g_bn )
                        {
                            $object->writedata( );
                        }
                        $last_g_bn = $object->importgoods( $proto, $gtype );
                        $proto['do_goods'] = false;
                        if ( $proto['spec'] == "-" || $proto['spec'] == "" || is_array( $proto['spec'] ) && !$proto['spec'] )
                        {
                            $proto['i_bn'] = $proto['bn'];
                            $object->importproduct( $proto );
                        }
                        else
                        {
                            $proto['goods_spec'] = $proto['spec'];
                        }
                    }
                    else
                    {
                        $object->importproduct( $proto, true );
                    }
                }
                ++$iLoop;
                usleep( 20 );
            }
        }
    }
    if ( $last_g_bn )
    {
        $object->writedata( );
    }
    return true;
}

?>
