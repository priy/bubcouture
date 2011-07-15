<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_brand extends objectPage
{

    public $workground = "goods";
    public $finder_action_tpl = "product/brand/finder_action.html";
    public $finder_default_cols = "brand_id,brand_name,brand_url,ordernum";
    public $object = "goods/brand";
    public $filterUnable = TRUE;

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "品牌详细信息" ),
                "tpl" => "product/brand/detail.html"
            )
        );
    }

    public function show_detail( $brand_id )
    {
        $oseo =& $this->system->loadModel( "system/seo" );
        $seo_info = $oseo->get_seo( "brand", $brand_id );
        $this->path[] = array(
            "text" => __( "商品品牌编辑" )
        );
        $brand =& $this->system->loadModel( "goods/brand" );
        $this->pagedata['brandInfo'] = $brand->getFieldById( $brand_id, array( "*" ) );
        if ( empty( $this->pagedata['brandInfo']['brand_url'] ) )
        {
            $this->pagedata['brandInfo']['brand_url'] = "http://";
        }
        foreach ( $brand->getBrandTypes( $brand_id ) as $row )
        {
            $aType[$row['type_id']] = 1;
        }
        $this->pagedata['seo'] = $seo_info;
        $this->pagedata['brandInfo']['type'] = $aType;
        $this->pagedata['type'] = $brand->getDefinedType( );
        $oGtype =& $this->system->loadModel( "goods/gtype" );
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined( );
    }

    public function addNew( )
    {
        $this->path[] = array(
            "text" => __( "商品品牌新增" )
        );
        $this->pagedata['brandInfo']['brand_url'] = "http://";
        $brand =& $this->system->loadModel( "goods/brand" );
        $this->pagedata['type'] = $brand->getDefinedType( );
        $this->pagedata['brandInfo']['type'][$this->pagedata['type']['default']['type_id']] = 1;
        $oGtype =& $this->system->loadModel( "goods/gtype" );
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined( );
        $this->page( "product/brand/detail.html" );
    }

    public function active( )
    {
        parent::active( );
        $brand =& $this->system->loadModel( "goods/brand" );
        $brand->brand2json( );
    }

    public function recycle( )
    {
        parent::recycle( );
        $brand =& $this->system->loadModel( "goods/brand" );
        $brand->brand2json( );
    }

    public function save( )
    {
        if ( $_POST['brand_id'] )
        {
            $this->begin( "index.php?ctl=goods/brand&act=detail&p[0]=".$_POST['brand_id'] );
        }
        else
        {
            $this->begin( "index.php?ctl=goods/brand&act=index" );
        }
        $brand =& $this->system->loadModel( "goods/brand" );
        $this->end( $brand->save( $_POST['brand_id'], $_POST ), __( "品牌保存成功" ) );
    }

    public function getCheckboxList( )
    {
        $brand =& $this->system->loadModel( "goods/brand" );
        $this->pagedata['checkboxList'] = $brand->getList( "brand_id,brand_name" );
        $this->display( "product/brand/checkbox_list.html" );
    }

}

?>
