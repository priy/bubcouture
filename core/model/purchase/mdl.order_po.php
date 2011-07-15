<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
include( API_DIR."/include/api_utility.php" );
class mdl_order_po extends shopobject
{

    function mdl_order_po( )
    {
        shopobject::shopobject( );
        $this->_token = $this->system->getconf( "certificate.token" );
    }

    function _catch_app_error( $obj_api_utility, $str_app_error_no, $is_catch = false )
    {
        if ( $obj_api_utility->error_no == "0x003" )
        {
            $_array_error = $obj_api_utility->application_error[$str_app_error_no];
            if ( isset( $_array_error ) )
            {
                if ( $is_catch === false )
                {
                    switch ( $_array_error['level'] )
                    {
                    case "notice" :
                        $_error_level = E_USER_NOTICE;
                        break;
                    case "warning" :
                        $_error_level = E_USER_WARNING;
                        break;
                    case "error" :
                        $_error_level = E_USER_ERROR;
                    }
                    trigger_error( $_array_error['desc'], $_error_level );
                }
                return $application_error[$str_app_error_no];
            }
            return false;
        }
        return false;
    }

    function getorderitemslist( $orderid )
    {
        $sql = "SELECT i.*,g.thumbnail_pic,g.goods_id,g.weight,p.store,i.supplier_id,i.bn dealer_bn,sp.source_bn supplier_bn\r\n            FROM sdb_order_items i\r\n            LEFT JOIN sdb_products p ON i.product_id = p.product_id\r\n            LEFT JOIN sdb_goods g ON g.goods_id = p.goods_id\r\n            LEFT JOIN sdb_supplier_pdtbn sp ON i.bn = sp.local_bn AND sp.default = 'true'\r\n            LEFT JOIN sdb_supplier s ON sp.sp_id = s.sp_id\r\n            WHERE order_id = '".$orderid."' AND i.is_type = 'goods' ";
        $aGoods = $this->db->select( $sql );
        $aPo = array(
            "local" => array( ),
            "supplier" => array( )
        );
        foreach ( $aGoods as $aGoodsItem )
        {
            if ( $aGoodsItem['supplier_id'] === null )
            {
                $aPo['local'][$aGoodsItem['bn']] = $aGoodsItem;
            }
            else
            {
                $aPo['supplier'][$aGoodsItem['supplier_id']][$aGoodsItem['bn']] = $aGoodsItem;
            }
        }
        $sql = "SELECT i.*,g.thumbnail_pic,g.goods_id,g.weight,g.store,i.bn dealer_bn\r\n            FROM sdb_order_items i\r\n            LEFT JOIN sdb_goods g ON i.product_id = g.goods_id\r\n            WHERE order_id = '".$orderid."' AND i.is_type = 'pkg' ";
        $aGoods = $this->db->select( $sql );
        foreach ( ( array )$aGoods as $aGoodsItem )
        {
            $aPo['local'][$aGoodsItem['bn']] = $aGoodsItem;
        }
        $aGoods = $this->db->select( "SELECT i.*\r\n                    FROM sdb_gift_items i LEFT JOIN sdb_gift f ON i.gift_id = f.gift_id \r\n                    WHERE order_id = '".$orderid."'" );
        foreach ( ( array )$aGoods as $aGoodsItem )
        {
            $aPo['gift'][$aGoodsItem['gift_id']] = $aGoodsItem;
        }
        return $aPo;
    }

    function getposhowstatusbypostatus( $status, $pay_status, $ship_status )
    {
        $aPayStatus = array(
            0 => __( "未付款" ),
            1 => __( "已全部付款" ),
            2 => __( "已付款至担保方" ),
            3 => __( "部分付款" ),
            4 => __( "部分退款" ),
            5 => __( "已全部退款" ),
            6 => __( "支付中" )
        );
        $aShipStatus = array(
            0 => __( "未发货" ),
            1 => __( "已全部发货" ),
            2 => __( "部分发货" ),
            3 => __( "部分退货" ),
            4 => __( "已全部退货" )
        );
        if ( $status == "dead" )
        {
            $_po_status = __( "采购单被取消" );
            return $_po_status;
        }
        if ( $status == "finish" )
        {
            $_po_status = __( "已完成" );
            return $_po_status;
        }
        if ( $pay_status == 6 )
        {
            $_po_status = __( "支付中" );
            return $_po_status;
        }
        if ( $status == "pending" )
        {
            $_po_status = __( "暂停" );
            return $_po_status;
        }
        if ( $pay_status == 0 && $ship_status == 0 )
        {
            $_po_status = __( "未付款" );
            return $_po_status;
        }
        if ( $pay_status == 1 && $ship_status == 0 )
        {
            $_po_status = __( "等待发货中" );
            return $_po_status;
        }
        if ( $pay_status == 5 && $ship_status == 0 )
        {
            $_po_status = __( "已全额退款,等待发货" );
            return $_po_status;
        }
        if ( $pay_status == 4 && $ship_status == 0 )
        {
            $_po_status = __( "部分已退款,等待发货" );
            return $_po_status;
        }
        if ( $pay_status == 1 && $ship_status == 1 )
        {
            $_po_status = __( "订单已完成" );
            return $_po_status;
        }
        if ( $pay_status == 5 && $ship_status == 1 )
        {
            $_po_status = __( "已全额退款,等待退货" );
            return $_po_status;
        }
        if ( $pay_status == 4 && $ship_status == 1 )
        {
            $_po_status = __( "已部分退款，已全部发货" );
            return $_po_status;
        }
        if ( $pay_status == 1 && $ship_status == 4 )
        {
            $_po_status = __( "已全部退货，暂未退款" );
            return $_po_status;
        }
        $_po_status = $aPayStatus[$pay_status].",".$aShipStatus[$ship_status];
        return $_po_status;
    }

