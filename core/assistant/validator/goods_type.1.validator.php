<?php
class goods_type_1Validator extends BaseValidator
{
    function goods_type_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (!isset($row['name']) || empty($row['name'])) return false;
        
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['is_physical'] = (isset($row['is_physical']) && $row['is_physical']) ? '1' : '0';
        $row['schema_id'] = isset($row['schema_id']) ? $row['schema_id'] : 'custom';        
        $row['dly_func'] = (isset($row['dly_func']) && $row['dly_func']) ? '1' : '0';
        $row['ret_func'] = (isset($row['ret_func']) && $row['ret_func']) ? '1' : '0';
        $row['reship'] = (isset($row['reship']) && !empty($row['reship'])) ?  $row['reship'] : 'normal';
        $row['is_def'] = (isset($row['is_def']) && $row['is_def']) ? 'true' : 'false';            
        
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        if (isset($row['is_def'])) $row['is_def'] = $row['is_def'] ? 'true' : 'false';                
        if (isset($row['reship']) && empty($row['reship'])) $row['reship'] = 'normal';
        if (isset($row['is_physical'])) $row['is_physical'] = $row['is_physical'] ? '1' : '0';
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