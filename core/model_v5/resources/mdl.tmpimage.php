<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_tmpimage extends shopObject
{

    public $idColumn = "id";
    public $textColumn = "name";
    public $adminCtl = "system/tmpimage";
    public $defaultCols = "name,filetype,memo";

    public function getColumns( )
    {
        return array(
            "id" => array(
                "label" => __( "唯一标识" ),
                "width" => 150
            ),
            "name" => array(
                "label" => __( "文件名" ),
                "width" => 150
            ),
            "filetype" => array(
                "label" => __( "文件类型" ),
                "width" => 110
            ),
            "memo" => array(
                "label" => __( "文件说明" ),
                "width" => 150
            ),
            "tmpid" => array(
                "label" => __( "模板ID" ),
                "width" => 150
            ),
            "type" => array(
                "label" => __( "类型" ),
                "width" => 150
            )
        );
    }

    public function getId( $strId )
    {
        $aTmp = explode( "-", $strId );
        $aRet['tmpid'] = $aTmp[0];
        $aRet['name'] = substr( $strId, strlen( $aTmp[0] ) + 1 );
        return $aRet;
    }

    public function count( $filter )
    {
        return count( $this->_fileList( $filter ) );
    }

    public function getList( $cols, $filter = "", $start = 0, $limit = 20, &$count, $orderType = null )
    {
        $data = $this->_fileList( $filter );
        $count = count( $data );
        if ( $orderType )
        {
            foreach ( $data as $key => $rows )
            {
                $order[$key] = strtolower( $rows[$orderType[0]] );
            }
            if ( $orderType[1] == "asc" )
            {
                array_multisort( $order, SORT_ASC, $data );
            }
            else
            {
                array_multisort( $order, SORT_DESC, $data );
            }
        }
        $data = array_slice( $data, $start / 20 * $limit, $limit );
        return $data;
    }

    public function _fileList( $filter, $istheme = true )
    {
        $key = md5( var_export( $filter, 1 ) );
        if ( !isset( $this->_cacheList[$key] ) )
        {
            if ( !$istheme )
            {
                $dir = CORE_DIR."/shop/view/".$filter['tmpid']."/";
            }
            else
            {
                $dir = THEME_DIR."/".$filter['tmpid']."/";
            }
            $dirhandle = @opendir( $dir );
            $ftype = array(
                "html" => __( "模板文件" ),
                "gif" => __( "图片文件" ),
                "jpg" => __( "图片文件" ),
                "jpeg" => __( "图片文件" ),
                "png" => __( "图片文件" ),
                "bmp" => __( "图片文件" ),
                "css" => __( "样式表文件" ),
                "js" => __( "脚本文件" )
            );
            while ( $file_name = @readdir( $dirhandle ) )
            {
                if ( $file_name != "." && $file_name != ".." && $file_name != "Thumbs.db" && $file_name != "theme.xml" && $file_name != ".svn" )
                {
                    if ( !$filter['show_bak'] && preg_match( "/.*\\.bak_[0-9]+\\.[^\\.]+/", $file_name ) )
                    {
                        continue;
                    }
                    if ( !is_dir( $dir.$file_name ) )
                    {
                        $fext = strtolower( substr( $file_name, strrpos( $file_name, "." ) + 1 ) );
                    }
                    else
                    {
                        $fext = "Folder";
                    }
                    $aRows[$file_name] = array(
                        "id" => ( $filter['tmpid'] ? $filter['tmpid']."-" : "" ).$file_name,
                        "name" => $file_name,
                        "filetype" => $fext,
                        "memo" => $ftype[$fext] ? $ftype[$fext] : __( "资源文件" )
                    );
                }
            }
            @closedir( $dirhandle );
            ksort( $aRows );
            $this->_cacheList[$key] =& $aRows;
        }
        return $this->_cacheList[$key];
    }

    public function _filter( $filter )
    {
        $where = array( 1 );
        $filter['to_type'] = 1;
        $where[] = "for_id = 0";
        if ( $filter['msg_from'] )
        {
            $where[] = "msg_from ='".addslashes( $filter['msg_from'] )."'";
        }
        return parent::_filter( $filter )." AND ".implode( $where, " AND " );
    }

    public function getFile( $sName, $tmpid, $istheme = true )
    {
        $aFile = $this->_fileList( array(
            "tmpid" => $tmpid,
            "show_bak" => 1,
            "type" => "all"
        ), $istheme );
        $p = strrpos( $sName, "." );
        $re = "/^".preg_quote( substr( $sName, 0, $p ) )."\\.bak_([0-9]+)\\.".preg_quote( substr( $sName, $p + 1 ) )."\$/";
        foreach ( $aFile as $key => $rows )
        {
            if ( $rows['name'] == $sName )
            {
                $file = $rows;
            }
            if ( preg_match( $re, $rows['name'] ) )
            {
                $itms[] = $rows;
            }
        }
        $file['files'] = $itms;
        return $file;
    }

    public function saveFile( $aParams, $istheme = true )
    {
        if ( $istheme )
        {
            $dir = THEME_DIR."/".$aParams['tmpid']."/";
        }
        else
        {
            $dir = CORE_DIR."/shop/view/".$aParams['tmpid']."/";
        }
        if ( 0 < $aParams['upfile']['size'] )
        {
            $image =& $this->system->loadModel( "system/storager" );
            $limited = $image->get_pic_upload_max( );
            if ( $limited['size'] < $aParams['upfile']['size'] )
            {
                return __( "上传图片不能大于" ).$limited['desc'];
            }
            if ( substr( $aParams['upfile']['type'], 0, 5 ) == "image" )
            {
                $file = $this->getFile( $aParams['name'], $aParams['tmpid'], $istheme );
                $aTmp = explode( ".", $aParams['name'] );
                $arrNum = count( $aTmp ) - 1;
                $lastStr = $aTmp[$arrNum];
                if ( substr( $aTmp[$arrNum - 1], 0, 4 ) == "bak_" )
                {
                    $arrNum -= 1;
                }
                $i = 0;
                for ( ; $i < $arrNum; ++$i )
                {
                    $preStr .= $aTmp[$i].".";
                }
                $iLoop = 1;
                foreach ( $file['files'] as $item )
                {
                    if ( $item['name'] !== $preStr."bak_".$iLoop.".".$lastStr )
                    {
                        break;
                    }
                    ++$iLoop;
                }
                $saveFile = $preStr."bak_".$iLoop.".".$lastStr;
                move_uploaded_file( $aParams['upfile']['tmp_name'], $dir.$saveFile );
                chmod( $dir.$saveFile, 438 );
                $aParams['imgdef'] = $saveFile;
            }
        }
        $aParams['imgdef'] = basename( $aParams['imgdef'] );
        if ( $aParams['name'] != $aParams['imgdef'] )
        {
            copy( $dir.$aParams['name'], $dir."tmp_image" );
            copy( $dir.$aParams['imgdef'], $dir.$aParams['name'] );
            copy( $dir."tmp_image", $dir.$aParams['imgdef'] );
            unlink( $dir."tmp_image" );
        }
        return true;
    }

    public function saveSource( $aParams, $istheme = true )
    {
        if ( $istheme )
        {
            $dir = THEME_DIR."/".$aParams['tmpid']."/";
        }
        else
        {
            $dir = CORE_DIR."/shop/view/".$aParams['tmpid']."/";
        }
        $aFile = $this->getFile( $aParams['name'], $aParams['tmpid'], $istheme );
        if ( count( $aFile['files'] ) == 0 || $aParams['isbak'] )
        {
            $aTmp = explode( ".", $aFile['name'] );
            $arrNum = count( $aTmp ) - 1;
            $lastStr = $aTmp[$arrNum];
            if ( substr( $aTmp[$arrNum - 1], 0, 4 ) == "bak_" )
            {
                $arrNum -= 1;
            }
            $i = 0;
            for ( ; $i < $arrNum; ++$i )
            {
                $preStr .= $aTmp[$i].".";
            }
            $iLoop = 1;
            foreach ( $aFile['files'] as $item )
            {
                if ( $item['name'] !== $preStr."bak_".$iLoop.".".$lastStr )
                {
                    break;
                }
                ++$iLoop;
            }
            if ( $aParams['isbak'] )
            {
                $saveFile = $preStr."bak_".$iLoop.".".$lastStr;
                file_rename( $dir.$aParams['name'], $dir.$saveFile );
            }
        }
        $fp = fopen( $dir.$aParams['name'], "wb" );
        fwrite( $fp, $aParams['file_source'] );
        fclose( $fp );
        return true;
    }

    public function recoverSource( $file, $dest, $tmpid, $istheme = true )
    {
        if ( $istheme )
        {
            $dir = THEME_DIR."/".$tmpid."/";
        }
        else
        {
            $dir = CORE_DIR."/shop/view/".$tmpid."/";
        }
        return copy( $dir.$file, $dir.$dest );
    }

    public function toRemove( $file, $tmpid, $istheme = true )
    {
        if ( $istheme )
        {
            $dir = THEME_DIR."/".$tmpid."/";
        }
        else
        {
            $dir = CORE_DIR."/shop/view/".$tmpid."/";
        }
        return unlink( $dir.$file );
    }

}

?>