    function getpoactionbypostatus( $status, $pay_status, $ship_status )
    {
        $_action_status = array( );
        if ( $status == "dead" || $status == "finish" )
        {
            $_action_status = array(
                "inquiry" => false,
                "create" => false,
                "reconciliation" => false,
                "pending" => "disabled",
                "pay" => false,
                "modify" => false
            );
            return $_action_status;
        }
        $_action_status['inquiry'] = false;
        $_action_status['create'] = false;
        if ( $pay_status == 6 || $pay_status == 2 )
        {
            $_action_status['reconciliation'] = true;
        }
        else
        {
            $_action_status['reconciliation'] = false;
        }
        if ( $status == "pending" )
        {
            $_action_status['pending'] = "cancelpending";
        }
        else if ( $pay_status == 1 && $ship_status == 0 )
        {
            $_action_status['pending'] = "pending";
        }
        else
        {
            $_action_status['pending'] = "disabled";
        }
        if ( in_array( $pay_status, array( 0, 2, 3 ) ) )
        {
            $_action_status['pay'] = true;
        }
        else
        {
            $_action_status['pay'] = false;
        }
        if ( $ship_status == 0 )
        {
            $_action_status['modify'] = true;
            return $_action_status;
        }
        $_action_status['modify'] = false;
        return $_action_status;
    }

    function getpolistbyorderid( $orderid, $show_status = "all" )
    {
        if ( !( $pdt_items = $this->getorderitemslist( $orderid ) ) )
        {
            return false;
        }
        $result = array( );
        $result['local'] =& $pdt_items['local'];
        $tmp_nums = 0;
        $tmp_sendnum = 0;
        if ( $pdt_items['local'] )
        {
            foreach ( $pdt_items['local'] as $_item )
            {
                $tmp_nums += $_item['nums'];
                $tmp_sendnum += $_item['sendnum'];
            }
        }
        if ( $tmp_sendnum == 0 )
        {
            $result['local_ship_status'] = 0;
        }
        else if ( $tmp_sendnum == $tmp_nums )
        {
            $result['local_ship_status'] = 1;
        }
        else
        {
            $result['local_ship_status'] = 2;
        }
        $s_pdt_items =& $pdt_items['supplier'];
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "columns" => "order_id|status|pay_status|ship_status|createtime|supplier_id|items|final_amount|cost_item|cost_freight|cost_protect|cost_tax|payed|shipping_id|ship_area|total_amount",
            "id" => $orderid
        );
        if ( !( $show_status == "all" ) || $show_status == "valid" )
        {
            $send['status'] = array( "pending", "active", "finish" );
        }
        if ( count( $pdt_items['supplier'] ) == 0 )
        {
            return $result;
        }
        $aPoList = $api_utility->getapidata( "getPOrdersBySOrderId", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        if ( !is_array( $aPoList ) )
        {
            $aPoList = array( );
        }
        foreach ( $aPoList as $poItem )
        {
            $poItem['_action_status'] = $this->getpoactionbypostatus( $poItem['status'], $poItem['pay_status'], $poItem['ship_status'] );
            $poItem['_po_status'] = $this->getposhowstatusbypostatus( $poItem['status'], $poItem['pay_status'], $poItem['ship_status'] );
            $poItem['ship_area'] = substr( $poItem['ship_area'], strrpos( $poItem['ship_area'], ":" ) + 1 );
            unset( $poItem->'final_amount' );
            $_s2l_cr = $this->get_s2lbns( array_item( $poItem['items'], "bn" ), true );
            foreach ( $poItem['items'] as $_k => $poProductItem )
            {
                $poItem['items'][$_k]['supplier_bn'] = $poProductItem['bn'];
                $poItem['items'][$_k]['dealer_bn'] = $_dealer_bn = $_s2l_cr[$poProductItem['bn']];
                $poItem['items'][$_k]['po_price'] = $poProductItem['price'];
                $poItem['items'][$_k]['price'] = $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['price'];
                $poItem['items'][$_k]['product_id'] = $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['product_id'];
                $poItem['items'][$_k]['goods_id'] = $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['goods_id'];
                if ( $poItem['status'] != "dead" )
                {
                    $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['nums'] -= $poProductItem['nums'];
                }
                unset( $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['nums']->'bn' );
                unset( $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['nums']->'type_id' );
                unset( $s_pdt_items[$poItem['supplier_id']][$_dealer_bn]['nums']->'product_id' );
            }
            $poItem['items'] = array_change_key( $poItem['items'], "dealer_bn" );
            $result['supplier'][$poItem['supplier_id']]['po'][$poItem['order_id']] = $poItem;
        }
        foreach ( $s_pdt_items as $_supplier_id => $_s_pdt_item )
        {
            foreach ( $_s_pdt_item as $_bn => $_item )
            {
                if ( intval( $_s_pdt_item[$_bn]['nums'] ) <= 0 )
                {
                    unset( $_s_pdt_item->$_bn );
                }
            }
            if ( !empty( $_s_pdt_item ) )
            {
                $result['supplier'][$_supplier_id]['local'] = $_s_pdt_item;
            }
            $_sql = sprintf( "select supplier_brief_name from sdb_supplier where supplier_id=%d", $_supplier_id );
            $_supplier = $this->db->selectrow( $_sql );
            $result['supplier'][$_supplier_id]['name'] = $_supplier['supplier_brief_name'];
        }
        return $result;
    }

