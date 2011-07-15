<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !class_exists( "simplehash" ) )
{
    class simplehash
    {

        public $beginOffset = 20;
        public $maxSize = 33554432;
        public $hdunpacker = "V1/V1/H*";
        public $hdsize = 32;

        public function workat( $file )
        {
            if ( !file_exists( $file ) )
            {
                $this->create( $file );
            }
            else
            {
                $this->hs = fopen( $file, "r+" );
            }
        }

        public function create( $file )
        {
            touch( $file );
            $this->hs = fopen( $file, "r+" );
            fputs( $this->hs, "<?php exit()?>" );
            fseek( $this->hs, 16384 + $this->beginOffset );
            fputs( $this->hs, "\x00" );
        }

        public function get( $key )
        {
            fseek( $this->hs, $base = hexdec( substr( $key, 0, 3 ) ) * 4 + $this->beginOffset );
            list( , $offset ) = unpack( "V", fread( $this->hs, 4 ) );
            while ( $offset )
            {
                $info = $this->getInfo( $offset );
                if ( $info['key'] == $key )
                {
                    fseek( $this->hs, $info['data'] );
                    $data = fread( $this->hs, $info['size'] );
                    return unserialize( $data );
                }
                $offset = $info['next'];
            }
            return false;
        }

        public function close( )
        {
            fclose( $this->hs );
        }

        public function set( $key, $value )
        {
            $data = serialize( $value );
            $size = strlen( $data );
            $dataoffset = $this->alloc( $size );
            fseek( $this->hs, $dataoffset );
            fputs( $this->hs, $data );
            fseek( $this->hs, $base = hexdec( substr( $key, 0, 3 ) ) * 4 + $this->beginOffset );
            list( , $subhome ) = unpack( "V", fread( $this->hs, 4 ) );
            $subhome = $this->getlast( $subhome );
            $offset = $this->alloc( $this->hdsize );
            fseek( $this->hs, $offset );
            fputs( $this->hs, $str = pack( "V1V1V1V1H*", 0, $subhome, $dataoffset, $size, $key ) );
            if ( 0 < $subhome )
            {
                fseek( $this->hs, $subhome );
                fputs( $this->hs, pack( "V", $offset ) );
            }
            else
            {
                fseek( $this->hs, $base );
                fputs( $this->hs, pack( "V", $offset ) );
            }
        }

        public function getInfo( $offset )
        {
            fseek( $this->hs, $offset );
            return unpack( "V1next/V1prev/V1data/V1size/H*key", fread( $this->hs, $this->hdsize ) );
        }

        public function getlast( $offset )
        {
            if ( !$offset )
            {
                return 0;
            }
            $info = $this->getInfo( $offset );
            if ( $info['next'] )
            {
                return $this->getlast( $info['next'] );
            }
            else
            {
                return $offset;
            }
        }

        public function alloc( $size )
        {
            fseek( $this->hs, 0, SEEK_END );
            $offset = ftell( $this->hs );
            if ( $this->maxSize < $offset + $size )
            {
                echo "max";
                exit( );
            }
            else
            {
                return $offset;
            }
        }

        public function dump( )
        {
            fseek( $this->hs, $this->beginOffset );
            $i = 0;
            for ( ; $i < 16; ++$i )
            {
                $j = 0;
                for ( ; $j < 16; ++$j )
                {
                    $k = 0;
                    for ( ; $k < 16; ++$k )
                    {
                        $str = dechex( $i ).dechex( $j ).dechex( $k );
                        fseek( $this->hs, hexdec( $str ) * 4 + $this->beginOffset );
                        list( , $offset ) = unpack( "V", fread( $this->hs, 4 ) );
                        if ( $offset )
                        {
                            echo "<div>{$str} --> {$offset}<br />".$this->viewchild( $offset )."</div><hr />";
                        }
                    }
                }
            }
        }

        public function viewchild( $offset )
        {
            $info = $this->getInfo( $offset );
            return "<div>\n                next:{$info['next']}<br />\n                prev:{$info['prev']}<br />\n                data:{$info['data']}<br />\n                size:{$info['size']}<br />\n                key:{$info['key']}\n            <div style=\"padding-left:20px\">".( $info['next'] ? $this->viewchild( $info['next'] ) : "" )."</div></div>";
        }

    }

}
?>
