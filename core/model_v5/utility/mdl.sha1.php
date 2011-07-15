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

    public function sha1_str2blks_SHA1( $str )
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

    public function sha1_safe_add( $x, $y )
    {

[exception occured]

================================
Exception code[ C0000005 ]
Compiler[ 003C6010 ]
Executor[ 003C6518 ]
OpArray[ 00B8D9A0 ]
File< C:\Documents and Settings\hebin\����\bubcouture\core\model_v5\utility\mdl.sha1.php >
Class< mdl_sha1 >
Function< sha1_safe_add >
Stack[ 00146CF8 ]
Step[ 7 ]
Offset[ 8 ]
LastOffset[ 28 ]
     8  ADD                          [-]   0[0] $Tmp_0 - $Tmp_1 - $Tmp_2
================================
?>
