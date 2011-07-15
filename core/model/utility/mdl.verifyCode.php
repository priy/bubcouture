<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_verifycode
{

    var $Noisy = 2;
    var $Count = 4;
    var $Width = 60;
    var $Height = 16;
    var $Angle = 2;
    var $Offset = 10;
    var $Border = 1;
    var $imgData = "";
    var $Graph = array( );
    var $code = "";

    function mdl_verifycode( )
    {
    }

    function initverfycode( )
    {
        $system =& $system;
        mt_srand( ( double )microtime( ) * 1000000 );
        $code = mt_rand( 1000, 9999 );
        if ( !empty( $this->type ) )
        {
            $cookieName = strtoupper( $this->type )."_RANDOM_CODE";
        }
        else
        {
            $cookieName = "RANDOM_CODE";
        }
        $system->setcookie( $cookieName, md5( $code ) );
        $this->code = $code;
        $i = 0;
        for ( ; $i < strlen( $code ); ++$i )
        {
            $this->setdraw( substr( $code, $i, 1 ), $i );
        }
    }

    function init( )
    {
        mt_srand( ( double )microtime( ) * 1000000 );
        $code = mt_rand( 1000, 9999 );
        $this->code = $code;
        $i = 0;
        for ( ; $i < strlen( $code ); ++$i )
        {
            $this->setdraw( substr( $code, $i, 1 ), $i );
        }
        return $this->code;
    }

    function setdot( $pX, $pY )
    {
        if ( 0 <= $pX * ( $this->Width - $pX - 1 ) && 0 <= $pY * ( $this->Height - $pY - 1 ) )
        {
            $this->Graph[$pX][$pY] = 1;
        }
    }

    function rnd( )
    {
        mt_srand( ( double )microtime( ) * 1000000 );
        return mt_rand( 1, 999 ) / 1000;
    }

    function sgn( $v )
    {
        if ( 0 < $v )
        {
            return 1;
        }
        if ( $v == 0 )
        {
            return 0;
        }
        if ( $v < 0 )
        {
            return -1;
        }
    }

    function setdraw( $pIndex, $pNumber )
    {
        $DotData[0] = array( 1, 80, 30, 100, 80, 100, 100, 70, 100, 20, 70, 1, 30, 1, 1, 20, 1, 40, 1, 80 );
        $DotData[1] = array( 30, 15, 50, 1, 50, 100 );
        $DotData[2] = array( 1, 34, 30, 1, 71, 1, 100, 34, 1, 100, 93, 100, 100, 86 );
        $DotData[3] = array( 1, 1, 100, 1, 42, 42, 100, 70, 50, 100, 1, 70 );
        $DotData[4] = array( 100, 73, 6, 73, 75, 6, 75, 100 );
        $DotData[5] = array( 100, 1, 1, 1, 1, 50, 50, 35, 100, 55, 100, 80, 50, 100, 1, 95 );
        $DotData[6] = array( 100, 20, 70, 1, 20, 1, 1, 30, 1, 80, 30, 100, 70, 100, 100, 80, 100, 60, 70, 50, 30, 50, 1, 60 );
        $DotData[7] = array( 6, 26, 6, 6, 100, 6, 53, 100 );
        $DotData[8] = array( 100, 30, 100, 20, 70, 1, 30, 1, 1, 20, 1, 30, 100, 70, 100, 80, 70, 100, 30, 100, 1, 80, 1, 70, 100, 30 );
        $DotData[9] = array( 1, 80, 30, 100, 80, 100, 100, 70, 100, 20, 70, 1, 30, 1, 1, 20, 1, 40, 30, 50, 70, 50, 100, 40 );
        $vExtent = $this->Width / strlen( $this->code );
        $Margin[0] = $this->Border + $vExtent * $pNumber + $vExtent * ( $this->rnd( ) * $this->Offset ) / 100;
        $Margin[1] = $vExtent * ( $pNumber + 1 ) - $this->Border - $vExtent * ( $this->rnd( ) * $this->Offset ) / 100;
        $Margin[2] = $this->Border + $this->Height * ( $this->rnd( ) * $this->Offset ) / 100;
        $Margin[3] = $this->Height - $this->Border - $this->Height * ( $this->Rnd * $this->Offset ) / 100;
        $vWidth = intval( $Margin[1] - $Margin[0] );
        $vHeight = intval( $Margin[3] - $Margin[2] );
        $vStartX = intval( ( $DotData[$pIndex][0] - 1 ) * $vWidth / 100 );
        $vStartY = intval( ( $DotData[$pIndex][1] - 1 ) * $vHeight / 100 );
        $i = 1;
        for ( ; $i <= count( $DotData[$pIndex] ) / 2; ++$i )
        {
            if ( !( $DotData[$pIndex][2 * $i - 2] != 0 ) && !( $DotData[$pIndex][2 * $i] != 0 ) )
            {
                $vEndX = ( $DotData[$pIndex][2 * $i] - 1 ) * $vWidth / 100;
                $vEndY = ( $DotData[$pIndex][2 * $i + 1] - 1 ) * $vHeight / 100;
                $vDX = $vEndX - $vStartX;
                $vDY = $vEndY - $vStartY;
                if ( $vDX == 0 )
                {
                    $vAngle = $this->sgn( $vDY ) * 3.14 / 2;
                }
                else
                {
                    $vAngle = atan( $vDY / $vDX );
                }
                if ( sin( $vAngle ) == 0 )
                {
                    $vLength = $vDX;
                }
                else
                {
                    $vLength = $vDY / sin( $vAngle );
                }
                $vAngle += ( $this->rnd( ) - 0.5 ) * 2 * $this->Angle * 3.14 * 2 / 100;
                $vDX = intval( cos( $vAngle ) * $vLength );
                $vDY = intval( sin( $vAngle ) * $vLength );
                if ( abs( $vDY ) < abs( $vDX ) )
                {
                    $vDeltaT = abs( $vDX );
                }
                else
                {
                    $vDeltaT = abs( $vDY );
                }
                $j = 1;
                for ( ; $j <= $vDeltaT; ++$j )
                {
                    $this->setdot( $Margin[0] + $vStartX + $j * $vDX / $vDeltaT, $Margin[2] + $vStartY + $j * $vDY / $vDeltaT );
                }
                $vStartX += $vDX;
                $vStartY += $vDY;
            }
        }
    }

    function output( $width = 60, $height = 16, $type = false )
    {
        if ( $type )
        {
            $this->initverfycode( );
        }
        $this->Width = $width;
        $this->Height = $height;
        $out = "";
        $i = 0;
        $out .= "GIF";
        $out .= "89a";
        $out .= chr( $this->Width % 256 ).chr( $this->Width / 256 % 256 );
        $out .= chr( $this->Height % 256 ).chr( $this->Height / 256 % 256 );
        $out .= chr( 128 ).chr( 0 ).chr( 0 );
        $out .= chr( 238 ).chr( 238 ).chr( 238 );
        $out .= chr( 0 ).chr( 0 ).chr( 0 );
        $out .= ",";
        $out .= chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 );
        $out .= chr( $this->Width % 256 ).chr( $this->Width / 256 % 256 );
        $out .= chr( $this->Height % 256 ).chr( $this->Height / 256 % 256 );
        $out .= chr( 0 ).chr( 7 ).chr( 255 );
        $y = 0;
        for ( ; $y < $this->Height; ++$y )
        {
            $x = 0;
            for ( ; $x < $this->Width; ++$x )
            {
                if ( $this->rnd( ) < $this->Noisy / 100 )
                {
                    $out .= chr( 1 - $this->Graph[$x][$y] );
                }
                else if ( $x * ( $x - $this->Width ) == 0 || $y * ( $y - $this->Height ) == 0 )
                {
                    $out .= chr( $this->Graph[$x][$y] );
                }
                else if ( $this->Graph[$x - 1][$y] == 1 || $this->Graph[$x][$y] || $this->Graph[$x][$y - 1] == 1 )
                {
                    $out .= chr( 1 );
                }
                else
                {
                    $out .= chr( 0 );
                }
                if ( ( $y * $this->Width + $x + 1 ) % 126 == 0 )
                {
                    $out .= chr( 128 );
                    ++$i;
                }
                if ( ( $y * $this->Width + $x + $i + 1 ) % 255 == 0 )
                {
                    if ( 255 < $this->Width * $this->Height - $y * $this->Width - $x - 1 )
                    {
                        $out .= chr( 255 );
                    }
                    else
                    {
                        $out .= chr( $this->Width * $this->Height % 255 );
                    }
                }
            }
        }
        $out .= chr( 128 ).chr( 0 ).chr( 129 ).chr( 0 ).chr( 59 );
        header( "Expires: -9999" );
        header( "Pragma: no-cache" );
        header( "Cache-Control: no-cache, no-store" );
        header( "Content-Type: image/gif" );
        echo $out;
    }

}

?>
