<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_storager( $ident, $type )
{
    $p = strpos( $ident, "|" );
    if ( $p !== false )
    {
        $ident = substr( $ident, 0, $p );
    }
    $system =& $system;
    if ( !$_gimage )
    {
        $gimage = $system->loadModel( "goods/gimage" );
        $_gimage =& $gimage;
    }
    else
    {
        $gimage =& $_gimage;
    }
    $imgurl = $gimage->getUrl( $ident, $type );
    return $imgurl;
}

?>
