<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function shop_match_network( $nets, $ip, $first = false )
{
    $return = false;
    if ( !is_array( $nets ) )
    {
        $nets = array(
            $nets
        );
    }
    foreach ( $nets as $net )
    {
        if ( $rev = $net[0] == "!" )
        {
            $net = substr( $net, 1 );
        }
        $ip_arr = explode( "/", $net );
        $net_long = ip2long( $ip_arr[0] );
        $x = ip2long( $ip_arr[1] );
        $mask = long2ip( $x ) == $ip_arr[1] ? $x : -1 << 32 - $ip_arr[1];
        $ip_long = ip2long( $ip );
        if ( $rev )
        {

[exception occured]

================================
Exception code[ C0000005 ]
Compiler[ 003C6010 ]
Executor[ 003C6518 ]
OpArray[ 00C9B568 ]
File< C:\Documents and Settings\hebin\����\bubcouture\core\include\shop\core.match_network.php >
Class< main >
Function< shop_match_network >
Stack[ 00146CF8 ]
Step[ 7 ]
Offset[ 61 ]
LastOffset[ 79 ]
    61  IS_EQUAL                     [-]   0[0] $Tmp_1 - $Tmp_2 - $Tmp_3
================================
?>
