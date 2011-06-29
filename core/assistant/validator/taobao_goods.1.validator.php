<?php
class taobao_goods_1Validator extends BaseValidator
{
    function products_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }
    
    function validateInsertBefore(&$row)
    {
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['outer_content'] = unserialize($row['outer_content']);
		$object = json_decode($row['outer_content']['content']);
		$content = objectToArray($object);
		$row['outer_content']['content'] = $content['item_get_response']['item'];
        $row['outer_content'] = serialize($row['outer_content']);
        return true;
    }
    
    function validateInsertAfter(&$row)
    {    
        return true;    
    }
    
    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled'])) $row['disabled'] = $row['disabled'] ? 'true' : 'false';        
        $row['outer_content'] = unserialize($row['outer_content']);
		$object = json_decode($row['outer_content']['content']);
		$row['outer_content']['content'] = objectToArray($object);
        $row['outer_content'] = serialize($row['outer_content']);
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
	function object_to_array(&$object){
		
	}
}
function objectToArray( $object )
{
	if( !is_object( $object ) && !is_array( $object ) )
	{
		return $object;
	}
	if( is_object( $object ) )
	{
		$object = get_object_vars( $object );
	}
	return array_map( 'objectToArray', $object );
}
?>