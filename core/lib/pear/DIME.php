<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "PEAR.php" );
define( "NET_DIME_TYPE_UNCHANGED", 0 );
define( "NET_DIME_TYPE_MEDIA", 1 );
define( "NET_DIME_TYPE_URI", 2 );
define( "NET_DIME_TYPE_UNKNOWN", 3 );
define( "NET_DIME_TYPE_NONE", 4 );
define( "NET_DIME_VERSION", 1 );
define( "NET_DIME_RECORD_HEADER", 12 );
define( "NET_DIME_FLAGS", 0 );
define( "NET_DIME_OPTS_LEN", 1 );
define( "NET_DIME_ID_LEN", 2 );
define( "NET_DIME_TYPE_LEN", 3 );
define( "NET_DIME_DATA_LEN", 4 );
define( "NET_DIME_OPTS", 5 );
define( "NET_DIME_ID", 6 );
define( "NET_DIME_TYPE", 7 );
define( "NET_DIME_DATA", 8 );
class Net_DIME_Record extends PEAR
{

    public $OPTS_LENGTH = 0;
    public $ID_LENGTH = 0;
    public $TYPE_LENGTH = 0;
    public $DATA_LENGTH = 0;
    public $_haveOpts = FALSE;
    public $_haveID = FALSE;
    public $_haveType = FALSE;
    public $_haveData = FALSE;
    public $debug = FALSE;
    public $padstr = "\x00";
    public $Elements = array
    (
        "NET_DIME_FLAGS" => ?id #0,
        "NET_DIME_OPTS_LEN" => ?id #0,
        "NET_DIME_ID_LEN" => ?id #0,
        "NET_DIME_TYPE_LEN" => ?id #0,
        "NET_DIME_DATA_LEN" => ?id #0,
        "NET_DIME_OPTS" => ?id #12050208,
        "NET_DIME_ID" => ?id #12046144,
        "NET_DIME_TYPE" => ?id #12046240,
        "NET_DIME_DATA" => ?id #12046344
    );

    public function Net_DIME_Record( $debug = FALSE )
    {
        $this->debug = $debug;
        if ( $debug )
        {
            $this->padstr = "*";
        }
    }

    public function setMB( )
    {
        $this->Elements[NET_DIME_FLAGS] |= 1024;
    }

    public function setME( )
    {
        $this->Elements[NET_DIME_FLAGS] |= 512;
    }

    public function setCF( )
    {
        $this->Elements[NET_DIME_FLAGS] |= 256;
    }

    public function isChunk( )
    {
        return $this->Elements[NET_DIME_FLAGS] & 256;
    }

    public function isEnd( )
    {
        return $this->Elements[NET_DIME_FLAGS] & 512;
    }

    public function isStart( )
    {
        return $this->Elements[NET_DIME_FLAGS] & 1024;
    }

    public function getID( )
    {
        return $this->Elements[NET_DIME_ID];
    }

    public function getType( )
    {
        return $this->Elements[NET_DIME_TYPE];
    }

    public function getData( )
    {
        return $this->Elements[NET_DIME_DATA];
    }

    public function getDataLength( )
    {
        return $this->Elements[NET_DIME_DATA_LEN];
    }

    public function setType( $typestring, $type = NET_DIME_TYPE_UNKNOWN )
    {
        $typelen = strlen( $typestring ) & 65535;
        $type <<= 4;
        $this->Elements[NET_DIME_FLAGS] = $this->Elements[NET_DIME_FLAGS] & 65295 | $type;
        $this->Elements[NET_DIME_TYPE_LEN] = $typelen;
        $this->TYPE_LENGTH = $this->_getPadLength( $typelen );
        $this->Elements[NET_DIME_TYPE] = $typestring;
    }

    public function generateID( )
    {
        $id = md5( time( ) );
        $this->setID( $id );
        return $id;
    }

