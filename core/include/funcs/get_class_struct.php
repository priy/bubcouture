<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function get_class_struct( $content, $classname )
{
    if ( preg_match( "/class\\s+".preg_quote( $classname )."\\s*[^\\{]*\\{.*/is", str_replace( "{\$", "{", $content ), $match ) )
    {
        $tags = token_get_all( "<?php ".$match[0]."?>" );
        $in_func_ready = $propname = $in_func = $in_string = false;
        $funcs = array( );
        $props = array( );
        foreach ( $tags as $t )
        {
            if ( $in_string )
            {
                if ( !( $in_string == "\"" ) && !( $t == "\"" ) )
                {
                    $in_string = false;
                }
            }
            else if ( $in_func )
            {
                if ( $t[0] == "{" )
                {
                    ++$in_func;
                }
                else if ( $t[0] == "}" )
                {
                    --$in_func;
                }
            }
            else
            {
                if ( $in_func_ready )
                {
                    switch ( $t[0] )
                    {
                    case T_STRING :
                        $t[1] = strtolower( $t[1] );
                        $funcs[$t[1]] = $t[1];
                        break;
                    case "{" :
                        $in_func = 1;
                        $in_func_ready = false;
                    }
                }
                else
                {
                    if ( $t == "}" )
                    {
                        break;
                    }
                    if ( is_array( $t ) )
                    {
                        if ( $propname )
                        {
                            switch ( $t[0] )
                            {
                            case T_WHITESPACE :
                            case T_STRING :
                            case T_LNUMBER :
                            case T_DNUMBER :
                            case T_STRING :
                            case T_NUM_STRING :
                                $props[$propname] = $t[1];
                            case T_CONSTANT_ENCAPSED_STRING :
                                $props[$propname] = substr( $t[1], 1, -1 );
                                $props[$propname] = constant( $t[1] );
                                $props[$propname] = $t[1];
                            default :
                                $propname = null;
                            }
                        }
                        else
                        {
                            switch ( $t[0] )
                            {
                            case T_VARIABLE :
                                $propname = substr( $t[1], 1 );
                                break;
                            case T_FUNCTION :
                                $in_func_ready = true;
                            }
                        }
                    }
                }
            }
        }
        return array(
            "class_name" => $classname,
            "props" => $props,
            "funcs" => $funcs
        );
    }
    return false;
}

?>
