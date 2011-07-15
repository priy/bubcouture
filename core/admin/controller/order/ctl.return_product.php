<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_return_product extends objectPage
{

    public $finder_filter_tpl = "order/return_product/filter.html";
    public $workground = "order";
    public $object = "trading/return_product";
    public $filterUnable = TRUE;

    public function new_msg( )
    {
        $result = array( "no_handle" => 1 );
        parent::index( array(
            "params" => $result
        ) );
    }

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "退货单信息" ),
                "tpl" => "order/return_product/detail.html"
            )
        );
    }

    public function show_detail( $return_id )
    {
        $rp =& $this->system->loadModel( "trading/return_product" );
        $info = $rp->load( $return_id );
        $this->pagedata['info'] = $info;
    }

    public function save( )
    {
        $rp =& $this->system->loadModel( "trading/return_product" );
        $return_id = $_POST['return_id'];
        $status = $_POST['status'];
        $this->pagedata['return_status'] = $rp->change_status( $return_id, $status );
        $this->display( "order/return_product/return_status.html" );
    }

    public function send_comment( )
    {
        $rp =& $this->system->loadModel( "trading/return_product" );
        $return_id = $_POST['return_id'];
        $comment = $_POST['comment'];
        $this->begin( "index.php?ctl=order/return_product&act=detail&p[0]=".$return_id );
        if ( $rp->send_comment( $return_id, $comment ) )
        {
            $this->end( TRUE, __( "发送成功！" ) );
        }
        else
        {
            trigger_error( __( "发送失败" ), E_USER_ERROR );
            $this->end( );
        }
    }

    public function file_download( $return_id )
    {
        $rp =& $this->system->loadModel( "trading/return_product" );
        $info = $rp->load( $return_id );
        $filename = $info['image_file'];
        $rp->file_download( $filename );
    }

    public function string( )
    {
        $oPage =& $this->system->loadModel( "content/page" );
        unset( ['path'] );
        $this->path[] = array(
            "text" => __( "功能配置" )
        );
        $this->pagedata['is_open'] = $this->system->getConf( "site.is_open_return_product" );
        $this->pagedata['data'] = $oPage->get_tpl_content( "return_policy" );
        $this->pagedata['enable_purview_options'] = array(
            "true" => __( "开启" ),
            "false" => __( "关闭" )
        );
        $this->page( "setting/return_product.html" );
    }

    public function string_save( )
    {
        $obj =& $this->system->loadModel( "trading/return_product" );
        $oPage =& $this->system->loadModel( "content/page" );
        if ( $_POST['return_is_open'] == "true" )
        {
            $this->system->setConf( "site.is_open_return_product", TRUE );
        }
        else if ( $_POST['return_is_open'] == "false" )
        {
            $this->system->setConf( "site.is_open_return_product", FALSE );
        }
        if ( $_POST['conmment'] )
        {
            $aData['content'] = $_POST['conmment'];
            $aData['tmpl_name'] = "return_policy";
            $oPage->set_tpl_content( $aData );
        }
        else
        {
            $aData['content'] = "";
            $aData['tmpl_name'] = "return_policy";
            $oPage->set_tpl_content( $aData );
        }
        $this->begin( "index.php?ctl=order/return_product&act=string" );
        $this->end( );
    }

}

?>
