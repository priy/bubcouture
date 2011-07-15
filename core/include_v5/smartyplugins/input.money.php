<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_money( $params, $ctl )
{
    if ( !isset( $params['vtype'] ) )
    {
        $params['vtype'] = "number";
    }
    $params['type'] = "text";
    $system =& $system;
    $oCur =& $system->loadModel( "system/cur" );
    $aCur = $oCur->getFormat( );
    if ( isset( $params['value'] ) && $params['value'] !== "" )
    {
        $params['value'] = number_format( $params['value'], $aCur['decimals'], ".", "" );
    }
    if ( isset( $params['emptytext'] ) )
    {
        if ( !$params['value'] )
        {
            $params['value'] = $params['emptytext'];
            $params['class'] = "emptytext";
        }
        $parmas['onfocus'] = "\$(this).emptyText()";
    }
    return $aCur['sign'].buildtag( $params, "input autocomplete=\"off\" class=\"x-input ".$params['class']."\"" );
}

?>
