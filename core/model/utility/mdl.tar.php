<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_tar
{

    var $filename;
    var $isGzipped;
    var $tar_file;
    var $files;
    var $directories;
    var $numFiles;
    var $numDirectories;
    var $targetDirectory;
    var $tarFile;

    function __computeunsignedchecksum( $bytestring )
    {
        $i = 0;
        for ( ; $i < 512; ++$i )
        {
            $unsigned_chksum += ord( $bytestring[$i] );
        }
        $i = 0;
        for ( ; $i < 8; ++$i )
        {
            $unsigned_chksum -= ord( $bytestring[148 + $i] );
        }
        $unsigned_chksum += ord( " " ) * 8;
        return $unsigned_chksum;
    }

    function __parsenullpaddedstring( $string )
    {
        $position = strpos( $string, chr( 0 ) );
        return substr( $string, 0, $position );
    }

    function __filesubstr( &$fp, $offset, $len )
    {
        fseek( $fp, $offset );
        return fread( $fp, $len );
    }

    function __parsetar( )
    {
        if ( !$this->tar_file_name )
        {
            return false;
        }
        $tar_length = filesize( $this->tar_file_name );
        $main_offset = 0;
        $this->tar_file = fopen( $this->tar_file_name, "r" );
        while ( $main_offset < $tar_length && !( $this->__filesubstr( $this->tar_file, $main_offset, 512 ) == str_repeat( chr( 0 ), 512 ) ) )
        {
            $file_name = $this->__parsenullpaddedstring( $this->__filesubstr( $this->tar_file, $main_offset, 100 ) );
            $file_mode = $this->__filesubstr( $this->tar_file, $main_offset + 100, 8 );
            $file_uid = octdec( $this->__filesubstr( $this->tar_file, $main_offset + 108, 8 ) );
            $file_gid = octdec( $this->__filesubstr( $this->tar_file, $main_offset + 116, 8 ) );
            $file_size = octdec( $this->__filesubstr( $this->tar_file, $main_offset + 124, 12 ) );
            $file_time = octdec( $this->__filesubstr( $this->tar_file, $main_offset + 136, 12 ) );
            $file_chksum = octdec( $this->__filesubstr( $this->tar_file, $main_offset + 148, 6 ) );
            $file_uname = $this->__parsenullpaddedstring( $this->__filesubstr( $this->tar_file, $main_offset + 265, 32 ) );
            $file_gname = $this->__parsenullpaddedstring( $this->__filesubstr( $this->tar_file, $main_offset + 297, 32 ) );
            if ( $this->__computeunsignedchecksum( $this->__filesubstr( $this->tar_file, $main_offset, 512 ) ) != $file_chksum )
            {
                return false;
            }
            if ( 0 < $file_size )
            {
                ++$this->numFiles;
                $activeFile =& $this->files[];
                $activeFile['name'] = $file_name;
                $activeFile['mode'] = $file_mode;
                $activeFile['size'] = $file_size;
                $activeFile['time'] = $file_time;
                $activeFile['member_id'] = $file_uid;
                $activeFile['group_id'] = $file_gid;
                $activeFile['user_name'] = $file_uname;
                $activeFile['group_name'] = $file_gname;
                $activeFile['checksum'] = $file_chksum;
                $activeFile['offset'] = $main_offset + 512;
            }
            else
            {
                ++$this->numDirectories;
                $activeDir =& $this->directories[];
                $activeDir['name'] = $file_name;
                $activeDir['mode'] = $file_mode;
                $activeDir['time'] = $file_time;
                $activeDir['member_id'] = $file_uid;
                $activeDir['group_id'] = $file_gid;
                $activeDir['user_name'] = $file_uname;
                $activeDir['group_name'] = $file_gname;
                $activeDir['checksum'] = $file_chksum;
            }
            $main_offset += 512 + ceil( $file_size / 512 ) * 512;
        }
        return true;
    }

    function __readtar( $filename = "" )
    {
        if ( !$filename )
        {
            $filename = $this->filename;
        }
        $fp = fopen( $filename, "r" );
        $header = fread( $fp, 3 );
        fclose( $fp );
        $this->tar_file_name = $filename;
        if ( $header[0] == chr( 31 ) && $header[1] == chr( 139 ) && $header == chr( 8 ) )
        {
            return false;
        }
        $this->__parsetar( );
        return true;
    }

    function __generatetar( $type = "file" )
    {
        unset( $this->'tar_file' );
        if ( 0 < $this->numDirectories )
        {
            foreach ( $this->directories as $key => $information )
            {
                unset( $header );
                $header .= str_pad( $information['name'], 100, chr( 0 ) );
                $header .= str_pad( decoct( $information['mode'] ), 7, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( substr( decoct( $information['member_id'] ), 0, 7 ), 7, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( decoct( $information['group_id'] ), 7, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( decoct( 0 ), 11, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( decoct( $information['time'] ), 11, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_repeat( " ", 8 );
                $header .= "5";
                $header .= str_repeat( chr( 0 ), 100 );
                $header .= str_pad( "ustar", 6, chr( 32 ) );
                $header .= chr( 32 ).chr( 0 );
                $header .= str_pad( "", 32, chr( 0 ) );
                $header .= str_pad( "", 32, chr( 0 ) );
                $header .= str_repeat( chr( 0 ), 8 );
                $header .= str_repeat( chr( 0 ), 8 );
                $header .= str_repeat( chr( 0 ), 155 );
                $header .= str_repeat( chr( 0 ), 12 );
                $checksum = str_pad( decoct( $this->__computeunsignedchecksum( $header ) ), 6, "0", STR_PAD_LEFT );
                $i = 0;
                for ( ; $i < 6; ++$i )
                {
                    $header[148 + $i] = substr( $checksum, $i, 1 );
                }
                $header[154] = chr( 0 );
                $header[155] = chr( 32 );
                $this->output( $header, $type );
            }
        }
        if ( 0 < $this->numFiles )
        {
            foreach ( $this->files as $key => $information )
            {
                unset( $header );
                $header .= str_pad( $information['name'], 100, chr( 0 ) );
                $header .= str_pad( decoct( $information['mode'] ), 7, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( substr( decoct( $information['member_id'] ), 0, 7 ), 7, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( decoct( $information['group_id'] ), 7, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( decoct( $information['size'] ), 11, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_pad( decoct( $information['time'] ), 11, "0", STR_PAD_LEFT ).chr( 0 );
                $header .= str_repeat( " ", 8 );
                $header .= "0";
                $header .= str_repeat( chr( 0 ), 100 );
                $header .= str_pad( "ustar", 6, chr( 32 ) );
                $header .= chr( 32 ).chr( 0 );
                $header .= str_pad( $information['user_name'], 32, chr( 0 ) );
                $header .= str_pad( $information['group_name'], 32, chr( 0 ) );
                $header .= str_repeat( chr( 0 ), 8 );
                $header .= str_repeat( chr( 0 ), 8 );
                $header .= str_repeat( chr( 0 ), 155 );
                $header .= str_repeat( chr( 0 ), 12 );
                $checksum = str_pad( decoct( $this->__computeunsignedchecksum( $header ) ), 6, "0", STR_PAD_LEFT );
                $i = 0;
                for ( ; $i < 6; ++$i )
                {
                    $header[148 + $i] = substr( $checksum, $i, 1 );
                }
                $header[154] = chr( 0 );
                $header[155] = chr( 32 );
                if ( $information['contents'] )
                {
                    $contents = $information['contents'];
                }
                else if ( $information['srcfile'] )
                {
                    $contents = $this->_readfile( $information['srcfile'] );
                }
                else
                {
                    $contents = $this->_readfile( getcwd( )."/".$information['name'] );
                }
                $file_contents = str_pad( $contents, ceil( $information['size'] / 512 ) * 512, chr( 0 ) );
                $this->output( $header.$file_contents, $type );
            }
        }
        $this->output( str_repeat( chr( 0 ), 512 ), $type );
        return true;
    }

    function _readfile( $file )
    {
        if ( is_file( $file ) && filesize( $file ) )
        {
            $handle = fopen( $file, "r" );
            $contents = "";
            while ( !feof( $handle ) )
            {
                $contents .= fread( $handle, 500000 );
            }
            fclose( $handle );
            return $contents;
        }
    }

    function output( $string, $type )
    {
        switch ( $type )
        {
        case "file" :
            if ( !$this->tar_file )
            {
                $this->tar_file = fopen( $this->filename, "wb" );
            }
            fwrite( $this->tar_file, $string );
            break;
        case "var" :
            $this->tar_file .= $string;
            break;
        case "output" :
            echo $string;
        }
    }

    function opentar( $filename, $target )
    {
        unset( $this->'filename' );
        unset( $this->'isGzipped' );
        unset( $this->'tar_file' );
        unset( $this->'files' );
        unset( $this->'directories' );
        unset( $this->'numFiles' );
        unset( $this->'numDirectories' );
        if ( !file_exists( $filename ) )
        {
            return false;
        }
        $this->filename = $filename;
        $this->targetDirectory = $target;
        $this->__readtar( );
        return true;
    }

    function closetar( )
    {
        if ( $this->tar_file )
        {
            fclose( $this->tar_file );
        }
        return true;
    }

    function appendtar( $filename )
    {
        if ( !file_exists( $filename ) )
        {
            return false;
        }
        $this->__readtar( $filename );
        return true;
    }

    function getfile( $filename )
    {
        if ( 0 < $this->numFiles )
        {
            foreach ( $this->files as $key => $information )
            {
                if ( !( $information['name'] == $filename ) )
                {
                    continue;
                }
                return $information;
            }
        }
        return false;
    }

    function getcontents( $information )
    {
        return $this->__filesubstr( $this->tar_file, $information['offset'], $information['size'] );
    }

    function getdirectory( $dirname )
    {
        if ( 0 < $this->numDirectories )
        {
            foreach ( $this->directories as $key => $information )
            {
                if ( !( $information['name'] == $dirname ) )
                {
                    continue;
                }
                return $information;
            }
        }
        return false;
    }

    function containsfile( $filename )
    {
        if ( 0 < $this->numFiles )
        {
            foreach ( $this->files as $key => $information )
            {
                if ( !( $information['name'] == $filename ) )
                {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    function containsdirectory( $dirname )
    {
        if ( 0 < $this->numDirectories )
        {
            foreach ( $this->directories as $key => $information )
            {
                if ( !( $information['name'] == $dirname ) )
                {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    function adddirectory( $dirname )
    {
        if ( !file_exists( $dirname ) )
        {
            return false;
        }
        $file_information = stat( $dirname );
        ++$this->numDirectories;
        $activeDir =& $this->directories[];
        $activeDir['name'] = $dirname;
        $activeDir['mode'] = $file_information['mode'];
        $activeDir['time'] = $file_information['time'];
        $activeDir['member_id'] = $file_information['uid'];
        $activeDir['group_id'] = $file_information['gid'];
        $activeDir['checksum'] = $checksum;
        return true;
    }

    function addfile( $filename, $file_contents = false, $file_information = null )
    {
        if ( !file_exists( $filename ) || !$file_contents )
        {
            return false;
        }
        if ( $this->containsfile( $filename ) )
        {
            return false;
        }
        if ( !$file_contents )
        {
            $file_information = stat( $filename );
        }
        else
        {
            if ( 0 < count( $this->files ) )
            {
                $file_information = $this->files[0];
            }
            $file_information['size'] = strlen( $file_contents );
            $file_information['time'] = time( );
        }
        ++$this->numFiles;
        $activeFile =& $this->files[];
        $activeFile['name'] = $filename;
        $activeFile['mode'] = $file_information['mode'];
        $activeFile['member_id'] = $file_information['uid'];
        $activeFile['group_id'] = $file_information['gid'];
        $activeFile['size'] = $file_information['size'];
        $activeFile['time'] = $file_information['mtime'];
        $activeFile['checksum'] = $checksum;
        $activeFile['user_name'] = "";
        $activeFile['group_name'] = "";
        if ( $file_contents )
        {
            $activeFile['contents'] = $file_contents;
        }
        return true;
    }

    function removefile( $filename )
    {
        if ( 0 < $this->numFiles )
        {
            foreach ( $this->files as $key => $information )
            {
                if ( !( $information['name'] == $filename ) )
                {
                    continue;
                }
                --$this->numFiles;
                unset( $this->$this->files->$key );
                return true;
            }
        }
        return false;
    }

    function removedirectory( $dirname )
    {
        if ( 0 < $this->numDirectories )
        {
            foreach ( $this->directories as $key => $information )
            {
                if ( !( $information['name'] == $dirname ) )
                {
                    continue;
                }
                --$this->numDirectories;
                unset( $this->$this->directories->$key );
                return true;
            }
        }
        return false;
    }

    function savetar( )
    {
        if ( !$this->filename )
        {
            return false;
        }
        $this->__generatetar( );
        return true;
    }

    function gettar( $type = "output" )
    {
        $this->__generatetar( $type );
        if ( $type != "output" )
        {
            return $this->tar_file;
        }
    }

    function totar( $filename, $useGzip )
    {
        if ( !$filename )
        {
            return false;
        }
        return file_put_contents( $filename, $this->gettar( $useGzip ) );
    }

    function gzcompressfile( $source, $level = false )
    {
        $dest = $source.".gz";
        $mode = "wb".$level;
        $error = false;
        if ( $fp_out = gzopen( $dest, $mode ) )
        {
            if ( $fp_in = fopen( $source, "rb" ) )
            {
                while ( !feof( $fp_in ) )
                {
                    gzwrite( $fp_out, fread( $fp_in, 524288 ) );
                }
                fclose( $fp_in );
            }
            else
            {
                $error = true;
            }
            gzclose( $fp_out );
        }
        else
        {
            $error = true;
        }
        if ( $error )
        {
            return false;
        }
        return $dest;
    }

}

?>
