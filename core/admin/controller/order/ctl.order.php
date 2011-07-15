<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "delivercorp.php" );
include_once( "objectPage.php" );
class ctl_order extends objectPage
{

    public $filter = array
    (
        "order_refer" => "local"
    );
    public $finder_action_tpl = "order/finder_action.html";
    public $finder_filter_tpl = "order/finder_filter.html";
    public $detail_title = "order/detail_title.html";
    public $workground = "order";
    public $ioType = "order";
    public $object = "trading/order";
    public $finder_default_cols = "_cmd,order_id,createtime,total_amount,ship_name,pay_status,ship_status,shipping,print_status,payment,member_id";

    public function index( $operate )
    {
        if ( $operate == "admin" )
        {
            $this->system->set_op_conf( "ordertime", time( ) );
        }
        parent::index( );
    }

    public function do_export( $io = "csv" )
    {
        $this->system->__session_close( 0 );
        if ( $io == "taobaoordercsv" || $io == "taobaogoodscsv" )
        {
            include_once( "shopObject.php" );
            $step = 20;
            $offset = 0;
            $dataio = $this->system->loadModel( "system/dataio" );
            $taobaoordercsv = $this->system->loadModel( "trading/taobaoordercsv" );
            $cols = $dataio->columns( $io );
            $list = $this->model->getList( $cols, $_POST, $offset, $step, array( "order_id", "ASC" ) );
        }
        else
        {
            parent::do_export( $io );
            exit( );
        }
        $count = $this->model->count( $_POST );
        if ( $io == "taobaoordercsv" )
        {
            while ( $offset < $count )
            {
                if ( $offset == 0 )
                {
                    $keys = $taobaoordercsv->orderExportTitle( );
                    $dataio->export_begin( $io, $keys, "ExportOrderList", $count );
                }
                foreach ( $list as $v )
                {
                    $data = $taobaoordercsv->getOrdersExportData( $v );
                    $dataio->export_rows( $io, $data );
                }
                $offset += $step;
                $list = $this->model->getList( $cols, $_POST, $offset, $step, array( "order_id", "ASC" ) );
            }
        }
        else if ( $io == "taobaogoodscsv" && $offset < $count )
        {
            while ( $Tmp_56 )
            {
                if ( $offset == 0 )
                {
                    $keys = $taobaoordercsv->goodsExportTitle( );
                    $dataio->export_begin( $io, $keys, "ExportOrderDetailList", $count );
                }
                $setMemo = TRUE;
                foreach ( $list as $v )
                {
                    $rows = $this->model->db->select( "SELECT name, price, nums, addon FROM sdb_order_items WHERE order_id=".$v['order_id'] );
                    $order = $this->model->db->selectRow( "SELECT memo FROM sdb_orders WHERE order_id=".$v['order_id'] );
                    $export['order_id'] = $v['order_id'];
                    foreach ( $rows as $delivery )
                    {
                        $export['name'] = $delivery['name'];
                        $export['price'] = $delivery['price'];
                        $export['nums'] = $delivery['nums'];
                        $export['sysid'] = "";
                        $addon = unserialize( $delivery['addon'] );
                        $export['pdt_desc'] = $addon['spec'][1];
                        $export['pdt_desc'] .= $addon['spec'][2] ? "、".$addon['spec'][2] : "";
                        $export['tinfo'] = "";
                        $export['memo'] = $setMemo ? $order['memo'] : "";
                        $setMemo = FALSE;
                        $data[] = $export;
                    }
                    $dataio->export_rows( $io, $data );
                    unset( $data );
                }
                $offset += $step;
                $list = $this->model->getList( $cols, $_POST, $offset, $step, array( "order_id", "ASC" ) );
            }
        }
        $dataio->export_finish( $io );
    }

    public function new_order_message_list( )
    {
        $oShopbbs = $this->system->loadModel( "resources/shopbbs" );
        $order_list = $oShopbbs->getNewOrderMessage( TRUE );
        if ( empty( $order_list ) )
        {
            $order_list = 0;
        }
        $params['order_id'] = array(
            "v" => $order_list,
            "t" => "最新留言订单"
        );
        $GLOBALS['_GET']['filter'] = serialize( $params );
        parent::index( );
    }

    public function _views( )
    {
        return array(
            "",
            __( "未处理" ) => array(
                "pay_status" => array( "0" ),
                "ship_status" => array( "0" ),
                "status" => "active"
            ),
            __( "已付款待发货" ) => array(
                "pay_status" => array( "1", "2", "3" ),
                "ship_status" => array( "0", "2" ),
                "status" => "active"
            ),
            __( "已发货" ) => array(
                "ship_status" => array( "1" ),
                "status" => "active"
            ),
            __( "已完成" ) => array( "status" => "finish" ),
            __( "已退款" ) => array(
                "pay_status" => array( "4", "5" ),
                "status" => "active"
            ),
            __( "已退货" ) => array(
                "ship_status" => array( "3", "4" ),
                "status" => "active"
            ),
            __( "已作废" ) => array( "status" => "dead" )
        );
    }

    public function save_addr( $order_id )
    {
        $data = array(
            "ship_name" => $_POST['order']['ship_name'],
            "ship_area" => $_POST['order']['ship_area'],
            "ship_zip" => $_POST['order']['ship_zip'],
            "ship_addr" => $_POST['order']['ship_addr'],
            "ship_mobile" => $_POST['order']['ship_mobile'],
            "ship_tel" => $_POST['order']['ship_tel'],
            "memo" => $_POST['order']['order_memo']
        );
        if ( $this->model->update( $data, array(
            "order_id" => $order_id
        ) ) )
        {
            echo "ok";
        }
    }

    public function printing( $type, $order_id )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $member =& $this->system->loadModel( "member/member" );
        $dbTmpl =& $this->system->loadModel( "content/systmpl" );
        $order->setPrintStatus( $order_id, $type, TRUE );
        $print_id = $order->getPrintId( $order_id );
        $orderInfo = $order->getFieldById( $order_id );
        $orderInfo['self'] = 0 - $orderInfo['discount'] - $orderInfo['pmt_amount'];
        $goodsItem = $order->getItemList( $order_id );
        $memberInfo = $member->getFieldById( $orderInfo['member_id'], array( "point" ) );
        foreach ( $goodsItem as $k => $rows )
        {
            $goodsItem[$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $goodsItem[$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $goodsItem[$k]['minfo'] = array( );
            }
        }
        $giftsItem = $order->getGiftItemList( $order_id );
        $orderSum = $order->sumOrder( $orderInfo['member_id'] );
        $orderSum['sum'] = $orderSum['sum'] ? $orderSum['sum'] : 0;
        $this->pagedata['goodsItem'] = $goodsItem;
        $this->pagedata['giftsItem'] = $giftsItem;
        $this->pagedata['orderSum'] = $orderSum;
        $this->pagedata['memberPoint'] = $memberInfo['point'] ? $memberInfo['point'] : 0;
        $this->pagedata['storeplace_display_switch'] = $this->system->getConf( "storeplace.display.switch" );
        $this->pagedata['shop'] = array(
            "name" => $this->system->getConf( "system.shopname" ),
            "url" => $this->system->getConf( "store.shop_url" ),
            "email" => $this->system->getConf( "store.email" ),
            "tel" => $this->system->getConf( "store.telephone" ),
            "logo" => $this->system->getConf( "site.logo" )
        );
        switch ( $type )
        {
        case ORDER_PRINT_CART :
            $this->pagedata['printType'] = array( "cart" );
            $this->pagedata['printContent']['cart'] = TRUE;
            $this->pagedata['goodsItem'] = $goodsItem;
            $this->pagedata['giftsItem'] = $giftsItem;
            $this->pagedata['orderInfo'] = $orderInfo;
            $this->pagedata['orderSum'] = $orderSum;
            $this->pagedata['memberPoint'] = $memberInfo['point'] ? $memberInfo['point'] : 0;
            $this->pagedata['print_cart_content'] = $dbTmpl->fetch( "../admin/view/order/print_cart", $this->pagedata );
            $this->display( "order/print.html" );
            break;
        case ORDER_PRINT_SHEET :
            $this->pagedata['printContent']['sheet'] = TRUE;
            $this->pagedata['goodsItem'] = $goodsItem;
            $this->pagedata['giftsItem'] = $giftsItem;
            $this->pagedata['orderInfo'] = $orderInfo;
            $this->pagedata['orderSum'] = $orderSum;
            $this->pagedata['memberPoint'] = $memberInfo['point'] ? $memberInfo['point'] : 0;
            $this->pagedata['print_sheet_content'] = $dbTmpl->fetch( "../admin/view/order/print_sheet", $this->pagedata );
            $this->display( "order/print.html" );
            break;
        case ORDER_PRINT_MERGE :
            $this->pagedata['printType'] = array( "cart" );
            $this->pagedata['printContent']['cart'] = TRUE;
            $this->pagedata['printContent']['sheet'] = TRUE;
            $this->pagedata['goodsItem'] = $goodsItem;
            $this->pagedata['giftsItem'] = $giftsItem;
            $this->pagedata['orderInfo'] = $orderInfo;
            $this->pagedata['orderSum'] = $orderSum;
            $this->pagedata['memberPoint'] = $memberInfo['point'] ? $memberInfo['point'] : 0;
            $this->pagedata['print_cart_content'] = $dbTmpl->fetch( "../admin/view/order/print_cart", $this->pagedata );
            $this->pagedata['print_sheet_content'] = $dbTmpl->fetch( "../admin/view/order/print_sheet", $this->pagedata );
            $this->display( "order/print.html" );
            break;
        case ORDER_PRINT_DLY :
            $printer =& $this->system->loadModel( "trading/dly_centers" );
            $this->pagedata['dly_centers'] = $printer->getList( "dly_center_id,name", array( "disable" => "false" ), 0, 100 );
            $this->pagedata['default_dc'] = $this->system->getConf( "system.default_dc" );
            $this->pagedata['orderInfo'] = $orderInfo;
            $this->pagedata['the_dly_center'] = $printer->instance( $this->pagedata['default_dc'] ? $this->pagedata['default_dc'] : $this->pagedata['dly_centers'][0]['dly_center_id'] );
            $printer =& $this->system->loadModel( "trading/dly_printer" );
            $this->pagedata['printers'] = $printer->getList( "prt_tmpl_id,prt_tmpl_title", array( "shortcut" => "true" ) );
            $this->display( "order/print_dly.html" );
            break;
        default :
            echo __( "无效的打印类型" );
            break;
        }
    }

