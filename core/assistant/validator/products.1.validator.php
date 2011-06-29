<?php
class products_1Validator extends BaseValidator
{
    function products_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['marketable'] = (isset($row['marketable']) && $row['marketable']) ? 'true' : 'false';
        $row['price'] = isset($row['price']) ? $row['price'] : 0;
        $row['name'] = isset($row['name']) ? $row['name'] : '';
        $row['last_modify'] = (isset($row['last_modify']) && !empty($row['last_modify'])) ? intval($row['last_modify']) : time();
        
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        if (isset($row['marketable'])) $row['marketable'] = $row['marketable'] ? 'true' : 'false';        
        if (isset($row['last_modify']) && empty($row['last_modify']))  $row['last_modify'] = time();
        
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