    function _verify_api_createorder_data( &$array_data )
    {
        $str_poinfo_rule = "shipping    varchar(100)    N\r\ndealer_order_id    integer    Y\r\nshipping_area    varchar(50)    N\r\nship_name    varchar(50)    N\r\nship_area    varchar(255)    N\r\nship_addr    varchar(100)    N\r\nship_zip    varchar(20)    N\r\nship_tel    varchar(30)    N\r\nship_email    varchar(150)    N\r\nship_time    varchar(50)    N\r\nship_mobile    varchar(50)    N\r\nshipping_id    integer    Y\r\nis_tax    enum('false','true')    Y\r\ntax_company    varchar(255)    N\r\nis_protect    enum('false','true')    Y\r\ncurrency    varchar(8)    N\r\nmember_memo    longtext    N\r\nsender_info    longtext    Y\r\nitems    array     Y";
        $obj_verify_data = $this->system->loadmodel( "utility/data_verify" );
        return $obj_verify_data->checkparams( $str_poinfo_rule, $array_data['struct'] );
    }

    function createpo( $supplierId, $orderid, $poInfo, $poItems )
    {
        $all_items = $this->getpolistbyorderid( $orderid, "valid" );
        if ( $all_items === false )
        {
            trigger_error( __( "无法获取PO单" ), E_USER_ERROR );
        }
        else
        {
            $_array_po_items = $all_items['supplier'][$supplierId]['local'];
            $_array_po_items = array_change_key( $_array_po_items, "dealer_bn" );
        }
        foreach ( $poItems as $_item )
        {
            $_array_po_item = $_array_po_items[$_item['dealer_bn']];
            if ( !empty( $_array_po_item ) || !( $_array_po_item['nums'] - $_item['nums'] < 0 ) )
            {
                trigger_error( __( "向供应商下单的商品超出B2C订单内商品,请刷新重试" ), E_USER_ERROR );
            }
        }
        $poInfo['dealer_order_id'] = $orderid;
        $poInfo['sender_info'] = "";
        $poInfo['is_tax'] = $poInfo['is_tax'] ? "true" : "false";
        $poInfo['is_protect'] = $poInfo['is_protect'] ? "true" : "false";
        $poItems = array_change_key( $poItems, "dealer_bn" );
        $_dealer_bns = array_item( $poItems, "dealer_bn" );
        $_l2s_cr = $this->get_l2sbns( $_dealer_bns, true );
        $_inquiry_data = array( );
        foreach ( $_l2s_cr as $_dealer_bn => $_supplier_bn )
        {
            $_inquiry_data[$_supplier_bn] = $poItems[$_dealer_bn]['nums'];
        }
        $_inquiry_result = $this->inquiry( $supplierId, $orderid, $_inquiry_data );
        if ( !$_inquiry_result )
        {
            trigger_error( __( "下单商品有错误,不能下单" ), E_USER_ERROR );
        }
        $_inquiry_result = $_inquiry_result['items'];
        $_err_msg = "";
        foreach ( $_inquiry_result as $_item )
        {
            if ( $_item['po_price'] != $poItems[$_item['dealer_bn']]['price'] )
            {
                trigger_error( sprintf( __( "货品: %s 价格与之前询单价格不同,请重新询单" ), $_item['dealer_bn'] ), E_USER_ERROR );
            }
            if ( !( $_item['status'] == "shelves" ) || !( $_item['status'] == "deleted" ) || !( $_item['stock'] === 0 ) )
            {
                unset( $poItems->$_item['dealer_bn'] );
            }
        }
        $send['id'] = $supplierId;
        $send['struct'] =& $poInfo;
        $send['struct']['items'] = $poItems;
        if ( !$poItems )
        {
            trigger_error( __( "采购单中的商品没有库存，或者无商品" ), E_USER_ERROR );
        }
        else
        {
            if ( ( $str_error_field = $this->_verify_api_createorder_data( $send ) ) !== true )
            {
                trigger_error( __( "下采购单数据有误" ).$str_error_field, E_USER_ERROR );
            }
            $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
            $result = $api_utility->getapidata( "createOrder", API_VERSION, $send );
            $api_utility->trigger_all_errors( );
            $poOrderId = $result;
            foreach ( $_dealer_bns as $_bn )
            {
                $_sql = sprintf( "select * from sdb_order_items where bn='%s'", $_bn );
                $rs = $this->db->query( $_sql );
                $_data = array(
                    "supplier_id" => $supplierId
                );
                $_sql = $this->db->getupdatesql( $rs, $_data );
                $this->db->exec( $sql );
            }
        }
        return $poOrderId;
    }