    public function _detail( )
    {
        return array(
            "detail_info" => array(
                "label" => __( "基本信息" ),
                "tpl" => "order/order_detail.html"
            ),
            "detail_items" => array(
                "label" => __( "商品" ),
                "tpl" => "order/od_items.html"
            ),
            "detail_bills" => array(
                "label" => __( "收退款记录" ),
                "tpl" => "order/od_bill.html"
            ),
            "detail_delivery" => array(
                "label" => __( "收发货记录" ),
                "tpl" => "order/od_delivery.html"
            ),
            "detail_pmt" => array(
                "label" => __( "优惠方案" ),
                "tpl" => "order/od_pmts.html"
            ),
            "detail_mark" => array(
                "label" => __( "订单备注" ),
                "tpl" => "order/od_mark.html"
            ),
            "detail_logs" => array(
                "label" => __( "订单日志" ),
                "tpl" => "order/od_logs.html"
            ),
            "detail_msg" => array(
                "label" => __( "顾客留言" ),
                "tpl" => "order/od_msg.html"
            )
        );
    }

    public function detail_items( $orderid )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $this->pagedata['orderid'] = $orderid;
        $aItems = $order->getItemList( $orderid );
        foreach ( $aItems as $k => $rows )
        {
            $aItems[$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $aItems[$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $aItems[$k]['minfo'] = array( );
            }
            if ( $aItems[$k]['addon']['adjname'] )
            {
                $aItems[$k]['name'] .= __( "<br>配件：" ).$aItems[$k]['addon']['adjname'];
            }
        }
        $this->pagedata['goodsItems'] = $aItems;
        $aGiftItems = $order->getGiftItemList( $orderid );
        $this->pagedata['giftItems'] = $aGiftItems;
    }

    public function detail_bills( $orderid )
    {
        $objPayment =& $this->system->loadModel( "trading/payment" );
        $aBill = $objPayment->getOrderBillList( $orderid );
        $objRefund =& $this->system->loadModel( "trading/refund" );
        $aRefund = $objRefund->getOrderBillList( $orderid );
        $this->pagedata['bills'] = $aBill;
        $this->pagedata['refunds'] = $aRefund;
        $this->pagedata['orderid'] = $orderid;
    }

    public function detail_delivery( $orderid )
    {
        $objDelivery =& $this->system->loadModel( "trading/delivery" );
        $this->pagedata['consign'] = $objDelivery->getConsignList( $orderid );
        $this->pagedata['reship'] = $objDelivery->getReshipList( $orderid );
        $this->pagedata['orderid'] = $orderid;
    }

