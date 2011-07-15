<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_supplier extends adminPage
{

    public $name = "供应商";
    public $workground = "distribution";
    public $object = "distribution/supplier";

    public function ctl_supplier( )
    {
        parent::adminpage( );
        if ( !$this->system->getConf( "certificate.distribute" ) )
        {
            $this->errorJump( );
        }
    }

    public function errorJump( )
    {
        switch ( $_GET['act'] )
        {
        case "suppilerApiList" :
            echo "[]";
            break;
            exit( );
        case "doDataSync" :
            echo "{\"status\":\"finish\"}";
            break;
            exit( );
        case "doGoodsSync" :
            echo "{\"cat_id\":null,\"msg\":\"\",\"status\":\"finish\"}";
            break;
            exit( );
        case "doImagesSync" :
            echo "{\"status\":\"finish\"}";
            break;
            exit( );
        case "doAutoSync" :
            echo "{\"status\":\"done\"}";
            break;
            exit( );
        case "doCostSync" :
            echo "{\"status\":\"done\"}";
            break;
            exit( );
        default :
            header( "Location: index.php?ctl=dashboard&act=index" );
            break;
        }
    }

    public function index( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $page = $_POST['page'];
        if ( $page )
        {
            $this->_supplierList( $filter, $page );
        }
        else
        {
            $this->_supplierList( $filter );
        }
        $this->pagedata['otype'] = "index";
        $this->page( "distribution/index.html" );
    }

    public function supplierList( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $filter['supplier_brief_name'] = $_POST['sname'];
        $page = $_POST['page'];
        if ( $page )
        {
            $this->_supplierList( $filter, $page );
        }
        else
        {
            $this->_supplierList( $filter );
        }
        $this->pagedata['otype'] = "list";
        $this->pagedata['sname'] = $filter['supplier_brief_name'];
        $this->display( "distribution/supplier_list.html" );
    }

    public function _supplierList( $filter, $page = 1, $limit = 20 )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $sList = $oSupplier->getList( "*", $filter, ( $page - 1 ) * $limit, $limit );
        $count = $oSupplier->count( $filter );
        $oCostSync = $this->system->loadModel( "distribution/costsync" );
        $sList = $oCostSync->getSupplierCostSyncStatus( $sList );
        $oSupplier->filterSupplierList( $sList );
        $pager = array(
            "current" => $page,
            "total" => ceil( $count / $limit ),
            "link" => "javascript:turnp(_PPP_)",
            "token" => "_PPP_"
        );
        $this->pagedata['status'] = array( "1" => "正常状态", "2" => "经销商解除关系", "3" => "供应商解除关系" );
        $this->pagedata['pager'] = $pager;
        $this->pagedata['supplier'] = $sList;
        $this->pagedata['page'] = $page;
        $this->pagedata['today_time'] = strtotime( date( "Y-m-d" )." 00:00:00" );
    }

    public function productLine( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $plList = $oDataSync->getProductLine( trim( $_POST['supplier_id'] ) );
        $cat = $this->system->loadModel( "goods/productCat" );
        $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        $this->pagedata['cats'] = $cat->get_cat_list( );
        $this->pagedata['plList'] = $plList;
        $this->display( "distribution/product_line.html" );
    }

    public function _checkSync( $supplier_id )
    {
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $plList = $oDataSync->getProductLine( trim( $supplier_id ) );
        if ( empty( $plList ) )
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    public function checkSync( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        if ( !$this->_checkSync( $_POST['supplier_id'] ) )
        {
            echo "invalid";
        }
        else
        {
            echo "valid";
        }
    }

    public function datasync( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $syncJob = $this->system->loadModel( "distribution/syncjob" );
        $supplier = $this->system->loadModel( "distribution/supplier" );
        $datasync = $this->system->loadModel( "distribution/datasync" );
        $oAutoSync = $this->system->loadModel( "distribution/autosync" );
        $oAutoSync->generateAutoSyncConfigFile( );
        $time = time( );
        $supplier->clearTmpData( $_POST['supplier_id'] );
        $syncJob->addDataSyncJob( $_POST['supplier_id'] );
        $datasync->filterUpdateList_1( $_POST['supplier_id'], "sync" );
        $supplier->updateSupplierSynctime( $_POST['supplier_id'], $time );
        $supplier->updateSupplierHasNew( $_POST['supplier_id'] );
        echo "success";
    }

    public function syncDataList( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $searchData = array(
            "update_content" => intval( $_POST['update_content'] ),
            "ctrl_status" => intval( $_POST['ctrl_status'] ),
            "s_update_time" => empty( $_POST['s_update_time'] ) ? FALSE : $_POST['s_update_time'],
            "e_update_time" => empty( $_POST['e_update_time'] ) ? FALSE : $_POST['e_update_time'],
            "search_name" => trim( $_POST['search_name'] )
        );
        $page = $_POST['page'] ? intval( $_POST['page'] ) : 1;
        $this->_syncDataList( $_GET['supplier_id'], $searchData, $page );
        $this->pagedata['otype'] = "index";
        $this->page( "distribution/data_sync.html" );
    }

    public function _syncDataList( $supplierid, $searchData, $page = 1, $limit = 20 )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $supplier = $this->system->loadModel( "distribution/supplier" );
        $sList = $supplier->getSyncDataList( $supplierid, $searchData, $count, $page, $limit );
        foreach ( $sList as $sk => $sv )
        {
            $sList[$sk]['goods_id'] = $supplier->getLocalGoodsId( $supplierid, $sv['object_id'] );
        }
        $command = array( "1-1" => "商品上架", "1-2" => "商品下架", "2" => "货品库存变更", "3" => "商品图片更新", "4" => "商品更新", "5" => "货品更新", "6" => "商品新增", "7" => "商品删除" );
        $status = array( "unoperated" => "<span class=\"fontcolorRed fontbold\">未操作</span>", "unmodified" => "<span class=\"fontcolorRed\">已下载未处理</span>", "uncompleted_image" => "<span class=\"fontcolorRed\">图片未下载完全</span>", "done" => "<span class=\"fontcolorLightGray\">操作完成</span>" );
        $pager = array(
            "current" => $page,
            "total" => ceil( $count / $limit ),
            "link" => "javascript:turnd(_PPP_)",
            "token" => "_PPP_"
        );
        $suDomain = $supplier->getSupplierInfo( $supplierid, "domain" );
        $this->pagedata['supplier_domain'] = substr( $suDomain['domain'], -1, 1 ) == "/" ? $suDomain['domain'] : $suDomain['domain']."/";
        $this->pagedata['pager'] = $pager;
        $this->pagedata['page'] = $page;
        $this->pagedata['status'] = $status;
        $this->pagedata['command'] = $command;
        $this->pagedata['sList'] = $sList;
        $this->pagedata['supplier_id'] = $supplierid;
    }

    public function getSyncDataList( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $searchData = array(
            "update_content" => intval( $_POST['update_content'] ),
            "ctrl_status" => intval( $_POST['ctrl_status'] ),
            "s_update_time" => empty( $_POST['s_update_time'] ) ? FALSE : $_POST['s_update_time'],
            "e_update_time" => empty( $_POST['e_update_time'] ) ? FALSE : $_POST['e_update_time'],
            "search_name" => trim( $_POST['search_name'] )
        );
        $page = $_POST['page'] ? intval( $_POST['page'] ) : 1;
        $this->_syncDataList( $_GET['supplier_id'], $searchData, $page );
        $this->pagedata['otype'] = "list";
        $this->display( "distribution/data_sync_list.html" );
    }

    public function downloadGoods( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $syncInfo = $oDataSync->downloadGoods( $_POST['command_id'], $_POST['supplier_id'], $_POST['object_id'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "unmodified" );
        $cat = $this->system->loadModel( "goods/productCat" );
        $brand = $this->system->loadModel( "goods/brand" );
        $brand->brand2json( );
        $cat->cat2json( );
        $rs = array( );
        if ( !empty( $syncInfo ) )
        {
            $cat_id = $syncInfo['locals']['local_cat_id'];
            $cat_info = $cat->getFieldById( $cat_id, array( "cat_name" ) );
            $rs['msg'] = "已下载到\"".$cat_info['cat_name']."\"中\n";
            foreach ( $syncInfo['type'] as $stv )
            {
                $rs['msg'] .= "已新增".$stv."类型\n";
            }
            foreach ( $syncInfo['spec'] as $ssv )
            {
                $rs['msg'] .= "已新增".$ssv."规格\n";
            }
            foreach ( $syncInfo['brand']['add'] as $sbav )
            {
                $rs['msg'] .= "已新增".$sbav."品牌\n";
            }
            foreach ( $syncInfo['brand']['update'] as $sbuv )
            {
                $rs['msg'] .= "已更新".$sbuv."品牌\n";
            }
            foreach ( $syncInfo['cat'] as $scv )
            {
                $rs['msg'] .= "已新增".$scv."分类\n";
            }
        }
        else
        {
            $rs['msg'] = "操作完成";
        }
        $rs['goods_id'] = $oSupplier->getLocalGoodsId( $_POST['supplier_id'], $_POST['object_id'] );
        echo json_encode( $rs );
    }

    public function updateGoods( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $syncInfo = $oDataSync->preDownload( $_POST['supplier_id'], $_POST['object_id'], $_POST['command_id'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "unmodified" );
        $cat = $this->system->loadModel( "goods/productCat" );
        $brand = $this->system->loadModel( "goods/brand" );
        $brand->brand2json( );
        $cat->cat2json( );
        $rs = array( );
        $rs['msg'] = "";
        foreach ( $syncInfo['type'] as $stv )
        {
            $rs['msg'] .= "已新增 ".$stv." 类型\n";
        }
        foreach ( $syncInfo['spec'] as $ssv )
        {
            $rs['msg'] .= "已新增 ".$ssv." 规格\n";
        }
        foreach ( $syncInfo['brand']['add'] as $sbav )
        {
            $rs['msg'] .= "已新增 ".$sbav." 品牌\n";
        }
        foreach ( $syncInfo['brand']['update'] as $sbuv )
        {
            $rs['msg'] .= "已更新 ".$sbuv." 品牌\n";
        }
        foreach ( $syncInfo['cat'] as $scv )
        {
            $rs['msg'] .= "已新增 ".$scv." 分类\n";
        }
        echo json_encode( $rs );
    }

    public function updateGoodsImage( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->updateGoodsImageFailed( $_POST['command_id'], $_POST['supplier_id'] );
        $oDataSync->updateGoodsImage( $_POST['command_id'], $_POST['supplier_id'], $_POST['object_id'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "done" );
    }

    public function syncStore( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oDatasync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oDatasync->syncProductStore( $_POST['supplier_id'], $_POST['object_id'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "done" );
    }

    public function syncMarketable( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $commandInfo = $oSupplier->getCommandInfo( $_POST['command_id'], $_POST['supplier_id'] );
        $oSupplier->updateGoodsMarketable( $_POST['supplier_id'], $_POST['object_id'], $commandInfo['goods_info']['marketable'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "done" );
    }

    public function syncUnMarketable( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->updateGoodsMarketable( $_POST['supplier_id'], $_POST['object_id'], "false" );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "done" );
    }

    public function deleteGoods( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->removeGoods( $_POST['supplier_id'], $_POST['object_id'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "done" );
    }

    public function updateGoodsProducts( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oDatasync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oDatasync->updateGoodsProduct( $_POST['supplier_id'], $_POST['object_id'], $_POST['command_id'] );
        $oSupplier->updateSyncStatus( $_POST['command_id'], $_POST['supplier_id'], "unmodified" );
    }

    public function refreshSupplier( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $osync = $this->system->loadModel( "distribution/datasync" );
        $count = "";
        $osync->syncSupplier( NULL, $count );
    }

    public function downloadPline( )
    {
        $datasync = $this->system->loadModel( "distribution/datasync" );
        if ( !$datasync->ifDownloading( $_POST['supplier_id'] ) )
        {
            set_error_handler( array(
                $this,
                "_ajaxErrorHandler"
            ) );
            $log_file = HOME_DIR."/logs/goodsdown.log";
            $log_info = json_decode( file_get_contents( $log_file ), TRUE );
            if ( isset( $log_info[$_POST['supplier_id']] ) )
            {
                unset( $log_info[$_POST['supplier_id']] );
            }
            file_put_contents( $log_file, json_encode( $log_info ), LOCK_EX );
            $oSyncJob = $this->system->loadModel( "distribution/syncjob" );
            $oSupplier = $this->system->loadModel( "distribution/supplier" );
            $time = time( );
            foreach ( $_POST['pline_id'] as $k => $v )
            {
                $v = explode( "|", $v );
                $GLOBALS['_POST']['pline_id'][$k] = array(
                    "cat_id" => $v[1] ? $v[0].",".$v[1] : $v[0],
                    "brand_id" => $v[2]
                );
            }
            $datasync->addSyncTmpData( $_POST['supplier_id'], $_POST['pline_id'] );
            $oSyncJob->addDataSyncJob( $_POST['supplier_id'], $_POST['pline_id'], TRUE, 20, $_POST['cat'] );
            $datasync->filterUpdateList_1( $_POST['supplier_id'], "download" );
            $oSupplier->updateSupplierSynctime( $_POST['supplier_id'], $time );
            $oSupplier->updateSupplierHasNew( $_POST['supplier_id'] );
        }
        else
        {
            header( "HTTP/1.1 501 Not Implemented" );
            $msg = urlencode( "正在下载中，请不要重复下载" );
            header( "notify_msg:".$msg );
        }
    }

    public function doDataSync( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oSyncJob = $this->system->loadModel( "distribution/syncjob" );
        if ( $_POST['step'] == "1" && $oSyncJob->checkLock( "data_sync" ) )
        {
            echo json_encode( array( "status" => "lock" ) );
        }
        else if ( $oSyncJob->doDataSyncJob( ) === 0 )
        {
            echo json_encode( array( "status" => "finish" ) );
        }
        else
        {
            $oSupplier = $this->system->loadModel( "distribution/supplier" );
            $jobList = $oSupplier->getDoSyncJobList( );
            if ( empty( $jobList ) )
            {
                echo json_encode( array( "status" => "continue" ) );
            }
            else
            {
                echo json_encode( array(
                    "status" => "continue",
                    "joblist" => $jobList
                ) );
            }
        }
    }

    public function doGoodsSync( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oSyncJob = $this->system->loadModel( "distribution/syncjob" );
        if ( $_POST['step'] == "1" && $oSyncJob->checkLock( "download_goods" ) )
        {
            $echoJson['msg'] = "";
            $echoJson['status'] = "lock";
            echo json_encode( $echoJson );
        }
        else
        {
            $rs = $oSyncJob->doGoodsDownloadJob( );
            $echoJson = array(
                "cat_id" => $rs['current'] == $rs['count'] ? $rs['cat_id'] : ""
            );
            if ( $rs === 0 )
            {
                if ( $_POST['step'] != "1" )
                {
                    $echoJson['msg'] = "下载完成";
                    $echoJson['status'] = "finish";
                    echo json_encode( $echoJson );
                    $cat = $this->system->loadModel( "goods/productCat" );
                    $brand = $this->system->loadModel( "goods/brand" );
                    $brand->brand2json( );
                    $cat->cat2json( );
                }
                else
                {
                    $echoJson['msg'] = "";
                    $echoJson['status'] = "finish";
                    echo json_encode( $echoJson );
                }
            }
            else
            {
                $oSupplier = $this->system->loadModel( "distribution/supplier" );
                $suinfo = $oSupplier->getSupplierInfo( $rs['supplier_id'], "supplier_brief_name" );
                $echoJson['msg'] = "已经下载了".$rs['current']."/".$rs['count']."条 ".$suinfo['supplier_brief_name'];
                $echoJson['status'] = "continue";
                echo json_encode( $echoJson );
            }
        }
    }

    public function doImagesSync( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oSyncJob = $this->system->loadModel( "distribution/syncjob" );
        if ( empty( $_POST['command_id'] ) )
        {
            $commandid = NULL;
        }
        else
        {
            $commandid = $_POST['command_id'];
        }
        if ( $_POST['step'] == "1" && $oSyncJob->checkLock( "download_image" ) || $oSyncJob->downloadImage( FALSE, $commandid ) === 0 )
        {
            echo json_encode( array( "status" => "finish" ) );
        }
        else
        {
            echo json_encode( array( "status" => "continue" ) );
        }
    }

    public function doSupplierApiListJob( $supplier_id, $api_name, $api_action )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        if ( $oSupplier->doSupplierApiListJob( $supplier_id, $api_name, $api_action ) )
        {
            echo json_encode( array( "status" => "continue" ) );
        }
        else
        {
            echo json_encode( array( "status" => "finish" ) );
        }
    }

    public function suppilerApiList( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        echo json_encode( $oSupplier->getSupplierApiList( ) );
    }

    public function coverGoodsInfo( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $oDatasync = $this->system->loadModel( "distribution/datasync" );
        $goods = $oDatasync->getSupplierGoodsInfo( $_POST['supplier_id'], $_POST['object_id'] );
        $goods['params'] = unserialize( $goods['params'] );
        $goods['type_id'] = $oDatasync->_getLocalTypeByPlatType( $_POST['supplier_id'], $goods['type_id'] );
        $goods['brand_id'] = $oDatasync->_getLocalBrandByPlatBrand( $_POST['supplier_id'], $goods['brand_id'] );
        $gType = $this->system->loadModel( "goods/gtype" );
        $typeinfo = $gType->getTypeDetail( $goods['type_id'] );
        $oBrand = $this->system->loadModel( "goods/brand" );
        $brandList = $oBrand->getTypeBrands( $goods['type_id'] );
        foreach ( $typeinfo['props'] as $key => $row )
        {
            $typeinfo['props'][$key]['selected'] = $goods["p_".$key];
        }
        if ( $typeinfo['params'] )
        {
            foreach ( $typeinfo['params'] as $key => $row )
            {
                foreach ( $row as $key1 => $row1 )
                {
                    $typeinfo['params'][$key][$key1] = array( );
                    $typeinfo['params'][$key][$key1]['value'] = $row1;
                    $typeinfo['params'][$key][$key1]['name'] = "goods[params][{$key}][{$key1}]";
                    $typeinfo['params'][$key][$key1]['selected'] = $goods['params'][$key][$key1];
                }
            }
        }
        $goodsJson = array( );
        $goodsJson['type_id'] = $goods['type_id'];
        $goodsJson['brand_id'] = $goods['brand_id'];
        $goodsJson['unit'] = $goods['unit'];
        $goodsJson['brief'] = $goods['brief'];
        $goodsJson['mktprice'] = $goods['mktprice'];
        $goodsJson['name'] = $goods['name'];
        $goodsJson['weight'] = $goods['weight'];
        $this->pagedata['brandList'] = $brandList;
        $this->pagedata['goodsInfoJson'] = json_encode( $goodsJson );
        $this->pagedata['goods'] = $goods;
        $this->pagedata['prototype'] = $typeinfo;
        $this->display( "distribution/goods/goods_type_info.html" );
    }

    public function supplierGoodsInfo( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $oDatasync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $goods = $oDatasync->getSupplierGoodsInfo( $_GET['supplier_id'], $_GET['object_id'] );
        $supplierName = $oSupplier->getSupplierInfo( $_GET['supplier_id'], "supplier_brief_name" );
        $goods['type_props'] = unserialize( $goods['type_props'] );
        $goods['params'] = unserialize( $goods['params'] );
        foreach ( $goods['products'] as $pk => $pv )
        {
            $goods['products'][$pk]['props'] = unserialize( $pv['props'] );
        }
        $this->pagedata['supplier_brief_name'] = $supplierName['supplier_brief_name'];
        $this->pagedata['goods'] = $goods;
        $this->pagedata['supplier_id'] = $_GET['supplier_id'];
        $this->pagedata['object_id'] = $_GET['object_id'];
        $this->pagedata['command_id'] = $_GET['command_id'];
        $this->pagedata['local_goods_id'] = $_GET['local_goods_id'];
        $this->display( "distribution/goods/goods_info.html" );
    }

    public function syncComplete( )
    {
        $oCat = $this->system->loadModel( "goods/productCat" );
        $catName = $oCat->getFieldById( $_POST['cat_id'], array( "cat_name" ) );
        $this->pagedata['cat_name'] = $catName['cat_name'];
        $this->pagedata['cat_id'] = $_POST['cat_id'];
        $this->display( "distribution/sync_complete.html" );
    }

    public function generalize( )
    {
        $this->pagedata['url'] = GENERALIZE_URL;
        $this->page( "distribution/generalize.html" );
    }

    public function doAutoSync( )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $oAutoSync = $this->system->loadModel( "distribution/autosync" );
        if ( empty( $_POST['step'] ) )
        {
            $count = $oAutoSync->getAutoSyncTaskCount( $_POST['supplier_id'] );
            if ( empty( $count ) )
            {
                $aResult['status'] = "done";
                exit( json_encode( $aResult ) );
            }
            $aResult['count'] = $count;
        }
        else
        {
            $aResult['count'] = $_POST['step'];
        }
        $aTask = $oAutoSync->getAutoSyncTask( $_POST['supplier_id'] );
        if ( empty( $aTask ) )
        {
            $aResult['status'] = "done";
            exit( json_encode( $aResult ) );
        }
        $oAutoSync->doSync( $aTask['supplier_id'], $aTask['command_id'], $aTask['local_op_id'] );
        $oAutoSync->deleteAutoSyncTask( $aTask['supplier_id'], $aTask['command_id'] );
        $aResult['command_id'] = $aTask['command_id'];
        $aResult['op_id'] = $aTask['local_op_id'];
        $aResult['status'] = "continue";
        --$aResult['count'];
        echo json_encode( $aResult );
    }

    public function autoSyncJob( )
    {
        $oAutoSync = $this->system->loadModel( "distribution/autosync" );
        echo json_encode( $oAutoSync->getAutoSyncSupplierList( ) );
    }

    public function doCostSync( )
    {
        $oCostSync = $this->system->loadModel( "distribution/costsync" );
        if ( !isset( $_POST['step'] ) )
        {
            $oCostSync->generateCostSyncJob( $_POST['supplier_id'] );
            $aResult['count'] = $oCostSync->getCostSyncJobCount( $_POST['supplier_id'] );
        }
        else
        {
            $aResult['status'] = $oCostSync->doCostSyncJob( $_POST['supplier_id'] );
            $aResult['count'] = $_POST['step'] - 1;
            if ( $aResult['status'] == "done" )
            {
                $aResult['count'] = $oCostSync->getCostSyncDoneCount( $_POST['supplier_id'] );
            }
        }
        exit( json_encode( $aResult ) );
    }

    public function costSyncJob( )
    {
        $oCostSync = $this->system->loadModel( "distribution/costsync" );
        echo json_encode( $oCostSync->getCostSyncList( ) );
    }

    public function _ajaxErrorHandler( $errno, $errstr, $errfile, $errline )
    {
        if ( $errstr == "0x015" )
        {
            if ( $_REQUEST['act'] == "refreshSupplier" )
            {
                exit( );
            }
            $this->_cancelTask( $_REQUEST['supplier_id'] );
            $this->_err_process( "供应商:".$_REQUEST['supplier_id']."服务暂时不可用", "ajax" );
        }
        parent::_ajaxerrorhandler( $errno, $errstr, $errfile, $errline );
    }

    public function _pageErrorHandler( $errno, $errstr, $errfile, $errline )
    {
        if ( $errstr == "0x015" )
        {
            $this->_cancelTask( $_REQUEST['supplier_id'] );
            if ( $_REQUEST['act'] != "index" && $_REQUEST['act'] != "supplierList" )
            {
                $this->_err_process( "供应商:".$_REQUEST['supplier_id']."服务暂时不可用", "page", "index.php?ctl=distribution/supplier&act=index" );
            }
        }
        parent::_pageerrorhandler( $errno, $errstr, $errfile, $errline );
    }

    public function _cancelTask( $supplier_id )
    {
        $supplier_id = intval( $supplier_id );
        if ( empty( $supplier_id ) )
        {
            return;
        }
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->cancelTask( $supplier_id );
    }

}

?>
