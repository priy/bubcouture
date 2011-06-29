<?php
class goods_cat_1Validator extends BaseValidator
{
    function goods_cat_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        if (!isset($row['cat_name']) || empty($row['cat_name'])) return false;
        if (empty($row['type_id'])) $row['type_id'] = 1;
        
        $row['parent_id'] = isset($row['parent_id']) ? $row['parent_id'] : 0;
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['is_leaf']  = (isset($row['is_leaf']) && $row['is_leaf']) ? 'true' : 'false';
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        $this->updateCache();
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        if (isset($row['is_leaf']))  $row['is_leaf']  = $row['is_leaf'] ? 'true' : 'false';
        
        if (isset($row['cat_id']) && !empty($row['cat_id'])){
            $sql = "select count(*) childs from sdb_goods_cat where parent_id=".$this->_db->quote($row['cat_id']);
            $crow = $this->_db->selectrow($sql);
            if ($crow){
                $row['child_count'] = $crow['childs'];
            }                        
        }
        
        return true;
    }
    
    function validateUpdateAfter(&$row)
    {        
        if (isset($row['parent_id']) && !empty($row['parent_id'])){
            $sql = "select count(*) childs from sdb_goods_cat where parent_id=".$this->_db->quote($row['parent_id']);            
            $crow = $this->_db->selectrow($sql);
            if ($crow) {
                $sql = "update sdb_goods_cat set child_count=".intval($crow['childs'])." where cat_id=".$this->_db->quote($row['parent_id']);
                $this->_db->exec($sql);                
            }
        }
            
        $this->updateCache();
        return true;
    }
    
    function validateDeleteBefore(&$row)
    {
        return true;
    }
    
    function validateDeleteAfter(&$row)
    {                
        $this->updateCache();
        return true;
    }
    
    function updateCache(){
        $cache_file = MEDIA_DIR.'/goods_cat.data';
        if (file_exists($cache_file)) {
            @unlink($cache_file);
        }
    }
} 

?>