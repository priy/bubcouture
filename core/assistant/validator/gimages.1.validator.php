<?php
class gimages_1Validator extends BaseValidator
{
    function gimages_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
            
    function validateInsertBefore(&$row)
    {        
        $row['orderby'] = (isset($row['orderby']) && $row['orderby']) ? $row['orderby'] : 0;
        $row['src_size_width'] = (isset($row['src_size_width']) && $row['src_size_width']) ? $row['src_size_width'] : 0;
        $row['src_size_height'] = (isset($row['src_size_height']) && $row['src_size_height']) ? $row['src_size_height'] : 0;
        $row['up_time'] = (isset($row['up_time']) && $row['up_time']) ? $row['up_time'] : time();
        $row['is_remote'] = (isset($row['is_remote']) && $row['is_remote']) ? 'true' : 'false';
        unset($row['gimage_id']);
        
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {        
        $row['orderby'] = (isset($row['orderby']) && $row['orderby']) ? $row['orderby'] : 0;
        $row['src_size_width'] = (isset($row['src_size_width']) && $row['src_size_width']) ? $row['src_size_width'] : 0;
        $row['src_size_height'] = (isset($row['src_size_height']) && $row['src_size_height']) ? $row['src_size_height'] : 0;
        $row['up_time'] = (isset($row['up_time']) && $row['up_time']) ? $row['up_time'] : time();
        $row['is_remote'] = (isset($row['is_remote']) && $row['is_remote']) ? 'true' : 'false';
        
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