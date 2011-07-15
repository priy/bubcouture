<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_coverflow( &$setting, &$system )
{
    $gimage =& $system->loadModel( "goods/gimage" );
    $o =& $system->loadModel( "goods/products" );
    $xml =& $system->loadModel( "utility/xml" );
    parse_str( $setting['filter'], $filter );
    $filter = array_merge( array( "marketable" => "true" ), $filter );
    $list = $o->getList( NULL, $filter, 0, $setting['limit'] ? $setting['limit'] : 10, $count );
    $aTmp = $list;
    foreach ( $aTmp as $k => $v )
    {
        $list[$k]['picture'] = $gimage->get_resource_by_id( $v['image_default'], "small" );
        unset( $Var_768['image_default'] );
        unset( $Var_816['thumbnail_pic'] );
        unset( $Var_864['pdt_desc'] );
        unset( $Var_912['brief'] );
    }
    return array(
        "xml" => $xml->array2xml( array(
            "products" => $list,
            "marketable" => "true"
        ) ),
        "count" => $count,
        "haystack" => time( )
    );
}

?>
