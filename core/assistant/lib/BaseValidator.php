<?php
class BaseValidator
{
    var $_sys, $_db, $_tbpre;
    
    function    BaseValidator($sys)
    {
        $this->_sys = $sys;
        $this->_db  = $sys->database();
        
        $this->_tbpre = isset($GLOBALS['_tbpre']) ? $GLOBALS['_tbpre'] : null;        
        if (!$this->_tbpre && defined('DB_PREFIX')) $this->_tbpre = DB_PREFIX;        
    }
    
    function validateInsertBefore(&$row)
    {
        return true;
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
    
    function loadValidators($dir, $table, $sys)
    {
        $validators = array();        
        foreach (as_find_files($dir, '/^'.$table.'\.([a-zA-Z0-9_]*)\.validator\.php$/') as $file => $matches)
        {
            include_once($dir.$file);
            $clsname = $table.'_'.$matches[1].'Validator';            
            if (class_exists($clsname))
            {                                
                $cls = new $clsname($sys);
                if (is_a($cls, 'BaseValidator'))
                {
                    $validators[] = $cls;
                }
            }    
        }
        return $validators;
    }
    
    function runValidateBefore($validators, $action, &$row)
    {                        
        foreach ($validators as $v)
        {            
            LogUtils::log_str('validate before '.$action.':'.get_class($v));    
            switch ($action)
            {
                case 'insert': 
                    if (!$v->ValidateInsertBefore($row)) return false;
                    break;
                case 'update': 
                    if (!$v->ValidateUpdateBefore($row)) return false; 
                    break;
                case 'delete': 
                    if (!$v->ValidateDeleteBefore($row)) return false; 
                    break;
            }
        }
        
        return true;
    }
    
    function runValidateAfter($validators, $action, &$row)
    {
        foreach ($validators as $v)
        {
            LogUtils::log_str('validate after '.$action.':'.get_class($v));    
            switch ($action)
            {
                case 'insert': 
                    if (!$v->ValidateInsertAfter($row)) return false;
                    break;
                case 'update': 
                    if (!$v->ValidateUpdateAfter($row)) return false; 
                    break;
                case 'delete': 
                    if (!$v->ValidateDeleteAfter($row)) return false; 
                    break;
            }            
        }
        
        return true;
    }
}

?>