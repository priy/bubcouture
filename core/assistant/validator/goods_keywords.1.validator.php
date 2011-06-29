<?php
class goods_keywords_1Validator extends BaseValidator
{
    function goods_keywords_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['goods_id']) && is_numeric($row['goods_id']) && isset($row['keyword']))
        {
            if (empty($row['res_type'])) $row['res_type'] = 'goods';
            $this->_db->exec('delete from sdb_goods_keywords where goods_id='.(int)$row['goods_id'].' and keyword='.$this->_db->quote($row['keyword']));
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