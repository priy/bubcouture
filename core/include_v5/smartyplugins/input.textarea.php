<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_textarea( $params, $ctl )
{
    $value = $params['value'];
    if ( $params['width'] )
    {
        $params['style'] .= ";width:".$params['width'];
        unset( $params['width'] );
    }
    if ( $params['height'] )
    {
        $params['style'] .= ";height:".$params['height'];
        unset( $params['height'] );
    }
    unset( $params['value'] );
    return buildtag( $params, "textarea", false ).htmlspecialchars( $value )."</textarea>";
}

?>
