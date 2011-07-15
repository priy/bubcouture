<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_toinput( $params, &$smarty )
{
    $html = NULL;
    _tpl_function_toinput( $params['from'], $ret, $params['name'] );
    foreach ( $ret as $k => $v )
    {
        $html .= "<input type=\"hidden\" name=\"".$k."\" value=\"".$v."\" />\n";
    }
    return $html;
}

function _tpl_function_toinput( $data, &$ret, $path = NULL )
{
    foreach ( $data as $k => $v )
    {
        $d = $path ? $path."[".$k."]" : $k;
        if ( is_array( $v ) )
        {
            _tpl_function_toinput( $v, $ret, $d );
        }
        else
        {
            $ret[$d] = $v;
        }
    }
}

?>
