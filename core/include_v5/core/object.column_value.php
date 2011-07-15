<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function object_column_value( $column, $value, &$object )
{
    if ( !isset( $object->_columns ) )
    {
        $object->_columns = $object->getColumns( );
    }
    switch ( $object->_columns[$column]['type'] )
    {
    case "number" :
        return intval( $value );
    case "date" :
    case "time" :
        return strtotime( $value );
    case "bool" :
        if ( $value === "1" || $value === "0" || $value === "true" || $value === "false" )
        {
            return $value;
        }
        else
        {
            if ( $object->_columns[$column]['bool'] == "number" )
            {
                return $value ? "1" : "0";
            }
            else
            {
                return $value ? "true" : "false";
            }
        }
    case "money" :
        if ( $value[0] == "+" || $value[0] == "*" || $value[0] == "/" )
        {
            return $column.$value[0].floatval( substr( $value, 1 ) );
        }
        if ( $value < 0 )
        {
            return $column."-".floatval( substr( $value, 1 ) );
        }
        else
        {
            return $value;
        }
    default :
        return $value;
    }
}

?>
