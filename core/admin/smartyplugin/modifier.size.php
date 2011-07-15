<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_size( $size )
{
    $size = intval( $size );
    if ( 1048576 < $size )
    {
        return round( $size / 1048576, 2 )." M";
    }
    else if ( 1024 < $size )
    {
        return round( $size / 1024, 2 )." K";
    }
    else
    {
        return $size." Bytes";
    }
}

?>
