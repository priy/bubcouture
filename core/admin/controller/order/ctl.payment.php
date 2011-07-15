<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_payment extends objectPage
{

    public $workground = "order";
    public $object = "trading/payment";
    public $finder_filter_tpl = "order/payment/finder_filter.html";
    public $deleteAble = TRUE;

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "收款单信息" ),
                "tpl" => "order/payment/detail.html"
            )
        );
    }

    public function show_detail( $nID )
    {
        $oPayment =& $this->system->loadModel( "trading/payment" );
        $aDetail = $oPayment->getById( $nID );
        $o =& $this->system->loadModel( "admin/operator" );
        $aOp = $o->instance( $aDetail['op_id'], "username" );
        $aDetail['op_id'] = $aOp['username'];
        $o =& $this->system->loadModel( "member/member" );
        $aMember = $o->getFieldById( $aDetail['member_id'] );
        $aDetail['member_id'] = $aMember['uname'];
        $this->pagedata['detail'] =& $aDetail;
    }

    public function edit( )
    {
        $oPayment =& $this->system->loadModel( "trading/payment" );
        if ( $oPayment->edit( $_POST ) )
        {
            $this->splash( "success", "index.php?ctl=order/payment&act=index", __( "修改成功" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=order/payment&act=index", __( "修改失败" ) );
        }
    }

}

?>
