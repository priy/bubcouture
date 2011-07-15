<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_import_goodsline( &$aData, &$object )
{
    $aData['intro'] = str_replace( "\\n", "\n", $aData['intro'] );
    $aData['intro'] = addslashes( $aData['intro'] );
    $aData['brief'] = str_replace( "\\n", "\n", $aData['brief'] );
    $aData['brief'] = addslashes( $aData['brief'] );
    $aData['name'] = addslashes( $aData['name'] );
    $aData['last_modify'] = time( );
    $aData['cost'] += 0;
    if ( $aData['goods_id'] )
    {
        $rs = $object->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $aData['goods_id'] ) );
        $sql = $object->db->GetUpdateSQL( $rs, $aData );
        if ( $sql && !$object->db->exec( $sql ) )
        {
            trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
            return false;
        }
    }
    else
    {
        $aData['cat_id'] = intval( $aData['cat_id'] );
        if ( !$aData['price'] )
        {
            $aData['price'] = 0;
        }
        $aData['uptime'] = time( );
        unset( $aData['goods_id'] );
        $rs = $object->db->query( "SELECT * FROM sdb_goods WHERE 0=1" );
        $sql = $object->db->GetInsertSQL( $rs, $aData );
        if ( $sql && !$object->db->exec( $sql ) )
        {
            trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
            return false;
        }
        $aData['goods_id'] = $object->db->lastInsertId( );
        $aData['p_order'] = 50;
        $rs = $object->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".$aData['goods_id'] );
        $sql = $object->db->GetUpdateSQL( $rs, $aData );
        if ( $sql && !$object->db->exec( $sql ) )
        {
            trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
            return false;
        }
        $status = $object->system->loadModel( "system/status" );
        $status->add( "GOODS_ADD" );
    }
    if ( $aData['image_file'] || $aData['thumbnail_pic'] )
    {
        $image_change = false;
        if ( $aData['image_file'] )
        {
            $images = explode( "#", $aData['image_file'] );
            $images = array_unique( $images );
        }
        else
        {
            $images = array( );
            $aData['image_default'] = 0;
        }
        $image_file = array( );
        $gimage = $object->system->loadModel( "goods/gimage" );
        if ( is_array( $images ) && 0 < count( $images ) )
        {
            $storager = $object->system->loadModel( "system/storager" );
            $aData['udfimg'] = in_array( $aData['thumbnail_pic'], $images ) ? "false" : "true";
            $i = 0;
            foreach ( $images as $k => $image )
            {
                if ( !$image )
                {
                    continue;
                }
                $gimage_id = null;
                if ( strpos( $image, "@" ) !== false )
                {
                    $aTmp = explode( "@", $image );
                    $gimage_id = $aTmp[0];
                    if ( !$gimage_id )
                    {
                        $gimage_id = $gimage->get_img_by_source( $image, "gimage_id" );
                    }
                }
                else if ( preg_match( "!^http(s|)://!i", $image ) )
                {
                    $gimage_id = $gimage->insert_new( array(
                        "is_remote" => "true",
                        "source" => "N",
                        "src_size_width" => 100,
                        "src_size_height" => 100,
                        "big" => $image,
                        "small" => $image,
                        "thumbnail" => $image,
                        "up_time" => time( )
                    ), $aData['goods_id'] );
                }
                else if ( file_exists( HOME_DIR."/upload/".$image ) )
                {
                    $pic['tmp_name'] = HOME_DIR."/upload/".$image;
                    $pic['goods_id'] = $aData['goods_id'];
                    $aImg = $gimage->save_upload( $pic );
                    $gimage_id = $aImg['gimage_id'];
                }
                $image_file[] = $gimage_id;
                if ( $i == 0 )
                {
                    $aData['image_default'] = $gimage_id;
                    ++$i;
                }
            }
        }
        if ( !preg_match( "!^http(s|)://!i", $aData['thumbnail_pic'] ) && file_exists( HOME_DIR."/upload/".$aData['thumbnail_pic'] ) )
        {
            $thumbnail_pic['goods_thumbnail_pic']['name'] = HOME_DIR."/upload/".$aData['thumbnail_pic'];
            $thumbnail_pic['goods_thumbnail_pic']['img_source'] = "local";
            $image_change = true;
        }
        else
        {
            if ( count( $images ) == 0 && preg_match( "!^http(s|)://!i", $aData['thumbnail_pic'] ) )
            {
                $aData['udfimg'] = "true";
            }
            if ( preg_match( "!^http(s|)://!i", $aData['thumbnail_pic'] ) )
            {
                $thumbnail_pic = $aData['thumbnail_pic'];
            }
            else
            {
                $thumbnail_pic = array( );
            }
        }
        $gimage->saveImage( $aData['goods_id'], "", $aData['image_default'], $image_file, $aData['udfimg'], $thumbnail_pic );
    }
    return $aData['goods_id'];
}

?>
