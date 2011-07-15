<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_tinybool( $params, $ctl )
{
    $params['type'] = "radio";
    $value = $params['value'];
    unset( $params['value'] );
    $id = $params['id'] ? $params['id'] : $ctl->new_dom_id( );
    $params['id'] = $id."-t";
    $return = buildtag( $params, "input value=\"Y\"".( $value == "Y" ? " checked=\"checked\"" : "" ) )."<label for=\"".$params['id'].__( "\">是</label>" );
    $params['id'] = $id."-f";
    $return .= "&nbsp".buildtag( $params, "input value=\"N\"".( $value == "N" ? " checked=\"checked\"" : "" ) )."<label for=\"".$params['id'].__( "\">否</label>" );
    return $return;
}

?>
