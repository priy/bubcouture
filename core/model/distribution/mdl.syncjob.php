<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_syncjob extends modelfactory
{

    function mdl_syncjob( )
    {
        modelfactory::modelfactory( );
        $token = $this->system->getconf( "certificate.token" );
        $this->api = $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $token );
    }

    function adddatasyncjob( $supplier_id, $supplier_pline = array( ), $auto_download = false, $limit = 20, $to_cat_id = NULL )
    {
        $supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
        $last_sync_time = $supplier_info['sync_time_for_plat'];
        if ( $auto_download )
        {
            $count = 0;
            $local_limit = 100;
            $this->getcommandbypline( $supplier_id, $supplier_pline, $count, 0, 1 );
            if ( 0 < $count )
            {
                $pages = ceil( $count / $local_limit );
                $i = 0;
                for ( ; $i < $pages; ++$i )
                {
                    $this->_adddatasyncjob( $last_sync_time, 0, $i + 1, $local_limit, $supplier_id, true, $supplier_pline, $to_cat_id );
                }
            }
        }
        $sync_list = $this->api->getapidata( "getUpdateList", API_VERSION, array(
            "pages" => 1,
            "counts" => 1,
            "supplier_id" => $supplier_id,
            "last_sync_time" => $last_sync_time
        ), true, true );
        if ( !empty( $sync_list ) )
        {
            $sync_list_count = $sync_list[0]['row_count'];
            if ( 0 < $sync_list_count )
            {
                $pages = ceil( $sync_list_count / $limit );
                $i = 0;
                for ( ; $i < $pages; ++$i )
                {
                    $this->_adddatasyncjob( $last_sync_time, $sync_list[0]['end_time'], $i + 1, $limit, $supplier_id, $auto_download, $supplier_pline, $to_cat_id );
                }
                $rs = $this->db->query( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
                $sql = $this->db->getupdatesql( $rs, array(
                    "sync_time_for_plat" => $sync_list[0]['end_time']
                ) );
                $this->db->exec( $sql );
            }
        }
    }

    function _adddatasyncjob( $from_time, $to_time, $page, $limit, $supplier_id, $auto_download, $supplier_pline, $to_cat_id )
    {
        $job_info = array(
            "from_time" => $from_time,
            "to_time" => $to_time,
            "page" => $page,
            "limit" => $limit,
            "supplier_id" => $supplier_id,
            "auto_download" => $auto_download ? "true" : "false",
            "supplier_pline" => empty( $supplier_pline ) ? "" : serialize( $supplier_pline ),
            "to_cat_id" => $to_cat_id
        );
        $rs = $this->db->query( "SELECT * FROM sdb_job_data_sync WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $job_info );
        $this->db->exec( $sql );
    }

    function addgoodsdownloadjob( $command_id, $supplier_id, $supplier_goods_id, $goods_count, $to_cat_id = NULL )
    {
        $rs = $this->db->query( "SELECT * FROM sdb_job_goods_download WHERE 0=1" );
        $insert_info = array(
            "command_id" => $command_id,
            "supplier_id" => $supplier_id,
            "supplier_goods_id" => $supplier_goods_id,
            "supplier_goods_count" => $goods_count,
            "to_cat_id" => $to_cat_id
        );
        $sql = $this->db->getinsertsql( $rs, $insert_info );
        $this->db->exec( $sql );
    }

    function dodatasyncjob( )
    {
        $time = time( );
        $job = $this->db->selectrow( "SELECT * FROM sdb_job_data_sync ORDER BY job_id ASC" );
        if ( !empty( $job ) )
        {
            $this->_updatelock( "data_sync" );
            if ( $job['auto_download'] == "true" )
            {
                $count = $this->getdownloadcount( $job );
            }
            else
            {
                $count = 0;
            }
            if ( $job['to_time'] == 0 && $job['auto_download'] == "true" )
            {
                $offset = ( $job['page'] - 1 ) * $job['limit'];
                $command_ids = $this->getcommandbypline( $job['supplier_id'], unserialize( $job['supplier_pline'] ), $t_count, $offset, $job['limit'] );
                foreach ( $command_ids as $v )
                {
                    $this->addgoodsdownloadjob( $v['command_id'], $job['supplier_id'], $v['object_id'], $count, $job['to_cat_id'] );
                }
            }
            else
            {
                $auto_download = $job['auto_download'];
                $supplier_id = $job['supplier_id'];
                $params = array(
                    "supplier_id" => $supplier_id,
                    "last_sync_time" => $job['from_time'],
                    "last_sync_time_end" => $job['to_time'],
                    "pages" => $job['page'],
                    "counts" => $job['limit']
                );
                $sync_list = $this->api->getapidata( "getUpdateList", API_VERSION, $params, true, true );
                if ( !empty( $sync_list ) )
                {
                    foreach ( $sync_list as $v )
                    {
                        $store = "";
                        $command_id = $v['command_id'];
                        $type = $v['cmd_action'] == 2 ? "product" : "goods";
                        $object_id = $v['ob_id'];
                        $status = "unoperated";
                        $command = $v['cmd_action'];
                        $command_info = array(
                            "thumbnail_pic" => $v['thumbnail_pic'],
                            "name" => $v['name'].( $type == "product" ? empty( $v['spec_value'] ) ? "" : "(".$v['spec_value'].")" : "" )
                        );
                        $goods_id = $v['goods_id'];
                        $brand_id = $v['brand_id'];
                        $brand_name = addslashes( $v['brand_name'] );
                        $cat_id = $v['cat_id'];
                        $cat_name = addslashes( $v['cat_name'] );
                        $marketable = $v['marketable'];
                        $store = $v['store'];
                        $bn = $v['bn'];
                        $command_info[$type."_info"] = array(
                            "cat_id" => $cat_id,
                            "type_id" => $v['type_id'],
                            "brand_id" => $brand_id,
                            "brand" => $brand_name,
                            "bn" => $bn,
                            "marketable" => $marketable,
                            "store" => $store
                        );
                        $name = $v['name'].( $type == "product" ? empty( $v['spec_value'] ) ? "" : "(".$v['spec_value'].")" : "" );
                        $name = addslashes( $name );
                        $last_modify = $v['cmd_lasttime'];
                        $command_type = $auto_download == "true" ? "download" : "sync";
                        $store = $store === "" || is_null( $store ) ? "NULL" : $store;
                        $table_name = "sdb_data_sync_".$job['supplier_id'];
                        $create_table = "                        CREATE TABLE IF NOT EXISTS `".$table_name."` (\r\n                          `command_id` int(10) unsigned NOT NULL,\r\n                          `type` enum('goods','product') NOT NULL,\r\n                          `supplier_id` int(10) unsigned NOT NULL,\r\n                          `object_id` mediumint(8) unsigned NOT NULL,\r\n                          `status` enum('unoperated','unmodified','done') NOT NULL default 'unoperated',\r\n                          `command` tinyint(3) unsigned NOT NULL,\r\n                          `command_info` text,\r\n                          `last_modify` int(10) unsigned NOT NULL,\r\n                          `command_type` enum('download','sync') NOT NULL default 'download',\r\n                          `img_down_failed` enum('true','false') NOT NULL default 'false',\r\n                          `if_show` enum('true','false') NOT NULL default 'true',\r\n                          `cat_id` int(11) NOT NULL,\r\n                          `brand_id` int(11) NOT NULL,\r\n                          `goods_id` mediumint(8) unsigned NOT NULL,\r\n                          `brand_name` varchar(100),\r\n                          `cat_name` varchar(100),\r\n                          `name` varchar(255),\r\n                          `bn` varchar(200),\r\n                          `marketable` varchar(10) NULL,\r\n                          `store` mediumint(8) unsigned NULL,\r\n                          PRIMARY KEY  (`command_id`),\r\n                          KEY `object_id` (`object_id`),\r\n                          KEY `last_modify` (`last_modify`),\r\n                          KEY `brand_name` (`brand_name`),\r\n                          KEY `cat_name` (`cat_name`),\r\n                          KEY `bn` (`bn`),\r\n                          KEY `store` (`store`)\r\n                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
                        $this->db->exec( $create_table );
                        $flag = $this->_checkinpline( $brand_id, $cat_id, $supplier_id );
                        if ( $flag )
                        {
                            if ( $command == 6 )
                            {
                                $tmp_goods = $this->db->selectrow( "SELECT * FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $goods_id ) );
                                if ( empty( $tmp_goods ) )
                                {
                                    $show = "true";
                                }
                                else if ( $tmp_goods['disabled'] == "true" || $tmp_goods['marketable'] == "false" )
                                {
                                    $show = "true";
                                }
                                else
                                {
                                    $show = "true";
                                    $tmp = $this->db->selectrow( "SELECT * FROM ".$table_name." WHERE command_id=".$command_id );
                                    $status = $tmp['status'];
                                }
                                switch ( $type )
                                {
                                }
                                else
                                {
                                case "goods" :
                                    if ( !$this->db->selectrow( "SELECT goods_id FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $goods_id ) ) )
                                    {
                                        $show = "false";
                                    }
                                    else
                                    {
                                        $show = "true";
                                    }
                                    if ( !( $command == 4 ) )
                                    {
                                        break;
                                    }
                                    if ( $show == "true" )
                                    {
                                        $rs = $this->db->query( "SELECT * FROM ".$table_name." WHERE if_show='false' AND goods_id=".intval( $goods_id ) );
                                        $sql = $this->db->getupdatesql( $rs, array(
                                            "last_modify" => $last_modify,
                                            "if_show" => "true"
                                        ) );
                                        if ( !$sql )
                                        {
                                            break;
                                        }
                                        $this->db->exec( $sql );
                                    }
                                    else
                                    {
                                        $rs = $this->db->query( "SELECT * FROM ".$table_name." WHERE if_show='false' AND command=6 AND goods_id=".intval( $goods_id ) );
                                        $sql = $this->db->getupdatesql( $rs, array(
                                            "last_modify" => $last_modify,
                                            "if_show" => "true"
                                        ) );
                                        if ( !$sql )
                                        {
                                            break;
                                        }
                                        $this->db->exec( $sql );
                                    }
                                    break;
                                case "product" :
                                    if ( !$this->db->selectrow( "SELECT goods_id FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $goods_id ) ) )
                                    {
                                        $show = "false";
                                    }
                                    else
                                    {
                                        $show = "true";
                                        break;
                                        $show = "false";
                                    }
                                }
                                else
                                {
                                    $show = "false";
                                    if ( !( $command == 4 ) )
                                    {
                                        break;
                                    }
                                    $rs = $this->db->query( "SELECT * FROM ".$table_name." WHERE if_show='true' AND goods_id=".intval( $goods_id ) );
                                    $sql = $this->db->getupdatesql( $rs, array( "if_show" => "false" ) );
                                    if ( !$sql )
                                    {
                                        break;
                                    }
                                    $this->db->exec( $sql );
                                }
                            }
                        }
                        if ( $command == 4 && !$this->db->selectrow( "SELECT command_id FROM ".$table_name." WHERE command_id=".$command_id." AND cat_id=".$cat_id." AND brand_id=".$brand_id ) )
                        {
                            $rs = $this->db->query( "SELECT * FROM ".$table_name." WHERE goods_id=".intval( $goods_id ) );
                            $sql = $this->db->getupdatesql( $rs, array(
                                "cat_id" => $cat_id,
                                "brand_id" => $brand_id,
                                "cat_name" => $cat_name,
                                "brand_name" => $brand_name
                            ) );
                            if ( $sql )
                            {
                                $this->db->exec( $sql );
                            }
                        }
                        $sql = "REPLACE INTO ".$table_name."(`command_id`,`type`,`supplier_id`,`object_id`,`status`,`command`,`command_info`,`last_modify`,`command_type`,`if_show`,`cat_id`,`brand_id`,`goods_id`,`brand_name`,`cat_name`,`name`,`bn`,`marketable`,`store`) VALUES({$command_id},'".$type.( "',".$supplier_id.",{$object_id},'" ).$status.( "',".$command.",'" ).addslashes( serialize( $command_info ) )."','".$last_modify."','".$command_type."','".$show."',".intval( $cat_id ).",".intval( $brand_id ).",".intval( $goods_id ).",'".$brand_name."','".$cat_name."','".$name."','".$bn."','".$marketable."',".$store.")";
                        $this->db->exec( $sql );
                        if ( $auto_download != "true" && $show == "true" )
                        {
                            $oAutoSync = $this->system->loadmodel( "distribution/autosync" );
                            $oAutoSync->addautosynctask( $supplier_id, $command_id );
                        }
                        if ( !( $auto_download == "true" ) && !( $type == "goods" ) && !( $command == 6 ) )
                        {
                            $supplier_pline = unserialize( $job['supplier_pline'] );
                            $flag = $this->_checkinpline( $brand_id, $cat_id, $supplier_id, $supplier_pline );
                            if ( $flag )
                            {
                                $this->addgoodsdownloadjob( $command_id, $job['supplier_id'], $object_id, $count, $job['to_cat_id'] );
                            }
                        }
                    }
                }
            }
            $this->db->exec( "DELETE FROM sdb_job_data_sync WHERE job_id=".intval( $job['job_id'] ) );
            return 1;
        }
        $this->_updatelock( "data_sync", false );
        return 0;
    }

    function dogoodsdownloadjob( )
    {
        $job = $this->db->selectrow( "SELECT * FROM sdb_job_goods_download WHERE failed='false' ORDER BY job_id ASC" );
        $log_file = HOME_DIR."/logs/goodsdown.log";
        if ( !empty( $job ) )
        {
            $this->_updatelock( "download_goods" );
            $return = "";
            $supplier_id = $job['supplier_id'];
            $log_info = json_decode( file_get_contents( $log_file ), true );
            $datasync = $this->system->loadmodel( "distribution/datasync" );
            $datasync->downloadgoods( $job['command_id'], $supplier_id, $job['supplier_goods_id'], $job['to_cat_id'] );
            if ( !isset( $log_info[$supplier_id] ) )
            {
                $log_info[$supplier_id] = array(
                    "current" => 1,
                    "count" => $job['supplier_goods_count'],
                    "cat_id" => $job['to_cat_id']
                );
                file_put_contents( $log_file, json_encode( $log_info ), LOCK_EX );
                $return = array(
                    "supplier_id" => $supplier_id,
                    "current" => 1,
                    "count" => $job['supplier_goods_count'],
                    "cat_id" => $job['to_cat_id']
                );
            }
            else
            {
                if ( $log_info[$supplier_id]['current'] + 1 < $log_info[$supplier_id]['count'] )
                {
                    $log_info[$supplier_id]['current'] += 1;
                    file_put_contents( $log_file, json_encode( $log_info ), LOCK_EX );
                }
                else if ( $log_info[$supplier_id]['current'] + 1 == $log_info[$supplier_id]['count'] )
                {
                    $log_info[$supplier_id]['current'] += 1;
                    unset( $log_info->$supplier_id );
                    file_put_contents( $log_file, json_encode( $log_info ), LOCK_EX );
                }
                else
                {
                    unset( $log_info->$supplier_id );
                    file_put_contents( $log_file, json_encode( $log_info ), LOCK_EX );
                }
                $return = array(
                    "supplier_id" => $supplier_id,
                    "current" => $log_info[$supplier_id]['current'],
                    "count" => $log_info[$supplier_id]['count'],
                    "cat_id" => $job['to_cat_id']
                );
            }
            $this->db->exec( "DELETE FROM sdb_job_goods_download WHERE job_id=".intval( $job['job_id'] ) );
            $table = "sdb_data_sync_".$supplier_id;
            $rs = $this->db->query( "SELECT * FROM ".$table." WHERE command_id=".intval( $job['command_id'] ) );
            $sql = $this->db->getupdatesql( $rs, array( "status" => "unmodified" ) );
            $this->db->exec( $sql );
            return $return;
        }
        $this->_updatelock( "download_goods", false );
        return 0;
    }

    function insertimagesynclist( $command_id, $type, $supplier_id, $object_id, $time = NULL )
    {
        $aData = array(
            "type" => $type,
            "supplier_id" => $supplier_id,
            "supplier_object_id" => $object_id,
            "add_time" => is_null( $time ) ? time( ) : $time,
            "command_id" => $command_id
        );
        $rs = $this->db->query( "SELECT * FROM sdb_image_sync WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $aData );
        return $this->db->exec( $sql );
    }

    function downloadimage( $retry = false, $command_id = NULL )
    {
        $image_type = array( "1" => "gif", "2" => "jpg", "3" => "png", "6" => "bmp" );
        $sql = "SELECT * FROM sdb_image_sync WHERE 1=1 ";
        if ( $retry )
        {
            $sql .= " AND failed='true'";
        }
        else
        {
            $sql .= " AND failed='false'";
        }
        if ( !is_null( $command_id ) )
        {
            $sql .= " AND command_id=".intval( $command_id );
        }
        $sql .= " ORDER BY add_time ASC,img_sync_id ASC";
        $image = $this->db->selectrow( $sql );
        if ( !empty( $image ) )
        {
            $this->_updatelock( "download_image" );
            $filename = "";
            $type = $image['type'];
            $supplier_id = $image['supplier_id'];
            $object_id = $image['supplier_object_id'];
            switch ( $type )
            {
            case "gimage" :
                $dir = HOME_DIR."/upload/gpic";
                if ( !is_dir( $dir ) )
                {
                    mkdir( $dir, 511 );
                }
                $dir = HOME_DIR."/upload/gpic/".date( "Ymd" );
                if ( !is_dir( $dir ) )
                {
                    mkdir( $dir, 511 );
                }
                $filename = $dir."/".md5( $supplier_id.$object_id );
                $p_type = "gimage";
                break;
            case "spec_value" :
                $filename = MEDIA_DIR."/default/spec-".md5( $supplier_id.$object_id );
                $p_type = "spec";
                break;
            case "udfimg" :
                $dir = MEDIA_DIR."/goods/".date( "Ymd" );
                if ( !is_dir( $dir ) )
                {
                    mkdir( $dir, 511 );
                }
                $filename = $dir."/".md5( $supplier_id.$object_id );
                $p_type = "udfimg";
                break;
            case "brand_logo" :
                $dir = MEDIA_DIR."/brand";
                if ( !is_dir( $dir ) )
                {
                    mkdir( $dir, 511 );
                }
                $dir .= "/".date( "Ymd" );
                if ( !is_dir( $dir ) )
                {
                    mkdir( $dir, 511 );
                }
                $filename = $dir."/".md5( $supplier_id.$object_id );
                $p_type = "brand";
            }
            $send_params = array(
                "supplier_id" => $supplier_id,
                "type" => $p_type,
                "id" => $object_id,
                "return_data" => "raw"
            );
            $token = $this->system->getconf( "certificate.token" );
            $img_api = $this->system->api_call( IMAGESERVER, IMAGESERVER_HOST, IMAGESERVER_PATH, IMAGESERVER_PORT, $token );
            $file = $img_api->getapidata( "getPicById", API_VERSION, $send_params );
            if ( $file === false )
            {
                if ( !is_null( $image['command_id'] ) )
                {
                    $table = "sdb_data_sync_".$supplier_id;
                    $rs = $this->db->query( "SELECT * FROM sdb_image_sync WHERE img_sync_id=".intval( $image['img_sync_id'] ) );
                    $sql = $this->db->getupdatesql( $rs, array( "failed" => "true" ) );
                    $this->db->exec( $sql );
                    $rs = $this->db->query( "SELECT * FROM ".$table." WHERE command_id=".intval( $image['command_id'] ) );
                    $sql = $this->db->getupdatesql( $rs, array( "img_down_failed" => "true" ) );
                    $this->db->exec( $sql );
                }
                return -1;
            }
            file_put_contents( $filename, $file );
            list( $img_width, $img_height, $img_type, $img_attr ) = getimagesize( $filename );
            $postfix = isset( $image_type[$img_type] ) ? $image_type[$img_type] : "jpg";
            rename( $filename, $filename.".".$postfix );
            $sql = "DELETE FROM sdb_image_sync WHERE img_sync_id=".$image['img_sync_id'];
            $this->db->exec( $sql );
            switch ( $type )
            {
            case "gimage" :
                $image_path = "gpic/".date( "Ymd" )."/".md5( $supplier_id.$object_id ).".".$postfix;
                $supplier_gimage_id = $object_id;
                $gimage_info = array(
                    "source" => $image_path,
                    "sync_time" => $image['add_time']
                );
                $rs = $this->db->query( "SELECT * FROM sdb_gimages WHERE supplier_id=".floatval( $supplier_id )." AND supplier_gimage_id=".intval( $supplier_gimage_id ) );
                $sql = $this->db->getupdatesql( $rs, addslashes_array( $gimage_info ) );
                $this->db->exec( $sql );
                $goods = $this->db->selectrow( "SELECT goods_id FROM sdb_gimages WHERE supplier_id=".floatval( $supplier_id )." AND supplier_gimage_id=".intval( $supplier_gimage_id )." ORDER BY goods_id DESC" );
                $local_goods_id = $goods['goods_id'];
                $goods_info = $this->db->selectrow( "SELECT goods_id,image_default,udfimg,spec_desc FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
                if ( !$this->_checkgenallimage( $supplier_id, $local_goods_id ) )
                {
                    break;
                }
                $gimage =& $this->system->loadmodel( "goods/gimage" );
                $gimage->gen_all_size_by_goods_id( $goods_info['goods_id'], $goods_info['image_default'], false );
                $goods_spec_desc = unserialize( $goods_info['spec_desc'] );
                if ( !empty( $goods_spec_desc ) )
                {
                    foreach ( $goods_spec_desc as $k1 => $v1 )
                    {
                        if ( !empty( $v1 ) )
                        {
                            foreach ( $v1 as $k2 => $v2 )
                            {
                                if ( !isset( $v2['spec_goods_images'] ) && empty( $v2['spec_goods_images'] ) )
                                {
                                    $spec_goods_images = explode( ",", $v2['spec_goods_images'] );
                                    $tmp_spec_goods_images = array( );
                                    foreach ( $spec_goods_images as $plat_gimage_id )
                                    {
                                        $tmp_gimage = $this->db->selectrow( "SELECT gimage_id FROM sdb_gimages WHERE supplier_id=".floatval( $supplier_id )." AND supplier_gimage_id=".intval( $plat_gimage_id ) );
                                        $tmp_spec_goods_images[] = $tmp_gimage['gimage_id'];
                                    }
                                    $goods_spec_desc[$k1][$k2]['spec_goods_images'] = implode( ",", $tmp_spec_goods_images );
                                }
                            }
                        }
                    }
                }
                $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
                $sql = $this->db->getupdatesql( $rs, array(
                    "spec_desc" => serialize( $goods_spec_desc )
                ) );
                $this->db->exec( $sql );
                $goods_gimage_info = $this->db->select( "SELECT * FROM sdb_gimages WHERE goods_id=".$local_goods_id );
                foreach ( $goods_gimage_info as $goods_gimage )
                {
                    if ( $this->db->selectrow( "SELECT img_sync_id FROM sdb_image_sync WHERE type='gimage' AND supplier_id=".floatval( $supplier_id )." AND supplier_object_id=".$goods_gimage['supplier_gimage_id']." AND failed='true'" ) )
                    {
                        $rs = $this->db->query( "SELECT * FROM sdb_gimages WHERE gimage_id=".$goods_gimage['gimage_id'] );
                        $sql = $this->db->getupdatesql( $rs, array( "small" => "", "big" => "", "thumbnail" => "" ) );
                        $this->db->exec( $sql );
                        if ( $goods_info['image_default'] == $goods_gimage['gimage_id'] )
                        {
                            $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
                            $sql = $this->db->getupdatesql( $rs, array( "thumbnail_pic" => "", "small_pic" => "", "big_pic" => "" ) );
                            $this->db->exec( $sql );
                        }
                    }
                }
                return 1;
            case "spec_value" :
                $image_path = "images/default/spec-".md5( $supplier_id.$object_id ).".".$postfix;
                $image_path = $image_path."|default/spec-".md5( $supplier_id.$object_id ).".".$postfix."|fs_storager";
                $supplier_spec_value_id = $object_id;
                $spec_value_info = array(
                    "spec_image" => $image_path
                );
                $rs = $this->db->query( "SELECT * FROM sdb_spec_values WHERE supplier_id=".floatval( $supplier_id )." AND supplier_spec_value_id=".intval( $supplier_spec_value_id ) );
                $sql = $this->db->getupdatesql( $rs, addslashes_array( $spec_value_info ) );
                $this->db->exec( $sql );
                return 1;
            case "udfimg" :
                $image_path = "images/goods/".date( "Ymd" )."/".md5( $supplier_id.$object_id ).".".$postfix;
                $image_path = $image_path."|/goods/".date( "Ymd" )."/".md5( $supplier_id.$object_id ).".".$postfix."|fs_storager";
                $goods_thumbnail_pic = array(
                    "thumbnail_pic" => $image_path
                );
                $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $object_id ) );
                $sql = $this->db->getupdatesql( $rs, addslashes_array( $goods_thumbnail_pic ) );
                $this->db->exec( $sql );
                return 1;
            case "brand_logo" :
                $image_path = "images/brand/".date( "Ymd" )."/".md5( $supplier_id.$object_id ).".".$postfix;
                $image_path = $image_path."|/brand/".date( "Ymd" )."/".md5( $supplier_id.$object_id ).".".$postfix."|fs_storager";
                $brand_logo_info = array(
                    "brand_logo" => $image_path
                );
                $rs = $this->db->query( "SELECT * FROM sdb_brand WHERE supplier_id=".floatval( $supplier_id )." AND supplier_brand_id=".intval( $object_id ) );
                $sql = $this->db->getupdatesql( $rs, addslashes_array( $brand_logo_info ) );
                $this->db->exec( $sql );
            }
            return 1;
        }
        $this->_updatelock( "download_image", false );
        return 0;
    }

    function addapilistjob( $supplier_id, $api_name, $api_params, $api_version, $api_action, $limit = 100 )
    {
        $data = $this->api->getapidata( $api_name, $api_version, array_merge( $api_params, array( "pages" => 1, "counts" => 1 ) ) );
        if ( !empty( $data ) )
        {
            $count = $data[0]['row_count'];
            $pages = ceil( $count / $limit );
            $i = 0;
            for ( ; $i < $pages; ++$i )
            {
                $data = array(
                    "supplier_id" => $supplier_id,
                    "api_name" => $api_name,
                    "api_params" => serialize( $api_params ),
                    "api_version" => $api_version,
                    "api_action" => $api_action,
                    "page" => $i + 1,
                    "limit" => $limit
                );
                $rs = $this->db->query( "SELECT * FROM sdb_job_apilist WHERE 0=1" );
                $sql = $this->db->getinsertsql( $rs, $data );
                if ( $sql )
                {
                    $this->db->exec( $sql );
                }
            }
        }
    }

    function doapilistjob( $supplier_id, $api_name, $api_version, $api_action )
    {
        $api_info = $this->db->selectrow( "SELECT * FROM sdb_job_apilist WHERE supplier_id=".floatval( $supplier_id )." AND api_name='".$api_name."' AND api_version='".$api_version."' AND api_action='".$api_action."'" );
        if ( !empty( $api_info ) )
        {
            $params = unserialize( $api_info['api_params'] );
            $params['pages'] = $api_info['page'];
            $params['counts'] = $api_info['limit'];
            $api_return = $this->api->getapidata( $api_info['api_name'], $api_info['api_version'], $params );
            $this->db->exec( "DELETE FROM sdb_job_apilist WHERE job_id=".$api_info['job_id'] );
            return $api_return;
        }
        return array( );
    }

    function _checkgenallimage( $supplier_id, $local_goods_id )
    {
        $gimages = $this->db->select( "SELECT supplier_gimage_id FROM sdb_gimages WHERE goods_id=".intval( $local_goods_id )." AND is_remote='false' AND supplier_id=".floatval( $supplier_id ) );
        if ( !empty( $gimages ) )
        {
            $supplier_gimage_ids = array( );
            foreach ( $gimages as $v )
            {
                $supplier_gimage_ids[] = $v['supplier_gimage_id'];
            }
            if ( !$this->db->select( "SELECT img_sync_id FROM sdb_image_sync WHERE type='gimage' AND failed='false' AND supplier_id=".floatval( $supplier_id )." AND supplier_object_id IN (".implode( ",", $supplier_gimage_ids ).")" ) )
            {
                return true;
            }
            return false;
        }
        return false;
    }

    function _updatelock( $job_name, $lock = true )
    {
        $session_id = $this->system->session->sess_id;
        if ( !is_dir( HOME_DIR."/lock" ) )
        {
            mkdir( HOME_DIR."/lock", 511 );
        }
        $lock_file = HOME_DIR."/lock/job.lock";
        $time = time( );
        if ( !file_exists( $lock_file ) )
        {
            touch( $lock_file );
        }
        $tmp_lock = trim( file_get_contents( $lock_file ) );
        if ( !empty( $tmp_lock ) )
        {
            $lock_info = unserialize( $tmp_lock );
        }
        else
        {
            $lock_info = array( );
        }
        $lock_info[$job_name] = array(
            "session_id" => $session_id,
            "lock_time" => $time,
            "if_lock" => $lock ? "true" : "false"
        );
        file_put_contents( $lock_file, serialize( $lock_info ) );
    }

    function checklock( $job_name )
    {
        if ( !is_dir( HOME_DIR."/lock" ) )
        {
            mkdir( HOME_DIR."/lock", 511 );
        }
        $lock_file = HOME_DIR."/lock/job.lock";
        $time = time( );
        if ( !file_exists( $lock_file ) )
        {
            touch( $lock_file );
        }
        $tmp_lock = trim( file_get_contents( $lock_file ) );
        if ( !empty( $tmp_lock ) )
        {
            $lock_info = unserialize( $tmp_lock );
            $lock_time = $lock_info[$job_name]['lock_time'];
            if ( $lock_info[$job_name]['if_lock'] == "true" )
            {
                if ( $lock_time + 30 <= $time )
                {
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    function _checkinpline( $brand_id, $cat_id, $supplier_id, $supplier_pline = NULL )
    {
        $flag = false;
        $pline_info = array( );
        if ( empty( $supplier_pline ) )
        {
            if ( $tmp_data = $this->db->selectrow( "SELECT supplier_pline FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) ) )
            {
                $supplier_pline = unserialize( $tmp_data['supplier_pline'] );
            }
            else
            {
                $supplier_pline = $this->api->getapidata( "getProductLineList", API_VERSION, array(
                    "id" => $supplier_id
                ), true, true );
                if ( !empty( $supplier_pline ) )
                {
                    foreach ( $supplier_pline as $k => $v )
                    {
                        $supplier_pline[$k]['cat_id'] .= $v['child_cat_path'] == "" ? "" : ",".$v['child_cat_path'];
                    }
                }
            }
        }
        if ( !empty( $supplier_pline ) || is_array( $supplier_pline ) )
        {
            foreach ( $supplier_pline as $k => $pline )
            {
                if ( $pline['cat_id'] == "-1" && $pline['brand_id'] == "-1" )
                {
                    $flag = true;
                }
                else if ( $pline['cat_id'] == "-1" )
                {
                    if ( $pline['brand_id'] == $brand_id )
                    {
                        $flag = true;
                        break;
                    }
                    else
                    {
                        $flag = false;
                    }
                }
                else if ( $pline['brand_id'] == "-1" )
                {
                    if ( in_array( $cat_id, explode( ",", $pline['cat_id'] ) ) )
                    {
                        $flag = true;
                        break;
                    }
                    else
                    {
                        $flag = false;
                    }
                }
                else if ( $pline['brand_id'] == $brand_id && in_array( $cat_id, explode( ",", $pline['cat_id'] ) ) )
                {
                    $flag = true;
                }
                else
                {
                    $flag = false;
                }
            }
            return $flag;
        }
        $flag = false;
        return $flag;
    }

    function getcommandbypline( $supplier_id, $supplier_pline, &$count, $offset = 0, $limit = 100 )
    {
        if ( !empty( $supplier_pline ) || is_array( $supplier_pline ) )
        {
            $sql = "SELECT command_id,object_id FROM sdb_data_sync_".$supplier_id." WHERE command=6 AND status='unoperated' AND command_type='download' ";
            $p_where = array( );
            foreach ( $supplier_pline as $pline )
            {
                if ( $pline['cat_id'] == "-1" && $pline['brand_id'] == "-1" )
                {
                    if ( $pline['cat_id'] == "-1" )
                    {
                        $p_where[] = " brand_id=".intval( $pline['brand_id'] );
                    }
                    else if ( $pline['brand_id'] == "-1" )
                    {
                        $p_where[] = " cat_id IN (".$pline['cat_id'].")";
                    }
                    else
                    {
                        $p_where[] = " cat_id IN(".$pline['cat_id'].") AND brand_id=".intval( $pline['brand_id'] );
                    }
                }
            }
            if ( $p_where )
            {
                $sql .= " AND (".implode( " OR ", $p_where ).")";
            }
            $count = $this->db->count( $sql );
            $sql .= " ORDER BY command_id ";
            $sql .= " LIMIT ".$limit." OFFSET ".$offset;
            error_log( $sql."\n", 3, "e:/log.txt" );
            return $this->db->select( $sql );
        }
        return array( );
    }

    function getdownloadcount( $data )
    {
        if ( !isset( $this->tmp_count ) )
        {
            $supplier_pline = unserialize( $data['supplier_pline'] );
            $count = 0;
            $this->getcommandbypline( $data['supplier_id'], $supplier_pline, $count, 0, 1 );
            foreach ( $supplier_pline as $k => $pline )
            {
                $pline_id[] = $k;
            }
            $supplier_id = $data['supplier_id'];
            if ( $data['to_time'] == 0 )
            {
                $supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
                $last_sync_time = $supplier_info['sync_time_for_plat'];
                $sync_list = $this->api->getapidata( "getUpdateList", API_VERSION, array(
                    "pages" => 1,
                    "counts" => 1,
                    "supplier_id" => $supplier_id,
                    "last_sync_time" => $data['from_time']
                ), true, true );
                if ( !empty( $sync_list ) )
                {
                    $from_time = $data['from_time'];
                    $to_time = $last_sync_time;
                }
                else
                {
                    $this->tmp_count = $count;
                    return $this->tmp_count;
                }
            }
            else
            {
                $from_time = $data['from_time'];
                $to_time = $data['to_time'];
            }
            $down_params = array(
                "supplier_id" => $supplier_id,
                "last_sync_time" => $from_time,
                "last_sync_time_end" => $to_time,
                "cmd_action" => 6,
                "pline" => $pline_id
            );
            $goods_count = $this->api->getapidata( "getUpdateListCount", API_VERSION, $down_params, true, true );
            $count += $goods_count['row_count'];
            $this->tmp_count = $count;
        }
        return $this->tmp_count;
    }

}

?>