    function modifyorder( $orderid, $modifyItems, $supplierId = 0, $poId = 0, &$return )
    {
        $all_items = $this->getpolistbyorderid( $orderid, "valid" );
        $_flag = 0;
        $modifyItems = array_change_key( $modifyItems, "dealer_bn" );
        $_delete_items = array( );
        if ( $supplierId == 0 )
        {
            $_flag = 0;
            $_old_order_items = $all_items['local'];
        }
        else if ( $poId === 0 )
        {
            $_flag = 1;
            $_old_order_items = $all_items['supplier'][$supplierId]['local'];
        }
        else
        {
            $_flag = 2;
            $_old_order_items = $all_items['supplier'][$supplierId]['po'][$poId]['items'];
        }
        $_old_order_items = array_change_key( $_old_order_items, "dealer_bn" );
        if ( empty( $modifyItems ) )
        {
            $_sql = sprintf( "select count(bn) as count from sdb_order_items where order_id=%s", $orderid );
            $_array_tmp_count = $this->db->selectrow( $_sql );
            if ( $_array_tmp_count['count'] - count( ( array )$_old_order_items ) <= 0 )
            {
                trigger_error( __( "不能将购物车商品删空" ), E_USER_ERROR );
            }
        }
        else
        {
            $_is_all_delete = true;
            foreach ( $modifyItems as $_bn => $_modifyItem )
            {
                if ( !( 0 < $_modifyItem['nums'] ) )
                {
                    continue;
                }
                $_is_all_delete = false;
                break;
            }
            if ( $_is_all_delete )
            {
                trigger_error( __( "不能将购物车商品删空" ), E_USER_ERROR );
            }
        }
        if ( $_flag === 1 || $_flag === 2 )
        {
            $_dealer_bns = array_item( $modifyItems, "dealer_bn" );
            $_l2s_cr = $this->get_l2sbns( $_dealer_bns, true );
            $_inquiry_data = array( );
            foreach ( $_l2s_cr as $_dealer_bn => $_supplier_bn )
            {
                $_inquiry_data[$_supplier_bn] = $modifyItems[$_dealer_bn]['nums'];
            }
            $_inquiry_result = $this->inquiry( $supplierId, $orderid, $_inquiry_data );
            $_inquiry_result = $_inquiry_result['items'];
            $_err_msg = "";
            foreach ( $_inquiry_result as $_item )
            {
                if ( $_item['stock'] === 0 && $_old_order_items[$_item['dealer_bn']]['nums'] === 0 )
                {
                    trigger_error( sprintf( __( "货品: %s 供应商库存为0,请将货品数量改为0否则无法修改保存" ), $_item['dealer_bn'] ), E_USER_ERROR );
                }
                if ( !( $_item['status'] == "shelves" ) || !( $_item['status'] == "deleted" ) )
                {
                    trigger_error( sprintf( __( "货品: %s 为已下架商品或删除商品,请先删除再下单" ), $_item['dealer_bn'] ), E_USER_ERROR );
                }
            }
            if ( $_flag === 2 )
            {
                foreach ( $_inquiry_result as $_item )
                {
                    if ( $_item['po_price'] != $modifyItems[$_item['dealer_bn']]['po_price'] )
                    {
                        trigger_error( sprintf( __( "货品: %s 价格与之前询单价格不同,请重新询单, 之前价格为%s,之后价格为%s" ), $_item['dealer_bn'], $_item['po_price'], $modifyItems[$_item['dealer_bn']]['po_price'] ), E_USER_ERROR );
                    }
                }
            }
        }
        if ( $_flag === 0 )
        {
            $_tmp_old_bns = array_keys( $_old_order_items );
            $this->_modifylocalitems( $orderid, $modifyItems, $_tmp_old_bns );
        }
        else if ( $_flag === 1 )
        {
            $_tmp_old_bns = array_keys( $_old_order_items );
            $this->_modifysupplieritems( $orderid, $modifyItems, $_old_order_items );
        }
        else if ( $_flag === 2 )
        {
            if ( $all_items['supplier'][$supplierId]['po'][$poId]['pay_status'] == 1 && $all_items['supplier'][$supplierId]['po'][$poId]['status'] == "active" )
            {
                $this->pendingpo( $poId );
            }
            $_edit_data = $modifyItems;
            foreach ( $_edit_data as $_k => $_item )
            {
                $_edit_data[$_k]['price'] = $_item['po_price'];
                unset( $_edit_data[$_k]['price']->'po_price' );
                unset( $_edit_data[$_k]['price']->'product_id' );
            }
            $send = array(
                "id" => $supplierId,
                "order_id" => $poId,
                "items" => $_edit_data
            );
            $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
            $result = $api_utility->getapidata( "editOrder", API_VERSION, $send );
            $api_utility->trigger_all_errors( );
            $_tmp_old_bns = array_keys( $_old_order_items );
            $this->_modifysupplieritems( $orderid, $modifyItems, $_old_order_items );
        }
        $this->_accountorders( $orderid );
        return true;
    }

