<?php
class goods_type_spec_1Validator extends BaseValidator
{
    function goods_type_spec_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        $row['spec_style'] = isset($row['spec_style']) ? $row['spec_type'] : 'flat';        
                
        if (isset($row['spec_id']) && isset($row['type_id']))
        {
            $this->_db->exec('delete from sdb_goods_type_spec where spec_id='.intval($row['spec_id']).' and type_id='.intval($row['type_id']));
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