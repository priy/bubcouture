<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_taobaoordercsv extends shopobject
{

    function getordersexportdata( $v )
    {
        $v['tpayscore'] = $v['tscore'] = $v['tsort'] = $v['shop_cat'] = "";
        if ( $v['member_id'] )
        {
            $member = $this->db->selectrow( "SELECT uname from sdb_members WHERE member_id=".$v['member_id'] );
            $v['member_id'] = $member['uname'];
        }
        if ( $v['delivery_id'] )
        {
            $delivery = $this->db->selectrow( "SELECT delivery_id from sdb_delivery WHERE order_id =".$v['order_id'] );
            $v['delivery_id'] = $delivery['delivery_id'];
        }
        $status = array( 0 => "未支付", 1 => "已支付", 2 => "已付款至担保方", 3 => "部分付款", 4 => "部分退款", 5 => "已退款" );
        $v['ship_addr'] .= $v['ship_zip'] ? "(".$v['ship_zip'].")" : "";
        $v['pay_status'] = $status[$v['pay_status']];
        if ( $v['pay_status'] == "已支付" )
        {
            $v['acttime'] = date( "Y-m-d H:i:s", $v['acttime'] );
        }
        else
        {
            $v['acttime'] = "";
        }
        $v['createtime'] = date( "Y-m-d H:i:s", $v['createtime'] );
        unset( $v->'mark_type' );
        unset( $v->'status' );
        unset( $v->'ship_status' );
        unset( $v->'dealer_id' );
        unset( $v->'ship_zip' );
        $data[] = $v;
        return $data;
    }

    function getgoodsexportdata( $order_id )
    {
    }

    function orderexporttitle( )
    {
        $id_title = array( "order_id" => "订单编号", "member_id" => "买家会员名", "shop_cat" => "买家支付宝账号", "final_amount" => "买家应付货款", "cost_freight" => "买家应付邮费", "tpayscore" => "买家支付积分", "total_amount" => "总金额", "score_g" => "返点积分", "payed" => "买家实际支付金额", "tscore" => "买家实际支付积分", "pay_status" => "订单状态", "memo" => "买家留言", "ship_name" => "收货人姓名", "ship_addr" => "收货地址", "shipping_id" => "运送方式", "ship_tel" => "联系电话", "ship_mobile" => "联系手机", "createtime" => "订单创建时间", "acttime" => "订单付款时间", "tostr" => "宝贝标题", "tsort" => "宝贝种类", "delivery_id" => "物流单号", "shipping" => "物流公司", "mark_text" => "订单备注" );
        return $id_title;
    }

    function goodsexporttitle( )
    {
        $id_title = array( "order_id" => "订单编号", "name" => "标题", "price" => "价格", "nums" => "购买数量", "sysid" => "外部系统编号", "pdt_desc" => "商品属性", "tinfo" => "套餐信息", "memo" => "备注" );
        return $id_title;
    }

}

?>