    function _modifysupplieritems( $orderid, $modifyItems, $old_order_items )
    {
        $oProduct = $this->system->loadmodel( "goods/products" );
        foreach ( $old_order_items as $_bn => $_old_order_item )
        {
            if ( !isset( $modifyItems[$_bn] ) )
            {
                $_sql = sprintf( "update sdb_products set freez=freez - %d where bn='%s' and order_id=%s", $_old_order_item['nums'], $_old_order_item['dealer_bn'], $orderid );
                $this->db->exec( $_sql );
                $_sql = sprintf( "update sdb_order_items set nums=nums - %d where bn='%s' and order_id=%s", $_old_order_item['nums'], $_old_order_item['dealer_bn'], $orderid );
                $this->db->exec( $_sql );
            }
            else if ( $_old_order_item['nums'] != $modifyItems[$_bn]['nums'] )
            {
                $_sql = sprintf( "update sdb_products set freez=freez + %d where bn='%s' and order_id=%s", $modifyItems[$_bn]['nums'] - $_old_order_item['nums'], $_old_order_item['dealer_bn'], $orderid );
                $this->db->exec( $_sql );
                $_sql = sprintf( "update sdb_order_items set nums=nums + %d where bn='%s' and order_id=%s", $modifyItems[$_bn]['nums'] - $_old_order_item['nums'], $_old_order_item['dealer_bn'], $orderid );
                $this->db->exec( $_sql );
            }
        }
        $_sql = sprintf( "delete from sdb_order_items where nums=0 and order_id=%s", $orderid );
        $this->db->exec( $_sql );
    }

