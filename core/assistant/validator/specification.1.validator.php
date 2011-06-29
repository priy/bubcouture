<?php
class specification_1Validator extends BaseValidator
{
    function specification_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['spec_show_type'] = isset($row['spec_show_type']) ? $row['spec_show_type'] : 'flat';
        $row['spec_type'] = isset($row['spec_type']) ? $row['spec_type'] : 'text';        
        $row['p_order'] = isset($row['p_order']) ? intval($row['p_order']) : 0;        
                
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled']))   $row['disabled'] = $row['disabled'] ? 'true' : 'false';
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