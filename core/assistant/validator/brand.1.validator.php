<?php
class brand_1Validator extends BaseValidator
{
    function brand_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        
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