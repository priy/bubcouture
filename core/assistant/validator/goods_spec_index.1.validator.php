<?php
class goods_spec_index_1Validator extends BaseValidator
{
    function goods_spec_index_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['product_id']) && isset($row['spec_value_id']))
        {
            $this->_db->exec('delete from sdb_goods_spec_index where product_id='.intval($row['product_id']).' and spec_value_id='.intval($row['spec_value_id']));
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