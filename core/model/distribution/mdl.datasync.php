<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_datasync extends modelfactory
{

    function mdl_datasync( )
    {
        modelfactory::modelfactory( );
        $token = $this->system->getconf( "certificate.token" );
        $this->api = $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $token );
    }

    function checkgoodsdownload( $supplier_id, $goods_id )
    {
        $brand_flag = false;
        $type_flag = false;
        $spec_flag = false;
        if ( $tmp_data = $this->db->selectrow( "SELECT command_info FROM sdb_data_sync_".floatval( $supplier_id )." WHERE command=4 AND goods_id=".intval( $goods_id ) ) )
        {
            $goods_info_tmp = unserialize( $tmp_data['command_info'] );
            $goods_info = $goods_info_tmp['goods_info'];
        }
        else
        {
            $goods_info = $this->api->getapidata( "getGoodsByID", API_VERSION, array(
                "supplier_id" => $supplier_id,
                "id" => $goods_id
            ), true, true );
        }
        $brand_id = $goods_info['brand_id'];
        $type_id = $goods_info['type_id'];
        $spec_info = $this->api->getapidata( "getSpecificationByGoodsID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $goods_id
        ), true, true );
        $brand_flag = $this->downloadbrand( $supplier_id, $brand_id, NULL, false );
        $type_flag = $this->downloadtype( $supplier_id, $type_id, false );
        if ( empty( $spec_info ) )
        {
            $spec_flag = false;
        }
        else
        {
            foreach ( $spec_info as $spec )
            {
                if ( !$this->downloadspecification( $supplier_id, $spec, NULL, false ) )
                {
                    continue;
                }
                $spec_flag = true;
                break;
            }
        }
        return $brand_flag || $type_flag || $spec_flag;
    }

    function downloadcat( $cat_id, $supplier_id, $supplier_cat_id, $flag = true )
    {
        $if_download = false;
        $return_cat = array( );
        $cat_id = intval( $cat_id );
        if ( $tmp_data = $this->db->selectrow( "SELECT * FROM sdb_sync_tmp WHERE supplier_id=".floatval( $supplier_id )." AND s_type='goods_cat' AND ob_id=".intval( $supplier_cat_id ) ) )
        {
            $plat_cat_info = unserialize( $tmp_data['s_data'] );
        }
        else
        {
            $plat_cat_info = $this->api->getapidata( "getCategoryByID", API_VERSION, array(
                "supplier_id" => $supplier_id,
                "id" => $supplier_cat_id
            ), true, true );
        }
        if ( !empty( $plat_cat_info ) )
        {
            $plat_cat_path = explode( ",", $plat_cat_info['cat_path'] );
            if ( $cat_id != 0 )
            {
                $sql = "SELECT * FROM sdb_goods_cat WHERE cat_id=".intval( $cat_id );
                $local_current_cat = $this->db->selectrow( $sql );
            }
            $sql = "SELECT * FROM sdb_goods_cat WHERE parent_id=".intval( $cat_id );
            $local_cats = $this->db->select( $sql );
            if ( $local_cats )
            {
                $plat_cat_len = count( $plat_cat_path );
                if ( empty( $plat_cat_path[0] ) )
                {
                    foreach ( $local_cats as $v )
                    {
                        if ( strcasecmp( trim( $v['cat_name'] ), trim( $plat_cat_info['cat_name'] ) ) == 0 )
                        {
                            $if_download = false;
                            $return_cat_id = $v['cat_id'];
                        }
                        else
                        {
                            $if_download = true;
                        }
                    }
                }
                else
                {
                    $tmp_cat_ids = array( );
                    $i = 0;
                    for ( ; $i < $plat_cat_len; ++$i )
                    {
                        if ( empty( $plat_cat_path[$i] ) )
                        {
                            break;
                        }
                        if ( $tmp_data = $this->db->selectrow( "SELECT * FROM sdb_sync_tmp WHERE supplier_id=".floatval( $supplier_id )." AND s_type='goods_cat' AND ob_id=".intval( $plat_cat_path[$i] ) ) )
                        {
                            $tmp_plat_cat_info = unserialize( $tmp_data['s_data'] );
                        }
                        else
                        {
                            $tmp_plat_cat_info = $this->api->getapidata( "getCategoryByID", API_VERSION, array(
                                "supplier_id" => $supplier_id,
                                "id" => $plat_cat_path[$i]
                            ), true, true );
                        }
                        if ( empty( $tmp_cat_ids ) )
                        {
                            $tmp_local_cats = $local_cats;
                        }
                        else
                        {
                            $sql = "SELECT * FROM sdb_goods_cat WHERE parent_id IN (".implode( ",", $tmp_cat_ids ).")";
                            $tmp_local_cats = $this->db->select( $sql );
                            $tmp_cat_ids = array( );
                            if ( empty( $tmp_local_cats ) )
                            {
                                $if_download = true;
                                break;
                            }
                        }
                        foreach ( $tmp_local_cats as $v )
                        {
                            if ( strcasecmp( trim( $v['cat_name'] ), trim( $tmp_plat_cat_info['cat_name'] ) ) == 0 )
                            {
                                $tmp_cat_ids[] = $v['cat_id'];
                            }
                        }
                        if ( empty( $tmp_cat_ids ) )
                        {
                            $if_download = true;
                        }
                        else
                        {
                            unset( $tmp_plat_cat_info );
                            unset( $tmp_local_cats );
                        }
                    }
                    if ( !$if_download )
                    {
                        $if_download = true;
                        $sql = "SELECT * FROM sdb_goods_cat WHERE parent_id IN (".implode( ",", $tmp_cat_ids ).")";
                        $tmp_local_cats = $this->db->select( $sql );
                        if ( !empty( $tmp_local_cats ) )
                        {
                            foreach ( $tmp_local_cats as $v )
                            {
                                if ( !( strcasecmp( trim( $v['cat_name'] ), trim( $plat_cat_info['cat_name'] ) ) == 0 ) )
                                {
                                    continue;
                                }
                                $if_download = false;
                                $return_cat_id = $v['cat_id'];
                                break;
                            }
                        }
                    }
                }
            }
            else
            {
                $if_download = true;
            }
            if ( $flag )
            {
                if ( $if_download )
                {
                    $myparent_id = $cat_id;
                    if ( $cat_id != 0 )
                    {
                        if ( $local_current_cat['cat_path'] == "," )
                        {
                            $mycat_path = $local_current_cat['cat_id'].",";
                        }
                        else
                        {
                            $mycat_path = $local_current_cat['cat_path'].$local_current_cat['cat_id'].",";
                        }
                    }
                    else
                    {
                        $mycat_path = ",";
                    }
                    $plat_cat_len = count( $plat_cat_path );
                    $i = 0;
                    for ( ; $i < $plat_cat_len; ++$i )
                    {
                        if ( empty( $plat_cat_path[$i] ) )
                        {
                            break;
                        }
                        $plat_cat_id = $plat_cat_path[$i];
                        if ( $tmp_data = $this->db->selectrow( "SELECT * FROM sdb_sync_tmp WHERE supplier_id=".floatval( $supplier_id )." AND s_type='goods_cat' AND ob_id=".intval( $plat_cat_path[$i] ) ) )
                        {
                            $tmp_plat_cat_info = unserialize( $tmp_data['s_data'] );
                        }
                        else
                        {
                            $tmp_plat_cat_info = $this->api->getapidata( "getCategoryByID", API_VERSION, array(
                                "supplier_id" => $supplier_id,
                                "id" => $plat_cat_path[$i]
                            ), true, true );
                        }
                        if ( $tmp_local_cats_info = $this->db->selectrow( "SELECT cat_id FROM sdb_goods_cat WHERE cat_name='".addslashes( $tmp_plat_cat_info['cat_name'] )."' AND parent_id=".$myparent_id ) )
                        {
                            $myparent_id = $tmp_local_cats_info['cat_id'];
                        }
                        else
                        {
                            $new_plat_cat_info = array(
                                "parent_id" => $myparent_id,
                                "supplier_id" => $supplier_id,
                                "supplier_cat_id" => $plat_cat_path[$i],
                                "cat_path" => $mycat_path,
                                "is_leaf" => "false",
                                "cat_name" => $tmp_plat_cat_info['cat_name'],
                                "p_order" => $tmp_plat_cat_info['p_order'],
                                "goods_count" => $tmp_plat_cat_info['goods_count'],
                                "tabs" => $tmp_plat_cat_info['tabs'],
                                "finder" => $tmp_plat_cat_info['finder'],
                                "addon" => $tmp_plat_cat_info['addon'],
                                "child_count" => 0
                            );
                            $rs = $this->db->query( "SELECT * FROM sdb_goods_cat WHERE 0=1" );
                            $sql = $this->db->getinsertsql( $rs, $new_plat_cat_info );
                            if ( $sql && !$this->db->exec( $sql ) )
                            {
                                trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
                                return false;
                            }
                            $tmp_myparent_id = $myparent_id;
                            $myparent_id = $this->db->lastinsertid( );
                            $this->db->exec( "UPDATE sdb_goods_cat SET child_count=child_count+1, is_leaf='false' WHERE cat_id=".intval( $tmp_myparent_id ) );
                        }
                        $return_cat_path[] = $tmp_plat_cat_info['cat_name'];
                        if ( $mycat_path == "," )
                        {
                            $mycat_path = $myparent_id.",";
                        }
                        else
                        {
                            $mycat_path .= $myparent_id.",";
                        }
                    }
                    $new_plat_cat_info = array(
                        "parent_id" => $myparent_id,
                        "supplier_id" => $supplier_id,
                        "supplier_cat_id" => $supplier_cat_id,
                        "cat_path" => $mycat_path,
                        "is_leaf" => "true",
                        "cat_name" => $plat_cat_info['cat_name'],
                        "p_order" => $plat_cat_info['p_order'],
                        "goods_count" => $plat_cat_info['goods_count'],
                        "tabs" => $plat_cat_info['tabs'],
                        "finder" => $plat_cat_info['finder'],
                        "addon" => $plat_cat_info['addon'],
                        "child_count" => 0
                    );
                    $return_cat_path[] = $plat_cat_info['cat_name'];
                    $rs = $this->db->query( "SELECT * FROM sdb_goods_cat WHERE 0=1" );
                    $sql = $this->db->getinsertsql( $rs, $new_plat_cat_info );
                    if ( $sql && !$this->db->exec( $sql ) )
                    {
                        trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
                        return false;
                    }
                    $return_cat_id = $this->db->lastinsertid( );
                    $this->db->exec( "UPDATE sdb_goods_cat SET child_count=child_count+1, is_leaf='false' WHERE cat_id=".intval( $myparent_id ) );
                }
                $return_cat['cat_id'] = $return_cat_id;
                $return_cat['cat_path'] = isset( $return_cat_path ) ? implode( "|", $return_cat_path ) : "";
                return $return_cat;
            }
            $local_cat_id = $return_cat_id;
            if ( $if_download )
            {
                return array(
                    "if_download" => true
                );
            }
            return array(
                "if_download" => false,
                "local_cat_id" => $local_cat_id
            );
        }
        return false;
    }

    function downloadspecification( $supplier_id, $spec_info, $command_id = NULL, $flag = true )
    {
        $return_spec_info = array( );
        $if_download = false;
        $sql = "SELECT * FROM sdb_specification WHERE supplier_id=".floatval( $supplier_id )." AND supplier_spec_id=".intval( $spec_info['spec_id'] )." ORDER BY spec_id DESC";
        $local_spec = $this->db->selectrow( $sql );
        if ( empty( $local_spec ) )
        {
            $if_download = true;
        }
        else if ( $local_spec['lastmodify'] < $spec_info['last_modified'] )
        {
            $if_download = true;
        }
        else
        {
            $if_download = false;
        }
        if ( $flag )
        {
            if ( $if_download )
            {
                $new_spec_info = array(
                    "spec_name" => $spec_info['spec_name'],
                    "spec_type" => $spec_info['spec_type'],
                    "spec_memo" => $spec_info['spec_memo'],
                    "p_order" => $spec_info['p_order'],
                    "supplier_id" => $supplier_id,
                    "supplier_spec_id" => $spec_info['spec_id'],
                    "lastmodify" => $spec_info['last_modified']
                );
                $rs = $this->db->query( "SELECT * FROM sdb_specification WHERE 0=1" );
                $sql = $this->db->getinsertsql( $rs, $new_spec_info );
                if ( $sql && !$this->db->exec( $sql ) )
                {
                    trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
                    return false;
                }
                $spec_id = $this->db->lastinsertid( );
                $return_spec_info = array(
                    "download" => true,
                    "spec_id" => $spec_id,
                    "spec_name" => $spec_info['spec_name'],
                    "supplier_id" => $supplier_id,
                    "supplier_spec_id" => $spec_info['spec_id'],
                    "detail" => array( )
                );
                $this->local_spec[md5( $supplier_id.$spec_info['supplier_spec_id']."spec" )] = $spec_id;
                if ( !empty( $spec_info['struct'] ) )
                {
                    $sync_job = $this->system->loadmodel( "distribution/syncjob" );
                    foreach ( $spec_info['struct'] as $spec_value )
                    {
                        $spec_value_info = array(
                            "spec_id" => $spec_id,
                            "spec_value" => $spec_value['spec_value'],
                            "spec_image" => $spec_value['spec_image'],
                            "p_order" => $spec_value['p_order'],
                            "supplier_id" => $supplier_id,
                            "supplier_spec_value_id" => $spec_value['spec_value_id']
                        );
                        $rs = $this->db->query( "SELECT * FROM sdb_spec_values WHERE 0=1" );
                        $sql = $this->db->getinsertsql( $rs, $spec_value_info );
                        $this->db->exec( $sql );
                        $spec_value_id = $this->db->lastinsertid( );
                        $return_spec_info['detail'][] = array_merge( $spec_value_info, array(
                            "spec_value_id" => $spec_value_id
                        ) );
                        $this->local_spec_value[md5( $spec_id.$supplier_id.$spec_value['spec_value_id']."spec_value" )] = $spec_value_id;
                        if ( $spec_info['spec_type'] == "image" )
                        {
                            $type = "spec_value";
                            $object_id = $spec_value['spec_value_id'];
                            $sync_job->insertimagesynclist( $command_id, $type, $supplier_id, $object_id );
                        }
                    }
                }
                return $return_spec_info;
            }
            $return_spec_info = array(
                "download" => false,
                "spec_id" => $local_spec['spec_id'],
                "spec_name" => $local_spec['spec_name'],
                "supplier_id" => $supplier_id,
                "supplier_spec_id" => $spec_info['spec_id'],
                "detail" => array( )
            );
            return $return_spec_info;
        }
        return $if_download;
    }

    function downloadbrand( $supplier_id, $supplier_brand_id, $command_id = NULL, $flag = true )
    {
        $return_brand = array( );
        $if_merge = false;
        $action = "";
        if ( $tmp_data = $this->db->selectrow( "SELECT * FROM sdb_sync_tmp WHERE supplier_id=".floatval( $supplier_id )." AND s_type='brand' AND ob_id=".intval( $supplier_brand_id ) ) )
        {
            $brand_info = unserialize( $tmp_data['s_data'] );
        }
        else
        {
            $brand_info = $this->api->getapidata( "getBrandByID", API_VERSION, array(
                "supplier_id" => $supplier_id,
                "id" => $supplier_brand_id
            ), true, true );
        }
        if ( !empty( $brand_info ) )
        {
            $sql = "SELECT * FROM sdb_brand WHERE disabled='false' AND  brand_name='".addslashes( $brand_info['brand_name'] )."'";
            $local_brand = $this->db->selectrow( $sql );
            if ( empty( $local_brand ) )
            {
                $action = "add";
            }
            else
            {
                if ( !empty( $brand_info['brand_keywords'] ) )
                {
                    $sql .= " AND brand_keywords='".addslashes( $brand_info['brand_keywords'] )."'";
                }
                else
                {
                    $sql .= " AND (brand_keywords IS NULL OR brand_keywords='')";
                }
                if ( $local_brand = $this->db->selectrow( $sql ) )
                {
                    $action = "update";
                }
                else
                {
                    $action = "add";
                }
            }
            $return_brand['action'] = $action;
            if ( $action == "update" )
            {
                $local_brand_update = array( );
                if ( empty( $local_brand['brand_url'] ) && !empty( $brand_info['brand_url'] ) )
                {
                    $local_brand_update['brand_url'] = $brand_info['brand_url'];
                    $if_merge = true;
                }
                if ( empty( $local_brand['brand_logo'] ) && !empty( $brand_info['brand_logo'] ) )
                {
                    $local_brand_update['brand_logo'] = $brand_info['brand_logo'];
                    if ( $flag && !$this->_checkremoteimage( $brand_info['brand_logo'] ) )
                    {
                        $sync_job = $this->system->loadmodel( "distribution/syncjob" );
                        $type = "brand_logo";
                        $object_id = $supplier_brand_id;
                        $sync_job->insertimagesynclist( $command_id, $type, $supplier_id, $object_id );
                    }
                    $if_merge = true;
                }
                if ( empty( $local_brand['brand_desc'] ) && !empty( $brand_info['brand_desc'] ) )
                {
                    $local_brand_update['brand_desc'] = $brand_info['brand_desc'];
                    $if_merge = true;
                }
                if ( $flag )
                {
                    if ( !empty( $local_brand_update ) )
                    {
                        $rs = $this->db->query( "SELECT * FROM sdb_brand WHERE brand_id=".$local_brand['brand_id'] );
                        $sql = $this->db->getupdatesql( $rs, $local_brand_update );
                        $this->db->exec( $sql );
                    }
                    else
                    {
                        $return_brand['action'] = "none";
                    }
                    $brand_id = $local_brand['brand_id'];
                    $return_brand['brand_id'] = $brand_id;
                    $return_brand['brand_name'] = $local_brand['brand_name'];
                    $return_brand['brand_keywords'] = $local_brand['brand_keywords'];
                    return $return_brand;
                }
                return $if_merge;
            }
            if ( $action == "add" )
            {
                if ( $flag )
                {
                    $new_brand_info = array(
                        "supplier_id" => $supplier_id,
                        "supplier_brand_id" => $supplier_brand_id,
                        "brand_url" => $brand_info['brand_url'],
                        "brand_logo" => $brand_info['brand_logo'],
                        "brand_name" => $brand_info['brand_name'],
                        "brand_desc" => $brand_info['brand_desc'],
                        "brand_keywords" => $brand_info['brand_keywords']
                    );
                    $rs = $this->db->query( "SELECT * FROM sdb_brand WHERE 0=1" );
                    $sql = $this->db->getinsertsql( $rs, $new_brand_info );
                    if ( $sql && !$this->db->exec( $sql ) )
                    {
                        trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
                        return false;
                    }
                    $brand_id = $this->db->lastinsertid( );
                    if ( !$this->_checkremoteimage( $brand_info['brand_logo'] ) )
                    {
                        $sync_job = $this->system->loadmodel( "distribution/syncjob" );
                        $type = "brand_logo";
                        $object_id = $supplier_brand_id;
                        $sync_job->insertimagesynclist( $command_id, $type, $supplier_id, $object_id );
                    }
                    $return_brand['brand_id'] = $brand_id;
                    $return_brand['brand_name'] = $brand_info['brand_name'];
                    $return_brand['brand_keywords'] = $brand_info['brand_keywords'];
                    return $return_brand;
                }
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    function downloadtype( $supplier_id, $supplier_type_id, $flag = true )
    {
        $return_type = array( );
        $if_download = false;
        if ( $tmp_data = $this->db->selectrow( "SELECT * FROM sdb_sync_tmp WHERE supplier_id=".floatval( $supplier_id )." AND s_type='goods_type' AND ob_id=".intval( $supplier_type_id ) ) )
        {
            $type_info = unserialize( $tmp_data['s_data'] );
        }
        else
        {
            $type_info = $this->api->getapidata( "getTypeByID", API_VERSION, array(
                "supplier_id" => $supplier_id,
                "id" => $supplier_type_id
            ), true, true );
        }
        if ( empty( $type_info ) )
        {
            return false;
        }
        $sql = "SELECT * FROM sdb_goods_type WHERE supplier_id=".floatval( $supplier_id )." AND supplier_type_id=".intval( $supplier_type_id )." ORDER BY type_id DESC";
        $local_type = $this->db->selectrow( $sql );
        if ( empty( $local_type ) )
        {
            $if_download = true;
        }
        else if ( $local_type['lastmodify'] < $type_info['last_modify'] )
        {
            $if_download = true;
        }
        else
        {
            $if_download = false;
        }
        if ( $flag )
        {
            if ( $if_download )
            {
                $new_type_info = array(
                    "name" => $type_info['name'],
                    "alias" => $type_info['alias'],
                    "is_physical" => $type_info['is_physical'],
                    "supplier_id" => $supplier_id,
                    "supplier_type_id" => $supplier_type_id,
                    "setting" => $type_info['setting'],
                    "params" => $type_info['params'],
                    "ret_func" => $type_info['ret_func'],
                    "spec" => $type_info['spec'],
                    "minfo" => $type_info['minfo'],
                    "dly_func" => $type_info['dly_func'],
                    "props" => $type_info['props'],
                    "schema_id" => "custom",
                    "lastmodify" => $type_info['last_modify']
                );
                $rs = $this->db->query( "SELECT * FROM sdb_goods_type WHERE 0=1" );
                $sql = $this->db->getinsertsql( $rs, $new_type_info );
                if ( $sql && !$this->db->exec( $sql ) )
                {
                    trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
                    return false;
                }
                $type_id = $this->db->lastinsertid( );
                $return_type = array(
                    "download" => true,
                    "type_id" => $type_id,
                    "name" => $type_info['name']
                );
                return $return_type;
            }
            $return_type = array(
                "download" => false,
                "type_id" => $local_type['type_id']
            );
            return $return_type;
        }
        return $if_download;
    }

    function bindspecwithtype( $supplier_id, $supplier_spec_id, $spec_id )
    {
        $return = array( );
        $plat_bind_type_info = $this->api->getapidata( "getTypeBySpecId", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_spec_id
        ), true, true );
        if ( !empty( $plat_bind_type_info ) )
        {
            foreach ( $plat_bind_type_info as $type )
            {
                $down_type = $this->downloadtype( $supplier_id, $type['type_id'] );
                if ( !empty( $down_type ) )
                {
                    if ( $down_type['download'] )
                    {
                        $return[$down_type['type_id']] = $down_type['name'];
                    }
                    $bind_type_id = $down_type['type_id'];
                    if ( !$this->db->selectrow( "SELECT * FROM sdb_goods_type_spec WHERE spec_id=".intval( $spec_id )." AND type_id=".intval( $bind_type_id ) ) )
                    {
                        $rs = $this->db->query( "SELECT * FROM sdb_goods_type_spec WHERE 0=1" );
                        $sql = $this->db->getinsertsql( $rs, array(
                            "spec_id" => $spec_id,
                            "type_id" => $bind_type_id
                        ) );
                        $this->db->exec( $sql );
                    }
                }
            }
        }
        return $return;
    }

    function bindbrandwithtype( $supplier_id, $supplier_brand_id, $brand_id )
    {
        $return = array( );
        $plat_bind_type_info = $this->api->getapidata( "getTypeByBrandId", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_brand_id
        ), true, true );
        if ( !empty( $plat_bind_type_info ) )
        {
            foreach ( $plat_bind_type_info as $type )
            {
                $down_type = $this->downloadtype( $supplier_id, $type['type_id'] );
                if ( !empty( $down_type ) )
                {
                    if ( $down_type['download'] )
                    {
                        $return[$down_type['type_id']] = $down_type['name'];
                    }
                    $bind_type_id = $down_type['type_id'];
                    if ( !$this->db->selectrow( "SELECT * FROM sdb_type_brand WHERE type_id=".intval( $bind_type_id )." AND brand_id=".intval( $brand_id ) ) )
                    {
                        $rs = $this->db->query( "SELECT * FROM sdb_type_brand WHERE 0=1" );
                        $sql = $this->db->getinsertsql( $rs, array(
                            "type_id" => $bind_type_id,
                            "brand_id" => $brand_id
                        ) );
                        $this->db->exec( $sql );
                    }
                }
            }
        }
        return $return;
    }

    function bindtypewithspec( $supplier_id, $supplier_type_id, $type_id, $command_id )
    {
        $return = array( );
        $plat_bind_spec_info = $this->api->getapidata( "getSpecificationByTypeId", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_type_id
        ), true, true );
        if ( !empty( $plat_bind_spec_info ) )
        {
            foreach ( $plat_bind_spec_info as $spec )
            {
                $down_spec = $this->downloadspecification( $supplier_id, $spec, $command_id );
                if ( $down_spec['download'] )
                {
                    $return[$down_spec['spec_id']] = $down_spec['spec_name'];
                }
                $bind_spec_id = $down_spec['spec_id'];
                if ( !$this->db->selectrow( "SELECT * FROM sdb_goods_type_spec WHERE spec_id=".intval( $bind_spec_id )." AND type_id=".intval( $type_id ) ) )
                {
                    $rs = $this->db->query( "SELECT * FROM sdb_goods_type_spec WHERE 0=1" );
                    $sql = $this->db->getinsertsql( $rs, array(
                        "spec_id" => $bind_spec_id,
                        "type_id" => $type_id
                    ) );
                    $this->db->exec( $sql );
                }
            }
        }
        return $return;
    }

    function bindtypewithbrand( $supplier_id, $supplier_type_id, $type_id, $command_id )
    {
        $return = array( );
        $plat_bind_brand_info = $this->api->getapidata( "getBrandByTypeID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_type_id
        ), true, true );
        if ( !empty( $plat_bind_brand_info ) )
        {
            foreach ( $plat_bind_brand_info as $brand )
            {
                $down_brand = $this->downloadbrand( $supplier_id, $brand['brand_id'], $command_id );
                if ( !empty( $down_brand ) )
                {
                    $return[$down_brand['action']][$down_brand['brand_id']] = $down_brand['brand_name'];
                    $bind_brand_id = $down_brand['brand_id'];
                    if ( !$this->db->selectrow( "SELECT * FROM sdb_type_brand WHERE type_id=".intval( $type_id )." AND brand_id=".intval( $bind_brand_id ) ) )
                    {
                        $rs = $this->db->query( "SELECT * FROM sdb_type_brand WHERE 0=1" );
                        $sql = $this->db->getinsertsql( $rs, array(
                            "type_id" => $type_id,
                            "brand_id" => $bind_brand_id
                        ) );
                        $this->db->exec( $sql );
                    }
                }
            }
        }
        return $return;
    }

    function downloadgoods( $command_id, $supplier_id, $supplier_goods_id, $cat_id = NULL, $flag = true )
    {
        $plat_goods_info = $this->api->getapidata( "getGoodsByID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        if ( !empty( $plat_goods_info ) )
        {
            if ( $flag )
            {
                if ( !$this->_checkgoodsexist( $supplier_id, $supplier_goods_id ) )
                {
                    $time = time( );
                    $marketable = "false";
                    $return = $this->predownload( $supplier_id, $supplier_goods_id, $command_id );
                    $supplier_cat_id = $plat_goods_info['cat_id'];
                    if ( is_null( $cat_id ) )
                    {
                        $tmp_down_cat = $this->downloadcat( $cat_id, $supplier_id, $supplier_cat_id, false );
                        if ( $tmp_down_cat['if_download'] )
                        {
                            $tmp_cat = $this->db->selectrow( "SELECT cat_id FROM sdb_goods_cat WHERE cat_name='未分类'" );
                            if ( !empty( $tmp_cat ) )
                            {
                                $local_cat_id = $tmp_cat['cat_id'];
                            }
                            else
                            {
                                $cat_info = array( "parent_id" => 0, "cat_path" => ",", "is_leaf" => "true", "cat_name" => "未分类", "disabled" => "true" );
                                $rs = $this->db->query( "SELECT * FROM sdb_goods_cat WHERE 0=1" );
                                $sql = $this->db->getinsertsql( $rs, $cat_info );
                                $this->db->exec( $sql );
                                $local_cat_id = $this->db->lastinsertid( );
                            }
                        }
                        else
                        {
                            $local_cat_id = $tmp_down_cat['local_cat_id'];
                        }
                    }
                    else
                    {
                        $down_cat = $this->downloadcat( $cat_id, $supplier_id, $supplier_cat_id );
                        if ( $down_cat['cat_path'] != "" )
                        {
                            $return['cat'] = $down_cat;
                        }
                        $local_cat_id = $down_cat['cat_id'] ? $down_cat['cat_id'] : 0;
                    }
                    $return['locals']['local_cat_id'] = $local_cat_id;
                    $sync_job = $this->system->loadmodel( "distribution/syncjob" );
                    $aData = $plat_goods_info;
                    unset( $aData->'goods_id' );
                    $aData['cat_id'] = $local_cat_id;
                    $aData['type_id'] = $return['locals']['local_type_id'];
                    $aData['brand_id'] = $return['locals']['local_brand_id'];
                    $aData['supplier_id'] = $supplier_id;
                    $aData['supplier_goods_id'] = $supplier_goods_id;
                    $aData['marketable'] = $marketable;
                    $aData['bn'] = $this->localbn( $supplier_id, $plat_goods_info['bn'] );
                    $aData['disabled'] = "false";
                    $aData['udfimg'] = "false";
                    $aData['last_modify'] = time( );
                    if ( empty( $aData['score_setting'] ) )
                    {
                        unset( $aData->'score_setting' );
                    }
                    if ( isset( $aData['spec'] ) )
                    {
                        unset( $aData->'spec' );
                    }
                    if ( isset( $aData['spec_desc'] ) )
                    {
                        unset( $aData->'spec_desc' );
                    }
                    if ( isset( $aData['pdt_desc'] ) )
                    {
                        unset( $aData->'pdt_desc' );
                    }
                    if ( empty( $aData['ws_policy'] ) )
                    {
                        unset( $aData->'ws_policy' );
                    }
                    $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE 0=1" );
                    $sql = $this->db->getinsertsql( $rs, $aData );
                    if ( $sql && !$this->db->exec( $sql ) )
                    {
                        trigger_error( "SQL Error:".$sql, E_USER_NOTICE );
                        return false;
                    }
                    $local_goods_id = $this->db->lastinsertid( );
                    $this->addproducts( $supplier_id, $supplier_goods_id, $local_goods_id );
                    $gimages = $this->api->getapidata( "getImagesByGoodsId", API_VERSION, array(
                        "supplier_id" => $supplier_id,
                        "id" => $supplier_goods_id
                    ), true, true );
                    foreach ( $gimages as $gimage )
                    {
                        $this->addgimage( $command_id, $supplier_id, $local_goods_id, $gimage );
                    }
                    $image_default = $this->_getlocalgimagebyplatgimage( $supplier_id, $plat_goods_info['image_default'] );
                    $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
                    $sql = $this->db->getupdatesql( $rs, array(
                        "image_default" => $image_default
                    ) );
                    $this->db->exec( $sql );
                    $oCostSync = $this->system->loadmodel( "distribution/costsync" );
                    $oCostSync->updatecostsyncinfo( $supplier_id, $local_goods_id );
                    $oCostSync->updateproductcost( $supplier_id, $local_goods_id );
                    $oCostSync->updategoodscost( $supplier_id, $local_goods_id );
                }
                else
                {
                    $goods_info = $this->db->selectrow( "SELECT * FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $supplier_goods_id ) );
                    if ( $goods_info['disabled'] == "true" )
                    {
                        $params['disabled'] = "false";
                    }
                    if ( $goods_info['marketable'] == "false" )
                    {
                        $params['marketable'] = "true";
                    }
                    $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $supplier_goods_id ) );
                    $sql = $this->db->getupdatesql( $rs, $params );
                    if ( $sql )
                    {
                        $this->db->exec( $sql );
                    }
                    return false;
                }
            }
            $this->_changesyncstatus( $supplier_id, $supplier_goods_id );
            return $return;
        }
        return false;
    }

    function _changesyncstatus( $supplier_id, $supplier_goods_id )
    {
        $table_name = "sdb_data_sync_".$supplier_id;
        $rs = $this->db->query( "SELECT * FROM ".$table_name." WHERE command<>6 AND goods_id=".intval( $supplier_goods_id ) );
        $sql = $this->db->getupdatesql( $rs, array( "status" => "done" ) );
        if ( $sql )
        {
            $this->db->exec( $sql );
        }
    }

    function addgimage( $command_id, $supplier_id, $local_goods_id, $gimage )
    {
        $is_remote = $this->_checkremoteimage( $gimage['source'] ) ? "true" : "false";
        $gimage_info = array(
            "goods_id" => $local_goods_id,
            "is_remote" => $is_remote,
            "source" => $gimage['source'],
            "src_size_width" => $gimage['src_size_width'],
            "src_size_height" => $gimage['src_size_height'],
            "supplier_id" => $supplier_id,
            "supplier_gimage_id" => $gimage['gimage_id'],
            "sync_time" => $gimage['up_time'],
            "up_time" => time( )
        );
        $rs = $this->db->query( "SELECT * FROM sdb_gimages WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $gimage_info );
        $this->db->exec( $sql );
        $local_gimage_id = $this->db->lastinsertid( );
        $this->local_gimage[md5( $supplier_id.$gimage['gimage_id']."gimage" )] = $local_gimage_id;
        if ( $is_remote == "false" )
        {
            $sync_job = $this->system->loadmodel( "distribution/syncjob" );
            $type = "gimage";
            $object_id = $gimage['gimage_id'];
            $sync_job->insertimagesynclist( $command_id, $type, $supplier_id, $object_id, $gimage['sync_time'] );
        }
    }

    function addproducts( $supplier_id, $supplier_goods_id, $local_goods_id )
    {
        $tmp_props = array( );
        $tmp_goods_images = array( );
        $products = $this->api->getapidata( "getProductsByGoodsID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        $local_goods_info = $this->db->selectrow( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
        $local_goods_pdt_desc = unserialize( $local_goods_info['pdt_desc'] );
        $local_goods_spec_desc = unserialize( $local_goods_info['spec_desc'] );
        $local_goods_spec = unserialize( $local_goods_info['spec'] );
        if ( !empty( $products ) )
        {
            $min_cost = NULL;
            foreach ( $products as $product )
            {
                $plat_product_id = $product['product_id'];
                unset( $product->'product_id' );
                $tmp_props = unserialize( $product['props'] );
                $tmp_goods_images = $product['goods_images'];
                if ( !empty( $tmp_props ) )
                {
                    $local_spec_ids = array( );
                    $t_spec = array( );
                    foreach ( $tmp_props['spec'] as $spec_id => $v )
                    {
                        $local_spec_id = $this->_getlocalspecbyplatspec( $supplier_id, $spec_id );
                        $t_spec[$local_spec_id] = $v;
                        $local_spec_ids[] = $local_spec_id;
                    }
                    unset( $tmp_props->'spec' );
                    $tmp_props['spec'] = $t_spec;
                    $t_spec_value_id = array( );
                    foreach ( $tmp_props['spec_value_id'] as $spec_id => $v )
                    {
                        $local_spec_id = $this->_getlocalspecbyplatspec( $supplier_id, $spec_id );
                        $local_spec_value_id = $this->_getlocalspecvaluebyplatspecvalue( $local_spec_id, $supplier_id, $v );
                        if ( $spec_id != $local_spec_id )
                        {
                            $tmp_goods_images[$local_spec_value_id] = $tmp_goods_images[$tmp_props['spec_value_id'][$spec_id]];
                            unset( $tmp_goods_images->$tmp_props['spec_value_id'][$spec_id] );
                        }
                        $t_spec_value_id[$local_spec_id] = $local_spec_value_id;
                    }
                    unset( $tmp_props->'spec_value_id' );
                    $tmp_props['spec_value_id'] = $t_spec_value_id;
                    $t_spec_private_value_id = array( );
                    foreach ( $tmp_props['spec_private_value_id'] as $spec_id => $v )
                    {
                        $local_spec_id = $this->_getlocalspecbyplatspec( $supplier_id, $spec_id );
                        $t_spec_private_value_id[$local_spec_id] = $v;
                    }
                    unset( $tmp_props->'spec_private_value_id' );
                    $tmp_props['spec_private_value_id'] = $t_spec_private_value_id;
                    foreach ( $local_spec_ids as $v )
                    {
                        $spec_value_id = $tmp_props['spec_value_id'][$v];
                        $spec_private_value_id = $tmp_props['spec_private_value_id'][$v];
                        $spec_info = $this->db->selectrow( "SELECT spec_name,spec_type FROM sdb_specification WHERE spec_id=".$v );
                        $spec_value_info = $this->db->selectrow( "SELECT spec_value,spec_image FROM sdb_spec_values WHERE spec_value_id=".$spec_value_id );
                        $local_goods_spec_desc[$v][$spec_private_value_id] = array(
                            "spec_value" => $spec_value_info['spec_value'],
                            "spec_type" => $spec_info['spec_type'],
                            "spec_value_id" => $spec_value_id,
                            "spec_image" => $spec_value_info['spec_image'],
                            "spec_goods_images" => $tmp_goods_images[$spec_value_id]
                        );
                        $local_goods_spec[$v] = $spec_info['spec_name'];
                    }
                }
                $source_product_bn = $product['bn'];
                $local_product_bn = $this->localbn( $supplier_id, $source_product_bn );
                $product['goods_id'] = $local_goods_id;
                $product['bn'] = $local_product_bn;
                $product['props'] = serialize( $tmp_props );
                $product['last_modify'] = $time;
                $product['uptime'] = $time;
                $product['name'] = $product['name'];
                $min_cost = $min_cost === NULL ? $product['cost'] : min( $min_cost, $product['cost'] );
                if ( $tmp = $this->db->selectrow( "SELECT p.product_id FROM sdb_products AS p,sdb_supplier_pdtbn AS sp WHERE sp.source_bn='".$source_product_bn."' AND sp.supplier_id=".intval( $supplier_id )." AND sp.local_bn=p.bn" ) )
                {
                    $local_product_id = $tmp['product_id'];
                    unset( $product->'bn' );
                    $rs = $this->db->query( "SELECT * FROM sdb_products WHERE product_id=".intval( $local_product_id ) );
                    $sql = $this->db->getupdatesql( $rs, $product );
                    $this->db->exec( $sql );
                }
                else
                {
                    $rs = $this->db->query( "SELECT * FROM sdb_products WHERE 0=1" );
                    $sql = $this->db->getinsertsql( $rs, $product );
                    $this->db->exec( $sql );
                    $local_product_id = $this->db->lastinsertid( );
                    foreach ( $tmp_props['spec_value_id'] as $key_spec_id => $value_spec_value_id )
                    {
                        $this->addgoodsspecindex( $local_type_id, $key_spec_id, $value_spec_value_id, $local_goods_id, $local_product_id );
                    }
                    $this->addsuplierpdtbn( $supplier_id, $local_product_bn, $source_product_bn );
                }
                if ( !empty( $product['pdt_desc'] ) )
                {
                    $local_goods_pdt_desc[$local_product_id] = $product['pdt_desc'];
                }
                else
                {
                    $local_goods_pdt_desc = "";
                    $local_goods_spec_desc = "";
                    $local_goods_spec = "";
                }
            }
            $goods_update_info = array( );
            if ( !empty( $local_goods_pdt_desc ) )
            {
                $goods_update_info['pdt_desc'] = serialize( $local_goods_pdt_desc );
            }
            else
            {
                $goods_update_info['pdt_desc'] = "";
            }
            if ( !empty( $local_goods_spec_desc ) )
            {
                $goods_update_info['spec_desc'] = serialize( $local_goods_spec_desc );
            }
            else
            {
                $goods_update_info['spec_desc'] = "";
            }
            if ( !empty( $local_goods_spec ) )
            {
                $goods_update_info['spec'] = serialize( $local_goods_spec );
            }
            else
            {
                $goods_update_info['spec'] = "";
            }
            $goods_update_info['cost'] = $min_cost;
            if ( !empty( $goods_update_info ) )
            {
                $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
                $sql = $this->db->getupdatesql( $rs, $goods_update_info );
                $this->db->exec( $sql );
            }
        }
    }

    function updategoodsproduct( $supplier_id, $supplier_goods_id, $command_id )
    {
        $return = $this->predownload( $supplier_id, $supplier_goods_id, $command_id );
        $local_goods_info = $this->db->selectrow( "SELECT * FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $supplier_goods_id ) );
        $local_goods_id = $local_goods_info['goods_id'];
        $local_product_info = $this->db->select( "SELECT * FROM sdb_products WHERE goods_id=".$local_goods_id );
        $products = $this->api->getapidata( "getProductsByGoodsID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        $tmp_product_id = array( );
        if ( !empty( $products ) )
        {
            foreach ( $products as $v )
            {
                $tmp = $this->db->selectrow( "SELECT p.product_id FROM sdb_products AS p,sdb_supplier_pdtbn AS sp WHERE sp.source_bn='".$v['bn']."' AND sp.supplier_id=".intval( $supplier_id )." AND sp.local_bn=p.bn" );
                if ( $tmp )
                {
                    $tmp_product_id[] = $tmp['product_id'];
                }
            }
        }
        foreach ( $local_product_info as $v )
        {
            if ( !in_array( $v['product_id'], $tmp_product_id ) )
            {
                $this->db->exec( "DELETE FROM sdb_supplier_pdtbn WHERE local_bn='".$v['bn']."'" );
                $this->db->exec( "DELETE FROM sdb_products WHERE product_id=".$v['product_id'] );
                $this->db->exec( "DELETE FROM sdb_goods_spec_index WHERE product_id=".$v['product_id'] );
            }
        }
        $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE supplier_id=".$supplier_id." AND supplier_goods_id=".$supplier_goods_id );
        $sql = $this->db->getupdatesql( $rs, array( "spec" => "", "pdt_desc" => "", "spec_desc" => "" ) );
        if ( $sql )
        {
            $this->db->exec( $sql );
        }
        $this->addproducts( $supplier_id, $supplier_goods_id, $local_goods_id );
        $product_info = $this->db->select( "SELECT store,price,weight,cost FROM sdb_products WHERE goods_id=".intval( $local_goods_id ) );
        $store = 0;
        $price = NULL;
        $weight = NULL;
        $min_cost = NULL;
        foreach ( $product_info as $product )
        {
            if ( is_null( $product['store'] ) || $product['store'] === "" )
            {
                $store = NULL;
            }
            else if ( !is_null( $store ) )
            {
                $store += $product['store'];
            }
            if ( is_null( $price ) )
            {
                $price = empty( $product['price'] ) ? 0 : $product['price'];
            }
            else
            {
                $price = min( $price, $product['price'] );
            }
            if ( is_null( $weight ) )
            {
                $weight = empty( $product['weight'] ) ? 0 : $product['weight'];
            }
            else
            {
                $weight = min( $weight, $product['weight'] );
            }
            if ( is_null( $min_cost ) )
            {
                $min_cost = $product['cost'];
            }
            else
            {
                $min_cost = min( $min_cost, $product['cost'] );
            }
        }
        $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
        $update_info = array(
            "store" => $store,
            "price" => $price,
            "weight" => $weight,
            "cost" => $min_cost
        );
        $sql = $this->db->getupdatesql( $rs, $update_info );
        $this->db->exec( $sql );
        return $return;
    }

    function predownload( $supplier_id, $supplier_goods_id, $command_id )
    {
        $return = array(
            "type" => array( ),
            "spec" => array( ),
            "brand" => array(
                "add" => array( ),
                "update" => array( )
            ),
            "cat" => array( ),
            "locals" => array( )
        );
        $plat_goods_info = $this->api->getapidata( "getGoodsByID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        $supplier_brand_id = $plat_goods_info['brand_id'];
        $supplier_type_id = $plat_goods_info['type_id'];
        $supplier_spec_info = $this->api->getapidata( "getSpecificationByGoodsID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        if ( !empty( $supplier_spec_info ) )
        {
            foreach ( $supplier_spec_info as $spec_info )
            {
                $down_spec = $this->downloadspecification( $supplier_id, $spec_info, $command_id );
                if ( $down_spec['download'] )
                {
                    $return['spec'] = $return['spec'] + array(
                        $down_spec['spec_id'] => $down_spec['spec_name']
                    );
                }
                $local_spec_id = $down_spec['spec_id'];
                $tmp_type = $this->bindspecwithtype( $supplier_id, $spec_info['spec_id'], $local_spec_id );
                $return['type'] = $return['type'] + $tmp_type;
            }
            unset( $tmp_type );
        }
        if ( !empty( $supplier_brand_id ) )
        {
            $down_brand = $this->downloadbrand( $supplier_id, $supplier_brand_id, $command_id );
            if ( !empty( $down_brand ) )
            {
                if ( $down_brand['action'] != "none" )
                {
                    $return['brand'][$down_brand['action']] = $return['brand'][$down_brand['action']] + array(
                        $down_brand['brand_id'] => $down_brand['brand_name']
                    );
                }
                $local_brand_id = $down_brand['brand_id'];
                $tmp_type = $this->bindbrandwithtype( $supplier_id, $supplier_brand_id, $local_brand_id );
                $return['type'] = $return['type'] + $tmp_type;
            }
        }
        if ( !empty( $supplier_type_id ) )
        {
            $down_type = $this->downloadtype( $supplier_id, $supplier_type_id );
            if ( !empty( $down_type ) )
            {
                if ( $down_type['download'] )
                {
                    $return['type'] = $return['type'] + array(
                        $down_type['type_id'] => $down_type['name']
                    );
                }
                $local_type_id = $down_type['type_id'];
                $tmp_brand = $this->bindtypewithbrand( $supplier_id, $supplier_type_id, $local_type_id, $command_id );
                if ( isset( $tmp_brand['add'] ) )
                {
                    $return['brand']['add'] = $return['brand']['add'] + $tmp_brand['add'];
                }
                else if ( isset( $tmp_brand['update'] ) )
                {
                    $return['brand']['update'] = $return['brand']['update'] + $tmp_brand['update'];
                }
                $tmp_spec = $this->bindtypewithspec( $supplier_id, $supplier_type_id, $local_type_id, $command_id );
                $return['spec'] = $return['spec'] + $tmp_spec;
            }
        }
        $return['locals']['local_type_id'] = $local_type_id;
        $return['locals']['local_spec_id'] = $local_spec_id;
        $return['locals']['local_brand_id'] = $local_brand_id;
        return $return;
    }

    function addgoodsspecindex( $type_id, $spec_id, $spec_value_id, $goods_id, $product_id )
    {
        $spec_index_info = array(
            "type_id" => $type_id,
            "spec_id" => $spec_id,
            "spec_value_id" => $spec_value_id,
            "goods_id" => $goods_id,
            "product_id" => $product_id
        );
        $rs = $this->db->query( "SELECT * FROM sdb_goods_spec_index WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $spec_index_info );
        $this->db->exec( $sql );
    }

    function addsuplierpdtbn( $supplier_id, $local_bn, $source_bn )
    {
        $supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
        $sp_id = $supplier_info['sp_id'];
        $supplier_pdtbn_info = array(
            "sp_id" => $sp_id,
            "supplier_id" => $supplier_id,
            "local_bn" => $local_bn,
            "source_bn" => $source_bn
        );
        $rs = $this->db->query( "SELECT * FROM sdb_supplier_pdtbn WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $supplier_pdtbn_info );
        $this->db->exec( $sql );
    }

    function _checkgoodsexist( $supplier_id, $supplier_goods_id )
    {
        $goods_info = $this->db->selectrow( "SELECT goods_id FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $supplier_goods_id ) );
        if ( empty( $goods_info ) )
        {
            return false;
        }
        return true;
    }

    function localbn( $supplier_id, $bn )
    {
        $rand_width = 2;
        $rand_atom = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" );
        $sp_id_width = 4;
        $rand_bn = "";
        $sql = "SELECT sp_id FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id );
        $supplier = $this->db->selectrow( $sql );
        $sp_id = $supplier['sp_id'];
        $sp_id_len = strlen( $sp_id );
        if ( $sp_id_len < $sp_id_width )
        {
            $prex = "";
            $i = 0;
            for ( ; $i < $sp_id_width - $sp_id_len; ++$i )
            {
                $prex .= "0";
            }
            $local_sp_id = $prex.$sp_id;
        }
        else
        {
            $local_sp_id = $sp_id;
        }
        $i = 0;
        for ( ; $i < $rand_width; ++$i )
        {
            $rand = rand( 0, 35 );
            $rand_bn .= $rand_atom[$rand];
        }
        $local_bn = $local_sp_id.$rand_bn.$bn;
        return $local_bn;
    }

    function _getlocalspecbyplatspec( $supplier_id, $supplier_spec_id )
    {
        $key = md5( $supplier_id.$supplier_spec_id."spec" );
        if ( !isset( $this->local_spec[$key] ) )
        {
            $local_spec_info = $this->db->selectrow( "SELECT spec_id FROM sdb_specification WHERE supplier_id=".floatval( $supplier_id )." AND supplier_spec_id=".intval( $supplier_spec_id )." ORDER BY spec_id DESC" );
            $this->local_spec[$key] = $local_spec_info['spec_id'];
            return $this->local_spec[$key];
        }
        return $this->local_spec[$key];
    }

    function _getlocalspecvaluebyplatspecvalue( $spec_id, $supplier_id, $plat_spec_value_id )
    {
        $key = md5( $spec_id.$supplier_id.$plat_spec_value_id."spec_value" );
        if ( !isset( $this->local_spec_value[$key] ) )
        {
            $local_spec_value_info = $this->db->selectrow( "SELECT spec_value_id FROM sdb_spec_values WHERE spec_id=".intval( $spec_id )." AND supplier_id=".floatval( $supplier_id )." AND supplier_spec_value_id=".intval( $plat_spec_value_id )." ORDER BY spec_value_id DESC" );
            $this->local_spec_value[$key] = $local_spec_value_info['spec_value_id'];
            return $this->local_spec_value[$key];
        }
        return $this->local_spec_value[$key];
    }

    function _getlocalgimagebyplatgimage( $supplier_id, $supplier_gimage_id )
    {
        $key = md5( $supplier_id.$supplier_gimage_id."gimage" );
        if ( !isset( $this->local_gimage[$key] ) )
        {
            $local_gimage_info = $this->db->selectrow( "SELECT gimage_id FROM sdb_gimages WHERE supplier_id=".floatval( $supplier_id )." AND supplier_gimage_id=".intval( $supplier_gimage_id )." ORDER BY gimage_id DESC" );
            $this->local_gimage[$key] = $local_gimage_info['gimage_id'];
            return $this->local_gimage[$key];
        }
        return $this->local_gimage[$key];
    }

    function _getlocaltypebyplattype( $supplier_id, $supplier_type_id )
    {
        $key = md5( $supplier_id.$supplier_type_id."type" );
        if ( !isset( $this->local_type[$key] ) )
        {
            $local_type_info = $this->db->selectrow( "SELECT type_id FROM sdb_goods_type WHERE supplier_id=".floatval( $supplier_id )." AND supplier_type_id=".intval( $supplier_type_id )." ORDER BY type_id DESC" );
            $this->local_type[$key] = $local_type_info['type_id'];
            return $this->local_type[$key];
        }
        return $this->local_type[$key];
    }

    function _getlocalbrandbyplatbrand( $supplier_id, $supplier_brand_id )
    {
        $key = md5( $supplier_id.$supplier_brand_id."brand" );
        if ( !isset( $this->local_brand[$key] ) )
        {
            $brand_info = $this->api->getapidata( "getBrandByID", API_VERSION, array(
                "supplier_id" => $supplier_id,
                "id" => $supplier_brand_id
            ), true, true );
            addslashes_array( $brand_info );
            if ( $brand_info['brand_keywords'] )
            {
                $local_brand_info = $this->db->selectrow( "SELECT brand_id FROM sdb_brand WHERE brand_name='".$brand_info['brand_name']."' AND brand_keywords='".$brand_info['brand_keywords']."' ORDER BY brand_id DESC" );
            }
            else
            {
                $local_brand_info = $this->db->selectrow( "SELECT brand_id FROM sdb_brand WHERE brand_name='".$brand_info['brand_name']."' AND (brand_keywords='' OR brand_keywords IS NULL) ORDER BY brand_id DESC" );
            }
            $this->local_brand[$key] = $local_brand_info['brand_id'];
            return $this->local_brand[$key];
        }
        return $this->local_brand[$key];
    }

    function _checkremoteimage( $image_path )
    {
        $check = substr( $image_path, 0, 7 );
        if ( $check == "imgget:" )
        {
            return false;
        }
        return true;
    }

    function updatebreaksupplierstatus( $aPlatSupplier )
    {
        $aLocSupplier = $this->db->select( "SELECT supplier_id FROM sdb_supplier" );
        foreach ( $aLocSupplier as $row )
        {
            $aLoc = $row['supplier_id'];
        }
        foreach ( $aPlatSupplier as $row )
        {
            $aPlat = $row['supplier_id'];
        }
        $aDiff = array_diff( $aLoc, $aPlat );
        if ( $aDiff )
        {
            $aDiff = array_values( $aDiff );
            $this->db->exec( "UPDATE sdb_supplier SET status=0 WHERE supplier_id IN('".explode( "','", $aDiff )."')" );
        }
    }

    function syncsupplier( $supplier_id = NULL, &$count, $page = 1, $limit = 0 )
    {
        $return = array( );
        if ( is_null( $supplier_id ) )
        {
            if ( $limit != 0 )
            {
                $params = array(
                    "pages" => $page,
                    "counts" => $limit
                );
            }
            else
            {
                $params = array( );
            }
            $supplier_list = $this->api->getapidata( "getSuppliers", API_VERSION, $params, true, true );
            if ( !empty( $supplier_list ) )
            {
                $count = $supplier_list[0]['row_count'];
                foreach ( $supplier_list as $v )
                {
                    $this->_syncsupplier( $v );
                }
                $this->updatebreaksupplierstatus( $supplier_list );
                return true;
            }
            return false;
        }
        $params = array(
            "id" => $supplier_id
        );
        $supplier = $this->api->getapidata( "getSupplierById", API_VERSION, $params, true, true );
        if ( !empty( $supplier ) )
        {
            return $this->_syncsupplier( $supplier );
        }
        return false;
    }

    function _syncsupplier( $plat_supplier_info )
    {
        $return = array( );
        $supplier_domain_info = $this->api->getapidata( "getDomain", API_VERSION, array(
            "id" => $plat_supplier_info['supplier_id']
        ) );
        $local_supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $plat_supplier_info['supplier_id'] ) );
        if ( !empty( $local_supplier_info ) )
        {
            $supplier_info_update = array( );
            if ( $local_supplier_info['brief_name'] != $plat_supplier_info['brief_name'] )
            {
                $supplier_info_update['supplier_brief_name'] = $plat_supplier_info['brief_name'];
                $return['supplier_brief_name'] = $plat_supplier_info['brief_name'];
            }
            else
            {
                $return['supplier_brief_name'] = $local_supplier_info['brief_name'];
            }
            if ( $local_supplier_info['status'] != $plat_supplier_info['bind_status'] )
            {
                $supplier_info_update['status'] = $plat_supplier_info['bind_status'];
                $return['status'] = $plat_supplier_info['bind_status'];
            }
            else
            {
                $return['status'] = $local_supplier_info['status'];
            }
            $supplier_info_update['has_new'] = $this->checksupplierhassync( $plat_supplier_info['supplier_id'] ) ? "true" : "false";
            $supplier_info_update['domain'] = isset( $supplier_domain_info['domain'] ) ? $supplier_domain_info['domain'] : "";
            $oCostSync = $this->system->loadmodel( "distribution/costsync" );
            $supplier_info_update['has_cost_new'] = $oCostSync->getcostsynccount( $plat_supplier_info['supplier_id'] ) ? "true" : "false";
            if ( !empty( $supplier_info_update ) )
            {
                $rs = $this->db->query( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $plat_supplier_info['supplier_id'] ) );
                $sql = $this->db->getupdatesql( $rs, $supplier_info_update );
                $this->db->exec( $sql );
                $return['action'] = "update";
                return $return;
            }
            $return['action'] = "none";
            return $return;
        }
        $supplier_info_insert = array(
            "supplier_id" => $plat_supplier_info['supplier_id'],
            "supplier_brief_name" => $plat_supplier_info['brief_name'],
            "status" => $plat_supplier_info['bind_status'],
            "has_new" => $this->checksupplierhassync( $plat_supplier_info['supplier_id'] ) ? "true" : "false",
            "domain" => $supplier_domain_info['domain']
        );
        $rs = $this->db->query( "SELECT * FROM sdb_supplier WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $supplier_info_insert );
        $this->db->exec( $sql );
        $return = array(
            "supplier_brief_name" => $plat_supplier_info['brief_name'],
            "status" => $plat_supplier_info['status'],
            "action" => "add"
        );
        return $return;
    }

    function checksupplierhassync( $supplier_id )
    {
        $time = time( );
        $flag = false;
        $pline_id = array( );
        $supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
        $params = array(
            "supplier_id" => $supplier_id,
            "last_sync_time" => empty( $supplier_info['sync_time_for_plat'] ) ? 0 : $supplier_info['sync_time_for_plat'],
            "pages" => 1,
            "counts" => 1
        );
        $pline_info = $this->api->getapidata( "getProductLineList", API_VERSION, array(
            "id" => $supplier_id
        ), true, true );
        if ( !empty( $pline_info ) )
        {
            foreach ( $pline_info as $pline )
            {
                $pline_id[] = $pline['pline_id'];
                $tmp_pline_info[$pline['pline_id']] = $pline;
            }
            unset( $pline_info );
            $pline_info = $tmp_pline_info;
            $params['pline'] = json_encode( $pline_id );
            $sync_list = $this->api->getapidata( "getUpdateList", API_VERSION, $params, true, true );
            if ( empty( $sync_list ) )
            {
                $flag = false;
            }
            else
            {
                $flag = true;
            }
        }
        else
        {
            $flag = false;
        }
        $old_supplier_pline = unserialize( $supplier_info['supplier_pline'] );
        $old_supplier_pline = empty( $old_supplier_pline ) ? array( ) : $old_supplier_pline;
        $old_pline_id = array_keys( $old_supplier_pline );
        if ( !$flag )
        {
            $new_pline_id = array_diff( $pline_id, $old_pline_id );
            if ( !empty( $new_pline_id ) )
            {
                $goods_id_list = $this->api->getapidata( "getGoodsIdByPline", API_VERSION, array(
                    "supplier_id" => $supplier_id,
                    "pline" => json_encode( $new_pline_id ),
                    "pages" => 1,
                    "counts" => 1
                ), true, true );
                if ( !empty( $goods_id_list ) )
                {
                    $flag = true;
                }
            }
            if ( !$flag )
            {
                $del_pline_id = array_diff( $old_pline_id, $pline_id );
                if ( !empty( $del_pline_id ) )
                {
                    $flag = true;
                }
                if ( !$flag )
                {
                    $same_pline_id = array_intersect( $pline_id, $old_pline_id );
                    if ( !empty( $same_pline_id ) )
                    {
                        foreach ( $same_pline_id as $v )
                        {
                            unset( json_encode( $pline_id )->'pline_name' );
                            if ( !( $old_supplier_pline[$v] != array(
                                "cat_id" => $pline_info[$v]['cat_id'].( $pline_info[$v]['child_cat_path'] == "" ? "" : ",".$pline_info[$v]['child_cat_path'] ),
                                "brand_id" => $pline_info[$v]['brand_id']
                            ) ) )
                            {
                                continue;
                            }
                            $flag = true;
                            break;
                        }
                    }
                }
            }
        }
        return $flag;
    }

    function filterupdatelist_1( $supplier_id, $command_type = "sync" )
    {
        $time = time( );
        $pline_id = array( );
        $new_pline_id = array( );
        $del_pline = array( );
        $simple_pline_info = array( );
        $supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
        $pline_info = $this->api->getapidata( "getProductLineList", API_VERSION, array(
            "id" => $supplier_id
        ), true, true );
        if ( !empty( $pline_info ) )
        {
            foreach ( $pline_info as $v )
            {
                $pline_id[] = $v['pline_id'];
                $tmp_pline_info[$v['pline_id']] = $v;
                $simple_pline_info[$v['pline_id']] = array(
                    "pline_name" => $v['pline_name'],
                    "cat_id" => $v['cat_id'].( $v['child_cat_path'] == "" ? "" : ",".$v['child_cat_path'] ),
                    "brand_id" => $v['brand_id']
                );
            }
            unset( $pline_info );
            $pline_info = $tmp_pline_info;
        }
        else
        {
            return true;
        }
        if ( $pline_info[0]['pline_id'] == 1 )
        {
            $new_pline_id = array( 1 );
        }
        else
        {
            $old_supplier_pline = unserialize( $supplier_info['supplier_pline'] );
            $old_supplier_pline = empty( $old_supplier_pline ) ? array( ) : $old_supplier_pline;
            $old_pline_id = array_keys( $old_supplier_pline );
            $new_pline_id = array_diff( $pline_id, $old_pline_id );
            $del_pline_id = array_diff( $old_pline_id, $pline_id );
            if ( !empty( $del_pline_id ) )
            {
                foreach ( $del_pline_id as $del_id )
                {
                    $del_pline[$del_id] = $old_supplier_pline[$del_id];
                }
            }
            $same_pline_id = array_intersect( $pline_id, $old_pline_id );
            if ( !empty( $same_pline_id ) )
            {
                foreach ( $same_pline_id as $v )
                {
                    if ( $old_supplier_pline[$v] != array(
                        "cat_id" => $pline_info[$v]['cat_id'].( $pline_info[$v]['child_cat_path'] == "" ? "" : ",".$pline_info[$v]['child_cat_path'] ),
                        "brand_id" => $pline_info[$v]['brand_id'],
                        "pline_name" => $pline_info[$v]['pline_name']
                    ) )
                    {
                        $new_pline_id[] = $v;
                        $del_pline[$v] = $old_supplier_pline[$v];
                    }
                }
            }
            if ( !empty( $del_pline ) )
            {
                $autosync = $this->system->loadmodel( "distribution/autosync" );
                $autosync->doloosepline( $supplier_id, $simple_pline_info );
                $remain_pline = $old_supplier_pline;
                $del_pline_id = array_keys( $del_pline );
                foreach ( $del_pline_id as $v )
                {
                    unset( $remain_pline->$v );
                }
                $new_remain_pline = array( );
                if ( !empty( $remain_pline ) )
                {
                    foreach ( $remain_pline as $v )
                    {
                        if ( isset( $new_remain_pline[$v['brand_id']] ) )
                        {
                            if ( $v['cat_id'] == "-1" || $new_remain_pline[$v['brand_id']]['cat_id'] == "-1" )
                            {
                                $new_remain_pline[$v['brand_id']] = array(
                                    "cat_id" => "-1",
                                    "brand_id" => $v['brand_id']
                                );
                            }
                            else
                            {
                                $tmp1 = explode( ",", $new_remain_pline[$v['brand_id']]['cat_id'] );
                                $tmp2 = explode( ",", $v['cat_id'] );
                                $new_remain_pline[$v['brand_id']] = array(
                                    "cat_id" => implode( ",", array_unique( array_merge( $tmp1, $tmp2 ) ) ),
                                    "brand_id" => $v['brand_id']
                                );
                                unset( $tmp1 );
                                unset( $tmp2 );
                            }
                        }
                        else
                        {
                            $new_remain_pline[$v['brand_id']] = array(
                                "cat_id" => $v['cat_id'],
                                "brand_id" => $v['brand_id']
                            );
                        }
                    }
                    if ( !empty( $new_remain_pline ) )
                    {
                        $tmp_new_remain_pline = $new_remain_pline;
                        if ( isset( $tmp_new_remain_pline[-1] ) )
                        {
                            unset( $tmp_new_remain_pline->-1 );
                        }
                        $all_brand_id = array_keys( $tmp_new_remain_pline );
                        foreach ( $tmp_new_remain_pline as $k => $v )
                        {
                            if ( isset( $new_remain_pline[-1] ) )
                            {
                                if ( !( $v['cat_id'] == "-1" ) )
                                {
                                    $tmp1 = explode( ",", $v['cat_id'] );
                                    $tmp2 = explode( ",", $new_remain_pline[-1]['cat_id'] );
                                    $stay_cat = implode( ",", array_unique( array_merge( $tmp1, $tmp2 ) ) );
                                    $del_brand = $v['brand_id'];
                                    $rs = $this->db->query( "SELECT * FROM sdb_data_sync_".$supplier_id.( " WHERE if_show='true' AND cat_id NOT IN (".$stay_cat.") AND brand_id=" ).intval( $del_brand ) );
                                    $sql = $this->db->getupdatesql( $rs, array( "if_show" => "false" ) );
                                    $this->db->exec( $sql );
                                    unset( $tmp1 );
                                    unset( $tmp2 );
                                }
                            }
                            else if ( !( $v['cat_id'] == "-1" ) )
                            {
                                $rs = $this->db->query( "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE if_show='true' AND cat_id NOT IN (".$v['cat_id'].") AND brand_id=".intval( $v['brand_id'] ) );
                                $sql = $this->db->getupdatesql( $rs, array( "if_show" => "false" ) );
                                $this->db->exec( $sql );
                            }
                        }
                        if ( isset( $new_remain_pline[-1] ) )
                        {
                            if ( !( $new_remain_pline[-1]['cat_id'] == "-1" ) )
                            {
                                $query = "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE if_show='true' AND cat_id NOT IN(".$new_remain_pline[-1]['cat_id'].")";
                                if ( isset( $all_brand_id, $all_brand_id ) )
                                {
                                    $query .= "AND brand_id NOT IN (".implode( ",", $all_brand_id ).")";
                                }
                                $rs = $this->db->query( $query );
                                $sql = $this->db->getupdatesql( $rs, array( "if_show" => "false" ) );
                                $this->db->exec( $sql );
                            }
                        }
                        else
                        {
                            $rs = $this->db->query( "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE if_show='true' AND brand_id NOT IN (".implode( ",", $all_brand_id ).")" );
                            $sql = $this->db->getupdatesql( $rs, array( "if_show" => "false" ) );
                            $this->db->exec( $sql );
                        }
                    }
                }
                else
                {
                    $rs = $this->db->query( "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE if_show='true'" );
                    $sql = $this->db->getupdatesql( $rs, array( "if_show" => "false" ) );
                    $this->db->exec( $sql );
                }
            }
        }
        if ( !empty( $new_pline_id ) )
        {
            $api_name = "getGoodsIdByPline";
            $api_params = array(
                "supplier_id" => $supplier_id,
                "pline" => json_encode( array_values( $new_pline_id ) ),
                "command_type" => $command_type
            );
            $api_version = API_VERSION;
            $api_action = "distribution/datasync|filterUpdateList_1";
            $sync_job = $this->system->loadmodel( "distribution/syncjob" );
            $sync_job->addapilistjob( $supplier_id, $api_name, $api_params, $api_version, $api_action, 100 );
        }
        $rs = $this->db->query( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
        $sSupplier_pline = serialize( $simple_pline_info );
        $sql = $this->db->getupdatesql( $rs, array(
            "supplier_pline" => $sSupplier_pline
        ) );
        $this->db->exec( $sql );
    }

    function filterupdatelist_2( $supplier_id, $command_type )
    {
        $time = time( );
        $api_name = "getGoodsIdByPline";
        $api_version = API_VERSION;
        $api_action = "distribution/datasync|filterUpdateList_1";
        $sync_job = $this->system->loadmodel( "distribution/syncjob" );
        $data = $sync_job->doapilistjob( $supplier_id, $api_name, $api_version, $api_action );
        if ( !empty( $data ) )
        {
            foreach ( $data as $goods_id )
            {
                $supplier_goods_id[] = $goods_id['ob_id'];
            }
            if ( $command_type == "sync" )
            {
                $oAutoSync =& $this->system->loadmodel( "distribution/autosync" );
                $command_info = $this->db->select( "SELECT command_id FROM sdb_data_sync_".$supplier_id." WHERE if_show='false' AND command=6 AND goods_id IN (".implode( ",", $supplier_goods_id ).")" );
                if ( $command_info )
                {
                    foreach ( $command_info as $v )
                    {
                        $oAutoSync->addautosynctask( $supplier_id, $v['command_id'] );
                    }
                }
            }
            $rs = $this->db->query( "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE if_show='false' AND command=6 AND goods_id IN (".implode( ",", $supplier_goods_id ).")" );
            $sql = $this->db->getupdatesql( $rs, array(
                "if_show" => "true",
                "command_type" => $command_type
            ) );
            $this->db->exec( $sql );
            if ( $downloaded_goods = $this->db->select( "SELECT supplier_goods_id FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id IN (".implode( ",", $supplier_goods_id ).")" ) )
            {
                foreach ( $downloaded_goods as $id )
                {
                    $d_goods_id[] = $id['supplier_goods_id'];
                }
                if ( $command_type == "sync" )
                {
                    $command_info = $this->db->select( "SELECT command_id FROM sdb_data_sync_".$supplier_id." WHERE if_show='false' AND command<>6 AND goods_id IN (".implode( ",", $d_goods_id ).")" );
                    if ( $command_info )
                    {
                        foreach ( $command_info as $v )
                        {
                            $oAutoSync->addautosynctask( $supplier_id, $v['command_id'] );
                        }
                    }
                }
                $rs = $this->db->query( "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE if_show='false' AND command<>6 AND goods_id IN (".implode( ",", $d_goods_id ).")" );
                $sql = $this->db->getupdatesql( $rs, array(
                    "if_show" => "true",
                    "command_type" => $command_type
                ) );
                $this->db->exec( $sql );
            }
            return 1;
        }
        return 0;
    }

    function getproductline( $supplier_id )
    {
        return $this->api->getapidata( "getProductLineList", API_VERSION, array(
            "id" => $supplier_id
        ), true, true );
    }

    function updategoodsimage( $command_id, $supplier_id, $supplier_goods_id )
    {
        $plat_gimages = $this->api->getapidata( "getImagesByGoodsId", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        $local_goods = $this->db->selectrow( "SELECT goods_id FROM sdb_goods WHERE supplier_id=".floatval( $supplier_id )." AND supplier_goods_id=".intval( $supplier_goods_id )." ORDER BY goods_id DESC" );
        $local_gimages = $this->db->select( "SELECT * FROM sdb_gimages WHERE goods_id=".intval( $local_goods['goods_id'] )." AND supplier_id=".floatval( $supplier_id ) );
        $storager = $this->system->loadmodel( "system/storager" );
        $plat_gimage_ids = array( );
        $local_supplier_gimage_ids = array( );
        if ( !empty( $plat_gimages ) )
        {
            foreach ( $plat_gimages as $plat_gimage )
            {
                $plat_gimage_ids[] = $plat_gimage['gimage_id'];
            }
        }
        if ( !empty( $local_gimages ) )
        {
            foreach ( $local_gimages as $local_gimage )
            {
                $local_supplier_gimage_ids[] = $local_gimage['supplier_gimage_id'];
            }
        }
        if ( !empty( $plat_gimages ) )
        {
            if ( empty( $local_gimages ) )
            {
                foreach ( $plat_gimages as $gimage )
                {
                    $this->addgimage( $command_id, $supplier_id, $local_goods['goods_id'], $gimage );
                }
            }
            else
            {
                foreach ( $local_gimages as $l_gimage )
                {
                    if ( !in_array( $l_gimage['supplier_gimage_id'], $plat_gimage_ids ) )
                    {
                        $this->db->exec( "DELETE FROM sdb_gimages WHERE gimage_id=".intval( $l_gimage['gimage_id'] ) );
                        if ( $l_gimage['is_remote'] == "false" )
                        {
                            $storager->remove( $l_gimage['small'] );
                            $storager->remove( $l_gimage['big'] );
                            $storager->remove( $l_gimage['thumbnail'] );
                            unlink( HOME_DIR."/upload/".$l_gimage['source'] );
                        }
                    }
                }
                foreach ( $plat_gimages as $p_gimage )
                {
                    if ( !in_array( $p_gimage['gimage_id'], $local_supplier_gimage_ids ) )
                    {
                        $this->addgimage( $command_id, $supplier_id, $local_goods['goods_id'], $p_gimage );
                    }
                }
            }
        }
        else if ( !empty( $local_gimages ) )
        {
            foreach ( $local_gimages as $l_gimage )
            {
                $this->db->exec( "DELETE FROM sdb_gimages WHERE gimage_id=".intval( $l_gimage['gimage_id'] ) );
                if ( $l_gimage['is_remote'] == "false" )
                {
                    $storager->remove( $l_gimage['small'] );
                    $storager->remove( $l_gimage['big'] );
                    $storager->remove( $l_gimage['thumbnail'] );
                    unlink( HOME_DIR."/upload/".$l_gimage['source'] );
                }
            }
        }
        $gimage_info = $this->db->selectrow( "SELECT gimage_id FROM sdb_gimages WHERE goods_id=".$local_goods['goods_id'] );
        if ( !$gimage_info )
        {
            $update_info['thumbnail_pic'] = NULL;
            $update_info['small_pic'] = NULL;
            $update_info['big_pic'] = NULL;
            $update_info['image_default'] = NULL;
        }
        else
        {
            $update_info['thumbnail_pic'] = $gimage_info['thumbnail'];
            $update_info['small_pic'] = $gimage_info['small'];
            $update_info['big_pic'] = $gimage_info['big'];
            $update_info['image_default'] = $gimage_info['gimage_id'];
        }
        $rs = $this->db->query( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $local_goods['goods_id'] ) );
        $sql = $this->db->getupdatesql( $rs, $update_info );
        $this->db->exec( $sql );
    }

    function syncproductstore( $supplier_id, $product_id )
    {
        $plat_product_info = $this->api->getapidata( "getProductByID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $product_id
        ), true, true );
        $plat_product_bn = $plat_product_info['bn'];
        if ( $plat_product_info['store'] === "" || is_null( $plat_product_info['store'] ) )
        {
            $plat_product_store = NULL;
        }
        else
        {
            $plat_product_store = $plat_product_info['store'] - intval( $plat_product_info['freez'] );
        }
        $store = array(
            "store" => $plat_product_store
        );
        $local_product_info = $this->db->selectrow( "SELECT * FROM sdb_products AS p,sdb_supplier_pdtbn AS s WHERE s.source_bn='".$plat_product_bn."' AND s.local_bn=p.bn AND s.supplier_id=".floatval( $supplier_id ) );
        $local_product_id = $local_product_info['product_id'];
        $local_goods_id = $local_product_info['goods_id'];
        $local_product_store = $local_product_info['store'];
        $rs = $this->db->query( "SELECT * FROM sdb_products WHERE product_id=".intval( $local_product_id ) );
        $sql = $this->db->getupdatesql( $rs, $store );
        $this->db->exec( $sql );
        if ( is_null( $plat_product_store ) )
        {
            $goods_store = NULL;
        }
        else if ( $this->db->selectrow( "SELECT product_id FROM sdb_products WHERE goods_id=".intval( $local_goods_id )." AND store IS NULL" ) )
        {
            $goods_store = NULL;
        }
        else
        {
            $all_store = $this->db->selectrow( "SELECT sum(store) as counts FROM sdb_products WHERE goods_id=".intval( $local_goods_id ) );
            $goods_store = $all_store['counts'];
        }
        $rs = $this->db->query( "SELECT store FROM sdb_goods WHERE goods_id=".intval( $local_goods_id ) );
        $sql = $this->db->getupdatesql( $rs, array(
            "store" => $goods_store
        ) );
        $this->db->exec( $sql );
    }

    function getsuppliergoodsinfo( $supplier_id, $supplier_goods_id )
    {
        $goods_info = $this->api->getapidata( "getGoodsByID", API_VERSION, array(
            "supplier_id" => $supplier_id,
            "id" => $supplier_goods_id
        ), true, true );
        if ( !empty( $goods_info ) )
        {
            $supplier_type_id = $goods_info['type_id'];
            $supplier_type_info = $this->api->getapidata( "getTypeByID", API_VERSION, array(
                "supplier_id" => $supplier_id,
                "id" => $supplier_type_id
            ), true, true );
            $goods_info['type_name'] = $supplier_type_info['name'];
            $goods_info['type_props'] = $supplier_type_info['props'];
            return $goods_info;
        }
        return false;
    }

    function addsynctmpdata( $supplier_id, $pline )
    {
        $oSupplier = $this->system->loadmodel( "distribution/supplier" );
        $pline_id = array_keys( $pline );
        $supplier_info = $this->db->selectrow( "SELECT * FROM sdb_supplier WHERE supplier_id=".floatval( $supplier_id ) );
        $down_params = array(
            "supplier_id" => $supplier_id,
            "last_sync_time" => $supplier_info['sync_time_for_plat'],
            "cmd_action" => 6,
            "pline" => json_encode( $pline_id )
        );
        $goods_count = $this->api->getapidata( "getUpdateListCount", API_VERSION, $down_params, true, true );
        if ( !empty( $goods_count ) || 0 < intval( $goods_count['row_count'] ) )
        {
            $oSupplier->cleartmpdata( $supplier_id );
            $sync_job = $this->system->loadmodel( "distribution/syncjob" );
            $params = array(
                "id" => $supplier_id
            );
            $api_version = API_VERSION;
            $action = "distribution/datasync|addSyncTmpData";
            $sync_job->addapilistjob( $supplier_id, "getBrands", $params, $api_version, $action, 50 );
            $sync_job->addapilistjob( $supplier_id, "getTypes", $params, $api_version, $action, 50 );
            $sync_job->addapilistjob( $supplier_id, "getSpecifications", $params, $api_version, $action, 50 );
            $sync_job->addapilistjob( $supplier_id, "getCategories", $params, $api_version, $action, 50 );
        }
    }

    function dosynctmpdata( $supplier_id, $api_name )
    {
        switch ( $api_name )
        {
        case "getBrands" :
            $s_type = "brand";
            break;
        case "getTypes" :
            $s_type = "goods_type";
            break;
        case "getSpecifications" :
            $s_type = "spec";
            break;
        case "getCategories" :
            $s_type = "goods_cat";
            break;
            $s_type = "";
        }
        $this->_filltmpdata( $supplier_id, $api_name, $s_type );
    }

    function _filltmpdata( $supplier_id, $api_name, $s_type )
    {
        switch ( $s_type )
        {
        case "brand" :
            $ob_id = "brand_id";
            break;
        case "goods_type" :
            $ob_id = "type_id";
            break;
        case "spec" :
            $ob_id = "spec_id";
            break;
        case "goods_cat" :
            $ob_id = "cat_id";
            break;
            $ob_id = "";
        }
        $sync_job = $this->system->loadmodel( "distribution/syncjob" );
        $api_version = API_VERSION;
        $api_action = "distribution/datasync|addSyncTmpData";
        $datas = $sync_job->doapilistjob( $supplier_id, $api_name, $api_version, $api_action );
        if ( !empty( $datas ) )
        {
            foreach ( $datas as $data )
            {
                unset( $data->'row_count' );
                $insert = array(
                    "s_type" => $s_type,
                    "ob_id" => $data[$ob_id],
                    "supplier_id" => $supplier_id,
                    "s_data" => serialize( $data )
                );
                $rs = $this->db->query( "SELECT * FROM sdb_sync_tmp WHERE 0=1" );
                $sql = $this->db->getinsertsql( $rs, $insert );
                if ( $sql )
                {
                    $this->db->exec( $sql );
                }
            }
        }
    }

    function ifdownloading( $supplier_id )
    {
        if ( $this->db->selectrow( "SELECT job_id FROM sdb_job_data_sync WHERE supplier_id=".floatval( $supplier_id ) ) || $this->db->selectrow( "SELECT job_id FROM sdb_job_goods_download WHERE supplier_id=".floatval( $supplier_id ) ) )
        {
            return true;
        }
        return false;
    }

}

?>
