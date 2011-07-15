<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_html_input( $params, &$tpl )
{
    require_once( "shared.escape_chars.php" );
    $name = null;
    $value = "";
    $password = false;
    $extra = "";
    foreach ( $params as $_key => $_value )
    {
        switch ( $_key )
        {
        case "name" :
        case "value" :
            $$_key = $_value;
            continue;
        case "password" :
            $$_key = true;
            continue;
        default :
            if ( !is_array( $_key ) )
            {
                $extra .= " ".$_key."=\"".$_value."\"";
                if ( is_array( $_key ) )
                {
                    break;
                }
            }
            $tpl->trigger_error( "html_input: attribute '".$_key."' cannot be an array" );
            if ( $params )
            {
                continue;
            }
            else
            {
                break;
            }
        }
    }
    if ( isset( $name, $name ) )
    {
        $tpl->trigger_error( "html_input: missing 'name' parameter" );
    }
    else
    {
        $toReturn = "<input type=\"";
        $toReturn .= $password ? "password" : "text";
        $toReturn .= "\" name=\"".tpl_escape_chars( $name )."\" value=\"".tpl_escape_chars( $value )."\" ".$extra." />";
    }
    return $toReturn;
}

?>
