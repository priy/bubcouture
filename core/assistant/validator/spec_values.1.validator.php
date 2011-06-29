<?php
class spec_values_1Validator extends BaseValidator
{
    function spec_values_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['spec_id']) && is_numeric($row['spec_id']) && isset($row['spec_value']))
        {
            $this->_db->exec("delete from sdb_spec_values where spec_id='".(int)$row['spec_id']."' and spec_value=".$this->_db->quote($row['spec_value']));
            return true;
        }else if (isset($row['spec_value'])){
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