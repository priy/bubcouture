<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_delivery_centers extends objectPage
{

    public $workground = "order";
    public $object = "trading/dly_centers";
    public $finder_action_tpl = "order/dly_center_action.html";
    public $finder_default_cols = "_cmd,name,region,address,area_id,zip,phone,uname";
    public $filterUnable = TRUE;

    public function add_center( )
    {
        $this->page( "order/dly_center_editor.html" );
    }

    public function save_data( )
    {
        $this->begin( "index.php?ctl=order/delivery_centers&act=index" );
        if ( $_POST['dly_center_id'] )
        {
            if ( $_POST['is_default'] )
            {
                $this->system->setConf( "system.default_dc", $_POST['dly_center_id'] );
            }
            $this->end( $this->model->update( $_POST, array(
                "dly_center_id" => $_POST['dly_center_id']
            ) ), __( "发货信息保存成功" ) );
        }
        else
        {
            $dly_center_id = $this->model->insert( $_POST );
            if ( $dly_center_id && $_POST['is_default'] )
            {
                $this->system->setConf( "system.default_dc", $dly_center_id );
            }
            $this->end( $dly_center_id, __( "发货信息添加成功" ) );
        }
    }

    public function instance( $dly_center_id )
    {
        $this->pagedata['the_dly_center'] = $this->model->instance( $dly_center_id );
        $this->display( "order/dly_center.html" );
    }

    public function editor( $dly_center_id )
    {
        $this->pagedata['default_dc'] = $dly_center_id == $this->system->getConf( "system.default_dc" );
        $this->pagedata['dly_center'] = $this->model->instance( $dly_center_id );
        $this->page( "order/dly_center_editor.html" );
    }

}

?>
