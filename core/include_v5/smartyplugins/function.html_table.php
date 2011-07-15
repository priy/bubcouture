<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_html_table( $params, &$template_object )
{
    $table_attr = "border=\"1\"";
    $tr_attr = "";
    $td_attr = "";
    $cols = 3;
    $trailpad = "&nbsp;";
    extract( $params );
    if ( !isset( $loop ) )
    {
        $template_object->trigger_error( "html_table: missing 'loop' parameter" );
        return;
    }
    $output = "<table {$table_attr}>\n";
    $output .= "<tr ".tpl_function_html_table_cycle( "tr", $tr_attr ).">\n";
    $x = 0;
    $y = count( $loop );
    for ( ; $x < $y; ++$x )
    {
        $output .= "<td ".tpl_function_html_table_cycle( "td", $td_attr ).">".$loop[$x]."</td>\n";
        if ( !( ( $x + 1 ) % $cols ) && $x < $y - 1 )
        {
            $output .= "</tr>\n<tr ".tpl_function_html_table_cycle( "tr", $tr_attr ).">\n";
        }
        if ( $x == $y - 1 )
        {
            $cells = $cols - $y % $cols;
            if ( $cells != $cols )
            {
                $padloop = 0;
                for ( ; $padloop < $cells; ++$padloop )
                {
                    $output .= "<td ".tpl_function_html_table_cycle( "td", $td_attr ).">{$trailpad}</td>\n";
                }
            }
            $output .= "</tr>\n";
        }
    }
    $output .= "</table>\n";
    return $output;
}

function tpl_function_html_table_cycle( $name, $var )
{
    static $names = array( );
    if ( !is_array( $var ) )
    {
        return $var;
    }
    if ( !isset( $names[$name] ) || $names[$name] == count( $var ) - 1 )
    {
        $names[$name] = 0;
        return $var[0];
    }
    ++$names[$name];
    return $var[$names[$name]];
}

?>
