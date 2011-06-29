<?php
if(!class_exists('ctl_product')){
    require(CORE_DIR.'/shop/controller/ctl.product.php');
}

class shopex_stat_lisproduct extends ctl_product{

    function shopex_stat_lisproduct(){
        parent::ctl_product();
        $this->system = &$GLOBALS['system'];
    }
      //商品信息
    function get_goodsinfo($gid,$specImg='',$spec_id=''){
        $this->pagedata['_MAIN_'] = 'product/index.html';
        parent::index($gid,$specImg,$spec_id);
        $objGoods = &$this->system->loadModel('trading/goods');
        if($aGoods = $objGoods->getGoods($gid)){
            //print_r($aGoods);exit;
            $objCat = &$this->system->loadModel('goods/productCat');
            $aCat = $objCat->getFieldById($aGoods['cat_id'], array('cat_name','addon'));
            $info_p = array('sale'=>$aGoods['price'],'mktprice'=>$aGoods['mktprice'],'cat_name'=>$aCat['cat_name'],'gname'=>$aGoods['name']);
            $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO_GOODS]", serialize($info_p),0,"/");
        }
    }
}
?>