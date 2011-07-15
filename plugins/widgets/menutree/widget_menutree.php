<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_menutree( $setting, &$system )
{
    $sitemap =& $system->loadModel( "content/sitemap" );
    $map = $sitemap->getMap( $setting['depth'] );
    $html = _menutree_make_html( $map, 0, $system->navPath );
    return $html;
}

function _menutree_make_html( $map, $level, &$path )
{
    foreach ( $map as $item )
    {
        $html .= ( "<div style=\"padding-left:".$level * 20 )."px\"".( $path[$item['link']] ? " class=\"current\"" : "" )."><a href=\"".$item['link']."\">".$item['title']."</a></div>";
        if ( is_array( $item['items'] ) && 0 < count( $item['items'] ) )
        {
            $html .= "<div class=\"".( $path[$item['link']] ? "open" : "close" )."\">"._menutree_make_html( $item['items'], $level + 1, $path )."</div>";
        }
    }
    return $html;
}

?>
