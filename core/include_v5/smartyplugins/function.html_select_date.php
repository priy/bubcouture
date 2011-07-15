<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_html_select_date( $params, &$template_object )
{
    require_once( "shared.make_timestamp.php" );
    require_once( "function.html_options.php" );
    $prefix = "Date_";
    $start_year = strftime( "%Y" );
    $end_year = $start_year;
    $display_days = true;
    $display_months = true;
    $display_years = true;
    $month_format = "%B";
    $month_value_format = "%m";
    $day_format = "%02d";
    $day_value_format = "%d";
    $year_as_text = false;
    $reverse_years = false;
    $field_array = null;
    $day_size = null;
    $month_size = null;
    $year_size = null;
    $all_extra = null;
    $day_extra = null;
    $month_extra = null;
    $year_extra = null;
    $field_order = "MDY";
    $field_separator = "\n";
    $time = time( );
    extract( $params );
    if ( !preg_match( "/^\\d{4}-\\d{2}-\\d{2}\$/", $time ) )
    {
        $time = strftime( "%Y-%m-%d", tpl_make_timestamp( $time ) );
    }
    $time = explode( "-", $time );
    if ( preg_match( "!^(\\+|\\-)\\s*(\\d+)\$!", $end_year, $match ) )
    {
        if ( $match[1] == "+" )
        {
            $end_year = strftime( "%Y" ) + $match[2];
        }
        else
        {
            $end_year = strftime( "%Y" ) - $match[2];
        }
    }
    if ( preg_match( "!^(\\+|\\-)\\s*(\\d+)\$!", $start_year, $match ) )
    {
        if ( $match[1] == "+" )
        {
            $start_year = strftime( "%Y" ) + $match[2];
        }
        else
        {
            $start_year = strftime( "%Y" ) - $match[2];
        }
    }
    $field_order = strtoupper( $field_order );
    $html_result = $month_result = $day_result = $year_result = "";
    if ( $display_months )
    {
        $month_names = array( );
        $month_values = array( );
        $i = 1;
        for ( ; $i <= 12; ++$i )
        {
            $month_names[] = strftime( $month_format, mktime( 0, 0, 0, $i, 1, 2000 ) );
            $month_values[] = strftime( $month_value_format, mktime( 0, 0, 0, $i, 1, 2000 ) );
        }
        $month_result .= "<select name=";
        if ( null !== $field_array )
        {
            $month_result .= "\"".$field_array."[".$prefix."Month]\"";
        }
        else
        {
            $month_result .= "\"".$prefix."Month\"";
        }
        if ( null !== $month_size )
        {
            $month_result .= " size=\"".$month_size."\"";
        }
        if ( null !== $month_extra )
        {
            $month_result .= " ".$month_extra;
        }
        if ( null !== $all_extra )
        {
            $month_result .= " ".$all_extra;
        }
        $month_result .= ">\n";
        $month_result .= tpl_function_html_options( array(
            "output" => $month_names,
            "values" => $month_values,
            "selected" => $month_values[$time[1] - 1],
            "print_result" => false
        ), $template_object );
        $month_result .= "</select>";
    }
    if ( $display_days )
    {
        $days = array( );
        $i = 1;
        for ( ; $i <= 31; ++$i )
        {
            $days[] = sprintf( $day_format, $i );
            $day_values[] = sprintf( $day_value_format, $i );
        }
        $day_result .= "<select name=";
        if ( null !== $field_array )
        {
            $day_result .= "\"".$field_array."[".$prefix."Day]\"";
        }
        else
        {
            $day_result .= "\"".$prefix."Day\"";
        }
        if ( null !== $day_size )
        {
            $day_result .= " size=\"".$day_size."\"";
        }
        if ( null !== $all_extra )
        {
            $day_result .= " ".$all_extra;
        }
        if ( null !== $day_extra )
        {
            $day_result .= " ".$day_extra;
        }
        $day_result .= ">\n";
        $day_result .= tpl_function_html_options( array(
            "output" => $days,
            "values" => $day_values,
            "selected" => $time[2],
            "print_result" => false
        ), $template_object );
        $day_result .= "</select>";
    }
    if ( $display_years )
    {
        if ( null !== $field_array )
        {
            $year_name = $field_array."[".$prefix."Year]";
        }
        else
        {
            $year_name = $prefix."Year";
        }
        if ( $year_as_text )
        {
            $year_result .= "<input type=\"text\" name=\"".$year_name."\" value=\"".$time[0]."\" size=\"4\" maxlength=\"4\"";
            if ( null !== $all_extra )
            {
                $year_result .= " ".$all_extra;
            }
            if ( null !== $year_extra )
            {
                $year_result .= " ".$year_extra;
            }
            $year_result .= ">";
        }
        else
        {
            $years = range( ( integer )$start_year, ( integer )$end_year );
            if ( $reverse_years )
            {
                rsort( $years, SORT_NUMERIC );
            }
            $year_result .= "<select name=\"".$year_name."\"";
            if ( null !== $year_size )
            {
                $year_result .= " size=\"".$year_size."\"";
            }
            if ( null !== $all_extra )
            {
                $year_result .= " ".$all_extra;
            }
            if ( null !== $year_extra )
            {
                $year_result .= " ".$year_extra;
            }
            $year_result .= ">\n";
            $year_result .= tpl_function_html_options( array(
                "output" => $years,
                "values" => $years,
                "selected" => $time[0],
                "print_result" => false
            ), $template_object );
            $year_result .= "</select>";
        }
    }
    $i = 0;
    for ( ; $i <= 2; ++$i )
    {
        $c = substr( $field_order, $i, 1 );
        switch ( $c )
        {
        case "D" :
            $html_result .= $day_result;
            break;
        case "M" :
            $html_result .= $month_result;
            break;
        case "Y" :
            $html_result .= $year_result;
            break;
        }
        if ( $i != 2 )
        {
            $html_result .= $field_separator;
        }
    }
    return $html_result;
}

?>
