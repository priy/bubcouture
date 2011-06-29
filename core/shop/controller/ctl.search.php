<?php
class ctl_search extends shopPage{
    function index(){
        $aBrands = array();
        $objBrand = &$this->system->loadModel('goods/brand');
        $this->pagedata['brand'] = $objBrand->getAll();
        $objCat = &$this->system->loadModel('goods/productCat');
        $this->pagedata['categorys'] = $objCat->get_cat_list();
        $this->pagedata['args'] = array($cat_id,$filter,$orderBy,$tab,$page);
        $this->output();
    }

    function result(){
        $oSearch = &$this->system->loadModel('goods/search');
        $cat_id = $_POST['cat_id'];
        unset($_POST['cat_id']);
        foreach($_POST as $k=>$v){

            if($k=="name" && $_POST[$k][0]){
               
                if($_POST[$k][0] == '\\'||$_POST[$k][0] == '\\\\\\'){
                   $_POST[$k][0]=addslashes(str_replace('_','%xia%',$_POST[$k][0]));
                }else{
                   $_POST[$k][0]=str_replace('_','%xia%',$_POST[$k][0]);
                }
            }
          // print_r();exit();
            if($k=="bn" && $_POST[$k][0]){
                 $_POST[$k][0] = addslashes(trim(str_replace('_','%xia%',$_POST[$k][0])));
            }

            if($k=="price" && $_POST[$k][1]){
                $_POST[$k][0]=floatval($_POST[$k][0]);
                $_POST[$k][1]=floatval($_POST[$k][1]);

            }
        }
        if($filter = $oSearch->decode($_POST['filter'],$path)){
            $filter = array_merge($filter,$_POST);

        }else{
            $filter = $_POST;
        }
        unset($_POST['filter']);
          
        header('Location: '.$this->system->mkUrl('gallery',$this->system->getConf('gallery.default_view'),array($cat_id,$oSearch->encode($filter))));
    }

/*
    function doSearch(){
        $oSearch = &$this->system->loadModel('goods/search');
        $result=$oSearch->decode($_POST,$path);
        header('Location: '.$this->system->mkUrl('gallery','index',$_POST));
    }
*/
    function showCat(){
        $objCat = &$this->system->loadModel('goods/productCat');
        $this->pagedata['cat'] = $objCat->get($_POST['cat_id']);
        $this->__tmpl = 'search/showCat.html';
        $this->output();
    }
}

?>
