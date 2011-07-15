<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_exchangeCoupon extends objectPage
{

    public $workground = "sale";
    public $object = "trading/exchangeCoupon";
    public $finder_action_tpl = "sale/coupon/exchange/finder_action.html";
    public $noRecycle = TRUE;
    public $filterUnable = TRUE;

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "退款单信息" ),
                "tpl" => "sale/coupon/exchange/addExchange.html"
            )
        );
    }

    public function show_detail( $cpnsId )
    {
        $oCoupon = $this->system->loadModel( "trading/coupon" );
        $aList = $oCoupon->getUserCouponArr( );
        $this->pagedata['cpns_list'] = $aList;
        if ( $cpnsId )
        {
            $this->pagedata['cpns'] = $oCoupon->getCouponById( $cpnsId );
        }
        else
        {
            $this->pagedata['cpns']['cpns_id'] = $aList[0][0]['cpns_id'];
        }
    }

    public function showAddExchange( $cpnsId = NULL )
    {
        $oCoupon = $this->system->loadModel( "trading/coupon" );
        $aList = $oCoupon->getUserCouponArr( );
        $this->pagedata['cpns_list'] = $aList;
        if ( $cpnsId )
        {
            $this->pagedata['cpns'] = $oCoupon->getCouponById( $cpnsId );
        }
        else
        {
            $this->pagedata['cpns']['cpns_id'] = $aList[0][0]['cpns_id'];
        }
        $this->page( "sale/coupon/exchange/addExchange.html" );
    }

    public function addExchange( )
    {
        $this->begin( "index.php?ctl=sale/exchangeCoupon&act=index" );
        if ( empty( $_POST['cpns_id'] ) || $_POST['cpns_id'] == "undefined" )
        {
            $this->end( FALSE, __( "优惠券名称不能为空" ), "index.php?ctl=sale/exchangeCoupon&act=index" );
        }
        $oExchangeCoupon =& $this->system->loadModel( "trading/exchangeCoupon" );
        if ( !$oExchangeCoupon->saveExchange( $_POST ) )
        {
            $this->end( FALSE, $oExchangeCoupon->message, "index.php?ctl=sale/exchangeCoupon&act=index" );
        }
        else
        {
            $this->end( TRUE, "保存成功", "index.php?ctl=sale/exchangeCoupon&act=index" );
        }
    }

}

?>
