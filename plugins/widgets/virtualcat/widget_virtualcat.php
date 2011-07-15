<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_virtualcat( &$setting, &$system )
{
    $objCat = $system->loadModel( "goods/virtualcat" );
    $data = $objCat->getMapTree( 0, "", $setting['virtualcat_id'] );
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
            $myData[$cat_id]['url'] = $data[$i]['url'];
        }
    }
    $i = 0;
    for ( ; $i < count( $data ); ++$i )
    {
        $cat_path = $data[$i]['cat_path'];
        $cat_name = $data[$i]['cat_name'];
        $cat_id = $data[$i]['cat_id'];
        $url = $data[$i]['url'];
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
            $myData[$c_1]['sub'][$c_2]['url'] = $url;
        }
        if ( $count == 3 )
        {
            $tmp = explode( ",", $cat_path );
            $c_1 = intval( $tmp[0] );
            $c_2 = intval( $tmp[1] );
            $c_3 = intval( $cat_id );
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['label'] = $cat_name;
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['cat_id'] = $cat_id;
            $myData[$c_1]['sub'][$c_2]['sub'][$c_3]['url'] = $url;
        }
    }
    return $myData;
}

?>
