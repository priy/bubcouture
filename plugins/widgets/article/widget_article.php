<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_article( &$setting, &$system )
{
    $o = $system->loadModel( "content/article" );
    $setting['colums'] = $setting['colums'] ? $setting['colums'] : 2;
    $setting['onSelect'] = $setting['onSelect'] ? $setting['onSelect'] : 0;
    $setting['max_length'] = $setting['max_length'] ? $setting['max_length'] : 35;
    if ( $setting['smallPic'] != 6 )
    {
        if ( $setting['smallPic'] )
        {
            $setting['smallPic'] = $system->base_url( )."statics/icons/".$setting['smallPic'];
        }
    }
    if ( 1 < $setting['columNum'] )
    {
        $return = array( );
        $i = 1;
        for ( ; $i <= $setting['columNum']; ++$i )
        {
            $return[] = $o->getList( "title,article_id", array(
                "node_id" => intval( $setting["id".$i] ),
                "ifpub" => 1
            ), 0, $setting['limit'], $count );
            $setting['id'][$i - 1] = $setting["id".$i];
        }
        return $return;
    }
    else
    {
        return $o->getList( "title,article_id", array(
            "node_id" => intval( $setting['id1'] ),
            "ifpub" => 1
        ), 0, $setting['limit'], $count );
    }
}

?>
