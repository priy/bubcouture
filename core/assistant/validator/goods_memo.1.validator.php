<?php
class goods_memo_1Validator extends BaseValidator
{
    function goods_memo_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['goods_id']) && is_numeric($row['goods_id']) && isset($row['p_key']))
        {
            $this->_db->exec('delete from sdb_goods_memo where goods_id='.(int)$row['goods_id'].' and p_key='.$this->_db->quote($row['p_key']));
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