    function _modifylocalitems( $orderid, $modifyItems, $old_bns )
    {
        $oOrder = $this->system->loadmodel( "trading/order" );
        $_orderInfo = $oOrder->getfieldbyid( $orderid, array( "status", "pay_status", "ship_status" ) );
        if ( $_orderInfo['status'] == "active" || $_orderInfo['pay_status'] == 0 || $_orderInfo['ship_status'] == 0 )
        {
            $add_Store = array( );
            $oProduct = $this->system->loadmodel( "goods/products" );
            $_tmp_new_bns = array_keys( $modifyItems );
            $_tmp_old_bns =& $old_bns;
            $new_pdts = array_intersect( ( array )$_tmp_old_bns, ( array )$_tmp_new_bns );
            $del_pdts = array_diff( ( array )$_tmp_old_bns, ( array )$new_pdts );
            foreach ( $new_pdts as $_bn )
            {
                $_product_id = $modifyItems[$_bn]['product_id'];
                $_pdts_store = $oProduct->getfieldbyid( $_product_id, array( "store", "freez" ) );
                if ( !( $_pdts_store['store'] !== null ) && !( $_pdts_store['store'] !== "" ) )
                {
                    $sql = sprintf( "SELECT nums, name  FROM sdb_order_items WHERE order_id=%s AND product_id = %d", $orderid, $_product_id );
                    $_result = $this->db->selectrow( $sql );
                    $_store = intval( $_pdts_store['store'] ) - intval( $_pdts_store['freez'] ) + intval( $_result['nums'] );
                    if ( $_store < $modifyItems[$_bn]['nums'] )
                    {
                        trigger_error( $_result.__( ":库存不足" ), E_USER_ERROR );
                    }
                    $add_store[$_product_id] = intval( $modifyItems[$_bn]['nums'] ) - intval( $_result['nums'] );
                }
            }
            foreach ( $modifyItems as $_k => $_item )
            {
                $_data = array(
                    "nums" => $_item['nums'],
                    "price" => $_item['price'],
                    "amount" => $_item['nums'] * $_item['price']
                );
                $rs = $this->db->query( sprintf( "select * from sdb_order_items where bn='%s' and order_id=%s", $_item['dealer_bn'], $orderid ) );
                $sql = $this->db->getupdatesql( $rs, $_data );
                $this->db->exec( $sql );
                if ( !isset( $add_store[$_item['product_id']] ) && !( 0 <= $add_store[$productId] ) )
                {
                    $this->db->exec( "UPDATE sdb_products SET freez = freez + ".$add_store[$productId]." WHERE product_id = ".$productId );
                }
            }
            foreach ( $del_pdts as $_bn )
            {
                $_sql = sprintf( "delete from sdb_order_items where bn='%s' and order_id=%s", $_bn, $orderid );
                $this->db->exec( $_sql );
            }
        }
        else
        {
            trigger_error( "此商品非活动订单或已发货或已付款", E_USER_ERROR );
        }
    }

    function _accountorders( $orderid )
    {
        $_sql = sprintf( "update sdb_order_items set amount=price*nums where order_id=%s", $orderid );
        $this->db->exec( $_sql );
        $sql = sprintf( "select sum(i.price) sum_price,sum(i.nums) sum_nums,sum(i.amount) sum_amount,sum(p.weight*i.nums) weight\r\n                           from sdb_order_items i\r\n                           left join sdb_products p on i.product_id=p.product_id\r\n                           where order_id=%s", $orderid );
        $_order_sum = $this->db->selectrow( $sql );
        $oOrder = $this->system->loadmodel( "trading/order" );
        $order_info = $oOrder->getfieldbyid( $orderid );
        $_data['cost_item'] = $_order_sum['sum_amount'];
        $_data['total_amount'] = $_order_sum['sum_amount'] + $order_info['cost_freight'] + $order_info['cost_protect'] + $order_info['cost_payment'] + $order_info['cost_tax'] - $order_info['discount'] - $order_info['pmt_amount'];
        $_rate = $oOrder->getfieldbyid( $orderid, array( "cur_rate" ) );
        $_data['final_amount'] = $_data['total_amount'] * $_rate['cur_rate'];
        if ( $oOrder->toedit( $orderid, $_data ) )
        {
            $oOrder->addlog( __( "订单编辑" ), $this->op_id ? $this->op_id : null, $this->op_name ? $this->op_name : null, __( "编辑" ) );
            return true;
        }
        return false;
    }

    function get_l2sbns( $bns, $is_ref = false )
    {
        foreach ( $bns as $_bn )
        {
            $_where_bns[] = sprintf( "'%s'", addslashes( $_bn ) );
        }
        $_sql = sprintf( "select local_bn, source_bn\r\n                             from sdb_supplier_pdtbn \r\n                             where local_bn in(%s) and `default`='true'", implode( ",", $_where_bns ) );
        $_supplier_pdtbn = $this->db->select( $_sql );
        $result = array( );
        if ( $is_ref )
        {
            foreach ( $_supplier_pdtbn as $_k => $_item )
            {
                $result[$_item['local_bn']] = $_item['source_bn'];
            }
            return $result;
        }
        $result = array_item( $_supplier_pdtbn, "source_bn" );
        return $result;
    }

