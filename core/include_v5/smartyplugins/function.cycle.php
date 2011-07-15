<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_cycle( $params, &$tpl )
{
    static $cycle_vars = NULL;
    $name = empty( $params['name'] ) ? "default" : $params['name'];
    $print = isset( $params['print'] ) ? $params['print'] : true;
    $advance = isset( $params['advance'] ) ? $params['advance'] : true;
    $reset = isset( $params['reset'] ) ? $params['reset'] : false;
    if ( !in_array( "values", array_keys( $params ) ) )
    {
        if ( !isset( $cycle_vars[$name]['values'] ) )
        {
            $tpl->trigger_error( "cycle: missing 'values' parameter" );
            return;
        }
    }
    else
    {
        if ( isset( $cycle_vars[$name]['values'] ) && $cycle_vars[$name]['values'] != $params['values'] )
        {
            $cycle_vars[$name]['index'] = 0;
        }
        $cycle_vars[$name]['values'] = $params['values'];
    }
    $cycle_vars[$name]['delimiter'] = isset( $params['delimiter'] ) ? $params['delimiter'] : ",";
    if ( is_array( $cycle_vars[$name]['values'] ) )
    {
        $cycle_array = $cycle_vars[$name]['values'];
    }
    else
    {
        $cycle_array = explode( $cycle_vars[$name]['delimiter'], $cycle_vars[$name]['values'] );
    }
    if ( !isset( $cycle_vars[$name]['index'] ) || $reset )
    {
        $cycle_vars[$name]['index'] = 0;
    }
    if ( isset( $params['assign'] ) )
    {
        $print = false;
        $tpl->pagedata[$params['assign']] = $cycle_array[$cycle_vars[$name]['index']];
    }
    if ( $print )
    {
        $retval = $cycle_array[$cycle_vars[$name]['index']];
    }
    else
    {
        $retval = null;
    }
    if ( $advance )
    {
        if ( count( $cycle_array ) - 1 <= $cycle_vars[$name]['index'] )
        {
            $cycle_vars[$name]['index'] = 0;
        }
        else
        {
            ++$cycle_vars[$name]['index'];
        }
    }
    return $retval;
}

?>
