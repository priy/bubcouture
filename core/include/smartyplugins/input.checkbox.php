<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_checkbox( $params, $tpl )
{
    if ( !defined( "CORE_INCLUDE_DIR" ) )
    {
        if ( defined( "SHOP_DEVELOPER" ) )
        {
        }
        define( "CORE_INCLUDE_DIR", CORE_DIR.( !constant( "SHOP_DEVELOPER" ) || version_compare( PHP_VERSION, "5.0", ">=" ) ? "/include_v5" : "/include" ) );
    }
    require_once( CORE_INCLUDE_DIR."/smartyplugins/shared.escape_chars.php" );
    $name = null;
    $value = null;
    $checked = null;
    $extra = "";
    foreach ( $params as $_key => $_value )
    {
        switch ( $_key )
        {
        case "name" :
        case "value" :
            $$_key = $_value;
            continue;
        case "checked" :
            if ( $_key == "true" || $_key == "yes" || $_key == "on" )
            {
                $$_key = true;
            }
            else
            {
                $$_key = false;
                continue;
            }
        default :
            if ( !is_array( $_key ) )
            {
                $extra .= " ".$_key."=\"".tpl_escape_chars( $_value )."\"";
                if ( is_array( $_key ) )
                {
                    break;
                }
            }
            $tpl->trigger_error( "html_checkbox: attribute '".$_key."' cannot be an array" );
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
    $toReturn = "<input type=\"checkbox\" name=\"".tpl_escape_chars( $name )."\"";
    if ( isset( $checked ) )
    {
        $toReturn .= " checked";
    }
    if ( isset( $value ) )
    {
        $toReturn .= " value=\"".tpl_escape_chars( $value )."\"";
    }
    $toReturn .= " ".$extra." />";
    return $toReturn;
}

?>
