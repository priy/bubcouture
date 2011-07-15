<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_counter( $params, &$tpl )
{
    static $count = array( );
    static $skipval = array( );
    static $dir = array( );
    static $name = "default";
    static $printval = array( );
    static $assign = "";
    extract( $params );
    if ( !isset( $name ) )
    {
        if ( isset( $id ) )
        {
            $name = $id;
        }
        else
        {
            $name = "default";
        }
    }
    if ( isset( $start ) )
    {
        $count[$name] = $start;
    }
    else if ( !isset( $count[$name] ) )
    {
        $count[$name] = 1;
    }
    if ( !isset( $print ) )
    {
        $printval[$name] = true;
    }
    else
    {
        $printval[$name] = $print;
    }
    if ( !empty( $assign ) )
    {
        $printval[$name] = false;
        $tpl->_vars[$assign] = $count[$name];
    }
    if ( $printval[$name] )
    {
        $retval = $count[$name];
    }
    else
    {
        $retval = null;
    }
    if ( isset( $skip ) )
    {
        $skipval[$name] = $skip;
    }
    else if ( empty( $skipval[$name] ) )
    {
        $skipval[$name] = 1;
    }
    if ( isset( $direction ) )
    {
        $dir[$name] = $direction;
    }
    else if ( !isset( $dir[$name] ) )
    {
        $dir[$name] = "up";
    }
    if ( $dir[$name] == "down" )
    {
        $count[$name] -= $skipval[$name];
        return $retval;
    }
    $count[$name] += $skipval[$name];
    return $retval;
}

?>
