<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_charset
{

    function local2utf( $strFrom, $charset = "zh" )
    {
        return $this->utfconvert( $strFrom, $charset, false );
    }

    function utf2local( $strFrom, $charset = "zh" )
    {
        return $this->utfconvert( $strFrom, $charset, true );
    }

    function utfconvert( $strFrom, $charset, $isfromUtf = false )
    {
        if ( !trim( $strFrom ) )
        {
            return $strFrom;
        }
        $fileGBU = fopen( CORE_DIR."/lib/charset/".( $isfromUtf ? "utf2".$charset : $charset."2utf" ).".dat", "rb" );
        $strBuf = fread( $fileGBU, 2 );
        $intCount = ord( $strBuf[0] ) + 256 * ord( $strBuf[1] );
        $strRet = "";
        $intLen = strlen( $strFrom );
        $i = 0;
        for ( ; $i < $intLen; ++$i )
        {
            if ( 127 < ord( $strFrom[$i] ) )
            {
                $strCurr = substr( $strFrom, $i, $isfromUtf ? 3 : 2 );
                if ( $isfromUtf )
                {
                    $intGB = $this->utf82u( $strCurr );
                }
                else
                {
                    $intGB = hexdec( bin2hex( $strCurr ) );
                }
                $intStart = 1;
                $intEnd = $intCount;
                while ( $intStart < $intEnd - 1 )
                {
                    $intMid = floor( ( $intStart + $intEnd ) / 2 );
                    $intOffset = 2 + 4 * ( $intMid - 1 );
                    fseek( $fileGBU, $intOffset );
                    $strBuf = fread( $fileGBU, 2 );
                    $intCode = ord( $strBuf[0] ) + 256 * ord( $strBuf[1] );
                    if ( $intGB == $intCode )
                    {
                        $intStart = $intMid;
                    }
                    else if ( $intCode < $intGB )
                    {
                        $intStart = $intMid;
                    }
                    else
                    {
                        $intEnd = $intMid;
                    }
                }
                $intOffset = 2 + 4 * ( $intStart - 1 );
                fseek( $fileGBU, $intOffset );
                $strBuf = fread( $fileGBU, 2 );
                $intCode = ord( $strBuf[0] ) + 256 * ord( $strBuf[1] );
                if ( $intGB == $intCode )
                {
                    $strBuf = fread( $fileGBU, 2 );
                    if ( $isfromUtf )
                    {
                        $strRet .= $strBuf[1].$strBuf[0];
                    }
                    else
                    {
                        $intCodeU = ord( $strBuf[0] ) + 256 * ord( $strBuf[1] );
                        $strRet .= $this->u2utf8( $intCodeU );
                    }
                }
                else
                {
                    $strRet .= "??";
                }
                $i += $isfromUtf ? 2 : 1;
            }
            else
            {
                $strRet .= $strFrom[$i];
            }
        }
        return $strRet;
    }

    function u2utf8( $c )
    {
        $str = "";
        if ( $c < 128 )
        {
            $str .= $c;
            return $str;
        }
        if ( $c < 2048 )
        {
            $str .= chr( 192 | $c >> 6 );
            $str .= chr( 128 | $c & 63 );
            return $str;
        }
        if ( $c < 65536 )
        {
            $str .= chr( 224 | $c >> 12 );
            $str .= chr( 128 | $c >> 6 & 63 );
            $str .= chr( 128 | $c & 63 );
            return $str;
        }
        if ( $c < 2097152 )
        {
            $str .= chr( 240 | $c >> 18 );
            $str .= chr( 128 | $c >> 12 & 63 );
            $str .= chr( 128 | $c >> 6 & 63 );
            $str .= chr( 128 | $c & 63 );
        }
        return $str;
    }

    function utf82u( $Char )
    {
        switch ( strlen( $Char ) )
        {
        case 1 :
            return ord( $Char );
        case 2 :
            $OutStr = ( ord( $Char[0] ) & 63 ) << 6;
            $OutStr += ord( $Char[1] ) & 63;
            return $OutStr;
        case 3 :
            $OutStr = ( ord( $Char[0] ) & 31 ) << 12;
            $OutStr += ( ord( $Char[1] ) & 63 ) << 6;
            $OutStr += ord( $Char[2] ) & 63;
            return $OutStr;
        case 4 :
            $OutStr = ( ord( $Char[0] ) & 15 ) << 18;
            $OutStr += ( ord( $Char[1] ) & 63 ) << 12;
            $OutStr += ( ord( $Char[2] ) & 63 ) << 6;
            $OutStr += ord( $Char[3] ) & 63;
            return $OutStr;
        }
    }

}

?>
