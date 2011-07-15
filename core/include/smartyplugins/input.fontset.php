<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_fontset( $params, $ctl )
{
    $params['type'] = "fontset";
    $options = array( "0" => "", "1" => "粗体", "2" => "斜体", "3" => "中线" );
    $html = buildtag( $params, "select class=\"x-input-select inputstyle\"", false );
    foreach ( $options as $k => $item )
    {
        $html .= "<option".( $params['value'] === $k ? " selected=\"selected\"" : "" )." value=\"".htmlspecialchars( $k )."\">".htmlspecialchars( $item )."</option>";
    }
    $html .= "</select>";
    return $html;
}

?>
