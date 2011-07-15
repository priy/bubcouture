<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_po extends objectPage
{

    public $name = "采购单";
    public $workground = "distribution";
    public $object = "purchase/po";
    public $filterView = "po/filter.html";
    public $detail_title = "po/detail_title.html";
    public $deleteAble = FALSE;

    public function index( )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $mdl = $this->system->loadModel( "purchase/po" );
        $this->pagedata['suppliers'] = $mdl->getSupplierList( );
        parent::index( );
    }

    public function _detail( )
    {
        return array(
            "detail_info" => array(
                "label" => __( "基本信息" ),
                "tpl" => "po/detail_info.html"
            ),
            "detail_money" => array(
                "label" => __( "财务往来" ),
                "tpl" => "po/detail_money.html"
            ),
            "detail_ship" => array(
                "label" => __( "物流往来" ),
                "tpl" => "po/detail_ship.html"
            )
        );
    }

    public function filterActions( &$row )
    {
        $return = $this->actions;
        if ( $row['status'] == "pending" )
        {
            $return['pause'] = "_none_";
        }
        else if ( $row['status'] == "active" && $row['__pay_status'] == 1 && $row['__ship_status'] == 0 )
        {
            $return['cancel_pause'] = "_none_";
        }
        else
        {
            $return['pause'] = "_none_";
            $return['cancel_pause'] = "_none_";
        }
        return $return;
    }

    public function detail_info( $order_id )
    {
        $api = $this->system->loadModel( "purchase/po" );
        $POrder = $api->getOrder( $order_id );
        $this->pagedata['POrder'] = $POrder;
    }

    public function detail_money( $order_id )
    {
        $api = $this->system->loadModel( "purchase/po" );
        $POrder = $api->getOrder( $order_id );
        $this->pagedata['POrder'] = $POrder;
    }

    public function detail_ship( $order_id )
    {
        $api = $this->system->loadModel( "purchase/po" );
        $POrder = $api->getOrder( $order_id );
        $this->pagedata['POrder'] = $POrder;
    }

    public function pause( $order_id )
    {
        $this->begin( "index.php?ctl=order/po&act=index" );
        $api = $this->system->loadModel( "purchase/po" );
        $this->end( $api->pausePOrder( $order_id ), __( "操作成功" ) );
    }

    public function cancel_pause( $order_id )
    {
        $this->begin( "index.php?ctl=order/po&act=index" );
        $api = $this->system->loadModel( "purchase/po" );
        $this->end( $api->cancelPausePOrder( $order_id ), __( "操作成功" ) );
    }

    public function cancel( $order_id )
    {
        $this->begin( "index.php?ctl=order/po&act=detail&p[0]=".$order_id );
        $api = $this->system->loadModel( "purchase/po" );
        $res = $api->cancelPOrder( $order_id );
        if ( $res )
        {
            $this->setError( 10001 );
            trigger_error( "撤销采购单成功", E_USER_NOTICE );
        }
        else
        {
            $this->setError( 10002 );
            trigger_error( "撤销采购单失败", E_USER_ERROR );
        }
        $this->end( );
    }

    public function refund( $order_id, $dealer_order_id )
    {
        $arr['order_id'] = $dealer_order_id;
        $arr['opid'] = $this->op->opid;
        $arr['opname'] = $this->op->loginName;
        $this->begin( "index.php?ctl=order/po&act=detail&p[0]=".$order_id );
        $obj = $this->system->loadModel( "trading/order" );
        $obj->op_id = $this->op->opid;
        $obj->op_name = $this->op->loginName;
        if ( $obj->refund( $arr ) )
        {
            $this->setError( 10001 );
            trigger_error( "退款成功", E_USER_NOTICE );
        }
        else
        {
            $this->setError( 10002 );
            trigger_error( "退款失败", E_USER_ERROR );
        }
        $this->end( );
    }

}

?>
