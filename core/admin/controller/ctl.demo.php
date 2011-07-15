<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_demo extends adminPage
{

    public $workground = "goods";
    public $requestAddress = "http://localhost/platform/";
    public $my_supply_id = 2;
    public $totalPage = 10;
    public $supplier_id = TEST_SUPPLIER;

    public function index( )
    {
        $this->page( "demo/list.html", TRUE );
    }

    public function set( )
    {
        $this->page( "demo/set.html", TRUE );
    }

    public function b2c( )
    {
        $this->page( "demo/b2c.html", TRUE );
    }

    public function orderlist( )
    {
        $this->page( "demo/订单列表.html", TRUE );
    }

    public function orderedit( )
    {
        $this->page( "demo/订单详情(采购单操作).html", TRUE );
    }

    public function orderedit2( )
    {
        $this->page( "demo/订单详情(缺库存商品下单).html", TRUE );
    }

    public function orderedit3( )
    {
        $this->page( "demo/订单详情(采购单修改逻辑).html", TRUE );
    }

    public function order_mai( )
    {
        $this->page( "demo/order_mai.html", TRUE );
    }

    public function order( )
    {
        $this->page( "demo/order.html", TRUE );
    }

    public function purchase( )
    {
        $this->page( "demo/下采购单确认页面.html", TRUE );
    }

    public function purchaselist( )
    {
        $this->page( "demo/采购单列表页面.html", TRUE );
    }

    public function purchaseset( )
    {
        $this->page( "demo/purchaseset.html", TRUE );
    }

    public function pay( )
    {
        $this->page( "demo/支付页面.html", TRUE );
    }

    public function subsist( )
    {
        $this->page( "demo/预存款合并付款页面.html", TRUE );
    }

    public function member_newsell( )
    {
        $this->page( "demo/member_newsell.html", TRUE );
    }

    public function planlist( )
    {
        $this->page( "demo/planlist.html", TRUE );
    }

    public function agentbuy( )
    {
        $this->page( "demo/agentbuy.html", TRUE );
    }

    public function userpurview( )
    {
        $this->page( "demo/userpurview.html", TRUE );
    }

    public function pricectr( )
    {
        $this->page( "demo/pricectr.html", TRUE );
    }

    public function pricectr2( )
    {
        $this->page( "demo/pricectr2.html", TRUE );
    }

    public function pricectr3( )
    {
        $this->page( "demo/pricectr3.html", TRUE );
    }

    public function addWholesale( )
    {
        $this->page( "demo/addWholesale.html", TRUE );
    }

    public function doSelectRule1( )
    {
        $this->page( "demo/doSelectRule1.html", TRUE );
    }

    public function doSelectRule2( )
    {
        $this->page( "demo/doSelectRule2.html", TRUE );
    }

    public function doSelectRule3( )
    {
        $this->page( "demo/doSelectRule3.html", TRUE );
    }

    public function doWriteRule( )
    {
        $this->page( "demo/doWriteRule.html", TRUE );
    }

    public function saveRule1( )
    {
        $this->page( "demo/saveRule1.html", TRUE );
    }

    public function saveRule2( )
    {
        $this->page( "demo/saveRule2.html", TRUE );
    }

    public function viewallRule( )
    {
        $this->page( "demo/viewallRule.html", TRUE );
    }

    public function productedit( )
    {
        $this->page( "demo/productedit.html", TRUE );
    }

    public function productedit2( )
    {
        $this->page( "demo/productedit2.html", TRUE );
    }

    public function returngoods( )
    {
        $this->page( "demo/returngoods.html", TRUE );
    }

    public function afterservice( )
    {
        $this->page( "demo/afterservice.html", TRUE );
    }

    public function registertype( )
    {
        $this->page( "demo/registertype.html", TRUE );
    }

    public function memberlist( )
    {
        $this->page( "demo/memberlist.html", TRUE );
    }

    public function member_editsell( )
    {
        $this->page( "demo/member_editsell.html", TRUE );
    }

    public function noticeDemo( )
    {
        $this->page( "demo/noticeDemo.html", TRUE );
    }

    public function freeze( )
    {
        $this->page( "demo/freeze.html", TRUE );
    }

    public function freeze2( )
    {
        $this->page( "demo/freeze2.html", TRUE );
    }

    public function setTab( )
    {
        $this->page( "demo/setTab.html", TRUE );
    }

    public function test_inquiry( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->inquiry( $this->supplier_id, "20090525070839", array( "G49Basdf771B4BB4E0-1" => 3, "G49B771Bdsa4BB4E0-2" => 4, "G49B771B4BBasdf4E0-3" => 5, "G49B771B4BB4E0-4" => 2 ) );
        echo "--";
        var_dump( $_result );
    }

    public function test_createPo( )
    {
        $_info = array( "shipping" => "shipping", "shipping_area" => "shipping_area", "ship_name" => "ship_name", "ship_area" => "ship_area", "ship_addr" => "ship_addr", "ship_zip" => "ship_zip", "ship_tel" => "ship_tel", "ship_email" => "ship_email", "ship_time" => "ship_time", "ship_mobile" => "ship_mobile", "is_tax" => "false", "tax_company" => "", "is_protect" => "true", "currency" => "CNY", "member_memo" => "asdfsadf", "sender_info" => "kk" );
        $_items = array(
            0 => array( "dealer_bn" => "00086lG49B771B4BB4E0-1", "supplier_bn" => "G49B771B4BB4E0-1", "price" => "339", "nums" => "3" ),
            1 => array( "dealer_bn" => "00086lG49B771B4BB4E0-2", "supplier_bn" => "G49B771B4BB4E0-2", "price" => "279", "nums" => "4" ),
            2 => array( "dealer_bn" => "00086lG49B771B4BB4E0-3", "supplier_bn" => "G49B771B4BB4E0-3", "price" => "339", "nums" => "5" ),
            3 => array( "dealer_bn" => "00086lG49B771B4BB4E0-4", "supplier_bn" => "G49B771B4BB4E0-4", "price" => "279", "nums" => "2" )
        );
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->createPo( $this->supplier_id, "20090525070839", $_info, $_items );
        var_dump( $_result );
    }

    public function test_getPoListByOrderId( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getPoListByOrderId( "20090525070839" );
        var_dump( $_result );
    }

    public function test_modifyOrder( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_items = array(
            0 => array( "dealer_bn" => "00086lG49B771B4BB4E0-1", "supplier_bn" => "G49B771B4BB4E0-1", "price" => 339, "po_price" => 339, "nums" => 13, "product_id" => 1008 ),
            1 => array( "dealer_bn" => "00086lG49B771B4BB4E0-2", "supplier_bn" => "G49B771B4BB4E0-2", "price" => 279, "po_price" => 279, "nums" => 14, "product_id" => 1009 ),
            2 => array( "dealer_bn" => "00086lG49B771B4BB4E0-3", "supplier_bn" => "G49B771B4BB4E0-3", "price" => 339, "po_price" => 339, "nums" => 10, "product_id" => 1010 ),
            3 => array( "dealer_bn" => "00086lG49B771B4BB4E0-4", "supplier_bn" => "G49B771B4BB4E0-4", "price" => 279, "po_price" => 279, "nums" => 3, "product_id" => 1011 )
        );
        $_item = json_decode( "{\"0008sbG49B771B4BB4E0-1\":{\"dealer_bn\":\"0008sbG49B771B4BB4E0-1\",\"supplier_bn\":\"G49B771B4BB4E0-1\",\"price\":\"339.000\",\"nums\":\"15\"},\"0008s5G49B771B4BB4E0-2\":{\"dealer_bn\":\"0008s5G49B771B4BB4E0-2\",\"supplier_bn\":\"G49B771B4BB4E0-2\",\"price\":\"279.000\",\"nums\":\"32\"},\"00087hG49B771B4BB4E0-3\":{\"dealer_bn\":\"00087hG49B771B4BB4E0-3\",\"supplier_bn\":\"G49B771B4BB4E0-3\",\"price\":\"339.000\",\"nums\":\"19\"},\"0008voG49B771B4BB4E0-4\":{\"dealer_bn\":\"0008voG49B771B4BB4E0-4\",\"supplier_bn\":\"G49B771B4BB4E0-4\",\"price\":\"279.000\",\"nums\":\"30\"}}", TRUE );
        $_result = $oOrderPo->modifyOrder( "20090525070839", $_items, $this->supplier_id, 188 );
        var_dump( $_result );
    }

    public function test_getSupplierDomain( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getSupplierDomain( $this->supplier_id, TRUE );
        var_dump( $_result );
    }

    public function test_getDlyArea( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getDlyArea( $this->supplier_id );
        var_dump( $_result );
    }

    public function test_getDlyCorp( $supplierId )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getDlyCorp( $this->supplier_id );
        var_dump( $_result );
    }

    public function test_getDlyType( )
    {
        echo "---";
        var_dump( $this->supplier_id );
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getDlyType( $this->supplier_id );
        var_dump( $_result );
    }

    public function test_getDlyHarea( $supplierId )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getDlyHarea( $this->supplier_id );
        var_dump( $_result );
    }

    public function test_getCurList( $supplierId )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getCurList( $this->supplier_id );
        var_dump( $_result );
    }

    public function test_getPaymentCfg( $supplierId )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getPaymentCfg( $this->supplier_id );
        var_dump( $_result );
    }

    public function test_pendingPo( $supplierId )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->pendingPo( "186" );
        var_dump( $_result );
    }

    public function test_cancelPendingPo( $supplierId )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->cancelPendingPo( "186" );
        var_dump( $_result );
    }

    public function test_payByDeposits( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->payByDeposits( "186", 1, 10 );
        var_dump( $_result );
    }

    public function test_getSubRegionsb( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getSubRegions( 1835307036, 0 );
        var_dump( $_result );
    }

    public function test_getDlyTypeById( )
    {
        $oOrderPo = $this->system->loadModel( "purchase/order_po" );
        $_result = $oOrderPo->getDlyTypeById( $this->supplier_id, 1 );
        var_dump( $_result );
    }

}

?>
