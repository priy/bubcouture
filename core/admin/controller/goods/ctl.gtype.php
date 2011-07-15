<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_gtype extends objectPage
{

    public $workground = "goods";
    public $object = "goods/gtype";
    public $finder_action_tpl = "product/gtype/finder_action.html";
    public $allowImport = TRUE;
    public $finder_default_cols = "_cmd,name,is_physical";
    public $allowExport = FALSE;
    public $ioType = "gtype";
    public $filter = array
    (
        "schema_id" => "custom"
    );
    public $filterUnable = TRUE;

    public function index( )
    {
        parent::index( array(
            "params" => array( "is_def" => "false" )
        ) );
    }

    public function finder_pager( )
    {
        $GLOBALS['_POST']['is_def'] = "false";
        parent::finder_pager( );
    }

    public function save_cell_value( $id, $key )
    {
        if ( empty( $_POST['data'] ) )
        {
            $this->begin( "index.php?ctl=goods/gtype&act=index" );
            trigger_error( __( "类型名称为必填项" ), E_USER_ERROR );
            $this->end( );
        }
        if ( $key == "name" )
        {
            $otype = $this->system->loadModel( "goods/gtype" );
            if ( $otype->checkTypeByName( $_POST['data'], $id ) )
            {
                $otype->nameSave( $id, $_POST['data'] );
                parent::save_cell_value( $id, $key );
            }
        }
    }

    public function newType( )
    {
        $this->path[] = array(
            "text" => __( "添加类型" )
        );
        $objGschema =& $this->system->loadModel( "goods/schema" );
        $aSchema = $objGschema->getList( );
        foreach ( $aSchema as $k => $item )
        {
            if ( $item['is_def'] )
            {
                unset( $aSchema[$k] );
            }
        }
        $this->pagedata['schemas'] =& $aSchema;
        $this->title = __( "new goods type" );
        $this->page( "product/gtype/newType.html" );
    }

    public function toAdd( $schema, $tag )
    {
        if ( $tag == "commit" )
        {
            $_SESSION['gtype']['setting'] = $_POST['setting'];
            $_SESSION['gtype']['is_physical'] = $_POST['is_physical'];
            if ( $_SESSION['gtype']['alias'] == "a:1:{i:0;s:0:\"\";}" )
            {
                unset( $this->gtype['alias'] );
            }
            $this->editType( );
        }
        else
        {
            unset( $_SESSION['gtype'] );
            $_SESSION['gtype']['schema_id'] = $schema;
            $objGschema =& $this->system->loadModel( "goods/schema" );
            $objGschema->dialog( $schema );
        }
    }

    public function toEdit( $typeid )
    {
        if ( $typeid )
        {
            $gtype =& $this->system->loadModel( "goods/gtype" );
            $aType = $gtype->getTypeDetail( $typeid, TRUE );
            $_SESSION['gtype'] = $aType;
            $GLOBALS['_POST']['setting'] = $aType['setting'];
            $GLOBALS['_POST']['is_physical'] = $aType['is_physical'];
        }
        $objGschema =& $this->system->loadModel( "goods/schema" );
        $objGschema->dialog( $aType['schema_id'] );
    }

    public function editType( )
    {
        $this->path[] = array(
            "text" => __( "类型编辑" )
        );
        $aType = $_SESSION['gtype'];
        $this->pagedata['gtype'] = $aType;
        $brand =& $this->system->loadModel( "goods/brand" );
        foreach ( $brand->getAll( ) as $rows )
        {
            $aTmpList[$rows['brand_id']] = $rows['brand_name'];
        }
        $this->pagedata['brands'] = $aTmpList;
        $this->page( "product/gtype/workpage.html" );
    }

    public function toSave( )
    {
        if ( empty( $_POST['name'] ) )
        {
            $this->begin( "index.php?ctl=goods/gtype&act=newType" );
            trigger_error( __( "类型名称不能为空" ), E_USER_ERROR );
            $this->end( );
        }
        $this->begin( "index.php?ctl=goods/gtype&act=index" );
        $objGtype =& $this->system->loadModel( "goods/gtype" );
        if ( isset( $_SESSION['gtype']['is_physical'] ) )
        {
            $GLOBALS['_POST']['is_physical'] = $_SESSION['gtype']['is_physical'];
        }
        if ( isset( $_SESSION['gtype']['schema_id'] ) )
        {
            $GLOBALS['_POST']['schema_id'] = $_SESSION['gtype']['schema_id'];
        }
        if ( isset( $_SESSION['gtype']['setting'] ) )
        {
            $GLOBALS['_POST']['setting'] = $_SESSION['gtype']['setting'];
        }
        if ( isset( $_SESSION['gtype']['dly_func'] ) )
        {
            $GLOBALS['_POST']['dly_func'] = $_SESSION['gtype']['dly_func'];
        }
        if ( isset( $_SESSION['gtype']['ret_func'] ) )
        {
            $GLOBALS['_POST']['ret_func'] = $_SESSION['gtype']['ret_func'];
        }
        if ( isset( $_SESSION['gtype']['reship'] ) )
        {
            $GLOBALS['_POST']['reship'] = $_SESSION['gtype']['reship'];
        }
        if ( isset( $_SESSION['gtype']['disabled'] ) )
        {
            $GLOBALS['_POST']['disabled'] = $_SESSION['gtype']['disabled'];
        }
        if ( isset( $_SESSION['gtype']['is_def'] ) )
        {
            $GLOBALS['_POST']['is_def'] = $_SESSION['gtype']['is_def'];
        }
        $this->end( $objGtype->toSave( $_POST ), __( "保存成功" ) );
    }

    public function delete( )
    {
        $this->begin( "index.php?ctl=goods/gtype&act=index" );
        $objType =& $this->system->loadModel( "goods/gtype" );
        if ( is_array( $_REQUEST['type_id'] ) )
        {
            foreach ( $_REQUEST['type_id'] as $id )
            {
                $objType->toRemove( $id );
            }
        }
        $objType->checkDefined( );
        $this->end_only( TRUE );
        echo __( "删除成功" );
    }

    public function recycle( )
    {
        $objType =& $this->system->loadModel( "goods/gtype" );
        $varGoto = 1;
        foreach ( $_REQUEST['type_id'] as $type_id )
        {
            if ( !$objType->checkDelete( $type_id, $result ) )
            {
                if ( $result == 1 )
                {
                    echo __( "通用商品类型为系统默认类型，不能删除" );
                }
                if ( $result == 2 )
                {
                    echo __( "类型下存在与之关联的商品，无法删除" );
                }
                $varGoto = 0;
                break;
            }
        }
        if ( $varGoto )
        {
            parent::recycle( );
            $objType =& $this->system->loadModel( "goods/gtype" );
            $objType->checkDefined( );
        }
    }

    public function fetchProtoTypes( $link, $querystring = "" )
    {
        header( "Content-Type: text/html;charset=utf-8" );
        $net =& $this->system->loadModel( "utility/http_client" );
        $cert = $this->system->getConf( "certificate.id" );
        $token = $this->system->getConf( "certificate.token" );
        $sc = md5( "goostypefeed".$cert.$token );
        $url = "http://feed.shopex.cn/goodstype/".$link."?certificate=".$cert."&sc=".$sc.( $querystring ? "&".$querystring : "" );
        $net->http_ver = "1.0";
        $result = $net->get( $url );
        if ( $result && FALSE !== substr( $result, "shopexfeed" ) )
        {
            echo $result;
        }
        else
        {
            echo "<div style=\"width:300px;height:80px;\"><BR><BR>".__( "因网络连接或其它原因，暂时无法获取系统默认类型信息。<BR>请稍候再试...错误信息" ).$net->responseCode."</div><div style=\"clear:both\">";
        }
    }

    public function getXml( $id )
    {
        $o =& $this->system->loadModel( "goods/gtype" );
        $xml =& $this->system->loadModel( "utility/xml" );
        $xmlpart = $xml->array2xml( $o->getTypeObj( $id, $name ), "goodstype" );
        $charset =& $this->system->loadModel( "utility/charset" );
        download( $name.".typ", $xmlpart );
    }

    public function saveSpec( )
    {
        $this->begin( "index.php?ctl=goods/gtype&act=index" );
        $objType =& $this->system->loadModel( "goods/gtype" );
        $this->end( $objType->saveSpec( $_POST['type_id'], $_POST['specs'] ), __( "保存成功" ) );
    }

    public function fetchSave( )
    {
        $this->begin( "index.php?ctl=goods/gtype&act=index", array(
            300001 => "index.php?ctl=goods/gtype&act=fetchProtoTypes&p[0]=gtype.php&p[1]=id=".$_POST['param_id']
        ) );
        $xml =& $this->system->loadModel( "utility/xml" );
        $map = $xml->xml2array( $_POST['xml'] );
        $gtype = $map['goodstype'];
        $gtype['name'] = $_POST['gtypename'];
        $o =& $this->system->loadModel( "goods/gtype" );
        $this->end( $o->fetchSave( $gtype ), __( "类型导入成功" ) );
    }

    public function import( )
    {
        $dataio =& $this->system->loadModel( "system/dataio" );
        $dataio->privateImport = TRUE;
        $this->pagedata['importer'] = $dataio->importer( $this->ioType );
        $this->pagedata['ctl'] = "goods/gtype";
        $this->pagedata['optionsView'] = $this->importOptions;
        $this->page( "finder/import.html" );
    }

    public function checkTypeNameExists( )
    {
        $o = $this->system->loadModel( "goods/gtype" );
        if ( $o->getList( "type_id", array(
            "name" => $_POST['gtypename']
        ) ) )
        {
            echo "<script>alert(\"本类型名在系统中已存在，请更名\");</script>";
        }
        else
        {
            echo "<script>alert(\"本类型名在系统中不存在，可正常添加\");</script>";
        }
    }

    public function importer( )
    {
        $this->begin( "index.php?ctl=goods/gtype&act=index" );
        $dataio =& $this->system->loadModel( "system/dataio" );
        $gtype =& $this->system->loadModel( "goods/gtype" );
        if ( substr( $_FILES['upload']['name'], -4 ) != ".typ" )
        {
            trigger_error( __( "文件格式有误" ), E_USER_ERROR );
            exit( );
        }
        $content = file_get_contents( $_FILES['upload']['tmp_name'] );
        if ( substr( $content, 0, 3 ) == "﻿" )
        {
            $content = substr( $content, 3 );
        }
        $data = $dataio->import_rows( $_POST['type'], $content );
        $imported = FALSE;
        foreach ( $data as $type )
        {
            if ( $type['name'] )
            {
                $type['name'] = $type['name'].time( );
                $gtype->fetchSave( $type );
                $imported = TRUE;
            }
        }
        if ( $imported )
        {
            $this->end( TRUE, __( "类型数据导入成功,请修改自动生成的类型名" ) );
        }
        else
        {
            trigger_error( __( "由于格式错误或上传问题，类型数据无法导入" ), E_USER_ERROR );
        }
    }

    public function typeTransformCheck( $type )
    {
        $oGtype = $this->system->loadModel( "goods/gtype" );
        $GLOBALS['_POST']['goods']['spec_desc'] = unserialize( urldecode( $_POST['goods']['spec_desc'] ) );
        if ( $type == "cat" )
        {
            $oCat = $this->system->loadModel( "goods/productCat" );
            $aTmp = $oCat->getFieldById( $_POST['goods']['cat_id'], array( "type_id" ) );
            $GLOBALS['_POST']['goods']['type_id'] = $aTmp['type_id'];
        }
        if ( $oGtype->typeTransformCheck( $_POST['oldTypeId'], $_POST['goods']['type_id'], $_POST['goods'] ) )
        {
            echo "1";
        }
        else
        {
            $errorMsg = " ";
            foreach ( $oGtype->specTransformError as $sv )
            {
                $errorMsg .= $sv['spec_name']." (";
                $errorMsg .= implode( "、", array_values( $sv['spec_value'] ) );
                $errorMsg .= ") ";
            }
            echo "该商品的".$errorMsg."找不到匹配项";
        }
    }

}

?>
