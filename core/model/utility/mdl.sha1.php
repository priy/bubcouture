<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_sha1
{

    function sha1_str2blks_sha1( $str )
    {
        $strlen_str = strlen( $str );
        $nblk = ( $strlen_str + 8 >> 6 ) + 1;
        $i = 0;
        for ( ; $i < $nblk * 16; ++$i )
        {
            $blks[$i] = 0;
        }
        $i = 0;
        for ( ; $i < $strlen_str; ++$i )
        {
            $blks[$i >> 2] |= ord( substr( $str, $i, 1 ) ) << 24 - $i % 4 * 8;
        }
        $blks[$i >> 2] |= 128 << 24 - $i % 4 * 8;
        $blks[$nblk * 16 - 1] = $strlen_str * 8;
        return $blks;
    }

    function sha1_safe_add( $x, $y )
    {

[exception occured]

================================
Exception code[ C0000005 ]
Compiler[ 003C6010 ]
Executor[ 003C6518 ]
OpArray[ 00B84ED8 ]
File< C:\Documents and Settings\hebin\����\bubcouture\core\model\utility\mdl.sha1.php >
Class< mdl_sha1 >
Function< sha1_safe_add >
Stack[ 00146CE8 ]
Step[ 7 ]
Offset[ 4 ]
LastOffset[ 16 ]
     4  ADD                          [-]   0[0] $Tmp_0 - $Tmp_1 - $Tmp_2
================================
?>
