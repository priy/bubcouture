<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$mode_dir = ( !defined( "SHOP_DEVELOPER" ) || !constant( "SHOP_DEVELOPER" ) ) && version_compare( PHP_VERSION, "5.0", ">=" ) ? "model_v5" : "model";
require_once( CORE_DIR."/".$mode_dir."/trading/mdl.order.php" );
class mdl_tborder extends mdl_order
{

    public function mdl_tborder( )
    {
        parent::mdl_order( );
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $tb_api =& $appmgr->load( "tb_order_ctl" );
        $this->tb =& $tb_api;
    }

    public function getColumns( )
    {
        $ret = parent::getcolumns( );
        $ret['order_status'] = array(
            "label" => "订单状态",
            "class" => "span-3",
            "html" => dirname( __FILE__ )."/view/order/order_status.html",
            "sql" => "1"
        );
        $ret['ship_status']['hidden'] = TRUE;
        $ret['pay_status']['hidden'] = TRUE;
        unset( $ret['_cmd'] );
        unset( $ret['shipping'] );
        unset( $ret['payment'] );
        return $ret;
    }

    public function tb_synchronization( $params, &$tb, $f_tb = FALSE, $session = FALSE, $f_id, $wucha, &$tb_model, &$tb_api )
    {
        $this->system->call( "tb_order_trans", $params, $tb, $f_tb = FALSE, $session = FALSE, $f_id, $wucha, $tb_model, $tb_api );
    }

    public function getdeliverid( $local_name )
    {
        $de = $this->db->selectrow( "select region_id from sdb_regions where local_name = '".$local_name."'" );
        return $de['region_id'];
    }

    public function getEleByTbOrd( $tb_id )
    {
        return $this->db->selectrow( "SELECT cost_protect FROM sdb_orders WHERE order_id  ='".$tb_id."'" );
    }

    public function gettborder_info( $order_id )
    {
        return $this->db->selectrow( "SELECT a.*,b.*,c.uname FROM sdb_orders a,sdb_tb_order_ctl_orders b,sdb_members c WHERE a.order_id = b.order_id AND a.member_id = c.member_id AND a.order_id ='".$order_id."'" );
    }

    public function get_delay_items( $order_id )
    {
        return $this->db->select( "SELECT order_id FROM sdb_order_items WHERE order_id=".$order_id." AND status='wait';" );
    }

    public function getdeliaddress( )
    {
        return $this->db->select( "SELECT * FROM sdb_dly_center" );
    }

    public function gettbareaid( $name )
    {
        $data = $this->db->selectrow( "SELECT region_id FROM sdb_tb_order_ctl_regions WHERE local_name LIKE '%".$name."%'" );
        return $data['region_id'];
    }

    public function userManage( $mendata, $session )
    {
        $mem['password'] = md5( time( ) );
        $mem['uname'] = $mendata['nick'];
        $mem['name'] = $mendata['ship_name'];
        $mem['email'] = $mendata['b_email'];
        $mem['mobile'] = $mendata['ship_mobile'];
        $mem['tel'] = $mendata['ship_tel'];
        $mem['zip'] = $mendata['ship_zip'];
        $mem['addr'] = $mendata['ship_addr'];
        $this->tb->member_refer = "taobao_login";
        $mem_info = $this->tb->passport_verify( $mem );
        return $mem_info['member_id'];
    }

    public function getrateitems( $order_id )
    {
        return $this->db->select( "SELECT a.order_id,tb_tid,img_path,b.name FROM sdb_tb_order_ctl_order_items a,sdb_order_items b WHERE a.order_id = b.order_id AND traderate = 0 AND a.order_id = ".$order_id );
    }

    public function settradenote( $taobao_order )
    {
        return $this->db->exec( "UPDATE sdb_tb_order_ctl_order_items SET traderate=1 WHERE tb_tid = ".$taobao_order );
    }

    public function getExtendItems( $item_id )
    {
        return $this->db->selectrow( "SELECT  * FROM sdb_tb_order_ctl_order_items where item_id = ".$item_id );
    }

}

?>
