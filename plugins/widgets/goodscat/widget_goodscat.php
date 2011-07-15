<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_goodscat( &$setting, &$system )
{
    $o = $system->loadModel( "goods/productCat" );
    $data = $o->getTreeList( );
    $setting['pageDevide'] = $setting['pageDevide'] ? $setting['pageDevide'] : 2;
    $setting['view'] = $system->getConf( "gallery.default_view" );
    $i = 0;
    for ( ; $i < count( $data ); ++$i )
    {
        $cat_path = $data[$i]['cat_path'];
        $cat_name = $data[$i]['cat_name'];
        $cat_id = $data[$i]['cat_id'];
        if ( empty( $cat_path ) || $cat_path == "," )
        {
            $myData[$cat_id]['label'] = $cat_name;
            $myData[$cat_id]['cat_id'] = $cat_id;
        }
    }
    $i = 0;
    for ( ; $i < count( $data ); ++$i )
    {
        $cat_path = $data[$i]['cat_path'];
        $cat_name = $data[$i]['cat_name'];
        $cat_id = $data[$i]['cat_id'];
        $parent_id = $data[$i]['pid'];
        if ( trim( $cat_path ) == "," )
        {
            $count = 0;
        }
        else
        {
            $count = count( explode( ",", $cat_path ) );
        }
        if ( $count == 2 )
        {
            $c_1 = intval( $parent_id );
            $c_2 = intval( $cat_id );
            $myData[$c_1]['sub'][$c_2]['label'] = $cat_name;
            $myData[$c_1]['sub'][$c_2]['cat_id'] = $cat_id;
        }
        if ( $count == 3 )
        {
            $tmp = explode( ",", $cat_path );
            $c_1 = intval( $tmp[0] );
            $c_2 = intval( $tmp[1] );
            $c_3 = intval( $cat_id );
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['label'] = $cat_name;
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['cat_id'] = $cat_id;
        }
    }
    return $myData;
}

?>
