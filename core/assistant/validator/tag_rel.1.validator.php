<?php
class tag_rel_1Validator extends BaseValidator
{
    function tag_rel_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (isset($row['tag_id'])      && is_numeric($row['tag_id'])
            && isset($row['rel_id']) && is_numeric($row['rel_id']))
        {
            $this->_db->exec("delete from sdb_tag_rel where tag_id='".(int)$row['tag_id']."' and rel_id='".(int)$row['tag_id']."'");
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