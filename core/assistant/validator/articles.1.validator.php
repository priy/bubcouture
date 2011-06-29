<?php
class articles_1Validator extends BaseValidator
{
    function articles_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (!isset($row['title']) || empty($row['title'])) return false;
        
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['ifpub']  = isset($row['ifpub']) ? $row['ifpub'] : 1;
        $row['uptime'] = time();
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        if (isset($row['ifpub']))  $row['ifpub']  = $row['ifpub'] ? 1 : 0;
        $row['uptime'] = time();
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