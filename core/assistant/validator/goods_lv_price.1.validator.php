<?php
class goods_lv_price_1Validator extends BaseValidator
{
    function goods_lv_price_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['product_id'])  && is_numeric($row['product_id']) 
            && isset($row['level_id']) && is_numeric($row['level_id'])
            && isset($row['goods_id']) && is_numeric($row['goods_id']))
        {
            $this->_db->exec("delete from sdb_goods_lv_price where product_id='".(int)$row['product_id']."' and level_id='".(int)$row['level_id']."' and goods_id='".(int)$row['goods_id']."'");
            return true;
        }
        
        return false;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        return true;
    }
    
    function validateUpdateAfter(&$row)
    {        
        return true;
    }
    
    function validateDeleteBefore(&$row)
    {
        return true;
    }
    
    function validateDeleteAfter(&$row)
    {        
        return true;
    }
} 

?>