    public function detail_pmt( $orderid )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $aPmt = $order->getPmtList( $orderid );
        $aItems = $order->getItemList( $orderid );
        foreach ( $aPmt as $key => $val )
        {
            $aPmt[$key]['pmt_amount'] = $aPmt[$key]['pmt_amount'];
        }
        $this->pagedata['pmtlist'] = $aPmt;
        $this->pagedata['orderid'] = $orderid;
    }

    public function detail_mark( $orderid )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $aOrder = $order->getFieldById( $orderid, array( "mark_text", "mark_type" ) );
        $this->pagedata['mark_text'] = $aOrder['mark_text'];
        $this->pagedata['mark_type'] = $aOrder['mark_type'];
        $this->pagedata['orderid'] = $orderid;
    }

    public function saveMarkText( )
    {
        $this->begin( "index.php?ctl=order/order&act=detail&p[0]=".$_POST['orderid']."&p[1]=detail_mark" );
        $order =& $this->system->loadModel( "trading/order" );
        $this->end( $order->saveMarkText( $_POST['orderid'], $_POST ), __( "保存成功" ) );
    }

    public function detail_logs( $orderid, $page = 1 )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $pageLimit = 30;
        $aLog = $order->getOrderLogList( $orderid, $page - 1, $pageLimit );
        $this->pagedata['logs'] = $aLog;
        $this->pagedata['result'] = array(
            "success" => __( "成功" ),
            "failure" => __( "失败" )
        );
        $pager = array(
            "current" => $page,
            "total" => ceil( $aLog['page'] ),
            "link" => "javascript:W.page('index.php?ctl=order/order&act=detail_logs&p[0]=".$orderid."&p[1]=_PPP_', {update:\$E('.tableform').parentNode, method:'post'});",
            "token" => "_PPP_"
        );
        $this->pagedata['pager'] = $pager;
        $this->pagedata['pagestart'] = ( $page - 1 ) * $pageLimit;
    }

    public function saveOrderMsgText( )
    {
        $this->begin( "index.php?ctl=order/order&act=detail&p[0]=".$_POST['orderid']."&p[1]=detail_msg" );
        $order =& $this->system->loadModel( "trading/order" );
        $oMsg = $this->system->loadModel( "resources/message" );
        $orderMsg = $oMsg->getOrderMessage( $_POST['orderid'] );
        foreach ( $orderMsg as $mk => $mv )
        {
            $oMsg->setReaded( $mv['msg_id'] );
        }
        $data = $_POST['msg'];
        $data['rel_order'] = $_POST['orderid'];
        $data['unread'] = "1";
        $data['date_line'] = time( );
        $data['msg_from'] = __( "管理员" );
        $data['from_type'] = "1";
        $data['message'] = htmlspecialchars( $data['message'] );
        $aOrder = $order->getFieldById( $_POST['orderid'], array( "total_amount", "is_tax", "member_id" ) );
        $eventData['order_id'] = $_POST['orderid'];
        $eventData['total_amount'] = $aOrder['total_amount'];
        $eventData['is_tax'] = $aOrder['is_tax'];
        $eventData['member_id'] = $aOrder['member_id'];
        $order->fireEvent( "reply_message", $eventData );
        $this->end( $order->addOrderMsg( $data ), __( "保存成功" ) );
    }

    public function detail_msg( $orderid )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $oMsg =& $this->system->loadModel( "resources/message" );
        $orderMsg = $oMsg->getOrderMessage( $orderid );
        $oMsg->sethasreaded( $orderid );
        $aItems = $order->getItemList( $orderid );
        foreach ( $aItems as $k => $rows )
        {
            $aItems[$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $aItems[$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $aItems[$k]['minfo'] = array( );
            }
            if ( $aItems[$k]['addon']['adjname'] )
            {
                $aItems[$k]['name'] .= __( "<br>配件：" ).$aItems[$k]['addon']['adjname'];
            }
        }
        $this->pagedata['ordermsg'] = $orderMsg;
        $this->pagedata['goodsItems'] = $aItems;
        $this->pagedata['orderid'] = $orderid;
    }

    public function detail_info( $order_id )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $aOrder = $order->getFieldById( $order_id );
        $oCur =& $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
        if ( intval( $aOrder['payment'] ) < 0 )
        {
            $aOrder['payment'] = "货到付款";
        }
        else
        {
            $payment =& $this->system->loadModel( "trading/payment" );
            $aPayment = $payment->getPaymentById( $aOrder['payment'] );
            $payid = $aOrder['payment'];
            $aOrder['payment'] = $aPayment['custom_name'];
            $aOrder['extendCon'] = $payment->getExtendCon( $aOrder['extend'], $payid );
        }
        if ( $aOrder['member_id'] )
        {
            $member =& $this->system->loadModel( "member/member" );
            $aOrder['member'] = $member->getFieldById( $aOrder['member_id'], array( "name", "uname", "mobile", "tel", "addr", "email", "area", "remark" ) );
        }
        $aItems = $order->getItemList( $order_id );
        $gItems = $order->getGiftItemList( $order_id );
        foreach ( $aItems as $k => $rows )
        {
            $aItems[$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $aItems[$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $aItems[$k]['minfo'] = array( );
            }
            if ( $aItems[$k]['addon']['adjname'] )
            {
                $aItems[$k]['name'] .= __( "<br>配件：" ).$aItems[$k]['addon']['adjname'];
            }
        }
        $this->pagedata['goodsItems'] = $aItems;
        $this->pagedata['giftItems'] = $gItems;
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $this->pagedata['order'] = $aOrder;
        $_is_all_ship = 1;
        $_is_all_return_ship = 1;
        foreach ( $aItems as $_item )
        {
            if ( !$_item['supplier_id'] && $_item['sendnum'] < $_item['nums'] )
            {
                $_is_all_ship = 0;
            }
            if ( !$_item['supplier_id'] && 0 < $_item['sendnum'] )
            {
                $_is_all_return_ship = 0;
            }
        }
        foreach ( $gItems as $g_item )
        {
            if ( $g_item['sendnum'] < $g_item['nums'] )
            {
                $_is_all_ship = 0;
            }
            if ( 0 < $g_item['sendnum'] )
            {
                $_is_all_return_ship = 0;
            }
        }
        $this->pagedata['order']['_is_all_ship'] = $_is_all_ship;
        $this->pagedata['order']['_is_all_return_ship'] = $_is_all_return_ship;
        $this->pagedata['order']['flow'] = array(
            "refund" => $this->system->getConf( "order.flow.refund" ),
            "consign" => $this->system->getConf( "order.flow.consign" ),
            "reship" => $this->system->getConf( "order.flow.reship" ),
            "payed" => $this->system->getConf( "order.flow.payed" )
        );
        $Mem =& $this->system->loadModel( "member/member" );
        $Memattr =& $this->system->loadModel( "member/memberattr" );
        $nowmember = $Memattr->getAlloption( $aOrder['member_id'] );
        $tree = $Mem->getContactObject( $aOrder['member_id'] );
        $this->pagedata['tree'] = $tree;
        $this->pagedata['recy'] = $aOrder['disabled'];
        $this->pagedata['distribute'] = $this->system->getConf( "certificate.distribute" );
    }

    public function toConfirm( $orderid )
    {
        $objOrder =& $this->system->loadModel( "trading/order" );
        if ( $orderid )
        {
            $order = $objOrder->toConfirm( $orderid );
            $this->status( $orderid, $order );
            exit( );
        }
        if ( is_array( $_POST['items']['items'] ) )
        {
            foreach ( $_POST['items']['items'] as $orderid )
            {
                $order = $objOrder->toConfirm( $orderid );
            }
        }
        echo __( "所选订单已确认完毕" );
    }

    public function status( $orderid, $order = NULL )
    {
        if ( !$order )
        {
            $order =& $this->system->loadModel( "trading/order" );
            $order = $order->load( $orderid );
        }
        $this->pagedata['order'] = $order;
        $this->pagedata['order']['flow'] = array(
            "refund" => $this->system->getConf( "order.flow.refund" ),
            "consign" => $this->system->getConf( "order.flow.consign" ),
            "reship" => $this->system->getConf( "order.flow.reship" ),
            "payed" => $this->system->getConf( "order.flow.payed" )
        );
        $this->display( "order/actbar.html" );
    }

    public function archive( $orderid )
    {
        $objOrder =& $this->system->loadModel( "trading/order" );
        if ( $orderid )
        {
            $objOrder->op_id = $this->system->op_id;
            $objOrder->op_name = $this->system->op_name;
            $order = $objOrder->toArchive( $orderid );
            echo __( "订单已确认完毕" );
            exit( );
        }
        echo __( "<span failedSplash=\"true\">订单确认失败</span>" );
    }

    public function remove( )
    {
        $objOrder =& $this->system->loadModel( "trading/order" );
        if ( is_array( $_POST['items']['items'] ) )
        {
            foreach ( $_POST['items']['items'] as $orderid )
            {
                if ( !$objOrder->toRemove( $orderid, $message ) )
                {
                    echo $message;
                    exit( );
                }
            }
            echo __( "删除成功;" );
        }
        else
        {
            echo __( "没有选中记录;" );
        }
    }

    public function cancel( $orderid )
    {
        $objOrder =& $this->system->loadModel( "trading/order" );
        if ( $orderid )
        {
            $objOrder->op_id = $this->system->op_id;
            $objOrder->op_name = $this->system->op_name;
            $order = $objOrder->toCancel( $orderid );
            echo __( "订单已作废" );
            exit( );
        }
        echo __( "<span failedSplash=\"true\">订单作废操作失败</span>" );
    }

    public function toReply( $orderid )
    {
        $order =& $this->system->loadModel( "trading/order" );
        $data['object_type'] = 1;
        $data['object_id'] = $orderid;
        $data['comment'] = $_POST['reply'];
        $data['time'] = time( );
        $data['member_id'] = 0;
        $order->toReply( $data );
        $this->detail( $orderid );
    }

    public function showPayed( $orderid )
    {
        if ( !$orderid )
        {
            echo __( "订单号传递出错" );
            return FALSE;
        }
        $this->pagedata['orderid'] = $orderid;
        $objOrder =& $this->system->loadModel( "trading/order" );
        $aORet = $objOrder->getFieldById( $orderid );
        $oCur =& $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aORet['cur_name'] = $aCur[$aORet['currency']];
        $objPayment =& $this->system->loadModel( "trading/payment" );
        $aPayment = $objPayment->getMethods( );
        $this->pagedata['payment'] = $aPayment;
        $aPayid = $objPayment->getPaymentById( $aORet['payment'] );
        $this->pagedata['payment_id'] = $aORet['payment'];
        $this->pagedata['op_name'] = "admin";
        $this->pagedata['typeList'] = array(
            "online" => __( "在线支付" ),
            "offline" => __( "线下支付" ),
            "deposit" => __( "预存款支付" )
        );
        $this->pagedata['pay_type'] = $aPayid['pay_type'] == "ADVANCE" ? "deposit" : "offline";
        if ( 0 < $aORet['member_id'] )
        {
            $objMember =& $this->system->loadModel( "member/member" );
            $aRet = $objMember->getMemberInfo( $aORet['member_id'] );
            $this->pagedata['member'] = $aRet;
        }
        else
        {
            $this->pagedata['member'] = array( );
        }
        $math = $this->system->loadModel( "system/math" );
        $this->pagedata['pay_amount'] = $aORet['total_amount'] - $aORet['payed'];
        $this->pagedata['pay_amount'] = $math->getOperationNumber( $this->pagedata['pay_amount'] );
        $this->pagedata['order'] = $aORet;
        $aRet = $objPayment->getAccount( );
        $aAccount = array(
            __( "--使用已存在帐户--" )
        );
        foreach ( $aRet as $v )
        {
            $aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
        }
        $this->pagedata['pay_account'] = $aAccount;
        $this->display( "order/orderpayed.html" );
    }

    public function toPayed( $orderid )
    {
        if ( !$orderid )
        {
            $orderid = $_POST['order_id'];
        }
        else
        {
            $GLOBALS['_POST']['order_id'] = $orderid;
        }
        $GLOBALS['_POST']['opid'] = $this->system->op_id;
        $GLOBALS['_POST']['opname'] = $this->system->op_name;
        $this->begin( "index.php?ctl=order/order&act=detail&p[0]=".$orderid );
        $objOrder =& $this->system->loadModel( "trading/order" );
        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if ( $objOrder->toPayed( $_POST, TRUE ) )
        {
            $this->end( TRUE, __( "支付成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "支付失败" ) );
        }
    }

    public function showRefund( $orderid )
    {
        if ( !$orderid )
        {
            echo __( "订单号传递出错" );
            return FALSE;
        }
        $this->pagedata['orderid'] = $orderid;
        $objOrder =& $this->system->loadModel( "trading/order" );
        $aORet = $objOrder->getFieldById( $orderid );
        $objPayment =& $this->system->loadModel( "trading/payment" );
        $aPayment = $objPayment->getMethods( );
        $this->pagedata['payment'] = $aPayment;
        $aPayid = $objPayment->getPaymentById( $aORet['payment'] );
        $this->pagedata['payment_id'] = $aORet['payment'];
        $this->pagedata['op_name'] = "admin";
        $this->pagedata['typeList'] = array(
            "online" => __( "在线支付" ),
            "offline" => __( "线下支付" ),
            "deposit" => __( "预存款支付" )
        );
        $this->pagedata['pay_type'] = $aPayid['pay_type'] == "ADVANCE" ? "deposit" : "offline";
        if ( 0 < $aORet['member_id'] )
        {
            $objMember =& $this->system->loadModel( "member/member" );
            $aRet = $objMember->getMemberInfo( $aORet['member_id'] );
            $this->pagedata['member'] = $aRet;
        }
        else
        {
            $this->pagedata['member'] = array( );
        }
        $this->pagedata['order'] = $aORet;
        $aRet = $objPayment->getAccount( );
        $aAccount = array(
            __( "--使用已存在帐户--" )
        );
        foreach ( $aRet as $v )
        {
            $aAccount[$v['bank']."-".$v['account']] = $v['bank']." - ".$v['account'];
        }
        $this->pagedata['pay_account'] = $aAccount;
        $oPointHistory =& $this->system->loadModel( "trading/pointHistory" );
        $this->pagedata['score_g'] = $this->pagedata['score_g'] - $oPointHistory->getOrderHistoryGetPoint( $orderid );
        $this->display( "order/orderrefund.html" );
    }

    public function toRefund( $orderid )
    {
        if ( !$orderid )
        {
            $orderid = $_POST['order_id'];
        }
        else
        {
            $GLOBALS['_POST']['order_id'] = $orderid;
        }
        $GLOBALS['_POST']['opid'] = $this->system->op_id;
        $GLOBALS['_POST']['opname'] = $this->system->op_name;
        $this->begin( "index.php?ctl=order/order&act=detail&p[0]=".$orderid );
        $objOrder =& $this->system->loadModel( "trading/order" );
        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if ( $objOrder->refund( $_POST ) )
        {
            $this->end( TRUE, __( "退款成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "退款失败" ) );
        }
    }

    public function showConsignFlow( $orderid )
    {
        if ( !$orderid )
        {
            echo __( "发货错误：订单ID传递出错" );
            return FALSE;
        }
        $objOrder =& $this->system->loadModel( "trading/order" );
        $aShipping = $objOrder->getFieldById( $orderid, array( "order_id", "ship_status", "createtime", "shipping_area", "shipping_id", "shipping", "ship_name", "is_delivery", "ship_email", "ship_tel", "ship_mobile", "ship_zip", "ship_area", "ship_addr", "cost_freight", "is_protect", "cost_protect" ) );
        if ( !$aShipping )
        {
            echo __( "发货错误：没有当前订单" );
            return FALSE;
        }
        $this->pagedata['order'] = $aShipping;
        $this->pagedata['order']['protectArr'] = array(
            "false" => __( "否" ),
            "true" => __( "是" )
        );
        $aItems = $objOrder->getItemList( $orderid, "", TRUE );
        $gItems = $objOrder->getGiftItemList( $orderid, "", TRUE );
        if ( empty( $aItems ) && empty( $gItems ) )
        {
            echo __( "订单里无货品或者都是远端数据,请点\"采购并编辑按钮\"进行发货处理" );
            return FALSE;
        }
        foreach ( $aItems as $k => $rows )
        {
            $aItems[$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $aItems[$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $aItems[$k]['minfo'] = array( );
            }
            if ( $aItems[$k]['is_type'] == "goods" )
            {
                $p =& $this->system->loadModel( "goods/products" );
                $aGoods = $p->getFieldById( $aItems[$k]['product_id'], array( "store" ) );
            }
            else
            {
                $g =& $this->system->loadModel( "trading/goods" );
                $aGoods = $g->getFieldById( $aItems[$k]['product_id'], array( "store" ) );
            }
            $aItems[$k]['store'] = $aGoods['store'];
        }
        $this->pagedata['items'] = $aItems;
        $this->pagedata['giftItems'] = $gItems;
        if ( $this->pagedata['giftItems'] )
        {
            foreach ( $this->pagedata['giftItems'] as $k => $v )
            {
                $this->pagedata['giftItems'][$k]['needsend'] = $v['nums'] - $v['sendnum'];
            }
        }
        $shipping =& $this->system->loadModel( "trading/delivery" );
        $this->pagedata['shippings'] = $shipping->getDlTypeList( );
        $this->pagedata['corplist'] = $shipping->getCropList( );
        if ( defined( "SAAS_MODE" ) && SAAS_MODE )
        {
            $this->pagedata['corplist'] = getdeliverycorplist( );
            $this->pagedata['corplist'][] = array(
                "corp_id" => "other",
                "name" => __( "其他" )
            );
        }
        $corp = $shipping->getCorpByShipId( $aShipping['shipping_id'] );
        $this->pagedata['corp_id'] = $corp['corp_id'];
        $this->display( "order/orderconsign.html" );
    }

    public function toDelivery( $orderid )
    {
        if ( !$orderid )
        {
            $orderid = $_POST['order_id'];
        }
        else
        {
            $GLOBALS['_POST']['order_id'] = $orderid;
        }
        $this->begin( "index.php?ctl=order/order&act=detail&p[0]=".$orderid );
        $GLOBALS['_POST']['opid'] = $this->system->op_id;
        $GLOBALS['_POST']['opname'] = $this->system->op_name;
        $objOrder =& $this->system->loadModel( "trading/order" );
        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if ( $objOrder->delivery( $_POST ) )
        {
            $oPro =& $this->system->loadModel( "goods/products" );
            $oPro->addSellLog( $_POST );
            $this->end( TRUE, __( "发货成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "发货失败" ) );
        }
    }

    public function showReturn( $orderid )
    {
        if ( !$orderid )
        {
            echo __( "退货错误：订单ID传递出错" );
            return FALSE;
        }
        $objOrder =& $this->system->loadModel( "trading/order" );
        $aShipping = $objOrder->getFieldById( $orderid, array( "order_id", "ship_status", "createtime", "is_delivery", "shipping_area", "shipping", "ship_name", "ship_email", "ship_tel", "ship_mobile", "ship_zip", "ship_area", "ship_addr", "cost_freight", "is_protect", "cost_protect" ) );
        if ( !$aShipping )
        {
            echo __( "退货错误：没有当前订单" );
            return FALSE;
        }
        $this->pagedata['order'] = $aShipping;
        $this->pagedata['order']['protectArr'] = array(
            "false" => __( "否" ),
            "true" => __( "是" )
        );
        $aItems = $objOrder->getItemList( $orderid, "", TRUE );
        $gItems = $objOrder->getGiftItemList( $orderid, "", TRUE );
        if ( empty( $aItems ) && empty( $gItems ) )
        {
            echo __( "订单里无货品或者都是远端数据,请点\"采购并编辑按钮\"进行发货处理" );
            return FALSE;
        }
        foreach ( $aItems as $k => $rows )
        {
            $aItems[$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $aItems[$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $aItems[$k]['minfo'] = array( );
            }
        }
        $this->pagedata['items'] = $aItems;
        $shipping =& $this->system->loadModel( "trading/delivery" );
        $this->pagedata['shippings'] = $shipping->getDlTypeList( );
        $this->pagedata['corplist'] = $shipping->getCropList( );
        if ( defined( "SAAS_MODE" ) && SAAS_MODE )
        {
            $this->pagedata['corplist'] = getdeliverycorplist( );
            $this->pagedata['corplist'][] = array(
                "corp_id" => "other",
                "name" => __( "其他" )
            );
        }
        $this->display( "order/orderreturn.html" );
    }

    public function toReturn( $orderid )
    {
        if ( !$orderid )
        {
            $orderid = $_POST['order_id'];
        }
        else
        {
            $GLOBALS['_POST']['order_id'] = $orderid;
        }
        $this->begin( "index.php?ctl=order/order&act=detail&p[0]=".$orderid );
        $GLOBALS['_POST']['opid'] = $this->system->op_id;
        $GLOBALS['_POST']['opname'] = $this->system->op_name;
        $objOrder =& $this->system->loadModel( "trading/order" );
        $objOrder->op_id = $this->system->op_id;
        $objOrder->op_name = $this->system->op_name;
        if ( $objOrder->toReship( $_POST ) )
        {
            $this->end( TRUE, __( "退货成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "退货失败" ) );
        }
    }

    public function showAdd( )
    {
        $this->singlepage( "order/page.html" );
    }

    public function create( )
    {
        if ( !empty( $_POST['username'] ) )
        {
            $objMember =& $this->system->loadModel( "member/member" );
            $aUser = $objMember->getList( "member_id,member_lv_id", array(
                "member_id" => $_POST['username']
            ), 0, 1 );
            $aUser = $aUser[0];
            if ( empty( $aUser['member_id'] ) )
            {
                echo __( "<script>alert(\"不存在的会员名称!\")</script>" );
                exit( );
            }
        }
        else
        {
            $aUser = array( "member_id" => NULL, "member_lv_id" => 0 );
        }
        $_SESSION['tmp_admin_create_order'] = array( );
        $_SESSION['tmp_admin_create_order']['member'] = $aUser;
        if ( $_POST['goods'] )
        {
            $aTmp['product_id'] = $_POST['goods'];
            $objPdt =& $this->system->loadModel( "goods/finderPdt" );
            $aPdt = $objPdt->getList( "goods_id, product_id", $aTmp, 0, count( $_POST['goods'] ) );
            unset( $aTmp );
            foreach ( $aPdt as $key => $row )
            {
                $num = ceil( $_POST['goodsnum'][$aPdt[$key]['product_id']] );
                if ( 0 < $num )
                {
                    $_SESSION['tmp_admin_create_order']['cart']['g']['cart'][$row['goods_id']."-".$aPdt[$key]['product_id']."-na"] = $num;
                    $oPromotion =& $this->system->loadModel( "trading/promotion" );
                    if ( $pmtid = $oPromotion->getGoodsPromotionId( $row['goods_id'], $aUser['member_lv_id'] ) )
                    {
                        $_SESSION['tmp_admin_create_order']['cart']['g']['pmt'][$row['goods_id']] = $pmtid;
                    }
                }
            }
        }
        if ( $_POST['package'] )
        {
            $aTmp['goods_id'] = $_POST['package'];
            $oPackage =& $this->system->loadModel( "trading/package" );
            $aPkg = $oPackage->getList( "goods_id", $aTmp, 0, count( $_POST['package'] ) );
            unset( $aTmp );
            foreach ( $aPkg as $key => $row )
            {
                $num = ceil( $_POST['pkgnum'][$aPkg[$key]['goods_id']] );
                if ( 0 < $num )
                {
                    $_SESSION['tmp_admin_create_order']['cart']['p'][$row['goods_id']]['num'] = $num;
                }
            }
        }
        if ( !$_SESSION['tmp_admin_create_order']['cart'] )
        {
            echo __( "<script>MessageBox.error(\"没有购买商品或者购买数量为0!\");</script>" );
            exit( );
        }
        $objCart =& $this->system->loadModel( "trading/cart" );
        $aOut = $objCart->getCheckout( $_SESSION['tmp_admin_create_order']['cart'], $aUser, "" );
        $aOut['trading']['admindo'] = 1;
        $this->pagedata['has_physical'] = $aOut['has_physical'];
        $this->pagedata['minfo'] = $aOut['minfo'];
        $this->pagedata['areas'] = $aOut['areas'];
        $this->pagedata['currencys'] = $aOut['currencys'];
        $this->pagedata['currency'] = $aOut['currency'];
        $this->pagedata['payments'] = $aOut['payments'];
        $payment = $this->system->loadModel( "trading/payment" );
        $payment->showPayExtendCon( $aOut['payments'] );
        $this->pagedata['payments'] = $aOut['payments'];
        $this->pagedata['trading'] = $aOut['trading'];
        if ( $this->pagedata['payments'] )
        {
            foreach ( $this->pagedata['payments'] as $key => $val )
            {
                $this->pagedata['payments'][$key]['config'] = unserialize( $val['config'] );
            }
        }
        if ( $aUser['member_id'] )
        {
            $member =& $this->system->loadModel( "member/member" );
            $addrlist = $member->getMemberAddr( $aUser['member_id'] );
            foreach ( $addrlist as $rows )
            {
                if ( empty( $rows['tel'] ) )
                {
                    $str_tel = __( "手机：" ).$rows['mobile'];
                }
                else
                {
                    $str_tel = __( "电话：" ).$rows['tel'];
                }
                $addr[] = array(
                    "addr_id" => $rows['addr_id'],
                    "def_addr" => $rows['def_addr'],
                    "addr_region" => $rows['area'],
                    "addr_label" => $rows['addr'].__( " (收货人：" ).$rows['name']." ".$str_tel.__( " 邮编：" ).$rows['zip'].")"
                );
            }
            $this->pagedata['trading']['receiver']['addrlist'] = $addr;
            $this->pagedata['is_allow'] = count( $addr ) < 5 ? 1 : 0;
        }
        $this->display( "order/order_create.html" );
    }

    public function getAddr( )
    {
        if ( $_GET['addr_id'] )
        {
            $oMem =& $this->system->loadModel( "member/member" );
            $this->pagedata['trading']['receiver'] = $oMem->getAddrById( $_GET['addr_id'] );
        }
        $this->pagedata['trading']['member_id'] = $_SESSION['tmp_admin_create_order']['member']['member_id'];
        $this->display( "shop:common/rec_addr.html" );
    }

    public function shipping( )
    {
        $aCart = $_SESSION['tmp_admin_create_order']['cart'];
        $aMember = $_SESSION['tmp_admin_create_order']['member'];
        $sale =& $this->system->loadModel( "trading/sale" );
        $trading = $sale->getCartObject( $aCart, $aMember['member_lv_id'], TRUE );
        $shipping =& $this->system->loadModel( "trading/delivery" );
        $aShippings = $shipping->getDlTypeByArea( $_POST['area'] );
        foreach ( $aShippings as $k => $s )
        {
            $aShippings[$k]['price'] = cal_fee( $s['expressions'], $trading['weight'], $trading['pmt_b']['totalPrice'], $s['price'] );
            $s['pad'] == 0 ? $aShippings[$k]['has_cod'] = 0 : $aShippings[$k]['has_cod'] = 1;
            if ( $s['protect'] == 1 )
            {
                $aShippings[$k]['protect'] = max( $trading['totalPrice'] * $s['protect_rate'], $s['minprice'] );
            }
            else
            {
                $aShippings[$k]['protect'] = FALSE;
            }
        }
        $this->pagedata['shippings'] = $aShippings;
        $this->display( "shop:cart/checkout_shipping.html" );
    }

    public function payment( )
    {
        $payment =& $this->system->loadModel( "trading/payment" );
        $oCur =& $this->system->loadModel( "system/cur" );
        $this->pagedata['payments'] = $payment->getByCur( $_POST['cur'] );
        $this->pagedata['delivery']['has_cod'] = $_POST['d_pay'];
        $this->pagedata['order']['payment'] = $_POST['payment'];
        $this->display( "shop:common/paymethod.html" );
    }

    public function total( )
    {
        $aCart = $_SESSION['tmp_admin_create_order']['cart'];
        $aMember = $_SESSION['tmp_admin_create_order']['member'];
        $tarea = explode( ":", $_POST['area'] );
        $GLOBALS['_POST']['area'] = $tarea[count( $tarea ) - 1];
        $objCart =& $this->system->loadModel( "trading/cart" );
        $this->pagedata['trading'] = $objCart->checkoutInfo( $aCart, $aMember, $_POST );
        $this->display( "shop:cart/checkout_total.html" );
    }

    public function doCreate( )
    {
        $this->begin( "index.php?ctl=order/order&act=index" );
        $aCart = $_POST['aCart'];
        $aCart = $_SESSION['tmp_admin_create_order']['cart'];
        $aMember = $_POST['aMember'];
        $aMember = $_SESSION['tmp_admin_create_order']['member'];
        unset( $_SESSION['tmp_admin_create_order'] );
        $order =& $this->system->loadModel( "trading/order" );
        $order->op_id = $this->system->op_id;
        $order->op_name = $this->system->op_name;
        $orderid = $order->create( $aCart, $aMember, $_POST['delivery'], $_POST['payment'], $_POST['minfo'], $_POST );
        $this->end( $orderid, __( "订单: " ).$orderid.__( " 生成成功" ) );
    }

    public function showEdit( $orderid )
    {
        $this->path[] = array(
            "text" => __( "订单编辑" )
        );
        $objOrder =& $this->system->loadModel( "trading/order" );
        $aOrder = $objOrder->getFieldById( $orderid );
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $oCur =& $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
        $aOrder['items'] = $objOrder->getItemList( $orderid );
        $aOrder['pmt'] = $objOrder->getPmtList( $orderid );
        if ( 0 < $aOrder['member_id'] )
        {
            $objMember =& $this->system->loadModel( "member/member" );
            $aOrder['member'] = $objMember->getFieldById( $aOrder['member_id'], array( "*" ) );
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }
        else
        {
            $aOrder['member'] = array( );
        }
        $objDelivery =& $this->system->loadModel( "trading/delivery" );
        $aArea = $objDelivery->getDlAreaList( );
        foreach ( $aArea as $v )
        {
            $aTmp[$v['name']] = $v['name'];
        }
        $aOrder['deliveryArea'] = $aTmp;
        $aRet = $objDelivery->getDlTypeList( );
        foreach ( $aRet as $v )
        {
            $aShipping[$v['dt_id']] = $v['dt_name'];
        }
        $aOrder['selectDelivery'] = $aShipping;
        $objPayment =& $this->system->loadModel( "trading/payment" );
        $aRet = $objPayment->getMethods( );
        $aPayment[-1] = "货到付款";
        foreach ( $aRet as $v )
        {
            $aPayment[$v['id']] = $v['custom_name'];
        }
        $aOrder['extendCon'] = $objPayment->getExtendCon( $aOrder['extend'], $aOrder['payment'] );
        $aOrder['selectPayment'] = $aPayment;
        $objCurrency =& $this->system->loadModel( "system/cur" );
        $aRet = $objCurrency->curAll( );
        foreach ( $aRet as $v )
        {
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;
        $this->pagedata['order'] = $aOrder;
        $this->singlepage( "order/page.html" );
    }

    public function addItem( )
    {
        if ( $_POST['order_id'] )
        {
            $flag = TRUE;
            while ( $flag )
            {
                $randomValue = rand( 1, 200 );
                if ( !in_array( $randomValue, array_keys( $_POST['aItems'] ) ) )
                {
                    $flag = FALSE;
                }
            }
            $loopValue = count( $_POST['aItems'] ) + 1;
            $objOrder =& $this->system->loadModel( "trading/order" );
            $productInfo = $objOrder->getProductInfo( $_POST['order_id'], $_POST['newbn'] );
            if ( $productInfo == "none" )
            {
                $aOrder['alertJs'] = __( "商品货号输入不正确，没有该商品或者商品已经下架。\\n注意：如果是多规格商品，请输入规格编号." );
            }
            else if ( $productInfo == "exist" )
            {
                $aOrder['alertJs'] = __( "订单中存在相同的商品货号。" );
            }
            else if ( $productInfo == "understock" )
            {
                $aOrder['alertJs'] = __( "商品库存不足。" );
            }
            if ( in_array( $_POST['newbn'], $_POST['add_bn'] ) )
            {
                $aOrder['alertJs'] = __( "该商品货号已存在。" );
            }
            if ( $aOrder['alertJs'] )
            {
                echo $aOrder['alertJs'];
                exit( );
            }
            $pdt_desc = $productInfo['pdt_desc'] ? "(".$productInfo['pdt_desc'].")" : "";
            $returnValue = "<tr>";
            $returnValue .= "<input type=\"hidden\" value=\"".$productInfo['product_id']."\" name=\"aItems[".$randomValue."]\">";
            $returnValue .= "<td>".$productInfo['bn']."<input type=\"hidden\" name=\"add_bn[]\" value=\"".$productInfo['bn']."\"></td>";
            $returnValue .= "<td>".$productInfo['name'].$pdt_desc."</td>";
            $returnValue .= "<td><input type=\"text\" vtype=\"unsigned\" size=\"8\" value=\"".$productInfo['mprice']."\" name=\"aPrice[".$randomValue."]\" class=\"x-input itemPrice_".$loopValue." itemrow\" required=\"true\" autocomplete=\"off\"></td>";
            $returnValue .= "<td><input type=\"text\" vtype=\"positive\" size=\"4\" value=\"1\" name=\"aNum[".$randomValue."]\" class=\"x-input itemNum_".$loopValue." itemrow\" required=\"true\" autocomplete=\"off\"></td>";
            $returnValue .= "<td class=\"itemSub_".$loopValue." itemCount Colamount\">".$productInfo['mprice']."</td>";
            $returnValue .= "<td><span onclick=\"delgoods(this)\" class=\"sysiconBtnNoIcon\">删除</span></td>";
            $returnValue .= "</tr>";
            echo $returnValue;
        }
    }

    public function toEdit( )
    {
        $GLOBALS['_POST']['is_protect'] = isset( $_POST['is_protect'] ) ? $_POST['is_protect'] : "false";
        $GLOBALS['_POST']['is_tax'] = isset( $_POST['is_tax'] ) ? $_POST['is_tax'] : "false";
        $GLOBALS['_POST']['discount'] = 0 - $_POST['discount'];
        $this->begin( "index.php?ctl=order/order&act=index" );
        if ( count( $_POST['aItems'] ) )
        {
            $objOrder =& $this->system->loadModel( "trading/order" );
            $objOrder->op_id = $this->system->op_id;
            $objOrder->op_name = $this->system->op_name;
            if ( $objOrder->editOrder( $_POST ) )
            {
                $objOrder->changeOrder( $_POST['order_id'], $_POST );
                $aOrder = $objOrder->getFieldById( $_POST['order_id'], array( "total_amount", "is_tax", "member_id" ) );
                $eventData['order_id'] = $_POST['order_id'];
                $eventData['total_amount'] = $aOrder['total_amount'];
                $eventData['is_tax'] = $aOrder['is_tax'];
                $eventData['member_id'] = $aOrder['member_id'];
                $objOrder->fireEvent( "editorder", $eventData );
                $this->end( TRUE, __( "保存成功" ) );
            }
            else
            {
                trigger_error( __( "库存不足，请确认！" ), E_USER_ERROR );
            }
        }
        else
        {
            trigger_error( __( "没有商品明细" ), E_USER_ERROR );
        }
    }

    public function toPrint( $orderid )
    {
        if ( $_POST['order_id'] )
        {
            if ( is_array( $_POST['order_id'] ) )
            {
                $aInput = $_POST['order_id'];
            }
            else
            {
                $aInput = array(
                    $_POST['order_id']
                );
            }
        }
        else if ( $orderid )
        {
            $aInput = array(
                $orderid
            );
        }
        else
        {
            $this->begin( "index.php?ctl=order/order&act=index" );
            $this->end( FALSE, __( "打印失败：订单参数传递出错" ) );
            exit( );
        }
        $oCur =& $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $dbTmpl =& $this->system->loadModel( "content/systmpl" );
        foreach ( $aInput as $orderid )
        {
            if ( strlen( $orderid ) < 14 )
            {
                continue;
            }
            $aData = array( );
            $objOrder =& $this->system->loadModel( "trading/order" );
            $aData = $objOrder->getFieldById( $orderid );
            $aData['currency'] = $aCur[$aData['currency']];
            $objMember =& $this->system->loadModel( "member/member" );
            $aMember = $objMember->getFieldById( $aData['member_id'], array( "uname", "name", "tel", "mobile", "email", "zip", "addr" ) );
            $aData['member'] = $aMember;
            $payment =& $this->system->loadModel( "trading/payment" );
            $aPayment = $payment->getPaymentById( $aData['payment'] );
            $aData['payment'] = $aPayment['custom_name'];
            $aData['shopname'] = $this->system->getConf( "store.company_name" );
            $aData['shopaddress'] = $this->system->getConf( "store.address" );
            $aData['shoptelphone'] = $this->system->getConf( "store.quhao" )."-".$this->system->getConf( "store.telephone" );
            $aData['shopzip'] = $this->system->getConf( "store.zip_code" );
            $aItems = $objOrder->getItemList( $orderid );
            foreach ( $aItems as $k => $rows )
            {
                $aItems[$k]['addon'] = unserialize( $rows['addon'] );
                if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
                {
                    $aItems[$k]['minfo'] = unserialize( $rows['minfo'] );
                }
                else
                {
                    $aItems[$k]['minfo'] = array( );
                }
                if ( $aItems[$k]['addon']['adjname'] )
                {
                    $aItems[$k]['name'] .= __( "<br>配件：" ).$aItems[$k]['addon']['adjname'];
                }
                $aItems[$k]['catname'] = $objOrder->getCatByPid( $rows['product_id'] );
            }
            $aData['goodsItems'] = $aItems;
            $aData['giftItems'] = $objOrder->getGiftItemList( $orderid );
            $this->pagedata['pages'][] = $dbTmpl->fetch( "misc/orderprint", array(
                "order" => $aData
            ) );
        }
        $this->pagedata['shopname'] = $aData['shopname'];
        $this->display( "print.html" );
    }

    public function showOrderFlow( )
    {
        $this->path[] = array(
            "text" => __( "订单是否创建单据" )
        );
        $this->page( "order/order_flow.html" );
    }

    public function saveFlow( $tag, $checkmark )
    {
        $items = array( "payed", "refund", "consign", "reship" );
        foreach ( $items as $item )
        {
            if ( !$_POST['aFlow'][$item] )
            {
                $GLOBALS['_POST']['aFlow'][$item] = FALSE;
            }
            $this->system->setConf( "order.flow.".$item, $_POST['aFlow'][$item] );
        }
        $this->splash( "success", "index.php?ctl=order/order&act=showOrderFlow" );
    }

    public function showPrintStyle( )
    {
        $this->path[] = array(
            "text" => __( "订单打印格式设置" )
        );
        $dbTmpl =& $this->system->loadModel( "content/systmpl" );
        $filetxt = $dbTmpl->get( "misc/orderprint" );
        $cartfiletxt = $dbTmpl->get( "../admin/view/order/print_cart" );
        $sheetfiletxt = $dbTmpl->get( "../admin/view/order/print_sheet" );
        $this->pagedata['styleContent'] = $filetxt;
        $this->pagedata['styleContentCart'] = $cartfiletxt;
        $this->pagedata['styleContentSheet'] = $sheetfiletxt;
        $this->page( "order/printstyle.html" );
    }

    public function savePrintStyle( )
    {
        $this->begin( "index.php?ctl=order/order&act=showPrintStyle" );
        $dbTmpl =& $this->system->loadModel( "content/systmpl" );
        $dbTmpl->set( "../admin/view/order/print_sheet", $_POST['txtcontentsheet'] );
        $dbTmpl->set( "../admin/view/order/print_cart", $_POST['txtcontentcart'] );
        $this->end( $dbTmpl->set( "misc/orderprint", $_POST['txtcontent'] ), __( "订单打印模板保存成功" ) );
    }

    public function rebackPrintStyle( )
    {
        $this->begin( "index.php?ctl=order/order&act=showPrintStyle" );
        $dbTmpl =& $this->system->loadModel( "content/systmpl" );
        $dbTmpl->clear( "../admin/view/order/print_sheet" );
        $dbTmpl->clear( "../admin/view/order/print_cart" );
        $this->end( $dbTmpl->clear( "misc/orderprint" ), __( "恢复默认值成功" ) );
    }

    public function delete( )
    {
        $oOrder =& $this->system->loadModel( "trading/order" );
        $msg = "";
        if ( $_POST['order_id'][0] == "_ALL_" )
        {
            $GLOBALS['_POST']['order_id'] = array( );
            $dd = $oOrder->db->select( "SELECT order_id FROM sdb_orders WHERE disabled = 'true';" );
            foreach ( $dd as $_k => $_v )
            {
                $GLOBALS['_POST']['order_id'][] = $_v['order_id'];
            }
        }
        foreach ( $_POST['order_id'] as $v )
        {
            $oOrder->toRemove( $v, $msg );
        }
        $status =& $this->system->loadModel( "system/status" );
        $status->count_order_to_pay( );
        $status->count_order_new( );
        echo $msg;
    }

    public function memberInfo( $nMId )
    {
        $this->pagedata['member_id'] = $nMId;
        $this->display( "order/order_membertab.html" );
    }

    public function recycle( )
    {
        $this->model->op_id = $this->system->op_id;
        $this->model->op_name = $this->system->op_name;
        parent::recycle( );
        $oOrder =& $this->system->loadModel( "trading/order" );
        foreach ( $_POST['order_id'] as $v )
        {
            $oOrder->toUnfreez( $v );
        }
        $status =& $this->system->loadModel( "system/status" );
        $status->count_order_to_pay( );
        $status->count_order_new( );
        $status->count_order_to_dly( );
    }

    public function active( )
    {
        $this->model->op_id = $this->system->op_id;
        $this->model->op_name = $this->system->op_name;
        parent::active( );
        $status =& $this->system->loadModel( "system/status" );
        $status->count_order_to_pay( );
        $status->count_order_new( );
        $status->count_order_to_dly( );
    }

    public function edit_po( $orderid )
    {
        set_error_handler( array(
            $this,
            "_pageErrorHandler"
        ) );
        $this->path[] = array( "text" => "订单采购并编辑" );
        $objOrder = $this->system->loadModel( "trading/order" );
        $aOrder = $objOrder->getFieldById( $orderid );
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $aOrder['cost_item'] = $objOrder->getCostItems( $orderid );
        $oCur = $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
        $aOrder['pmt'] = $objOrder->getPmtList( $orderid );
        if ( 0 < $aOrder['member_id'] )
        {
            $objMember = $this->system->loadModel( "member/member" );
            $aOrder['member'] = $objMember->getFieldById( $aOrder['member_id'], array( "*" ) );
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }
        else
        {
            $aOrder['member'] = array( );
        }
        $objDelivery = $this->system->loadModel( "trading/delivery" );
        $aArea = $objDelivery->getDlAreaList( );
        foreach ( $aArea as $v )
        {
            $aTmp[$v['name']] = $v['name'];
        }
        $aOrder['deliveryArea'] = $aTmp;
        $aRet = $objDelivery->getDlTypeList( );
        foreach ( $aRet as $v )
        {
            $aShipping[$v['dt_id']] = $v['dt_name'];
        }
        $aOrder['selectDelivery'] = $aShipping;
        $objPayment = $this->system->loadModel( "trading/payment" );
        $aRet = $objPayment->getMethods( );
        foreach ( $aRet as $v )
        {
            $aPayment[$v['id']] = $v['custom_name'];
        }
        $aOrder['selectPayment'] = $aPayment;
        $objCurrency = $this->system->loadModel( "system/cur" );
        $aRet = $objCurrency->curAll( );
        foreach ( $aRet as $v )
        {
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aOrder['po'] = $objPo->getPoListByOrderId( $orderid );
        $this->pagedata['order'] = $aOrder;
        $this->singlepage( "order/page.html" );
    }

    public function _err_handler( $errno, $errstr, $errfile, $errline )
    {
        if ( $errno == E_USER_ERROR )
        {
            $this->errorinfo = $errstr;
        }
        return TRUE;
    }

    public function inquiry( $orderid )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $inq_bn = array( );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aData = $objPo->getPoListByOrderId( $orderid );
        foreach ( $_POST['aItems']['dealer_bn'] as $k => $v )
        {
            $inq_bn[$_POST['aItems']['supplier_bn'][$k]] = $_POST['aItems']['nums'][$k];
        }
        if ( $inq_bn )
        {
            $aRet = $objPo->inquiry( $_POST['supplier_id'], $orderid, $inq_bn );
            $this->pagedata['sItem']['name'] = $aData['supplier'][$_POST['supplier_id']]['name'];
            $this->pagedata['sItem']['local'] = $aRet['items'];
            $this->pagedata['order']['order_id'] = $orderid;
            $this->pagedata['sItem']['total_amount'] = $aRet['total_amount'];
            $this->pagedata['sItem']['store_status'] = $aRet['store_status'];
            $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        }
        $this->__tmpl = "order/po_items_local.html";
        $this->output( );
    }

    public function reInquiry( $orderid, $supplier_id = 0 )
    {
        $this->errorinfo = "";
        set_error_handler( array(
            $this,
            "_err_handler"
        ) );
        $inq_bn = array( );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aData = $objPo->getPoListByOrderId( $orderid );
        foreach ( $_POST['aItems']['dealer_bn'] as $k => $v )
        {
            $inq_bn[$_POST['aItems']['supplier_bn'][$k]] = $_POST['aItems']['quiry_num'][$k];
            $tmp_arr[$_POST['aItems']['dealer_bn'][$k]] = $_POST['aItems']['nums'][$k];
        }
        if ( $inq_bn )
        {
            $aRet = $objPo->inquiry( $supplier_id, $orderid, $inq_bn );
            foreach ( $aRet['items'] as $k => $v )
            {
                $aRet['items'][$k]['quiry_num'] = $v['nums'];
                $aRet['items'][$k]['nums'] = $tmp_arr[$k];
            }
            $this->pagedata['sItem']['name'] = $aData['supplier'][$supplier_id]['name'];
            $this->pagedata['poItem']['items'] = $aRet['items'];
            $this->pagedata['poItem']['_action_status'] = $aData['supplier'][$supplier_id]['po'][$_POST['po_id']]['_action_status'];
            $this->pagedata['order']['order_id'] = $orderid;
            $this->pagedata['po_id'] = $_POST['po_id'];
            $this->pagedata['supplier_id'] = $supplier_id;
        }
        if ( $this->errorinfo )
        {
            echo "<script>MessageBox.error(\"".str_replace( "\"", "\\\"", $this->errorinfo )."\");</script>";
        }
        $this->__tmpl = "order/po_detail.html";
        $this->output( );
    }

    public function refresh_local( $orderid )
    {
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aData = $objPo->getPoListByOrderId( $orderid );
        $this->pagedata['order']['po']['local_ship_status'] = $aData['local_ship_status'];
        $this->pagedata['order']['po']['local'] = $aData['local'];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->__tmpl = "order/edit_local_items.html";
        $this->output( );
    }

    public function refresh_po_local( $orderid, $supplier_id = 0 )
    {
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aData = $objPo->getPoListByOrderId( $orderid );
        $this->pagedata['sItem']['name'] = $aData['supplier'][$_POST['supplier_id']]['name'];
        $this->pagedata['sItem']['local'] = $aData['supplier'][$_POST['supplier_id']]['local'];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->pagedata['supplier_id'] = $_POST['supplier_id'];
        $this->__tmpl = "order/po_items_local.html";
        $this->output( );
    }

    public function refresh_po_supplier( $orderid, $supplier_id = 0 )
    {
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aData = $objPo->getPoListByOrderId( $orderid );
        $this->pagedata['sItem'] = $aData['supplier'][$supplier_id];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->pagedata['supplier_id'] = $supplier_id;
        $this->__tmpl = "order/po_items.html";
        $this->output( );
    }

    public function refresh_po_detail( $orderid, $supplier_id = 0 )
    {
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aData = $objPo->getPoListByOrderId( $orderid );
        $this->pagedata['sItem']['name'] = $aData['supplier'][$supplier_id]['name'];
        $this->pagedata['poItem'] = $aData['supplier'][$supplier_id]['po'][$_POST['po_id']];
        $this->pagedata['order']['order_id'] = $orderid;
        $this->pagedata['po_id'] = $_POST['po_id'];
        $this->__tmpl = "order/po_detail.html";
        $this->output( );
    }

    public function consignLocal( $orderid )
    {
        if ( !$orderid )
        {
            echo __( "发货错误：订单ID传递出错" );
            return FALSE;
        }
        $objOrder = $this->system->loadModel( "trading/order" );
        $aShipping = $objOrder->getFieldById( $orderid, array( "order_id", "ship_status", "createtime", "shipping_area", "shipping_id", "shipping", "ship_name", "is_delivery", "ship_email", "ship_tel", "ship_mobile", "ship_zip", "ship_area", "ship_addr", "cost_freight", "is_protect", "cost_protect" ) );
        if ( !$aShipping )
        {
            echo __( "发货错误：没有当前订单" );
            return FALSE;
        }
        $this->pagedata['order'] = $aShipping;
        $this->pagedata['order']['protectArr'] = array(
            "false" => __( "否" ),
            "true" => __( "是" )
        );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aItems = $objPo->getPoListByOrderId( $orderid );
        foreach ( $aItems['local'] as $k => $rows )
        {
            $aItems['local'][$k]['addon'] = unserialize( $rows['addon'] );
            if ( $rows['minfo'] && unserialize( $rows['minfo'] ) )
            {
                $aItems['local'][$k]['minfo'] = unserialize( $rows['minfo'] );
            }
            else
            {
                $aItems['local'][$k]['minfo'] = array( );
            }
            if ( $aItems['local'][$k]['is_type'] == "goods" )
            {
                $p = $this->system->loadModel( "goods/products" );
                $aGoods = $p->getFieldById( $aItems['local'][$k]['product_id'], array( "store" ) );
            }
            else
            {
                $g = $this->system->loadModel( "trading/goods" );
                $aGoods = $g->getFieldById( $aItems['local'][$k]['product_id'], array( "store" ) );
            }
            $aItems['local'][$k]['store'] = $aGoods['store'];
        }
        $this->pagedata['items'] = $aItems['local'];
        $this->pagedata['giftItems'] = $objOrder->getGiftItemList( $orderid );
        if ( $this->pagedata['giftItems'] )
        {
            foreach ( $this->pagedata['giftItems'] as $k => $v )
            {
                $this->pagedata['giftItems'][$k]['needsend'] = $v['nums'] - $v['sendnum'];
            }
        }
        $shipping = $this->system->loadModel( "trading/delivery" );
        $this->pagedata['shippings'] = $shipping->getDlTypeList( );
        $this->pagedata['corplist'] = $shipping->getCropList( );
        if ( defined( "SAAS_MODE" ) && SAAS_MODE )
        {
            $this->pagedata['corplist'] = getdeliverycorplist( );
            $this->pagedata['corplist'][] = array( "corp_id" => "other", "name" => "其他" );
        }
        $corp = $shipping->getCorpByShipId( $aShipping['shipping_id'] );
        $this->pagedata['corp_id'] = $corp['corp_id'];
        $this->__tmpl = "order/orderconsign.html";
        $this->output( );
    }

    public function saveOrder( $orderid )
    {
        foreach ( $_POST['aItems'] as $k => $v )
        {
            $aItems[$v] = $_POST['aNum'];
        }
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $objPo->modifyOrder( $orderid, $_POST );
        $this->refresh_local( $orderid );
    }

    public function savePo( $orderid, $supplier_id )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $this->begin( "index.php?ctl=order/order&act=refresh_po" );
        $aItems = array( );
        foreach ( ( array )$_POST['aItems']['dealer_bn'] as $k => $v )
        {
            $aItems[] = array(
                "dealer_bn" => $v,
                "supplier_bn" => $_POST['aItems']['supplier_bn'][$k],
                "price" => $_POST['aItems']['price'][$k],
                "po_price" => $_POST['aItems']['po_price'][$k],
                "nums" => $_POST['aItems']['quiry_num'][$k] ? $_POST['aItems']['quiry_num'][$k] : $_POST['aItems']['nums'][$k],
                "product_id" => $_POST['aItems']['product_id'][$k]
            );
        }
        $po_id = $_POST['po_id'] ? $_POST['po_id'] : 0;
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $this->end( $objPo->modifyOrder( $orderid, $aItems, $supplier_id, $po_id ), __( "操作成功" ) );
    }

    public function makePo( $orderid )
    {
        set_error_handler( array(
            $this,
            "_dialogErrorHandler"
        ) );
        if ( empty( $_POST['aItems'] ) )
        {
            trigger_error( __( "需要采购的商品为空" ), E_USER_ERROR );
        }
        $objOrder = $this->system->loadModel( "trading/order" );
        $aOrder = $objOrder->getFieldById( $orderid );
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $oCur = $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
        $aOrder['pmt'] = $objOrder->getPmtList( $orderid );
        if ( 0 < $aOrder['member_id'] )
        {
            $objMember = $this->system->loadModel( "member/member" );
            $aOrder['member'] = $objMember->getFieldById( $aOrder['member_id'], array( "*" ) );
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }
        else
        {
            $aOrder['member'] = array( );
        }
        $objShopInfo = $this->system->loadModel( "trading/dly_centers" );
        $dly_center_id = $this->system->getConf( "system.default_dc" );
        $this->pagedata['sender'] = $objShopInfo->instance( $dly_center_id );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $area_list = $objPo->getSubRegions( $_POST['supplier_id'], 0 );
        $aOrder['area_list'] = $area_list['data_info'];
        $aData = $objPo->getPoListByOrderId( $orderid );
        $aOrder['items'] = $aData['supplier'][$_POST['supplier_id']]['local'];
        $aOrder['total_amount'] = 0;
        $aOrder['total_weight'] = 0;
        foreach ( $aOrder['items'] as $bn => $item )
        {
            $k = array_search( $bn, $_POST['aItems']['dealer_bn'] );
            $aOrder['items'][$bn]['price'] = $_POST['aItems']['po_price'][$k];
            $aOrder['total_amount'] += $_POST['aItems']['po_price'][$k] * $item['nums'];
            $aOrder['total_weight'] += $item['weight'] * $item['nums'];
            $aOrder['items'][$bn]['amount'] = $_POST['aItems']['po_price'][$k] * $item['nums'];
        }
        $aOrder['payment'] = $objPo->getPaymentCfg( $_POST['supplier_id'] );
        $objCurrency = $this->system->loadModel( "system/cur" );
        $aRet = $objCurrency->curAll( );
        foreach ( $aRet as $v )
        {
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;
        $order_setting = $objPo->getOrderSetting( $_POST['supplier_id'] );
        $decimal_digit = $order_setting['decimal_digit'];
        $decimal_type = $order_setting['decimal_type'];
        $trigger_tax = 0;
        $tax_ratio = $order_setting['tax_ratio'];
        if ( !is_null( $trigger_tax ) && $trigger_tax )
        {
            $supplier_tax = $tax_ratio / 100 * $aOrder['total_amount'];
            $aOrder['is_tax'] = "true";
            $aOrder['cost_tax'] = number_format( $supplier_tax, 2, ".", "" );
        }
        else
        {
            $aOrder['is_tax'] = "false";
            $aOrder['cost_tax'] = 0;
        }
        $aOrder['po'] = $objPo->getPoListByOrderId( $orderid );
        $aOrder['supplier_id'] = $_POST['supplier_id'];
        $this->pagedata['order'] = $aOrder;
        $this->pagedata['order_setting'] = $order_setting;
        $this->__tmpl = "order/make_po.html";
        $this->output( );
    }

    public function toMakePo( $orderid )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $poInfo = $_POST['shipinfo'];
        $poInfo['ship_time'] = $poInfo['ship_date']." - ".$poInfo['ship_time'];
        unset( $poInfo['ship_date'] );
        $poInfo['sender_info'] = $_POST['sender_info'];
        $poInfo['is_tax'] = $_POST['is_tax'];
        $poInfo['tax_company'] = $_POST['tax_company'];
        $poInfo['is_protect'] = $_POST['delivery']['is_protect'][$_POST['delivery']['shipping_id']];
        $poInfo['currency'] = $_POST['currency'];
        $poInfo['member_memo'] = $_POST['member_memo'];
        $poInfo['shipping_id'] = $_POST['delivery']['shipping_id'];
        foreach ( $_POST['dealer_bn'] as $k => $v )
        {
            $poItems[] = array(
                "dealer_bn" => $v,
                "supplier_bn" => $_POST['supplier_bn'][$k],
                "price" => $_POST['price'][$k],
                "nums" => $_POST['nums'][$k]
            );
        }
        $po_id = $objPo->createPo( $_POST['supplier_id'], $orderid, $poInfo, $poItems );
        if ( $po_id )
        {
            if ( $_POST['subtype'] == 2 )
            {
                if ( $_POST['payment']['pay_type'][$_POST['payment']['payment_id']] == "deposit" )
                {
                    if ( $objPo->payByDeposits( $po_id, $_POST['payment']['payment_id'] ) )
                    {
                        echo "[{'type' : 1,'msg' : '采购单：".$po_id."已经成功生成，并支付成功！'}]";
                    }
                    else
                    {
                        echo "[{'type' : 1,'msg' : '采购单：".$po_id."已经成功生成，但支付失败！'}]";
                    }
                }
                else
                {
                    echo "[{'type' : 2, 'action' : '".$objPo->getSupplierDomain( $_POST['supplier_id'], TRUE )."/api.php','info' : '<input type=\"hidden\" name=\"act\" value=\"online_pay_center\" /><input type=\"hidden\" name=\"order_id\" value=\"".$po_id."\" /><input type=\"hidden\" name=\"pay_id\" value=\"".$_POST['payment']['payment_id']."\" /><input type=\"hidden\" name=\"currency\" value=\"CNY\" /><input type=\"hidden\" name=\"api_version\" value=\"1.0\" /><input type=\"submit\" />'}]";
                }
            }
            else
            {
                echo "采购单：".$po_id."已经成功生成！";
            }
        }
        if ( $this->errorinfo )
        {
            echo str_replace( "\"", "\\\"", $this->errorinfo );
        }
    }

    public function stoppo( $poid )
    {
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $objPo->pendingPo( $poid );
    }

    public function activepo( $poid )
    {
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $objPo->cancelPendingPo( $poid );
    }

    public function checkPay( $poid )
    {
        $this->begin( );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $this->end( $objPo->reconciliation( $poid ), __( "对账成功" ) );
    }

    public function addPoItem( )
    {
        if ( $_POST['order_id'] )
        {
            $orderid = $_POST['order_id'];
            $objOrder = $this->system->loadModel( "trading/order" );
            $retMark = $objOrder->insertOrderItem( $_POST['order_id'], $_POST['newbn'], 1 );
            if ( $retMark == "none" )
            {
                $aOrder['alertJs'] = "<script>alert(\"商品货号输入不正确，没有该商品或者商品已经下架。\\n注意：如果是多规格商品，请输入规格编号。\");</script>";
            }
            else if ( $retMark == "exist" )
            {
                $aOrder['alertJs'] = "<script>alert(\"订单中存在相同的商品货号。\");</script>";
            }
            else if ( !$retMark )
            {
                $aOrder['alertJs'] = "<script>alert(\"插入数据库失败\");</script>";
            }
            else
            {
                $aOrder['alertJs'] = "<script>countF();</script>";
            }
            $aOrder = $objOrder->getFieldById( $orderid );
            $aOrder['discount'] = 0 - $aOrder['discount'];
            $oCur = $this->system->loadModel( "system/cur" );
            $aCur = $oCur->getSysCur( );
            $aOrder['cur_name'] = $aCur[$aOrder['currency']];
            $aOrder['pmt'] = $objOrder->getPmtList( $orderid );
            if ( 0 < $aOrder['member_id'] )
            {
                $objMember = $this->system->loadModel( "member/member" );
                $aOrder['member'] = $objMember->getFieldById( $aOrder['member_id'], array( "*" ) );
                $aOrder['ship_email'] = $aOrder['member']['email'];
            }
            else
            {
                $aOrder['member'] = array( );
            }
            $objDelivery = $this->system->loadModel( "trading/delivery" );
            $aArea = $objDelivery->getDlAreaList( );
            foreach ( $aArea as $v )
            {
                $aTmp[$v['name']] = $v['name'];
            }
            $aOrder['deliveryArea'] = $aTmp;
            $aRet = $objDelivery->getDlTypeList( );
            foreach ( $aRet as $v )
            {
                $aShipping[$v['dt_id']] = $v['dt_name'];
            }
            $aOrder['selectDelivery'] = $aShipping;
            $objPayment = $this->system->loadModel( "trading/payment" );
            $aRet = $objPayment->getMethods( );
            foreach ( $aRet as $v )
            {
                $aPayment[$v['id']] = $v['custom_name'];
            }
            $aOrder['selectPayment'] = $aPayment;
            $objCurrency = $this->system->loadModel( "system/cur" );
            $aRet = $objCurrency->curAll( );
            foreach ( $aRet as $v )
            {
                $aCurrency[$v['cur_code']] = $v['cur_name'];
            }
            $aOrder['curList'] = $aCurrency;
            $objPo = $this->system->loadModel( "purchase/order_po" );
            $aOrder['po'] = $objPo->getPoListByOrderId( $orderid );
            $this->pagedata['order'] = $aOrder;
            $this->__tmpl = "order/edit_po.html";
            $this->output( );
        }
    }

    public function saveOrderInfo( )
    {
        $orderid = $_POST['order_id'];
        $objOrder = $this->system->loadModel( "trading/order" );
        $objOrder->op_id = $this->op->opid;
        $objOrder->op_name = $this->op->loginName;
        $GLOBALS['_POST']['is_protect'] = isset( $_POST['is_protect'] ) ? $_POST['is_protect'] : "false";
        $GLOBALS['_POST']['is_tax'] = isset( $_POST['is_tax'] ) ? $_POST['is_tax'] : "false";
        $GLOBALS['_POST']['discount'] = 0 - $_POST['discount'];
        $objOrder->editOrder( $_POST, FALSE );
        $aOrder = $objOrder->getFieldById( $orderid );
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $oCur = $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
        $aOrder['pmt'] = $objOrder->getPmtList( $orderid );
        if ( 0 < $aOrder['member_id'] )
        {
            $objMember = $this->system->loadModel( "member/member" );
            $aOrder['member'] = $objMember->getFieldById( $aOrder['member_id'], array( "*" ) );
            $aOrder['ship_email'] = $aOrder['member']['email'];
        }
        else
        {
            $aOrder['member'] = array( );
        }
        $objDelivery = $this->system->loadModel( "trading/delivery" );
        $aArea = $objDelivery->getDlAreaList( );
        foreach ( $aArea as $v )
        {
            $aTmp[$v['name']] = $v['name'];
        }
        $aOrder['deliveryArea'] = $aTmp;
        $aRet = $objDelivery->getDlTypeList( );
        foreach ( $aRet as $v )
        {
            $aShipping[$v['dt_id']] = $v['dt_name'];
        }
        $aOrder['selectDelivery'] = $aShipping;
        $objPayment = $this->system->loadModel( "trading/payment" );
        $aRet = $objPayment->getMethods( );
        foreach ( $aRet as $v )
        {
            $aPayment[$v['id']] = $v['custom_name'];
        }
        $aOrder['selectPayment'] = $aPayment;
        $objCurrency = $this->system->loadModel( "system/cur" );
        $aRet = $objCurrency->curAll( );
        foreach ( $aRet as $v )
        {
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;
        $this->pagedata['order'] = $aOrder;
        $this->__tmpl = "order/order_info.html";
        $this->output( );
    }

    public function payPo( $orderid, $supplierid, $poid )
    {
        $objOrder = $this->system->loadModel( "trading/order" );
        $aOrder = $objOrder->getFieldById( $orderid );
        $aOrder['discount'] = 0 - $aOrder['discount'];
        $oCur = $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getSysCur( );
        $aOrder['cur_name'] = $aCur[$aOrder['currency']];
        $objCurrency = $this->system->loadModel( "system/cur" );
        $aRet = $objCurrency->curAll( );
        foreach ( $aRet as $v )
        {
            $aCurrency[$v['cur_code']] = $v['cur_name'];
        }
        $aOrder['curList'] = $aCurrency;
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $this->pagedata['payment'] = $objPo->getPaymentCfg( $supplierid );
        $aOrder['po'] = $objPo->getPoListByOrderId( $orderid );
        $aOrder['supplier'][$supplierid]['po'][$poid];
        $aOrder['supplier_id'] = $supplierid;
        $this->pagedata['order_id'] = $poid;
        $this->pagedata['po'] = $aOrder['po']['supplier'][$supplierid]['po'][$poid];
        $this->pagedata['delivery'] = $objPo->getDlyIsPay( $supplierid, $this->pagedata['po']['shipping_id'], $this->pagedata['po']['ship_area'] );
        $this->pagedata['payurl'] = $objPo->getSupplierDomain( $supplierid, TRUE )."/api.php";
        $this->pagedata['api_version'] = API_VERSION;
        $this->pagedata['order_setting'] = $objPo->getOrderSetting( $supplierid );
        $this->pagedata['supplierid'] = $supplierid;
        $this->__tmpl = "order/po_pay.html";
        $this->output( );
    }

    public function payByDeposits( )
    {
        set_error_handler( array(
            $this,
            "_err_handler"
        ) );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $objPo->payByDeposits( $_POST['order_id'], $_POST['pay_id'] );
        if ( $this->errorinfo )
        {
            echo "<script>MessageBox.error(\"".str_replace( "\"", "\\\"", $this->errorinfo )."\");</script>";
        }
        else
        {
            echo "success";
        }
    }

    public function payByAfter( )
    {
        set_error_handler( array(
            $this,
            "_err_handler"
        ) );
        if ( $this->errorinfo )
        {
            echo "<script>MessageBox.error(\"".str_replace( "\"", "\\\"", $this->errorinfo )."\");</script>";
        }
        else
        {
            echo "success";
        }
    }

    public function get_area( $sid, $areaid = 0 )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $arr_area = $objPo->getSubRegions( $sid, $areaid );
        if ( $arr_area['data_info'] )
        {
            echo "<select name=\"shipinfo[area_list][]\" onchange=\"get_b2b_area(this)\">";
            echo "<option is_node=\"0\" value=\"0\">- 请选择 -</option>";
            foreach ( $arr_area['data_info'] as $rows )
            {
                echo "<option value=\"".$rows['region_id']."\" is_node=\"".$rows['is_node']."\">".$rows['local_name']."</option>";
            }
            echo "</select>";
        }
    }

    public function get_delivery( $sid, $areaid = 0 )
    {
        set_error_handler( array(
            $this,
            "_ajaxErrorHandler"
        ) );
        $objPo = $this->system->loadModel( "purchase/order_po" );
        $aShipping = $objPo->getDlyTypeByArea( $sid, $areaid );
        $aOrder['delivery'] = $aShipping['data_info'];
        $aOrder['total_amount'] = 0;
        $aOrder['total_weight'] = 0;
        $product = $this->system->loadModel( "goods/products" );
        foreach ( $_POST['dealer_bn'] as $k => $v )
        {
            $aTmp = $product->getFieldByBn( $v, array( "weight" ) );
            $aOrder['total_amount'] += $_POST['price'][$k] * $_POST['nums'][$k];
            $aOrder['total_weight'] += $aTmp['weight'] * $_POST['nums'][$k];
        }
        foreach ( $aShipping['data_info'] as $k => $s )
        {
            $aOrder['delivery'][$k]['price'] = cal_fee( $s['expressions'], $aOrder['total_weight'], $aOrder['total_amount'], $s['price'] );
            if ( $s['protect'] == 1 )
            {
                $aOrder['delivery'][$k]['protect_fee'] = max( $aOrder['total_amount'] * $s['protect_rate'], $s['minprice'] );
            }
            else
            {
                $aOrder['delivery'][$k]['protect_fee'] = 0;
            }
        }
        $this->pagedata['order'] = $aOrder;
        $this->__tmpl = "order/po_delivery.html";
        $this->output( );
    }

    public function _err_page_handler( $errno, $errstr, $errfile = NULL, $errline = NULL )
    {
        switch ( $errno )
        {
        case E_USER_ERROR :
            exit( $errstr );
        }
    }

}

?>