    function get_s2lbns( $bns, $is_ref = "false" )
    {
        foreach ( $bns as $_bn )
        {
            $_where_bns[] = sprintf( "'%s'", addslashes( $_bn ) );
        }
        $_sql = sprintf( "SELECT s.local_bn, s.source_bn FROM sdb_supplier_pdtbn AS s\r\n                         RIGHT JOIN sdb_products AS p ON p.bn = s. local_bn\r\n                         WHERE  s.source_bn IN(%s)", implode( ",", $_where_bns ) );
        $_supplier_pdtbn = $this->db->select( $_sql );
        $result = array( );
        if ( $is_ref )
        {
            foreach ( $_supplier_pdtbn as $_k => $_item )
            {
                $result[$_item['source_bn']] = $_item['local_bn'];
            }
            return $result;
        }
        $result = array_item( $_supplier_pdtbn, "source_bn" );
        return $result;
    }

    function inquiry( $supplierId, $orderid, $bns )
    {
        if ( empty( $bns ) || !is_array( $bns ) )
        {
            return false;
        }
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $result_items = array( );
        $send = array(
            "id" => $supplierId,
            "bns" => array_keys( $bns )
        );
        $inquiry_result = $api_utility->getapidata( "inquiry", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        $store_mark = 0;
        foreach ( $inquiry_result as $k => $item )
        {
            $result_items[$k] = array(
                "supplier_bn" => $item['bn'],
                "po_price" => $item['price'],
                "stock" => $item['stock']
            );
            if ( $item['status'] == "normal" )
            {
                if ( $item['stock'] === null || $item['store'] === "" )
                {
                    $result_items[$k]['stock_status'] = 0;
                    $store_mark = 1;
                }
                else if ( $bns[$item['bn']] + $item['threshold'] < $item['stock'] )
                {
                    $result_items[$k]['stock_status'] = 0;
                    $store_mark = 1;
                }
                else if ( $bns[$item['bn']] < $item['stock'] )
                {
                    $result_items[$k]['stock_status'] = 1;
                    $store_mark = 1;
                }
                else if ( 0 < $item['stock'] )
                {
                    $result_items[$k]['stock_status'] = 2;
                    $store_mark = 1;
                }
                else
                {
                    $result_items[$k]['stock_status'] = 3;
                }
            }
            $result_items[$k]['status'] = $item['status'];
        }
        $_s2l_cr = $this->get_s2lbns( array_item( $result_items, "supplier_bn" ), true );
        $_where_bns = array( );
        $oCostSync = $this->system->loadmodel( "distribution/costsync" );
        foreach ( $result_items as $_k => $_item )
        {
            $result_items[$_k]['dealer_bn'] = $_s2l_cr[$_item['supplier_bn']];
            $result_items[$_k]['nums'] = $bns[$_item['supplier_bn']];
            $_where_bns[] = sprintf( "'%s'", addslashes( $result_items[$_k]['dealer_bn'] ) );
            $sSql = "SELECT i.name,i.price, i.product_id,p.goods_id FROM sdb_order_items AS i\r\n                    LEFT JOIN sdb_products AS p ON p.product_id = i.product_id WHERE i.bn='".$result_items[$_k]['dealer_bn']."' and i.order_id=".$orderid;
            $aProduct = $this->db->selectrow( $sSql );
            if ( empty( $aProduct ) )
            {
                $sSql = "SELECT i.name,i.price, i.product_id,i.bn \r\n                         FROM sdb_order_items AS i \r\n                         LEFT JOIN sdb_supplier_pdtbn AS s ON s.local_bn = i.bn \r\n                         WHERE s.source_bn='".$_item['supplier_bn']."' and i.order_id=".$orderid;
                $aProduct = $this->db->selectrow( $sSql );
                $sSql = "SELECT product_id FROM sdb_products WHERE bn ='".$result_items[$_k]['dealer_bn']."'";
                $aTemp = $this->db->selectrow( $sSql );
                if ( $aTemp )
                {
                    $aTemp['bn'] = $result_items[$_k]['dealer_bn'];
                    $rs = $this->db->exec( "SELECT * FROM sdb_order_items WHERE order_id='".$orderid."' AND bn='".$aProduct['bn']."'" );
                    $sSql = $this->db->getupdatesql( $rs, $aTemp );
                    $this->db->exec( $sSql );
                }
                unset( $aProduct->'bn' );
            }
            $result_items[$_k] = array_merge( ( array )$aProduct, $result_items[$_k] );
            $oCostSync->updatealoneproductcost( $aProduct['product_id'], $_item['po_price'] );
        }
        $_sql = sprintf( "select bn as dealer_bn,nums,supplier_id from sdb_order_items where order_id=%s and bn in(%s)", $orderid, implode( ",", $_where_bns ) );
        $_order_items = $this->db->select( $_sql );
        $_order_items = array_change_key( $_order_items, "dealer_bn" );
        $result_total_amount = 0;
        foreach ( $result_items as $_k => $_item )
        {
            if ( $_order_items[$_item['dealer_bn']]['supplier_id'] !== null )
            {
                $result_items[$_k]['store'] += $_order_items[$_item['dealer_bn']]['nums'];
            }
            if ( $_item['stock'] <= 0 )
            {
                $result_items[$_k]['stock'] = 0;
            }
            $result_items[$_k]['amount'] = $bns[$_item['supplier_bn']] * $_item['po_price'];
            $result_total_amount += $result_items[$_k]['amount'];
            if ( $_item['stock'] == null || $_item['stock'] == "" )
            {
                $result_items[$_k]['stock'] = -1;
            }
            $_data = array(
                "store" => $result_items[$_k]['stock'] != -1 ? $result_items[$_k]['stock'] : null
            );
            $_sql = sprintf( "SELECT * FROM sdb_products WHERE bn='%s'", addslashes( $_item['dealer_bn'] ) );
            $rs = $this->db->exec( $_sql );
            $_sql = $this->db->getupdatesql( $rs, $_data );
            $this->db->exec( $_sql );
        }
        $result_items = array_change_key( $result_items, "dealer_bn" );
        $result = array(
            "items" => $result_items,
            "store_status" => $store_mark,
            "total_amount" => $result_total_amount
        );
        return $result;
    }

    function pendingpo( $orderid )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "id" => $orderid
        );
        $api_utility->getapidata( "setOrderPending", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return true;
    }