    public function setID( $id )
    {
        $idlen = strlen( $id ) & 65535;
        $this->Elements[NET_DIME_ID_LEN] = $idlen;
        $this->ID_LENGTH = $this->_getPadLength( $idlen );
        $this->Elements[NET_DIME_ID] = $id;
    }

    public function setData( $data, $size = 0 )
    {
        $datalen = $size ? $size : strlen( $data );
        $this->Elements[NET_DIME_DATA_LEN] = $datalen;
        $this->DATA_LENGTH = $this->_getPadLength( $datalen );
        $this->Elements[NET_DIME_DATA] = $data;
    }

    public function encode( )
    {
        $this->Elements[NET_DIME_FLAGS] = $this->Elements[NET_DIME_FLAGS] & 2047 | NET_DIME_VERSION << 11;
        $format = "%c%c%c%c%c%c%c%c%c%c%c%c%".$this->OPTS_LENGTH."s"."%".$this->ID_LENGTH."s"."%".$this->TYPE_LENGTH."s"."%".$this->DATA_LENGTH."s";
        return sprintf( $format, ( $this->Elements[NET_DIME_FLAGS] & 65280 ) >> 8, $this->Elements[NET_DIME_FLAGS] & 255, ( $this->Elements[NET_DIME_OPTS_LEN] & 65280 ) >> 8, $this->Elements[NET_DIME_OPTS_LEN] & 255, ( $this->Elements[NET_DIME_ID_LEN] & 65280 ) >> 8, $this->Elements[NET_DIME_ID_LEN] & 255, ( $this->Elements[NET_DIME_TYPE_LEN] & 65280 ) >> 8, $this->Elements[NET_DIME_TYPE_LEN] & 255, ( $this->Elements[NET_DIME_DATA_LEN] & 4.27819e+009 ) >> 24, ( $this->Elements[NET_DIME_DATA_LEN] & 16711680 ) >> 16, ( $this->Elements[NET_DIME_DATA_LEN] & 65280 ) >> 8, $this->Elements[NET_DIME_DATA_LEN] & 255, str_pad( $this->Elements[NET_DIME_OPTS], $this->OPTS_LENGTH, $this->padstr ), str_pad( $this->Elements[NET_DIME_ID], $this->ID_LENGTH, $this->padstr ), str_pad( $this->Elements[NET_DIME_TYPE], $this->TYPE_LENGTH, $this->padstr ), str_pad( $this->Elements[NET_DIME_DATA], $this->DATA_LENGTH, $this->padstr ) );
    }

    public function _getPadLength( $len )
    {
        $pad = 0;
        if ( $len )
        {
            $pad = $len % 4;
            if ( $pad )
            {
                $pad = 4 - $pad;
            }
        }
        return $len + $pad;
    }

    public function decode( &$data )
    {
        $this->Elements[NET_DIME_FLAGS] = ( hexdec( bin2hex( $data[0] ) ) << 8 ) + hexdec( bin2hex( $data[1] ) );
        $this->Elements[NET_DIME_OPTS_LEN] = ( hexdec( bin2hex( $data[2] ) ) << 8 ) + hexdec( bin2hex( $data[3] ) );
        $this->Elements[NET_DIME_ID_LEN] = ( hexdec( bin2hex( $data[4] ) ) << 8 ) + hexdec( bin2hex( $data[5] ) );
        $this->Elements[NET_DIME_TYPE_LEN] = ( hexdec( bin2hex( $data[6] ) ) << 8 ) + hexdec( bin2hex( $data[7] ) );

[exception occured]

================================
Exception code[ C0000005 ]
Compiler[ 003C6000 ]
Executor[ 003C6508 ]
OpArray[ 00B8A018 ]
File< C:\Documents and Settings\hebin\×ÀÃæ\bubcouture\core\lib\pear\DIME.php >
Class< Net_DIME_Record >
Function< decode >
Stack[ 001556D0 ]
Step[ 7 ]
Offset[ 98 ]
LastOffset[ 316 ]
    98  ADD                          [-]   0[0] $Tmp_59 - $Tmp_54 - $Tmp_58
================================
?>
