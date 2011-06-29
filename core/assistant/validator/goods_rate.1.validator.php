<?php
class goods_rate_1Validator extends BaseValidator
{
    function goods_rate_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['goods_1']) && isset($row['goods_2']))
        {
            $this->_db->exec("delete from sdb_goods_rate where goods_1='".(int)$row['goods_1']."' and goods_2='".(int)$row['goods_1']."'");
        }
        
        return true;
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