    function cancelpendingpo( $orderid )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "id" => $orderid
        );
        $api_utility->getapidata( "setOrderAwake", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return true;
    }

    function reconciliation( $orderid )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "id" => $orderid
        );
        $api_utility->getapidata( "reconciliation", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return true;
    }

    function paybydeposits( $orderid, $payId )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "pay_id" => $payId,
            "id" => $orderid
        );
        $api_utility->getapidata( "PayByDeposits", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return true;
    }

    function getsupplierdomain( $supplierId, $is_real_url = false )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "id" => $supplierId
        );
        $domain = $api_utility->getapidata( "getDomain", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        if ( !$is_real_url )
        {
            $_pattern = "/^(((http|https):\\/\\/)?)([-A-Z0-9+&@#\\/%?=~_|!:,.;]*[-A-Z0-9+&@#%=~_|])\\/*\$/i";
            $_replacement = "\$4";
            $domain['domain'] = preg_replace( $_pattern, $_replacement, $domain['domain'] );
        }
        return $domain['domain'];
    }

    function getdlyarea( $supplierId )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $_result = $api_utility->getapidata( "search_dly_area", API_VERSION );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getdlycorp( $supplierId )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $_result = $api_utility->getapidata( "search_dly_corp", API_VERSION );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getdlytype( $supplierId )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $_result = $api_utility->getapidata( "search_dly_type", API_VERSION );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getdlyharea( $supplierId )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $_result = $api_utility->getapidata( "search_dly_h_area", API_VERSION );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getcurlist( $supplierId )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $_result = $api_utility->getapidata( "search_cur_list", API_VERSION );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getpaymentcfg( $supplierId )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "id" => $supplierId
        );
        $_result = $api_utility->getapidata( "getPaymentCfg", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getporderbyid( $poId )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $send = array(
            "id" => $poId
        );
        $_result = $api_utility->getapidata( "getPOrderById", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getsubregions( $supplierId, $regionId = 0 )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $send = array(
            "p_region_id" => intval( $regionId )
        );
        $_result = $api_utility->getapidata( "search_sub_regions", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getdlytypebyarea( $supplierId, $areaId = 0 )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $send = array(
            "area_id" => intval( $areaId )
        );
        $_result = $api_utility->getapidata( "search_dltype_byarea", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getdlyispay( $supplierId, $deliveryId, $areaId = 0 )
    {
        $_api_domain = $this->getsupplierdomain( $supplierId );
        $api_utility =& $this->system->api_call( "b2b-".strval( $supplierId ), $_api_domain, "/api.php", 80, $this->_token );
        $send = array(
            "delivery_id" => intval( $deliveryId ),
            "area_id" => intval( $areaId )
        );
        $_result = $api_utility->getapidata( "search_dly_type_byid", API_VERSION, $send );
        $api_utility->trigger_all_errors( );
        return $_result;
    }

    function getordersetting( $supplier_id )
    {
        $api_utility =& $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $this->_token );
        $setting = $api_utility->getapidata( "getOrderSetting", API_VERSION, array(
            "id" => $supplier_id
        ) );
        if ( $setting )
        {
            return $setting;
        }
        $api_utility->trigger_all_errors( );
        return array( );
    }

}

?>
