<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_supplier extends shopobject
{

    var $idColumn = "supplier_id";
    var $textColumn = "supplier_brief_name";
    var $defaultCols = "sp_id,supplier_id,supplier_brief_name,status,supplier_pline_id,sync_time";
    var $appendCols = "";
    var $adminCtl = "distribution/supplier";
    var $defaultOrder = array
    (
        0 => "has_new",
        1 => " ASC",
        2 => ",sp_id",
        3 => " DESC"
    );
    var $tableName = "sdb_supplier";
    var $typeName = "supplier";

    function getcolumns( $filter )
    {
        $columns = array(
            "sp_id" => array(
                "label" => "id",
                "class" => "span-3",
                "fuzzySearch" => 1,
                "required" => true,
                "primary" => true
            ),
            "supplier_id" => array(
                "label" => "供应商id",
                "class" => "span-8",
                "fuzzySearch" => 1,
                "required" => true,
                "primary" => true
            ),
            "supplier_brief_name" => array( "label" => "供应商简称", "class" => "span-3" ),
            "status" => array(
                "label" => "分销状态",
                "class" => "span-2",
                "readonly" => true
            ),
            "supplier_pline_id" => array( "label" => "产品线id", "class" => "span-2" ),
            "sync_time" => array(
                "label" => "同步时间",
                "class" => "span-2",
                "readonly" => true
            )
        );
        return $columns;
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['supplier_brief_name'] )
        {
            $where[] = " supplier_brief_name LIKE \"%".$filter['supplier_brief_name']."%\" ";
        }
        return implode( $where, " AND " );
    }

    function updatesupplierhasnew( $supplierId )
    {
        $data = array( "has_new" => "false" );
        $rs = $this->db->exec( "SELECT * FROM sdb_supplier WHERE supplier_id = ".$supplierId );
        $sql = $this->db->getupdatesql( $rs, $data );
        return $this->db->exec( $sql );
    }

    function updatesuppliersynctime( $supplierId, $time = null )
    {
        $data = array(
            "sync_time" => $time == null ? time( ) : $time
        );
        $rs = $this->db->exec( "SELECT * FROM sdb_supplier WHERE supplier_id = ".$supplierId );
        $sql = $this->db->getupdatesql( $rs, $data );
        return $this->db->exec( $sql );
    }

    function getsyncdatalist( $supplierId, $searchData, &$count, $page = 1, $limit = 20 )
    {
        $isTableExist = $this->db->select( "SHOW TABLES LIKE \"".$this->db->prefix."data_sync_".$supplierId."\" " );
        if ( empty( $isTableExist ) )
        {
            return null;
        }
        $where = "";
        switch ( $searchData['update_content'] )
        {
        case "1" :
            $where = " AND type = \"goods\" AND command = 6 ";
            break;
        case "2" :
            $where = " AND type = \"goods\" AND command = 4 ";
            break;
        case "3" :
            $where = " AND type = \"goods\" AND command = 1 AND marketable=\"true\" ";
            break;
        case "4" :
            $where = "";
            break;
        case "5" :
            $where = " AND type = \"product\" AND command = 2 AND store>0 ";
            break;
        case "6" :
            $where = " AND type = \"product\" AND command = 2 AND store=0 ";
            break;
        case "7" :
            $where = " AND type = \"goods\" AND command = 1 AND marketable=\"false\" ";
            break;
        case "8" :
            $where = " AND type = \"goods\" AND command = 7 ";
            break;
        case "9" :
            $where = " AND type = \"goods\" AND command = 5 ";
            break;
        case "10" :
            $where = " AND type = \"goods\" AND command = 3 ";
        }
        switch ( $searchData['ctrl_status'] )
        {
        case "1" :
            $where .= " AND command = 6 AND status = \"unmodified\" ";
            break;
        case "2" :
            $where .= " AND status = \"unoperated\" ";
            break;
        case "3" :
            $where .= " AND command = 6 AND status = \"unoperated\" ";
            break;
        case "4" :
            $where .= " AND command = 4 AND statuts = \"unmodified\" ";
            break;
        case "5" :
            $where .= " AND command = 1 AND marketable=\"true\" AND status = \"unoperated\" ";
            break;
        case "6" :
            $where .= " AND command = 2 AND status = \"unoperated\" ";
            break;
        case "7" :
            $where .= " AND (command = 7 OR (command = 1 AND marketable=\"false\")) AND status = \"unoperated\" ";
            break;
        case "8" :
            $where .= " AND status = \"done\" ";
            break;
        case "9" :
            $where .= " AND status = \"done\" AND command = 6 ";
            break;
        case "10" :
            $where .= " AND marketable=\"true\" AND command = 1 AND status = \"done\" ";
            break;
        case "11" :
            $where .= " AND command = 2 AND status = \"done\" ";
            break;
        case "12" :
            $where .= " AND marketable=\"false\" AND command = 1 AND status = \"done\" ";
            break;
        case "13" :
            $where .= " AND stauts = \"done\" AND command = 7 ";
            break;
        case "14" :
            $where .= " AND status = \"done\" AND command = 4 ";
        }
        if ( $searchData['s_update_time'] )
        {
            $where .= " AND last_modify >= ".$searchData['s_update_time'];
        }
        if ( $searchData['e_update_time'] )
        {
            $where .= " AND last_modify <= ".( $searchData['e_update_time'] + 86400 );
        }
        if ( $searchData['search_name'] )
        {
            $where .= " AND (bn=\"".$searchData['search_name']."\" OR name LIKE \"%".$searchData['search_name']."%\" )";
        }
        $count = $this->db->selectrow( "SELECT COUNT(command_id) AS c FROM sdb_data_sync_".$supplierId." WHERE if_show = \"true\" ".$where );
        $count = $count['c'];
        $slist = $this->db->select( "SELECT * FROM sdb_data_sync_".$supplierId." WHERE if_show = \"true\" ".$where." ORDER BY last_modify DESC LIMIT ".( $page - 1 ) * $limit.", ".$limit );
        $oDatasync = $this->system->loadmodel( "distribution/datasync" );
        foreach ( $slist as $k => $v )
        {
            if ( $v['command'] == "4" )
            {
                $slist[$k]['show_download'] = $oDatasync->checkgoodsdownload( $supplierId, $v['object_id'] ) ? 1 : 0;
            }
            $slist[$k]['command_info'] = unserialize( $v['command_info'] );
            if ( $v['command'] == "1" )
            {
                $slist[$k]['command_type'] = $v['command']."-".( $v['marketable'] == "true" ? "1" : "2" );
            }
            else
            {
                $slist[$k]['command_type'] = $v['command'];
            }
        }
        return $slist;
    }

    function updatesyncstatus( $commandId, $supplierId, $status )
    {
        $rs = $this->db->exec( "SELECT * FROM sdb_data_sync_".$supplierId." WHERE command_id = ".$commandId );
        $data = array(
            "status" => $status
        );
        $sql = $this->db->getupdatesql( $rs, $data );
        return $this->db->exec( $sql );
    }

    function getcommandinfo( $commandId, $supplierId )
    {
        $sql = "SELECT command_info FROM sdb_data_sync_".$supplierId." WHERE command_id = ".$commandId;
        $rs = $this->db->selectrow( $sql );
        return unserialize( $rs['command_info'] );
    }

    function updategoodsmarketable( $supplierId, $supplierGoodsId, $marketAble )
    {
        $data = array(
            "marketable" => $marketAble
        );
        $sql = "SELECT * FROM sdb_goods WHERE supplier_id = ".$supplierId." AND supplier_goods_id = ".$supplierGoodsId;
        $rs = $this->db->exec( $sql );
        $sql = $this->db->getupdatesql( $rs, $data );
        return $this->db->exec( $sql );
    }

    function removegoods( $supplierId, $supplierGoodsId )
    {
        $goodsId = $this->db->selectrow( "SELECT goods_id FROM sdb_goods WHERE supplier_id = ".$supplierId." AND supplier_goods_id = ".$supplierGoodsId );
        $goodsId = $goodsId['goods_id'];
        $this->db->exec( "UPDATE sdb_goods SET disabled = \"true\" WHERE goods_id = ".$goodsId );
        $objProduct = $this->system->loadmodel( "goods/products" );
        return $objProduct->setdisabled( array(
            $goodsId
        ), "true" );
    }

    function getsupplierinfo( $supplierId, $col = "*" )
    {
        return $this->db->selectrow( "SELECT ".$col." FROM sdb_supplier WHERE supplier_id = ".$supplierId );
    }

    function getlocalgoodsid( $supplierId, $objectId )
    {
        $sql = "SELECT goods_id FROM sdb_goods WHERE supplier_id = ".$supplierId." AND supplier_goods_id = ".$objectId;
        $rs = $this->db->selectrow( $sql );
        return $rs['goods_id'];
    }

    function getsourcebnbylocalbn( $localBn )
    {
        $bn = $this->db->selectrow( "SELECT source_bn FROM sdb_supplier_pdtbn WHERE local_bn = \"".$localBn."\"" );
        return $bn['source_bn'];
    }

    function updatesupplierpdtbn( $newBns, $delBns, $supplier_id )
    {
        foreach ( $newBns as $oldBn => $newBn )
        {
            $srcBn = $this->getsourcebnbylocalbn( $oldBn );
            $this->db->exec( "INSERT INTO sdb_supplier_pdtbn(local_bn,source_bn,supplier_id) VALUES(\"".$newBn."\",\"".$srcBn."\",\"".$supplier_id."\")" );
        }
        if ( !empty( $delBns ) )
        {
            $this->db->exec( "DELETE sdb_supplier_pdtbn WHERE local_bn IN (\"".implode( "\",\"", array_keys( $delBns ) )."\")" );
        }
    }

    function getdosyncjoblist( )
    {
        $sql = "SELECT supplier_id FROM sdb_job_data_sync GROUP BY supplier_id";
        $rs = $this->db->select( $sql );
        $ret = array( );
        foreach ( $rs as $v )
        {
            $ret[] = $v['supplier_id'];
        }
        return $ret;
    }

    function filtersupplierlist( &$list )
    {
        foreach ( $list as $k => $v )
        {
            $sql = "SELECT job_id FROM sdb_job_data_sync WHERE supplier_id = ".$v['supplier_id'];
            $rs = $this->db->selectrow( $sql );
            if ( $rs )
            {
                $list[$k]['sync_loading'] = "true";
            }
        }
    }

    function updategoodsimagefailed( $commandId, $supplierId )
    {
        $sql = "UPDATE sdb_image_sync SET failed = \"false\" WHERE failed = \"true\" AND command_id = ".$commandId;
        $this->db->exec( $sql );
        $sql = "UPDATE sdb_sdb_data_sync_".$supplierId." SET img_down_failed='false' WHERE img_down_failed='true' AND command_id=".$commandId;
        $this->db->exec( $sql );
    }

    function getsupplierapilist( )
    {
        $sql = "SELECT supplier_id FROM sdb_job_apilist GROUP BY supplier_id";
        $rs = $this->db->select( $sql );
        $res = array( );
        foreach ( $rs as $k => $v )
        {
            $res[$k]['supplier_id'] = $v['supplier_id'];
        }
        return $res;
    }

    function dosupplierapilistjob( $supplier_id, $api_name = NULL, $api_action = NULL )
    {
        $sql = "SELECT * FROM sdb_job_apilist WHERE supplier_id=".floatval( $supplier_id );
        if ( !empty( $api_name ) )
        {
            $sql .= " AND api_name='".$api_name."'";
        }
        if ( !empty( $api_action ) )
        {
            $sql .= " AND api_action='".$api_action."'";
        }
        $api_job = $this->db->selectrow( $sql );
        if ( !empty( $api_job ) )
        {
            if ( empty( $api_name ) )
            {
                $api_name = $api_job['api_name'];
            }
            if ( empty( $api_action ) )
            {
                $api_action = $api_job['api_action'];
            }
            switch ( $api_action )
            {
            case "distribution/datasync|filterUpdateList_1" :
                if ( !( $api_name == "getGoodsIdByPline" ) )
                {
                    break;
                }
                $api_params = unserialize( $api_job['api_params'] );
                $command_type = $api_params['command_type'];
                unset( $api_params->'command_type' );
                $rs = $this->db->query( "SELECT * FROM sdb_job_apilist WHERE job_id=".$api_job['job_id'] );
                $sql = $this->db->getupdatesql( $rs, array(
                    "api_params" => serialize( $api_params )
                ) );
                $this->db->exec( $sql );
                $oDataSync = $this->system->loadmodel( "distribution/datasync" );
                $oDataSync->filterupdatelist_2( $supplier_id, $command_type );
                return 1;
            case "distribution/datasync|addSyncTmpData" :
                if ( !( $api_name == "getBrands" ) || !( $api_name == "getTypes" ) || !( $api_name == "getSpecifications" ) || !( $api_name == "getCategories" ) )
                {
                    break;
                }
                $oDataSync = $this->system->loadmodel( "distribution/datasync" );
                $oDataSync->dosynctmpdata( $supplier_id, $api_name );
                return 1;
            case "distribution/datasync|doCostSyncJob" :
                $oCostSync = $this->system->loadmodel( "distribution/costsync" );
                $oCostSync->docostsyncjob( $supplier_id );
            }
            return 1;
        }
        return 0;
    }

    function cleartmpdata( $supplier_id )
    {
        $this->db->exec( "DELETE FROM sdb_sync_tmp WHERE supplier_id=".floatval( $supplier_id ) );
    }

    function canceltask( $supplier_id )
    {
        $sSql = "DELETE FROM sdb_job_apilist WHERE supplier_id='".$supplier_id."'";
        $this->db->exec( $sSql );
        $sSql = "DELETE FROM sdb_job_data_sync WHERE supplier_id='".$supplier_id."'";
        $this->db->exec( $sSql );
        $this->cleartmpdata( $supplier_id );
        $sSql = "DELETE FROM sdb_autosync_task WHERE supplier_id='".$supplier_id."'";
        $this->db->exec( $sSql );
    }

}

?>
