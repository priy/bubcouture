<?php
/**
 * api model�����
 * @package
 * @version 1.0: 
 * @copyright 2003-2009 ShopEx
 * @author dreamdream
 * @license Commercial
 */
class shop_api_model_object{
    
    /**
    * ���캯��
    */
    function shop_api_model_object(){
        if(!$this->system){
            $this->system = &$GLOBALS['system'];
        }
        if(!$this->db){
            $this->db = $this->system->database();
        }
    }

}
?>