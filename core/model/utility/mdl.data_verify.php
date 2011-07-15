<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_data_verify
{

    function inenum( $str_text, $str_enum )
    {
        $str_enum = str_replace( "enum", "array", $str_enum );
        eval( "\$array_enum = ".$str_enum.";" );
        if ( in_array( $str_text, $array_enum ) )
        {
            return true;
        }
        return false;
    }

    function checkparams( $str_input, &$array_data )
    {
        $str_input = str_replace( "\r", "", $str_input );
        $array_input = explode( "\n", $str_input );
        foreach ( $array_input as $str_key => $str_params )
        {
            $array_params = explode( "    ", $str_params );
            $array_keys[$array_params[0]] = $array_params[0];
            if ( strtoupper( trim( $array_params[2] ) ) == "Y" && !isset( $array_data[$array_params[0]] ) )
            {
                return $array_params[0];
            }
            if ( !isset( $array_data[$array_params[0]] ) )
            {
                continue;
            }
            $str_preg = "/^(\\w+)\\(?(.*?)\\)?\$/";
            if ( $int_match = preg_match( $str_preg, trim( $array_params[1] ), $array_match ) )
            {
                $str_data = $array_data[$array_params[0]];
                switch ( $array_match[1] )
                {
                case "string" :
                case "int" :
                case "integer" :
                    if ( $this->isint( $str_data ) )
                    {
                        break;
                    }
                    return $array_params[0];
                case "varchar" :
                    if ( !( $array_match[2] < strlenchinaese( $str_data ) ) )
                    {
                        break;
                    }
                    return $array_params[0];
                case "enum" :
                    if ( $this->inenum( $str_data, $array_match[0] ) )
                    {
                        break;
                    }
                    return $array_params[0];
                case "decimal" :
                    if ( $this->isfloat( $str_data, $array_match[2] ) )
                    {
                        break;
                    }
                    return $array_params[0];
                case "array" :
                    if ( is_array( $str_data ) )
                    {
                        break;
                    }
                    return $array_params[0];
                }
            }
            else
            {
                return false;
            }
        }
        foreach ( $array_data as $str_key => $str_value )
        {
            if ( array_search( $str_key, $array_keys ) === false )
            {
                unset( $array_data->$str_key );
            }
        }
        return true;
    }

    function isfloat( $str_text, $str_len )
    {
        $int_int = substr( $str_len, 0, strpos( $str_len, "," ) );
        $int_float = substr( $str_len, strpos( $str_len, "," ) + 1 );
        $str_preg = "/^(\\d{1,".$int_int."})\\.?(\\d{1,".$int_float."})?\$/";
        if ( $int_matched = preg_match( $str_preg, $str_text, $array_match ) )
        {
            return true;
        }
        return false;
    }

    function isint( $str_text )
    {
        $str_preg = "/^(\\d+)\$/";
        if ( $int_matched = preg_match( $str_preg, $str_text, $array_match ) )
        {
            return true;
        }
        return false;
    }

}

?>
