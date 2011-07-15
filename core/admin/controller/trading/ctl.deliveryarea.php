<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_deliveryarea extends objectPage
{

    public $workground = "setting";
    public $object = "trading/deliveryarea";
    public $finder_action_tpl = "delivery/area_finder_action.html";

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "配送地区" ),
                "tpl" => "delivery/area_edit.html"
            )
        );
    }

    public function show_detail( $aAreaId )
    {
        $this->path[] = array(
            "text" => __( "配送地区编辑" )
        );
        $oObj =& $this->system->loadModel( "trading/deliveryarea" );
        $this->pagedata['area'] = $oObj->getDlAreaById( $aAreaId );
    }

    public function index( )
    {
        $dArea =& $this->system->loadModel( "trading/deliveryarea" );
        $this->path[] = array(
            "text" => __( "配送地区列表" )
        );
        if ( $dArea->getTreeSize( ) )
        {
            $this->pagedata['area'] = $dArea->getRegionById( );
            $this->page( "delivery/area_treeList.html" );
        }
        else
        {
            $dArea->getMap( );
            $this->pagedata['area'] = $dArea->regions;
            $this->page( "delivery/area_map.html" );
        }
    }

    public function getChildNode( $regionId )
    {
        $dArea =& $this->system->loadModel( "trading/deliveryarea" );
        $this->pagedata['area'] = $dArea->getRegionById( $_POST['regionId'] );
        $this->display( "delivery/area_sub_treeList.html" );
    }

    public function showNewArea( $pRegionId )
    {
        if ( $pRegionId )
        {
            $dArea =& $this->system->loadModel( "trading/deliveryarea" );
            $this->pagedata['parent'] = $dArea->getRegionByParentId( $pRegionId );
        }
        $this->path[] = array(
            "text" => __( "添加配送地区" )
        );
        $this->page( "delivery/area_new.html" );
    }

    public function addDlArea( )
    {
        $oObj =& $this->system->loadModel( "trading/deliveryarea" );
        if ( !$oObj->insertDlArea( $_POST, $msg ) )
        {
            $this->message = array(
                "string" => __( "保存失败，" ).$msg,
                "type" => MSG_ERROR
            );
            $this->splash( "failed", "index.php?ctl=trading/deliveryarea&act=index", $this->message['string'] );
        }
        else
        {
            $this->splash( "success", "index.php?ctl=trading/deliveryarea&act=index" );
        }
    }

    public function saveDlArea( )
    {
        $oObj =& $this->system->loadModel( "trading/deliveryarea" );
        if ( !$oObj->updateDlArea( $_POST, $msg ) )
        {
            $this->message = array(
                "string" => __( "保存失败，" ).$msg,
                "type" => MSG_ERROR
            );
            $this->splash( "failed", "index.php?ctl=trading/deliveryarea&act=detailDlArea&p[0]=".$_POST['region_id'], $this->message['string'] );
        }
        else
        {
            $this->splash( "success", "index.php?ctl=trading/deliveryarea&act=detailDlArea&p[0]=".$_POST['region_id'] );
        }
    }

    public function detailDlArea( $aRegionId )
    {
        $this->path[] = array(
            "text" => __( "配送地区编辑" )
        );
        $oObj =& $this->system->loadModel( "trading/deliveryarea" );
        $this->pagedata['area'] = $oObj->getDlAreaById( $aRegionId );
        $this->page( "delivery/area_edit.html" );
    }

    public function toRemoveArea( $regionId )
    {
        $this->begin( "index.php?ctl=trading/deliveryarea&act=index" );
        $dArea =& $this->system->loadModel( "trading/deliveryarea" );
        $this->end( $dArea->toRemoveArea( $regionId ), __( "删除成功！" ) );
    }

    public function updateOrderNum( )
    {
        $this->begin( "index.php?ctl=trading/deliveryarea&act=index" );
        $dArea =& $this->system->loadModel( "trading/deliveryarea" );
        $this->end( $dArea->updateOrderNum( $_POST['p_order'] ), __( "排序成功！" ) );
    }

}

?>
