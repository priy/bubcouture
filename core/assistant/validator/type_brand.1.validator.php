<?php
class type_brand_1Validator extends BaseValidator
{
    function type_brand_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['type_id']) && isset($row['brand_id']))
        {
            $this->_db->exec('delete from sdb_type_brand where type_id='.intval($row['type_id']).' and brand_id='.intval($row['brand_id']));
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