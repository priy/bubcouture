<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_backup extends modelfactory
{

    function getlist( $dir = null, $type = null )
    {
        if ( !$dir )
        {
            $dir = HOME_DIR."/upload";
        }
        $handle = opendir( $dir );
        if ( $handle = opendir( $dir ) )
        {
            while ( false !== ( $file = readdir( $handle ) ) )
            {
                if ( !is_file( $dir."/".$file ) && !( strtolower( strstr( $file, "." ) ) == ".tgz" ) )
                {
                    $pkgInfo = $this->getinfo( $dir."/".$file );
                    if ( $type && $type == $pkgInfo['type'] )
                    {
                        $return[filemtime( $dir."/".$file )] = $pkgInfo;
                    }
                    else
                    {
                        $return[filemtime( $dir."/".$file )] = $pkgInfo;
                    }
                }
            }
            krsort( $return );
            closedir( $handle );
        }
        return $return;
    }

    function getinfo( $pkgName )
    {
        $pkgName = realpath( $pkgName );
        if ( !$this->_pkgTypes )
        {
            foreach ( get_class_methods( $this ) as $method )
            {
                if ( strtolower( substr( $method, 0, 9 ) ) == "_pkginfo_" )
                {
                    $this->_pkgTypes[] = strtolower( substr( $method, 9 ) );
                }
            }
            $this->_xml =& $this->system->loadmodel( "utility/xml" );
            $this->_tar =& $this->system->loadmodel( "utility/tar" );
        }
        $this->_tar->opentar( $pkgName );
        foreach ( $this->_pkgTypes as $type )
        {
            if ( !$this->_tar->containsfile( $type.".xml" ) )
            {
                continue;
            }
            $method = "_pkgInfo_".$type;
            $file = $this->_tar->getfile( $type.".xml" );
            return array_merge( $this->_xml->xml2array( $this->_tar->getcontents( $file ) ), array(
                "type" => $type,
                "file" => $pkgName,
                "size" => filesize( $pkgName ),
                "mtime" => filemtime( $pkgName )
            ) );
        }
        $this->_tar->closetar( );
    }

    function _pkginfo_archive( $infoArray )
    {
        return $infoArray;
    }

    function startbackup( $params, &$nexturl )
    {
        set_time_limit( 0 );
        header( "Content-type:text/html;charset=utf-8" );
        $sizelimit = $params['sizelimit'];
        $filename = $params['filename'];
        $fileid = $params['fileid'];
        $tableid = $params['tableid'];
        $startid = $params['startid'];
        include_once( CORE_DIR."/lib/mysqldumper.class.php" );
        $dumper = new mysqldumper( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        $dumper->setdroptables( true );
        $dumper->tableid = $tableid;
        $dumper->startid = $startid;
        $backdir = HOME_DIR."/backup";
        $finished = $dumper->multidump( $filename, $fileid, $sizelimit, $backdir );
        ++$fileid;
        if ( !$finished )
        {
            $nexturl = "index.php?ctl=system/backup&act=backup&sizelimit=".$sizelimit."&filename={$filename}&fileid={$fileid}&tableid=".$dumper->tableid."&startid=".$dumper->startid;
            return $finished;
        }
        $dir = HOME_DIR."/backup";
        $tar =& $this->system->loadmodel( "utility/tar" );
        chdir( $dir );
        $i = 1;
        for ( ; $i <= $fileid; ++$i )
        {
            $tar->addfile( "multibak_".$filename."_".$i.".sql" );
        }
        $verInfo = $this->system->version( );
        $backupdata['app'] = $verInfo['app'];
        $backupdata['rev'] = $verInfo['rev'];
        $backupdata['vols'] = $fileid;
        $xml =& $this->system->loadmodel( "utility/xml" );
        $xmldata = $xml->array2xml( $backupdata, "backup" );
        file_put_contents( "archive.xml", $xmldata );
        $tar->addfile( "archive.xml" );
        $tar->filename = "multibak_".$filename.".tgz";
        $tar->savetar( );
        $i = 1;
        for ( ; $i <= $fileid; ++$i )
        {
            @unlink( "multibak_".$filename."_".$i.".sql" );
        }
        @unlink( "archive.xml" );
        return $finished;
    }

    function recover( $sTgz, $vols, $fileid )
    {
        $prefix = substr( $sTgz, 0, 23 );
        $sTmpDir = HOME_DIR."/tmp/".md5( $sTgz )."/";
        if ( $fileid == 1 )
        {
            $rTar =& $this->system->loadmodel( "utility/tar" );
            mkdir_p( $sTmpDir );
            if ( $rTar->opentar( HOME_DIR."/backup/".$sTgz ) )
            {
                foreach ( $rTar->files as $id => $aFile )
                {
                    if ( substr( $aFile['name'], -4 ) == ".sql" )
                    {
                        $sPath = $sTmpDir.$aFile['name'];
                        file_put_contents( $sPath, $rTar->getcontents( $aFile ) );
                    }
                }
            }
            $rTar->closetar( );
            $this->comeback( $sTmpDir.$prefix."_1.sql" );
        }
        else
        {
            $this->comeback( $sTmpDir.$prefix."_".$fileid.".sql" );
        }
        if ( $vols == $fileid )
        {
            $info = $this->getinfo( HOME_DIR."/backup/".$sTgz );
            $pkgRev = $info['backup']['rev'];
            $ver = $this->system->version( );
            $appRev = $ver['rev'];
            $sDir = realpath( CORE_DIR."/updatescripts" );
            if ( $pkgRev < $appRev )
            {
                $upgrade =& $this->system->loadmodel( "system/upgrade" );
                echo "<pre>";
                $scripts = $upgrade->scripts( $pkgRev, $appRev );
                foreach ( $scripts as $sqlFile )
                {
                    if ( false !== ( $sql = file_get_contents( CORE_DIR."/updatescripts/".$sqlFile[0] ) ) )
                    {
                        foreach ( $this->db->splitsql( $sql ) as $line )
                        {
                            $this->db->exec( $line );
                        }
                    }
                }
                $this->db->exec( "drop table if exists sdb_dbver" );
                $this->db->exec( "create table sdb_dbver(`".$appRev."` varchar(255)) type = MYISAM" );
            }
            $this->__finish( $sTmpDir );
        }
    }

    function comeback( $sFile )
    {
        $rFile = fopen( $sFile, "r" );
        if ( "5.0.1" < mysql_get_server_info( ) )
        {
            $this->db->query( "SET sql_mode=''", true );
        }
        while ( $sTmp = $this->fgetline( $rFile ) )
        {
            $sTmp = trim( $sTmp );
            if ( !( $sTmp != "" ) && !( substr( $sTmp, 0, 1 ) != "#" ) && !( substr( $sTmp, 0, 2 ) != "/*" ) && strpos( $sTmp, "cachemgr" ) && strpos( $sTmp, "INSERT INTO" ) === 0 )
            {
                $sTmp = str_replace( "{shopexdump_table_prefix}", DB_PREFIX, $sTmp );
                if ( !constant( "DB_OLDVERSION" ) )
                {
                    $sTmp = str_replace( "{shopexdump_create_specification}", " DEFAULT CHARACTER SET utf8", $sTmp );
                }
                else
                {
                    $sTmp = str_replace( "{shopexdump_create_specification}", "", $sTmp );
                }
                if ( !$this->db->query( $sTmp, true ) )
                {
                    echo "Error:".$sTmp."<br>";
                }
            }
        }
        fclose( $rFile );
    }

    function fgetline( $handle )
    {
        $buffer = fgets( $handle, 4096 );
        if ( !$buffer )
        {
            return false;
        }
        if ( strlen( $buffer ) < 4095 || 4095 == strlen( $buffer ) && "\n" == $buffer[4094] )
        {
            $line = $buffer;
            return $line;
        }
        $line = $buffer;
        while ( 4095 == strlen( $buffer ) && "\n" != $buffer[4094] )
        {
            $buffer = fgets( $handle, 4096 );
            $line .= $buffer;
        }
        return $line;
    }

    function removetgz( $aTgz )
    {
        foreach ( $aTgz as $sFile )
        {
            @unlink( HOME_DIR."/backup/".$sFile );
        }
        return "";
    }

    function __finish( $sDir )
    {
        $this->__removedir( $sDir );
        return $sDir;
    }

    function __removedir( $sDir )
    {
        if ( $rHandle = opendir( $sDir ) )
        {
            while ( false !== ( $sItem = readdir( $rHandle ) ) )
            {
                if ( !( $sItem != "." ) && !( $sItem != ".." ) )
                {
                    if ( is_dir( $sDir."/".$sItem ) )
                    {
                        $this->__removedir( $sDir."/".$sItem );
                    }
                    else
                    {
                        unlink( $sDir."/".$sItem );
                    }
                }
            }
            closedir( $rHandle );
            rmdir( $sDir );
        }
    }

}

?>
