<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_time( $params, $ctl )
{
    $params['type'] = "time";
    $return = tpl_input_date( $params, $ctl );
    if ( $params['value'] )
    {
        $hour = mydate( "H", $params['value'] );
        $minute = mydate( "i", $params['value'] );
    }
    $select = "&nbsp;&nbsp; <select name=\"_DTIME_[H][".htmlspecialchars( $params['name'] )."]\">";
    $i = 0;
    for ( ; $i < 24; ++$i )
    {
        $tmpNum = str_pad( $i, 2, "0", STR_PAD_LEFT );
        $select .= ( $hour == $i ? "<option value=\"".$tmpNum."\" selected=\"selected\">" : "<option value=\"".$tmpNum."\">" ).$tmpNum."</option>";
    }
    $select .= "</select> : <select name=\"_DTIME_[M][".htmlspecialchars( $params['name'] )."]\">";
    $i = 0;
    for ( ; $i < 60; ++$i )
    {
        $tmpNum = str_pad( $i, 2, "0", STR_PAD_LEFT );
        $select .= ( $minute == $i ? "<option value=\"".$tmpNum."\" selected=\"selected\">" : "<option value=\"".$tmpNum."\">" ).$tmpNum."</option>";
    }
    $select .= "</select>";
    return $return.$select;
}

if ( !function_exists( "tpl_input_date" ) )
{
    require( dirname( __FILE__ )."/input.date.php" );
}
?>
