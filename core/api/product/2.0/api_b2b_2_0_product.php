<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_2_0_product extends shop_api_object
{

    public $api_type = "native_api";
    public $max_number = 200;
    public $app_error = array
    (
        "sync_goods_no_exist" => array
        (
            "no" => "b_product_001",
            "debug" => "",
            "level" => "error",
            "desc" => "同步订单商品数据不存在",
            "info" => ""
        ),
        "goods_not_exists" => array
        (
            "no" => "b_product_002",
            "debug" => "",
            "level" => "error",
            "desc" => "订单商品不存在",
            "info" => ""
        ),
        "product_not_exists" => array
        (
            "no" => "b_product_003",
            "debug" => "",
            "level" => "error",
            "desc" => "订单货品不存在",
            "info" => ""
        ),
        "goods_can_not_publish" => array
        (
            "no" => "b_product_004",
            "debug" => "",
            "level" => "error",
            "desc" => "订单商品未发布不能下单",
            "info" => ""
        ),
        "goods_price_is_not_equal_to_the_suppliers_price" => array
        (
            "no" => "b_product_005",
            "debug" => "",
            "level" => "error",
            "desc" => "供应商价格或者库存变动，请重新询价后下单",
            "info" => ""
        ),
        "product_no_store" => array
        (
            "no" => "b_product_006",
            "debug" => "",
            "level" => "error",
            "desc" => "订单货品无库存",
            "info" => ""
        ),
        "product_no_available_store" => array
        (
            "no" => "b_product_007",
            "debug" => "",
            "level" => "error",
            "desc" => "订单货品没有可下单库存",
            "info" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array(
            "goods_id" => array( "type" => "int" ),
            "title" => array( "type" => "string" ),
            "bn" => array( "type" => "int" ),
            "price" => array( "type" => "int" ),
            "cost" => array( "type" => "int" ),
            "name" => array( "type" => "string" ),
            "weight" => array( "type" => "int" ),
            "unit" => array( "type" => "string" ),
            "store" => array( "type" => "int" ),
            "pdt_info" => array( "type" => "string" ),
            "props" => array( "type" => "string" ),
            "last_modify" => array( "type" => "int" )
        );
        return $columns;
    }

    public function search_product_by_bn( $data )
    {
        $data = stripslashes_array( $data );
        $member = array( );
        $result['alert_num'] = $this->system->getConf( "system.product.alert.num" );
        $products = array( );
        $product_list = json_decode( $data['bns'] );
        $dealer_id = $data['dealer_id'];
        $arr_goods_list = array( );
        $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
        $obj_member->verify_member_valid( $dealer_id, $member );
        $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
        foreach ( $product_list as $bn )
        {
            $status = "normal";
            if ( $product = $this->db->selectrow( "select goods_id,product_id,price,store,freez,name,pdt_desc,weight from sdb_products where bn=\"".$bn."\"" ) )
            {
                if ( !isset( $arr_goods_list[$product['goods_id']] ) )
                {
                    $goods = $this->db->selectrow( "select goods_id,cat_id,brand_id,marketable,disabled from sdb_goods where goods_id=".$product['goods_id'] );
                    if ( $goods )
                    {
                        $arr_goods_list[] = $goods;
                    }
                }
                else
                {
                    $goods = $arr_goods_list[$product['goods_id']];
                }
                if ( !$goods || $goods['disabled'] == "true" )
                {
                    $status = "deleted";
                }
                if ( $goods['marketable'] == "false" )
                {
                    $status = "shelves";
                }
                if ( is_null( $product['store'] ) )
                {
                    $product['store'] = $product['store'];
                }
                else
                {
                    $product['store'] = $product['store'] - $product['freez'];
                }
                $product['link'] = $this->system->base_url( )."index.php?ctl=product&p[0]=".$product['goods_id'];
                unset( $product['freez'] );
            }
            else
            {
                $status = "deleted";
            }
            $product['price'] = $obj_payments->changer( $this->get_product_lv_price( $member['member_lv_id'], $product['product_id'], $product['price'] ) );
            $product['bn'] = $bn;
            $product['status'] = $status;
            $goods = $this->db->selectrow( "SELECT thumbnail_pic FROM sdb_goods WHERE goods_id=".$product['goods_id'] );
            $thumbnail_pic = explode( "|", $goods['thumbnail_pic'] );
            $product['thumbnail_pic'] = empty( $goods['thumbnail_pic'] ) ? "" : count( $thumbnail_pic ) == 3 ? $this->system->base_url( ).$thumbnail_pic[0] : $thumbnail_pic[0];
            unset( $product['goods_id'] );
            unset( $product['product_id'] );
            $products[] = $product;
        }
        $obj_product_line = $this->load_api_instance( "search_product_line", "2.0" );
        $obj_product_line->checkDealerPurview( $member, $arr_goods_list );
        $result['data_info'] = $products;
        $this->api_response( "true", FALSE, $result );
    }

    public function filter_product_invalid( $member, $arr_order_item, &$filter_order_item )
    {
        if ( is_array( $arr_order_item ) && 0 < count( $arr_order_item ) )
        {
            safevar( $arr_order_item );
            $arr_goods = array( );
            $is_buy = FALSE;
            $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
            foreach ( $arr_order_item as $k => $order_item )
            {
                $product = $this->db->selectrow( "select goods_id,product_id,store,freez,pdt_desc,name,weight,cost,price,disabled from sdb_products where bn=\"".$order_item['supplier_bn']."\"" );
                if ( !$product )
                {
                    $this->add_application_error( "product_not_exists" );
                }
                if ( !isset( $arr_goods[$product['goods_id']] ) )
                {
                    $goods = $this->db->selectrow( "select goods_id,cat_id,brand_id,type_id,marketable,disabled,score from sdb_goods where goods_id=".intval( $product['goods_id'] ) );
                    if ( !$goods )
                    {
                        $this->add_application_error( "goods_not_exists" );
                    }
                    $arr_goods[$product['goods_id']] = $goods;
                }
                else
                {
                    $goods = $arr_goods[$product['goods_id']];
                }
                if ( $goods['marketable'] == "false" || $goods['disabled'] == "true" )
                {
                    $this->add_application_error( "goods_can_not_publish" );
                }
                $product['price'] = $obj_payments->changer( $this->get_product_lv_price( $member['member_lv_id'], $product['product_id'], $product['price'] ) );
                $order_item['price'] .= "";
                $product['price'] .= "";
                if ( $product['price'] != $order_item['price'] )
                {
                    $this->add_application_error( "goods_price_is_not_equal_to_the_suppliers_price" );
                }
                $product['freez'] = !is_null( $product['freez'] ) ? $product['freez'] : 0;
                if ( $product['store'] === 0 )
                {
                    continue;
                }
                if ( !is_null( $product['store'] ) && $product['store'] - $product['freez'] <= 0 )
                {
                    continue;
                }
                if ( !is_null( $product['store'] ) && $product['store'] - $product['freez'] < $order_item['nums'] )
                {
                    $order_item['nums'] = $product['store'] - $product['freez'];
                }
                $product = array_merge( $product, $order_item );
                $product['type_id'] = $goods['type_id'];
                $product['amount'] = $order_item['nums'] * $order_item['price'];
                $product['amount_weight'] = $order_item['nums'] * $product['weight'];
                $product['score'] = $goods['score'];
                $product['bn'] = $order_item['supplier_bn'];
                $product['name'] = $product['pdt_desc'] ? $product['name']."(".$product['pdt_desc'].")" : $product['name'];
                $is_buy = TRUE;
                unset( $product['store'] );
                unset( $product['freez'] );
                $filter_order_item[$k] = $product;
            }
            if ( !$is_buy )
            {
                $this->add_application_error( "goods_price_is_not_equal_to_the_suppliers_price" );
            }
            $obj_product_line = $this->load_api_instance( "search_product_line", "2.0" );
            $obj_product_line->checkDealerPurview( $member, $arr_goods );
        }
        else
        {
            $this->add_application_error( "sync_goods_no_exist" );
        }
    }

    public function get_product_lv_price( $level_id, $product_id, $product_price )
    {
        $product_lv_price = $this->db->selectrow( "select price from sdb_goods_lv_price where product_id=\"".$product_id."\" and level_id=".$level_id );
        if ( $product_lv_price )
        {
            return $product_lv_price['price'];
        }
        else
        {
            $obj_member = $this->load_api_instance( "verify_member_valid", "2.0" );
            $obj_member->verify_member_lv_valid( $level_id, $member_lv );
            return $member_lv['dis_count'] * $product_price;
        }
    }

    public function update_product_store( $bn, $store, $flag = "freeze" )
    {
        safevar( $bn );
        $product = $this->db->selectrow( "select product_id,store,freez from sdb_products where bn=\"".$bn."\"" );
        if ( $product )
        {
            $freez = $product['freez'];
            if ( is_null( $freez ) )
            {
                $this->db->exec( "UPDATE sdb_products SET freez = 0 WHERE bn = \"".$bn."\" AND freez IS NULL" );
                $freez = 0;
            }
            if ( $flag == "freeze" )
            {
                $this->db->exec( "UPDATE sdb_products SET freez = freez + ".intval( $store )." WHERE bn = \"".$bn."\"" );
            }
            else if ( $freez < $store )
            {
                if ( $freez != 0 )
                {
                    $this->db->exec( "UPDATE sdb_products SET freez = 0 WHERE bn = \"".$bn."\"" );
                }
            }
            else
            {
                $this->db->exec( "UPDATE sdb_products SET freez = freez - ".intval( $store )." WHERE bn = \"".$bn."\"" );
            }
        }
    }

    public function freeze_product_store( $bn, $store )
    {
    }

    public function update_product( $product_id, $aData )
    {
        $rs = $this->db->exec( "SELECT * FROM sdb_products WHERE product_id=".intval( $product_id ) );
        $sSql = $this->db->getUpdateSQL( $rs, $aData );
        $this->db->exec( $sSql );
    }

    public function update_goods( $goods_id, $aData )
    {
        $rs = $this->db->exec( "SELECT * FROM sdb_goods WHERE goods_id=".intval( $goods_id ) );
        $sSql = $this->db->getUpdateSQL( $rs, $aData );
        $this->db->exec( $sSql );
    }

    public function update_store_by_orderitem( $order_item_list )
    {
        $arr_goods_id = array( );
        $arr_goods = array( );
        foreach ( $order_item_list as $order_item )
        {
            $products = $this->db->selectrow( "select goods_id,store,freez from sdb_products where product_id=".intval( $order_item['product_id'] ) );
            if ( !$products )
            {
                continue;
            }
            $update_product_store = array( );
            if ( !is_null( $products['store'] ) )
            {
                if ( $order_item['nums'] <= $products['store'] )
                {
                    $update_product_store['store'] = $products['store'] - $order_item['nums'];
                }
                else
                {
                    $update_product_store['store'] = 0;
                }
            }
            if ( !is_null( $products['freez'] ) )
            {
                if ( $order_item['nums'] <= $products['freez'] )
                {
                    $update_product_store['freez'] = $products['freez'] - $order_item['nums'];
                }
                else
                {
                    $update_product_store['freez'] = 0;
                }
            }
            if ( !empty( $update_product_store ) )
            {
                $this->update_product( $order_item['product_id'], $update_product_store );
            }
            if ( isset( $arr_goods_id[$products['goods_id']] ) )
            {
                $arr_goods_id[$products['goods_id']] += $order_item['nums'];
            }
            else
            {
                $arr_goods_id[$products['goods_id']] = $order_item['nums'];
            }
        }
        if ( !empty( $arr_goods_id ) )
        {
            foreach ( $arr_goods_id as $goods_id => $store )
            {
                $goods = $this->db->selectrow( "select store from sdb_goods where goods_id=".intval( $goods_id ) );
                if ( !$goods )
                {
                    continue;
                }
                if ( !is_null( $goods['store'] ) )
                {
                    if ( $store < $goods['store'] )
                    {
                        $update_goods_store['store'] = $goods['store'] - $store;
                    }
                    else
                    {
                        $update_goods_store['store'] = 0;
                    }
                    $this->update_goods( $goods_id, $update_goods_store );
                }
            }
        }
        return TRUE;
    }

}

?>
