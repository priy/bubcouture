<?php
class sitemaps_1Validator extends BaseValidator
{
    function sitemaps_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (!isset($row['title']) || empty($row['title'])) return false;
        
        $row['hidden'] = (isset($row['hidden']) && $row['hidden']) ? 'true' : 'false';
        $row['manual'] = (isset($row['manual']) && $row['manual']) ? 1 : 0;        
        $row['p_node_id'] = isset($row['p_node_id']) ? $row['p_node_id'] : 0; 
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['hidden'])) $row['hidden'] = $row['hidden'] ? 'true' : 'false';        
        if (isset($row['manual']))  $row['manual']  = $row['manual'] ? 1 : 0;        
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