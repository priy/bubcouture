<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_productcat extends shopobject
{

    var $idColumn = "cat_id";
    var $textColumn = "cat_name";
    var $adminCtl = "goods/category";
    var $defaultCols = "cat_id,parent_id,s_cat_id,cat_path,is_leaf,type_id,cat_name,disabled";
    var $defaultOrder = array
    (
        0 => "cat_id",
        1 => "desc"
    );
    var $tableName = "sdb_goods_cat";
    var $catMap = array( );
    var $catTree = array( );
    var $catMapTree = array( );
    var $disabledMark = "all";

    function getcolumns( )
    {
        return array(
            "cat_id" => array(
                "label" => __( "分类ID" ),
                "width" => 110
            ),
            "parent_id" => array(
                "label" => __( "分类ID" ),
                "width" => 110
            ),
            "s_cat_id" => array(
                "label" => __( "Shopex分类ID" ),
                "width" => 110
            ),
            "cat_path" => array(
                "label" => __( "分类路径(从根至本结点的路径,逗号分隔,首部有逗号)" ),
                "width" => 110
            ),
            "is_leaf" => array(
                "label" => __( "是否叶子结点（true：是；false：否）" ),
                "width" => 110
            ),
            "type_id" => array(
                "label" => __( "类型序号" ),
                "width" => 110
            ),
            "cat_name" => array(
                "label" => __( "分类名称" ),
                "width" => 110
            ),
            "disabled" => array(
                "label" => __( "是否屏蔽（true：是；false：否）" ),
                "width" => 110
            ),
            "p_order" => array(
                "label" => __( "排序" ),
                "width" => 110
            ),
            "goods_count" => array(
                "label" => __( "商品数" ),
                "width" => 110
            ),
            "finder" => array(
                "label" => __( "渐进式筛选容器" ),
                "width" => 110
            )
        );
    }

    function getall( $catid = null )
    {
        if ( 0 < $catid )
        {
            $catid = "= ".$catid;
        }
        else
        {
            $catid = "IS NULL or parent_id=0";
        }
        return $this->db->select( "SELECT cat_name AS text, cat_id AS id, is_leaf AS cls FROM sdb_goods_cat\r\n            WHERE parent_id ".$catid." ORDER BY cat_id,p_order desc" );
    }

    function getexists( )
    {
        return $this->db->select( "select cat_id from sdb_goods_cat" );
    }

    function getpath( $cat_id, $method = null )
    {
        $method = $this->system->getconf( "gallery.default_view" );
        $row = $this->db->selectrow( "select cat_path,cat_name from sdb_goods_cat where cat_id=".intval( $cat_id ) );
        $ret = array(
            array(
                "type" => "goodsCat",
                "title" => $row['cat_name'],
                "link" => $this->system->mkurl( "gallery", $method, array(
                    $cat_id
                ) )
            )
        );
        if ( $row['cat_path'] != "," && $row['cat_path'] )
        {
            foreach ( $this->db->select( "select cat_name,cat_id from sdb_goods_cat where cat_id in(".substr( $row['cat_path'], 0, -1 ).") order by cat_path desc" ) as $row )
            {
                array_unshift( $ret, array(
                    "type" => "goodsCat",
                    "title" => $row['cat_name'],
                    "link" => $this->system->mkurl( "gallery", $method, array(
                        $row['cat_id']
                    ) )
                ) );
            }
        }
        return $ret;
    }

    function get_cat_depth( )
    {
        $row = $this->db->selectrow( "select cat_path from sdb_goods_cat order by cat_path desc" );
        return count( explode( ",", $row['cat_path'] ) );
    }

    function get_cat_list( $show_stable = false )
    {
        $file = MEDIA_DIR."/goods_cat.data";
        if ( $contents = file_get_contents( $file ) )
        {
            if ( $result = json_decode( $contents, true ) )
            {
                if ( $show_stable )
                {
                    foreach ( $result as $key => $value )
                    {
                        if ( 1 < $result[$key]['step'] )
                        {
                            $result[$key]['cat_name'] = str_repeat( " ", ( $result[$key]['step'] - 1 ) * 2 )."└".$result[$key]['cat_name'];
                        }
                    }
                }
                return $result;
            }
            return $this->cat2json( true );
        }
        return $this->cat2json( true );
    }

    function cat2json( $return = false )
    {
        $file = MEDIA_DIR."/goods_cat.data";
        $contents = $this->getmaptree( 0, "" );
        if ( $return )
        {
            file_put_contents( $file, json_encode( $contents ) );
            return $contents;
        }
        return file_put_contents( $file, json_encode( $contents ) );
    }

    function getmap( $depth = -1, $cat_id = 0 )
    {
        $var_depth = $depth;
        $var_cat_id = $cat_id;
        if ( isset( $this->catMap[$var_depth][$var_cat_id] ) )
        {
            return $this->catMap[$var_depth][$var_cat_id];
        }
        if ( 0 < $cat_id )
        {
            $row = $this->db->select( "select cat_path from sdb_goods_cat where cat_id=".intval( $cat_id ) );
            if ( 0 < $depth )
            {
                $depth += substr_count( $row['cat_path'], "," );
            }
            $rows = $this->db->select( "select cat_name,cat_id,parent_id,is_leaf,cat_path,type_id from sdb_goods_cat where cat_path like \"".$row['cat_path'].$cat_id."%\" order by cat_path,p_order" );
        }
        else
        {
            $rows = $this->db->select( "select cat_name,cat_id,parent_id,is_leaf,cat_path,type_id from sdb_goods_cat order by p_order" );
        }
        $cats = array( );
        $ret = array( );
        foreach ( $rows as $k => $row )
        {
            if ( !( $depth < 0 ) || !( substr_count( $row['cat_path'], "," ) < $depth ) )
            {
                $cats[$row['cat_id']] = array(
                    "type" => "gcat",
                    "parent_id" => $row['parent_id'],
                    "title" => $row['cat_name'],
                    "link" => $this->system->mkurl( "gallery", "index", array(
                        $row['cat_id']
                    ) )
                );
            }
        }
        foreach ( $cats as $cid => $cat )
        {
            if ( $cat['parent_id'] == $cat_id )
            {
                $ret[] =& $cats[$cid];
            }
            else
            {
                $cats[$cat['parent_id']]['items'][] =& $cats[$cid];
            }
        }
        $this->catMap[$var_depth][$var_cat_id] = $ret;
        return $ret;
    }

    function treeoptions( )
    {
        return array(
            "label" => __( "商品分类" ),
            "actions" => array( "default" => "index.php?ctl=goods/product&act=index&p[0]=", "add" => "index.php?ctl=goods/category&act=addNew&p[0]=", "del" => "index.php?ctl=goods/category&act=toRemove&p[0]=", "edit" => "index.php?ctl=goods/category&act=edit&p[0]=", "view" => "index.php?ctl=goods/category&act=views&p[0]=" )
        );
    }

    function settabs( $catid, $tabs )
    {
        $rs = $this->db->exec( "select tabs,cat_id from sdb_goods_cat where cat_id=".intval( $catid ) );
        if ( $rs )
        {
            $sql = $this->db->getupdatesql( $rs, array(
                "tabs" => $tabs
            ) );
            return !$sql || $this->db->exec( $sql );
        }
        return false;
    }

    function gettabs( $catid )
    {
        $row = $this->db->selectrow( "select tabs,cat_id from sdb_goods_cat where cat_id=".intval( $catid ) );
        return unserialize( $row['tabs'] );
    }

    function getnodes( $catid = null )
    {
        $sqlWhere = " WHERE p.parent_id ".( $catid ? "=".intval( $catid ) : "IS NULL OR p.parent_id = 0" );
        return $this->db->select( "SELECT p.cat_name as text,p.cat_id as id,c.cat_id as has_child FROM sdb_goods_cat p left join sdb_goods_cat c on c.parent_id=p.cat_id".$sqlWhere." group by(p.cat_id) order by p.p_order, p.cat_id" );
    }

    function getcat( $catid = 0 )
    {
        $sqlWhere = " WHERE parent_id = ".intval( $catid );
        return $this->db->select( "SELECT * FROM sdb_goods_cat".$sqlWhere );
    }

    function updateorder( $p_order )
    {
        foreach ( $p_order as $k => $v )
        {
            $this->db->exec( "update sdb_goods_cat set p_order=".intval( $v )." where cat_id=".intval( $k ) );
        }
        $this->cat2json( );
        return true;
    }

    function gettree( )
    {
        return $this->db->select( "SELECT o.cat_name AS text,o.cat_id AS id,o.parent_id AS pid,o.p_order,o.cat_path,\r\n                    is_leaf,o.type_id as type,o.child_count,t.name as type_name FROM sdb_goods_cat o\r\n                    LEFT JOIN sdb_goods_type t on t.type_id=o.type_id ORDER BY o.p_order,o.cat_id" );
    }

    function gettreelist( $pid = 0, $listMark = "all" )
    {
        $var_pid = $pid;
        $var_listMark = $listMark;
        if ( isset( $this->catTree[$var_pid][$var_listMark] ) )
        {
            return $this->catTree[$var_pid][$var_listMark];
        }
        if ( $listMark == "all" )
        {
            $aCat = $this->db->select( "SELECT cat_name,cat_id,o.parent_id AS pid,o.p_order,o.cat_path,o.is_leaf AS cls,o.type_id as type\r\n                    FROM sdb_goods_cat o WHERE o.disabled='false' ORDER BY o.cat_path,o.p_order,o.cat_id" );
        }
        else
        {
            if ( $pid === 0 )
            {
                $sqlWhere = "(parent_id IS NULL OR parent_id=".intval( $pid ).")";
            }
            else
            {
                $sqlWhere = "parent_id=".intval( $pid );
            }
            $sqlWhere .= " AND o.disabled='false'";
            $aCat = $this->db->select( "SELECT cat_name, cat_id, o.parent_id AS pid, o.p_order, o.cat_path, o.is_leaf AS cls,o.type_id, t.name AS type_name FROM sdb_goods_cat o\r\n                    LEFT JOIN sdb_goods_type t ON o.type_id = t.type_id\r\n                    WHERE ".$sqlWhere." ORDER BY o.cat_path,o.p_order,o.cat_id" );
            foreach ( $aCat as $k => $row )
            {
                $aCat[$k]['pid'] = intval( $aCat[$k]['pid'] );
                if ( $row['cat_path'] == "" || $row['cat_path'] == "," )
                {
                    $aCat[$k]['step'] = 1;
                }
                else
                {
                    $aCat[$k]['step'] = substr_count( $row['cat_path'], "," ) + 1;
                }
                $aCat[$k]['url'] = $this->system->realurl( "gallery", $this->system->getconf( "gallery.default_view" ), array(
                    $aCat[$k]['cat_id']
                ), null, $this->system->base_url( ) );
            }
        }
        $this->catTree[$var_pid][$var_listMark] = $aCat;
        return $aCat;
    }

    function getmaptree( $ss = 0, $str = "└" )
    {
        $var_ss = $ss;
        $var_str = $str;
        if ( isset( $this->catMapTree[$var_ss][$var_str] ) )
        {
            return $this->catMapTree[$var_ss][$var_str];
        }
        $retCat = $this->map( $this->gettree( ), $ss, $str, $no, $num );
        $this->catMapTree[$var_ss][$var_str] = $retCat;
        global $step;
        global $cat;
        $step = "";
        $cat = array( );
        return $retCat;
    }

    function checktreesize( )
    {
        $aCount = $this->db->selectrow( "SELECT count(*) AS rowNum FROM sdb_goods_cat" );
        if ( 100 < $aCount['rowNum'] )
        {
            return false;
        }
        return true;
    }

    function getcatparentbyid( $id, $view = "index" )
    {
        if ( !$id )
        {
            return false;
        }
        if ( is_array( $id ) )
        {
            if ( implode( $id, " , " ) === "" )
            {
                return false;
            }
            $sqlString = "SELECT cat_id,cat_name FROM sdb_goods_cat WHERE parent_id in (".implode( $id, " , " ).") order by p_order,cat_id desc";
        }
        else
        {
            $sqlString = "SELECT cat_id,cat_name FROM sdb_goods_cat WHERE parent_id = ".$id." order by p_order,cat_id desc";
        }
        $default_view = $view ? $view : $this->system->getconf( "gallery.default_view" );
        $result = $this->db->select( $sqlString );
        foreach ( $result as $cat_key => $cat_value )
        {
            $result[$cat_key]['link'] = $this->system->mkurl( "gallery", $default_view, array(
                $cat_value['cat_id']
            ) );
        }
        return $result;
    }

    function getfieldbyid( $id, $aField = array
    (
        0 => "*"
    ) )
    {
        if ( is_array( $id ) )
        {
            $sqlString = "SELECT ".implode( ",", $aField )." FROM sdb_goods_cat WHERE cat_id in (".implode( $id, " , " ).")";
            return $this->db->select( $sqlString );
        }
        $sqlString = "SELECT ".implode( ",", $aField )." FROM sdb_goods_cat WHERE cat_id = ".intval( $id );
        return $this->db->selectrow( $sqlString );
    }

    function updatechildcount( $id, $cat_id = false )
    {
        if ( !$id )
        {
            return false;
        }
        $row = $this->db->selectrow( "SELECT count(*) AS num FROM sdb_goods_cat WHERE parent_id=".intval( $id ) );
        $aData['child_count'] = $row['num'];
        if ( $row['num'] )
        {
            $aData['is_leaf'] = "false";
        }
        else
        {
            $aData['is_leaf'] = "true";
        }
        $rs = $this->db->exec( "SELECT * FROM sdb_goods_cat WHERE cat_id=".intval( $id ) );
        $sql = $this->db->getupdatesql( $rs, $aData );
        if ( !$sql && $this->db->exec( $sql ) )
        {
            return $id;
        }
        return false;
    }

    function addnew( $data )
    {
        $oTemplate = $this->system->loadmodel( "system/template" );
        $data['parent_id'] = intval( $data['parent_id'] );
        $data['addon']['meta']['title'] = htmlspecialchars( $data['title'] );
        $data['addon']['meta']['keywords'] = htmlspecialchars( $data['keywords'] );
        $data['addon']['meta']['description'] = htmlspecialchars( $data['description'] );
        $parent_id = $data['parent_id'];
        $path = array( );
        while ( $parent_id )
        {
            if ( $data['cat_id'] && $data['cat_id'] == $parent_id )
            {
                return false;
            }
            array_unshift( $path, $parent_id );
            $row = $this->db->selectrow( "SELECT parent_id, cat_path, p_order FROM sdb_goods_cat WHERE cat_id=".intval( $parent_id ) );
            $parent_id = $row['parent_id'];
        }
        $data['cat_path'] = implode( ",", $path ).",";
        $oseo =& $this->system->loadmodel( "system/seo" );
        $aData = array(
            "keywords" => $data['addon']['meta']['keywords'],
            "descript" => $data['addon']['meta']['description'],
            "title" => $data['addon']['meta']['title']
        );
        if ( $data['cat_id'] )
        {
            $oseo->set_seo( "goods_cat", $data['cat_id'], $aData );
            $sDefine = $this->db->selectrow( "SELECT parent_id FROM sdb_goods_cat WHERE cat_id=".intval( $data['cat_id'] ) );
            $rs = $this->db->exec( "SELECT * FROM sdb_goods_cat WHERE cat_id=".$data['cat_id'] );
            $sql = $this->db->getupdatesql( $rs, $data );
            if ( !$sql && $this->db->exec( $sql ) )
            {
                if ( $sDefine['parent_id'] != $data['parent_id'] )
                {
                    $this->updatepath( $data['cat_id'], $data['cat_path'] );
                    $this->updatechildcount( $sDefine['parent_id'] );
                    $this->updatechildcount( $data['parent_id'] );
                }
                $oTemplate->update_template( "cat", $data['cat_id'], $_POST['goodscat_template'], "gallery" );
                $oTemplate->update_template( "cat", $data['cat_id'], $_POST['product_template'], "product" );
                $this->cat2json( );
                return true;
            }
            return false;
        }
        unset( $data->'cat_id' );
        $rs = $this->db->exec( "SELECT * FROM sdb_goods_cat WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $data );
        if ( !$sql && $this->db->exec( $sql ) )
        {
            $cat_id = $this->db->lastinsertid( );
            $oseo->set_seo( "goods_cat", $cat_id, $aData );
            $oTemplate->set_template( "cat", $cat_id, $_POST['goodscat_template'], "gallery" );
            $oTemplate->set_template( "cat", $cat_id, $_POST['product_template'], "product" );
            $this->updatechildcount( $data['parent_id'] );
            $this->cat2json( );
            return true;
        }
        return false;
    }

    function updatepath( $cat_id, $cat_path )
    {
        $result = $this->db->select( "SELECT cat_id,cat_path FROM sdb_goods_cat WHERE cat_path like '".$cat_id.",%' or parent_id=".intval( $cat_id )."" );
        foreach ( $result as $k => $v )
        {
            if ( $cat_path == "," )
            {
                unset( $cat_path );
            }
            $path = $cat_path.substr( $v['cat_path'], strpos( $v['cat_path'], $cat_id."," ), strlen( $v['cat_path'] ) );
            $this->db->exec( "update sdb_goods_cat set cat_path=\"".$path."\" where cat_id=".intval( $v['cat_id'] ) );
        }
    }

    function toremove( $catid )
    {
        $aCats = $this->db->select( "SELECT * FROM sdb_goods_cat WHERE parent_id = ".intval( $catid ) );
        if ( 0 < count( $aCats ) )
        {
            trigger_error( __( "删除失败：本分类下面还有子分类" ), E_USER_ERROR );
            return false;
        }
        $aGoods = $this->db->select( "SELECT goods_id FROM sdb_goods WHERE cat_id = ".intval( $catid )." and disabled=\"false\"" );
        if ( 0 < count( $aGoods ) )
        {
            trigger_error( __( "删除失败：本分类下面还有商品" ), E_USER_ERROR );
            return false;
        }
        $row = $this->db->selectrow( "SELECT parent_id FROM sdb_goods_cat WHERE cat_id=".intval( $catid ) );
        $parent_id = $row['parent_id'];
        $this->db->exec( "DELETE FROM sdb_goods_cat WHERE cat_id=".intval( $catid ) );
        $this->db->exec( "update sdb_goods set cat_id=\"0\" WHERE cat_id=".intval( $catid ) );
        $this->updatechildcount( $parent_id );
        $this->cat2json( );
        return true;
    }

    function get( $cat_id, $view, $type_id = null )
    {
        if ( !function_exists( "gcat_get" ) )
        {
            require( CORE_INCLUDE_DIR."/core/gcat.get.php" );
        }
        return gcat_get( $cat_id, $view, $type_id, $this );
    }

    function deliveryinfo( $aGoodsId, $cat_has_pdt )
    {
        $info = array(
            "custom" => array( )
        );
        $sqlString = "SELECT c.cat_id,t.member_req,t.is_physical FROM sdb_goods g\r\n            LEFT JOIN sdb_goods_cat c ON c.cat_id = g.cat_id\r\n            LEFT JOIN sdb_goods_type t ON c.type_id = t.type_id\r\n            WHERE g.goods_id IN (".implode( ",", $aGoodsId ).") GROUP BY c.cat_id";
        $aDelivery = $this->db->select( $sqlString );
        foreach ( $aDelivery as $cat )
        {
            if ( $req = unserialize( $cat['member_req'] ) )
            {
                if ( $info['custom'][$cat['schema_id']]['infos'] )
                {
                    $info['custom'][$cat['schema_id']]['infos'] = array_merge( $req, $info['custom'][$cat['schema_id']]['infos'] );
                }
                else
                {
                    $info['custom'][$cat['schema_id']]['infos'] = $req;
                }
                $info['custom'][$cat['schema_id']]['products'] =& $cat_has_pdt[$cat['cat_id']];
            }
            if ( $info['physical'] || !$cat['is_physical'] )
            {
                $info['physical'] = true;
            }
        }
        return $info;
    }

    function gettypelist( )
    {
        $sqlString = "SELECT type_id,name FROM sdb_goods_type WHERE disabled = 'false'";
        return $this->db->select( $sqlString );
    }

    function gettypedetail( $catid )
    {
        $sqlString = "SELECT c.cat_name,c.cat_id,t.* FROM sdb_goods_cat c\r\n            LEFT JOIN sdb_goods_type t ON c.type_id = t.type_id\r\n            WHERE c.cat_id = ".intval( $catid );
        $row = $this->db->selectrow( $sqlString );
        $row['props'] = unserialize( $row['props'] );
        $row['setting'] = unserialize( $row['setting'] );
        $row['setting']['use_spec'] = true;
        $row['minfo'] = unserialize( $row['minfo'] );
        $row['params'] = unserialize( $row['params'] );
        $s = 0;
        foreach ( $row['params'] as $g )
        {
            foreach ( $g as $p )
            {
                $s = 1;
            }
        }
        if ( $s == 0 )
        {
            unset( $row->'params' );
        }
        return $row;
    }

    function gettypebyid( $typeid )
    {
        $sqlString = "SELECT * FROM sdb_goods_type WHERE type_id = ".intval( $typeid );
        return $this->db->selectrow( $sqlString );
    }

    function updatetype( $data )
    {
        $rs = $this->db->exec( "select * from sdb_goods_type where type_id=".$data['type_id'] );
        $sql = $this->db->getupdatesql( $rs, $data );
        return !$sql || $this->db->exec( $sql );
    }

    function map( $data, $sID = 0, $preStr = "", &$cat_cuttent, &$step )
    {
        ++$step;
        $baseurl = $this->system->base_url( );
        $default_view = $this->system->getconf( "gallery.default_view" );
        if ( $data )
        {
            foreach ( $data as $i => $value )
            {
                $id = $data[$i]['id'];
                $cls = $data[$i]['child_count'] ? "true" : "false";
                $link = $this->system->realurl( "gallery", $default_view, array(
                    $id
                ), "html", $baseurl );
                if ( !$sID )
                {
                    if ( empty( $data[$i]['pid'] ) )
                    {
                        $cat_cuttent[] = array(
                            "cat_name" => $data[$i]['text'],
                            "cat_id" => $data[$i]['id'],
                            "pid" => $data[$i]['pid'],
                            "type" => $data[$i]['type'],
                            "type_name" => $data[$i]['type_name'],
                            "step" => $step,
                            "p_order" => $data[$i]['p_order'],
                            "cat_path" => $data[$i]['cat_path'],
                            "cls" => $cls,
                            "url" => $link
                        );
                        unset( $data->$i );
                        $this->map( $data, $id, $preStr, $cat_cuttent, $step );
                    }
                }
                else if ( $sID == $data[$i]['pid'] )
                {
                    $cat_cuttent[] = array(
                        "cat_name" => $data[$i]['text'],
                        "cat_id" => $data[$i]['id'],
                        "pid" => $data[$i]['pid'],
                        "type" => $data[$i]['type'],
                        "type_name" => $data[$i]['type_name'],
                        "step" => $step,
                        "p_order" => $data[$i]['p_order'],
                        "cat_path" => $data[$i]['cat_path'],
                        "cls" => $cls,
                        "url" => $link
                    );
                    unset( $data->$i );
                    $this->map( $data, $id, $preStr, $cat_cuttent, $step );
                }
            }
        }
        --$step;
        return $cat_cuttent;
    }

    function del( $id, &$msg )
    {
        if ( $this->getcat( $id ) )
        {
            $msg = __( "当前目录下有子目录，不允许删除" );
            return false;
        }
        return $this->db->exec( "delete from sdb_goods_cat where cat_id=".$id );
    }

    function getcatidbyalias( $alias )
    {
        $alias = trim( $alias );
        if ( $alias )
        {
            if ( strstr( $alias, "->" ) )
            {
                $aCatName = explode( "->", $alias );
                $cat_name = $aCatName[count( $aCatName ) - 1];
                $sql = "SELECT cat_id,parent_id,cat_name,cat_path FROM sdb_goods_cat WHERE cat_name = '".$cat_name."'";
                $aRows = $this->db->select( $sql );
                if ( count( $aRows ) == 1 )
                {
                    return $aRows[0]['cat_id'];
                }
                foreach ( $aRows as $k => $row )
                {
                    $errStatus = false;
                    $aTmp = explode( ",", $row['cat_path'] );
                    $aId = array( );
                    foreach ( $aTmp as $cid )
                    {
                        if ( $cid )
                        {
                            $aId[] = $cid;
                        }
                    }
                    if ( !( count( $aId ) == count( $aCatName ) - 1 ) )
                    {
                        continue;
                    }
                    $iLoop = 0;
                    foreach ( $aId as $i => $catid )
                    {
                        $sql = "SELECT count(*) AS num FROM sdb_goods_cat WHERE cat_name = '".$aCatName[$i]."' AND cat_id =".$catid;
                        $aTmp = $this->db->selectrow( $sql );
                        if ( !$aTmp['num'] )
                        {
                            break;
                        }
                        ++$iLoop;
                    }
                    if ( !( count( $aId ) == $iLoop ) )
                    {
                        continue;
                    }
                    $cat_id = $row['cat_id'];
                    break;
                }
                if ( $cat_id )
                {
                    return $cat_id;
                }
                return false;
            }
            $sql = "SELECT cat_id FROM sdb_goods_cat WHERE cat_name = '".$alias."'";
            $row = $this->db->selectrow( $sql );
            if ( $row['cat_id'] )
            {
                return $row['cat_id'];
            }
            return false;
        }
    }

    function getnamepathbyid( $catId )
    {
        $aRet = $this->db->selectrow( "SELECT cat_path,cat_name FROM sdb_goods_cat WHERE cat_id =".$catId );
        if ( $aRet )
        {
            if ( $aRet['cat_path'] == "," || $aRet['cat_path'] == "" )
            {
                return $aRet['cat_name'];
            }
            $catPath = substr( $aRet['cat_path'], 0, strlen( $aRet['cat_path'] ) - 1 );
            $sql = "SELECT cat_id,cat_name FROM sdb_goods_cat WHERE cat_id IN('".$catPath."') ORDER BY cat_path";
            foreach ( $this->db->select( $sql ) as $k => $row )
            {
                $namePath .= $row['cat_name']."->";
            }
            return $namePath.$aRet['cat_name'];
        }
    }

    function propsort( $prop = array( ) )
    {
        if ( is_array( $prop ) )
        {
            foreach ( $prop as $key => $val )
            {
                $tmpP[$val['ordernum']] = $key;
            }
            ksort( $tmpP );
            return $tmpP;
        }
    }

}

?>
