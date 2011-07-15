<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_reship extends objectPage
{

    public $workground = "order";
    public $object = "trading/reship";
    public $deleteAble = TRUE;

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "退货单信息" ),
                "tpl" => "order/reship/detail.html"
            )
        );
    }

    public function show_detail( $nID )
    {
        $oReship =& $this->system->loadModel( "trading/reship" );
        $aDetail = $oReship->detail( $nID );
        $o =& $this->system->loadModel( "member/member" );
        $aMember = $o->getFieldById( $aDetail['member_id'] );
        $aDetail['member_id'] = $aMember['uname'];
        $this->pagedata['detail'] = $aDetail;
        $this->pagedata['items'] = $oReship->getItemList( $nID );
    }

    public function edit( )
    {
        $oReship =& $this->system->loadModel( "trading/reship" );
        if ( $oReship->edit( $_POST ) )
        {
            $this->splash( "success", "index.php?ctl=order/reship&act=index", __( "修改成功" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=order/reship&act=index", __( "修改失败" ) );
        }
    }

    public function delete( )
    {
        $oReship =& $this->system->loadModel( "trading/reship" );
        foreach ( $_POST['delivery_id'] as $v )
        {
            $oReship->toRemove( $v );
        }
        echo __( "删除成功！" );
    }

}

?>
