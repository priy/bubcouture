<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function objectToArray( $object )
{
    if ( !is_object( $object ) && !is_array( $object ) )
    {
        return $object;
    }
    if ( is_object( $object ) )
    {
        $object = get_object_vars( $object );
    }
    return array_map( "objectToArray", $object );
}

class taobao_goods_1Validator extends BaseValidator
{

    public function products_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        $row['disabled'] = isset( $row['disabled'] ) && $row['disabled'] ? "true" : "false";
        $row['outer_content'] = unserialize( $row['outer_content'] );
        $object = json_decode( $row['outer_content']['content'] );
        $content = objecttoarray( $object );
        $row['outer_content']['content'] = $content['item_get_response']['item'];
        $row['outer_content'] = serialize( $row['outer_content'] );
        return TRUE;
    }

    public function validateInsertAfter( &$row )
    {
        return TRUE;
    }

    public function validateUpdateBefore( &$row )
    {
        if ( isset( $row['disabled'] ) )
        {
            $row['disabled'] = $row['disabled'] ? "true" : "false";
        }
        $row['outer_content'] = unserialize( $row['outer_content'] );
        $object = json_decode( $row['outer_content']['content'] );
        $row['outer_content']['content'] = objecttoarray( $object );
        $row['outer_content'] = serialize( $row['outer_content'] );
        return TRUE;
    }

    public function validateUpdateAfter( &$row )
    {
        return TRUE;
    }

    public function validateDeleteBefore( &$row )
    {
        return TRUE;
    }

    public function validateDeleteAfter( &$row )
    {
        return TRUE;
    }

    public function object_to_array( &$object )
    {
    }

}

?>
