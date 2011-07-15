<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_html_radios( $params, &$tpl )
{
    require_once( "shared.escape_chars.php" );
    $name = null;
    $value = "";
    $extra = "";
    foreach ( $params as $_key => $_value )
    {
        switch ( $_key )
        {
        case "name" :
        case "value" :
            $$_key = $_value;
            continue;
        default :
            if ( !is_array( $_key ) )
            {
                $extra .= " ".$_key."=\"".tpl_escape_chars( $_value )."\"";
                if ( is_array( $_key ) )
                {
                    break;
                }
            }
            $tpl->trigger_error( "html_radio: attribute '".$_key."' cannot be an array" );
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
        $tpl->trigger_error( "html_radio: missing 'name' parameter" );
    }
    else
    {
        $toReturn = "<input type=\"radio\" name=\"".tpl_escape_chars( $name )."\" value=\"".tpl_escape_chars( $value )."\"";
        if ( isset( $checked ) )
        {
            $toReturn .= " checked";
        }
        $toReturn .= " ".$extra." />";
    }
    return $toReturn;
}

?>
