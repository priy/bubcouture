<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_sitemap extends modelfactory
{

    function update( )
    {
        $items = $this->db->select( "select * from sdb_sitemaps" );
        if ( count( $items ) == 0 )
        {
            $xml =& $this->system->loadmodel( "utility/xml" );
            $map = $xml->xml2arrayvalues( file_get_contents( CORE_DIR."/site.xml" ) );
            foreach ( $items as $k => $item )
            {
                $list[$item['node_type'].":".$item['action'].":".$item['item_id']] =& $items[$k];
            }
            $rows = array( );
            $this->_mkrows( $list, $map['site']['item'], 0, 0 );
            $toRemove = array( );
            foreach ( $list as $item )
            {
                if ( $item['manual'] == "1" || $item['keep'] )
                {
                    if ( $item['move'] )
                    {
                        $sql = "update sdb_sitemaps set p_node_id=".intval( $item['move'] )." where node_id=".intval( $item['node_id'] );
                        $this->db->exec( $sql );
                    }
                }
                else
                {
                    $toRemove[$item['node_id']] = $item['p_node_id'];
                }
            }
            foreach ( $toRemove as $k => $item )
            {
                while ( $toRemove[$toRemove[$k]] )
                {
                    $toRemove[$k] = $toRemove[$toRemove[$k]];
                }
            }
            if ( 0 < count( $toRemove ) )
            {
                $sql = "delete from sdb_sitemaps where node_id in(".implode( ",", array_keys( $toRemove ) ).")";
                $this->db->exec( $sql );
                foreach ( $toRemove as $k => $v )
                {
                    $this->db->exec( "update sdb_sitemaps set p_node_id=".intval( $v )." where p_node_id=".intval( $k ) );
                }
            }
        }
        return $this->getlist( );
    }

    function getlinknode( )
    {
        $return = array( );
        foreach ( $this->db->select( "select * from sdb_sitemaps where node_type='page' or node_type='articles' order by p_order,path" ) as $row )
        {
            $return[$row['node_type']][] = $row;
        }
        return $return;
    }

    function checkdel( $nodid, &$string )
    {
        $nodid = intval( $nodid );
        $row = $this->db->selectrow( "select node_type from sdb_sitemaps where node_id=\"".$nodid."\"" );
        if ( $row['node_type'] == "articles" )
        {
            if ( $this->db->selectrow( "select * from sdb_articles where node_id=".$nodid ) )
            {
                $string = __( "该文档栏目下面还有文章，不能删除该栏目" );
                return false;
            }
            return true;
        }
        if ( $this->db->selectrow( "select node_id from sdb_sitemaps where p_node_id=\"".$nodid."\"" ) )
        {
            return false;
        }
        return true;
    }

    function getparent( $nodid )
    {
        $rows = $this->db->select( "select * from sdb_sitemaps where p_node_id=".$nodid );
        return $rows;
    }

    function gettitlebyaction( $action )
    {
        return $this->db->selectrow( "select title from sdb_sitemaps where action=\"".$action."\"" );
    }

    function getlist( $nodid = "" )
    {
        $rows = $this->getresult( $nodid );
        foreach ( $rows as $k => $row )
        {
            $this->_apply( $rows[$k] );
            if ( 0 == $row['p_node_id'] )
            {
                $map[] =& $rows[$k];
            }
            $list[$row['node_id']] =& $rows[$k];
        }
        foreach ( $rows as $k => $row )
        {
            $list[$row['p_node_id']]['items'][] =& $rows[$k];
        }
        $this->_p_order( $ret, $map );
        return $ret;
    }

    function getresult( $nodid = "" )
    {
        if ( $nodid )
        {
            $rows = $this->db->select( "select * from sdb_sitemaps where node_id=".$nodid );
            return $rows;
        }
        $rows = $this->db->select( "select * from sdb_sitemaps order by p_order,path" );
        return $rows;
    }

    function getnownod( $nodid = "" )
    {
        if ( $nodid )
        {
            $rows = $this->db->select( "select * from sdb_sitemaps where node_id=".intval( $nodid ) );
        }
        return $rows;
    }

    function _p_order( &$list, &$map )
    {
        foreach ( $map as $k => $item )
        {
            unset( $item->'items' );
            $list[] = $item;
            if ( $map[$k]['items'] )
            {
                $this->_p_order( $list, $map[$k]['items'] );
            }
        }
    }

    function _mkrows( &$list, $items, $parent_node_id = null, $depth = null, $path = null )
    {
        $duration = array( 0 => 100, 1 => 150 );
        foreach ( $items as $i => $item )
        {
            $row = array(
                "node_type" => $item['attr']['type'],
                "title" => $item['attr']['title'],
                "hidden" => $item['attr']['hidden'] == "true" ? "true" : "false",
                "p_node_id" => $parent_node_id,
                "manual" => 0,
                "depth" => $depth,
                "path" => $path,
                "p_order" => $i
            );
            if ( $item['attr']['node_id'] )
            {
                $row['node_id'] = $item['attr']['node_id'];
            }
            switch ( $item['attr']['type'] )
            {
            case "page" :
                $row['action'] = "page:".$item['attr']['id'];
                break;
            case "goodsCat" :
                $row['action'] = $item['attr']['filter'];
                $row['item_id'] = $item['attr']['id'];
                break;
            case "articles" :
                $row['action'] = "artlist:index";
                $row['item_id'] = $item['attr']['node_id'];
                break;
            case "action" :
                $row['action'] = $item['attr']['ctl'].":".$item['attr']['act'];
                break;
            case "pageurl" :
                $row['action'] = $item['attr']['ctl'];
            }
            $node =& $list[$row['node_type'].":".$row['action'].":".$row['item_id']];
            if ( !$node )
            {
                $rs = $this->db->exec( "select * from sdb_sitemaps where 0=1" );
                $sql = $this->db->getinsertsql( $rs, $row );
                $this->db->exec( $sql );
                $row['node_id'] = $this->db->lastinsertid( );
                $node = $row;
            }
            else if ( $parent_node_id != $node['p_node_id'] )
            {
                $node['move'] = $parent_node_id;
            }
            $node['keep'] = true;
            if ( !$item['item'] && !( 0 < count( $item['item'] ) ) )
            {
                if ( $item['item']['attr'] )
                {
                    $this->_mkrows( $list, array(
                        $item['item']
                    ), $node['node_id'], $node['depth'] + 1, $node['path'].$node['node_id']."," );
                }
                else
                {
                    $this->_mkrows( $list, $item['item'], $node['node_id'], $node['depth'] + 1, $node['path'].$node['node_id']."," );
                }
            }
        }
    }

    function getdefinemap( $nowId, $treenum, $treelistnum )
    {
        if ( !isset( $this->__link_map ) )
        {
            $rows = $this->db->select( "select * from sdb_sitemaps order by path,p_order" );
            foreach ( $rows as $k => $row )
            {
                $this->_apply( $rows[$k] );
                if ( 0 == $row['p_node_id'] )
                {
                    $map[$row['node_id']]['label'] = $row['title'];
                    $map[$row['node_id']]['link'] = $rows[$k]['link'];
                    $map[$row['node_id']]['hidden'] = $rows[$k]['hidden'];
                    $map[$row['node_id']]['item_id'] = $rows[$k]['item_id'];
                    $map[$row['node_id']]['depth'] = 0;
                    $link[$row['node_id']] =& $map[$row['node_id']];
                }
                else
                {
                    $link[$row['p_node_id']]['sub'][$row['node_id']]['label'] = $row['title'];
                    $link[$row['p_node_id']]['sub'][$row['node_id']]['depth'] = $row['depth'];
                    $link[$row['p_node_id']]['sub'][$row['node_id']]['hidden'] = $row['hidden'];
                    $link[$row['p_node_id']]['sub'][$row['node_id']]['item_id'] = $row['item_id'];
                    $link[$row['p_node_id']]['sub'][$row['node_id']]['link'] = $rows[$k]['link'];
                    $link[$row['node_id']] =& $link[$row['p_node_id']]['sub'][$row['node_id']];
                }
            }
            $this->__link_map =& $link;
        }
        if ( $treelistnum )
        {
            $return = $this->__link_map[$treelistnum];
            return $return;
        }
        $return = $this->__link_map[$nowId[count( $nowId ) - 1]['node_id']];
        return $return;
    }

    function getmap( $depth = -1, $root = 0, $type = null )
    {
        if ( !$depth )
        {
            $depth = -1;
        }
        $where = defined( "IN_SHOP" ) ? "hidden!=\"true\" and" : "";
        if ( 0 < $depth )
        {
            $rows = $this->db->select( "select * from sdb_sitemaps where ".$where." depth<".$depth." order by p_order asc" );
        }
        else
        {
            $rows = $this->db->select( "select * from sdb_sitemaps where ".$where." 1=1 order by path,p_order asc" );
        }
        foreach ( $rows as $k => $row )
        {
            if ( $root == $row['p_node_id'] )
            {
                $ret[] =& $rows[$k];
            }
            $list[$row['node_id']] =& $rows[$k];
            $this->_apply( $rows[$k], $depth - $row['depth'] - 1 );
        }
        foreach ( $rows as $k => $row )
        {
            $list[$row['p_node_id']]['items'][] =& $rows[$k];
        }
        return $ret;
    }

    function &_mkfilter( $filter )
    {
        parse_str( $filter, $filter );
        if ( $filter['type_id'] )
        {
            $filter['type_id'] = array(
                $filter['type_id']
            );
        }
        if ( !is_array( $filter['cat_id'] ) )
        {
            $filter['cat_id'] = array(
                $filter['cat_id']
            );
        }
        if ( $filter['props'] )
        {
            foreach ( $filter['props'] as $k => $v )
            {
                if ( $v != "_ANY_" )
                {
                    $filter["p_".$k] = $v;
                }
            }
        }
        $filter['price'][0] = $filter['pricefrom'] ? $filter['pricefrom'] : 0;
        $filter['price'][1] = $filter['priceto'];
        $filter['name'][0] = $filter['searchname'];
        return $filter;
    }

    function _apply( &$item, $depth = -1 )
    {
        $pos = strpos( $item['action'], ":" );
        switch ( $item['node_type'] )
        {
        case "action" :
            $item['link'] = $this->system->realurl( substr( $item['action'], 0, $pos ), substr( $item['action'], $pos + 1 ), $item['item_id'] ? array(
                $item['item_id']
            ) : null, null, $this->system->base_url( ) );
            break;
        case "goodsCat" :
            $searchtools =& $this->system->loadmodel( "goods/search" );
            $filter = $this->_mkfilter( $item['action'] );
            $cat_id = implode( $filter['cat_id'], "," );
            $item['link'] = $this->system->realurl( "gallery", $this->system->getconf( "gallery.default_view" ), array(
                $cat_id,
                $searchtools->encode( $filter )
            ), null, $this->system->base_url( ) );
            break;
        case "articles" :
            $item['link'] = $this->system->realurl( substr( $item['action'], 0, $pos ), substr( $item['action'], $pos + 1 ), array(
                $item['node_id']
            ), null, $this->system->base_url( ) );
            break;
        case "page" :
            $item['link'] = $this->system->realurl( "page", substr( $item['action'], $pos + 1 ), array( ), null, $this->system->base_url( ) );
            break;
        case "pageurl" :
            if ( $item['action'] == "?" )
            {
                $item['action'] = $this->system->realurl( "index" );
            }
            $item['link'] = $item['action'];
            break;
        case "custompage" :
            $item['link'] = $this->system->realurl( "custompage", "index", array(
                $item['node_id']
            ) );
        }
    }

    function getpathbyid( $node_id, $showtime = true )
    {
        return $this->_getpath( array(
            "node_id" => $node_id
        ), null, $showtime );
    }

    function getpath( $type, $info, $method = "index" )
    {
        if ( $type == "goods" )
        {
            $path = $this->_getpath( array( "node_type" => "goodsCat" ) );
            $goods =& $this->system->loadmodel( "goods/products" );
            $path = array_merge( $path, $goods->getpath( $info, $method ) );
        }
        else if ( $type == "goodsCat" )
        {
            $goods =& $this->system->loadmodel( "goods/productCat" );
            $path = $this->_getpath( array( "node_type" => "goodsCat" ), $method );
            if ( $info != 0 )
            {
                $path = array_merge( $path, $goods->getpath( $info, $method ) );
            }
        }
        else if ( $type == "virtualcat" )
        {
            $goods =& $this->system->loadmodel( "goods/virtualcat" );
            $path = $this->_getpath( array( "node_type" => "virtualcat" ), $method );
            if ( $info != 0 )
            {
                $path = array_merge( $path, $goods->getpath( $info, $method ) );
            }
        }
        else if ( $type == "articles" )
        {
            $article =& $this->system->loadmodel( "content/article" );
            $result = $article->get( $info['node_id'] );
            if ( $result )
            {
                if ( $result['node_id'] )
                {
                    $article_info = $this->getnownod( $result['node_id'] );
                    $path[] = array(
                        "title" => $article_info[0]['title'],
                        "link" => $this->system->mkurl( "artlist", "index", array(
                            $result['node_id']
                        ) )
                    );
                }
            }
            else
            {
                $path = array( );
            }
        }
        else
        {
            $path = $this->_getpath( $info );
        }
        $return =& $path;
        if ( count( $return ) == 1 && $return[0]['link'] == $this->system->request['base_url'] )
        {
            $return = array( );
            return $return;
        }
        array_unshift( $return, array(
            "title" => __( "首页" ),
            "link" => $this->system->request['base_url']
        ) );
        return $return;
    }

    function _getpath( $info, $method = false, $showtime = true )
    {
        foreach ( $info as $k => $v )
        {
            if ( $k == "item_id" )
            {
                $item[] = "item_id=".intval( $v )." or item_id=null";
            }
            else
            {
                $item[] = $k."=\"".$v."\"";
            }
        }
        if ( count( $item ) == 1 && !is_array( $item[0] ) || $showtime )
        {
            return array( );
        }
        $nav = array( );
        $row = $this->db->selectrow( "select node_type,title,action,path,node_id,action from sdb_sitemaps where ".implode( "and", $item ) );
        if ( $row['path'] )
        {
            $path = $this->db->select( "select node_type,title,action,path,node_id,action from sdb_sitemaps where node_id in(".substr( $row['path'], 0, -1 ).") order by depth" );
            $path[] = $row;
        }
        else
        {
            $path = array(
                $row
            );
        }
        if ( $path )
        {
            foreach ( $path as $k => $p )
            {
                switch ( $p['node_type'] )
                {
                case "goodsCat" :
                    $p['link'] = $this->system->mkurl( "gallery", $method ? $method : $this->system->getconf( "gallery.default_view" ), array(
                        $p['item_id']
                    ) );
                    break;
                case "action" :
                    $p['link'] = $this->system->mkurl( $p['ctl'], $p['act'], array(
                        $p['item_id']
                    ) );
                    break;
                default :
                    $pos = strpos( $p['action'], ":" );
                    $p['link'] = $this->system->mkurl( substr( $p['action'], 0, $pos ), substr( $p['action'], $pos + 1 ) );
                }
                $nav[] = $p;
            }
        }
        return $nav;
    }

    function updatechildcount( $node_id = false )
    {
        if ( $node_id )
        {
            $row = $this->db->selectrow( "SELECT count(*) AS num FROM sdb_sitemaps WHERE p_node_id=".intval( $node_id ) );
            $aData['child_count'] = $row['num'];
            $rs = $this->db->exec( "SELECT * FROM sdb_sitemaps WHERE node_id=".intval( $node_id ) );
            $sql = $this->db->getupdatesql( $rs, $aData );
            if ( !$sql && $this->db->exec( $sql ) )
            {
                return true;
            }
            return false;
        }
    }

    function newnode( $data )
    {
        $oTemplate = $this->system->loadmodel( "system/template" );
        $data['manual'] = "1";
        if ( $data['p_node_id'] )
        {
            $rs = $this->db->exec( "select * from sdb_sitemaps where node_id=".intval( $data['p_node_id'] ) );
            $result = $this->db->selectrow( "select * from sdb_sitemaps where node_id=".intval( $data['p_node_id'] ) );
            $data['depth'] = $result['depth'] + 1;
            $data['path'] = $result['path'].$data['p_node_id'].",";
        }
        else
        {
            $data['p_node_id'] = 0;
            $data['depth'] = 0;
            $rs = $this->db->exec( "select * from sdb_sitemaps where 0=1" );
        }
        $row = $this->db->selectrow( "select max(p_order) as max_p_order from sdb_sitemaps where p_node_id=".intval( $data['p_node_id'] ) );
        $data['p_order'] = $row['max_p_order'] + 1;
        $sql = $this->db->getinsertsql( $rs, $data );
        if ( $this->db->exec( $sql ) )
        {
            $data['node_id'] = $this->db->lastinsertid( );
            if ( $data['node_type'] == "articles" )
            {
                $data['node_type'] = "artlist";
                $oTemplate->set_template( $data['node_type'], $data['node_id'], $_POST['artlist_template'], "artlist" );
            }
            if ( $data['node_type'] == "page" )
            {
                $oTemplate->set_template( $data['node_type'], $data['node_id'], $_POST['singlepage_template'], "page" );
            }
            $this->updatechildcount( $data['p_node_id'] );
            if ( $data['node_type'] == "custompage" )
            {
                $tmpl =& $this->system->loadmodel( "content/systmpl" );
                $tmpl->updatecontent( md5( $data['node_id'] ), "[header][footer]" );
            }
            return $data;
        }
        return false;
    }

    function title2page( $title )
    {
        return str_replace( "-", "_", $title );
    }

    function save( $node_id, $info )
    {
        if ( $info['p_node_id'] )
        {
            $p_node = $this->db->selectrow( "select * from sdb_sitemaps where node_id=".intval( $info['p_node_id'] ) );
        }
        else
        {
            $p_node = array( "depth" => -1 );
        }
        if ( $rs = $this->db->exec( "select * from sdb_sitemaps where node_id=".intval( $node_id ) ) )
        {
            $row = $this->db->getrows( $rs, 1 );
            $p_path = 0 < $p_node['node_id'] ? $p_node['path'].$p_node['node_id']."," : "";
            $sql = $this->db->getupdatesql( $rs, array(
                "title" => $info['title'],
                "depth" => $p_node['depth'] + 1,
                "path" => $p_path,
                "hidden" => $info['display'] ? "false" : "true",
                "p_node_id" => $info['p_node_id'],
                "item_id" => $info['item_id'] ? "1" : "0"
            ) );
            if ( !$sql )
            {
                return true;
            }
            if ( !$this->db->exec( $sql ) )
            {
                return false;
            }
            if ( $info['p_node_id'] != $row[0]['p_node_id'] )
            {
                $this->updatechildcount( $info['p_node_id'] );
                $this->updatechildcount( $row[0]['p_node_id'] );
                $depthDiff = $p_node['depth'] - $row[0]['depth'] + 1;
                $pathLength = strlen( $row[0]['path'] ) + 1;
                $depthDiff += 0;
                $sql = "update sdb_sitemaps set depth=depth".( 0 <= $depthDiff ? "+".$depthDiff : $depthDiff )."\n                    ,path = CONCAT('".$this->db->quote( $p_path )."',SUBSTRING(path FROM ".$pathLength."))\n                     where path like '".$this->db->quote( $row[0]['path'].$row[0]['node_id'] ).",%'";
                $this->db->exec( $sql );
            }
            return true;
        }
        return false;
    }

    function remove( $node_id )
    {
        $node_id = intval( $node_id );
        if ( $row = $this->db->selectrow( "select p_node_id,action,node_type,title from sdb_sitemaps where node_id=".intval( $node_id ) ) )
        {
            $this->db->exec( "update sdb_sitemaps set p_node_id=".intval( $row['p_node_id'] )." where p_node_id=".intval( $node_id ) );
            if ( $row['node_type'] == "page" )
            {
                $page_ident = $this->title2page( substr( $row['action'], 5 ) );
                $this->db->exec( "delete from sdb_pages where page_name=\"".$page_ident."\"" );
                $this->db->exec( "delete from sdb_widgets_set where base_file=\"".$page_ident."\"" );
            }
            if ( $this->db->exec( "delete from sdb_sitemaps where node_id =".intval( $node_id ) ) )
            {
                $this->updatechildcount( $row['p_node_id'] );
                return true;
            }
            return false;
        }
        return true;
    }

    function settitle( $node_id, $title )
    {
        if ( $rs = $this->db->exec( "select title from sdb_sitemaps where node_id=".intval( $node_id ) ) )
        {
            $sql = $this->db->getupdatesql( $rs, array(
                "title" => $title
            ) );
            return !$sql || $this->db->exec( $sql );
        }
        return false;
    }

    function setaction( $node_id, $actions )
    {
        if ( $rs = $this->db->exec( "select action,item_id from sdb_sitemaps where node_id=".intval( $node_id ) ) )
        {
            if ( is_array( $actions ) )
            {
                if ( empty( $actions['item_id'] ) )
                {
                    $actions['item_id'] = 0;
                }
                $sql = $this->db->getupdatesql( $rs, array(
                    "action" => $actions['action'],
                    "item_id" => $actions['item_id']
                ) );
            }
            else
            {
                $sql = $this->db->getupdatesql( $rs, array(
                    "action" => $actions
                ) );
            }
            return !$sql || $this->db->exec( $sql );
        }
        return false;
    }

    function getnode( $node_id )
    {
        return $this->db->selectrow( "select * from sdb_sitemaps where node_id='".intval( $node_id )."'" );
    }

    function _walkactions( &$action, $map )
    {
        foreach ( $map as $k => $v )
        {
            if ( $v['attr']['type'] == "action" )
            {
                $action[] = $v['attr'];
            }
            if ( $v['item'] )
            {
                $this->_walkactions( $action, $v['item'] );
            }
        }
    }

    function actions( )
    {
        $xml =& $this->system->loadmodel( "utility/xml" );
        $map = $xml->xml2arrayvalues( file_get_contents( CORE_DIR."/site.xml" ) );
        foreach ( $items as $k => $item )
        {
            $list[$item['node_type'].":".$item['action'].":".$item['item_id']] =& $items[$k];
        }
        $this->_walkactions( $actions, $map['site']['item'] );
        return $actions;
    }

    function getactions( )
    {
        foreach ( $this->db->select( "select node_id,action from sdb_sitemaps" ) as $item )
        {
            $actions[$item['action']] = $item['node_id'];
        }
        $ret = array( );
        foreach ( $this->actions( ) as $a )
        {
            if ( !isset( $actions[$a['ctl'].":".$a['act']] ) )
            {
                $ret[$a['ctl'].":".$a['act']] = $a['title'];
            }
        }
        return $ret;
    }

    function genelist( $array, $_key )
    {
        $content = "";
        $tmpdata = $this->db->selectrow( "select max(d_order) as num_g from sdb_goods" );
        $i = 0;
        for ( ; $i < count( $array ); ++$i )
        {
            $content .= "<url>";
            $content .= "<loc>".$this->system->mkurl( $_key[1], "index", array(
                $array[$i][$_key[0]]
            ) )."</loc>";
            if ( $_key[4] )
            {
                $content .= "<lastmod>".date( "Y-m-d", $array[$i]['last_modify'] )."</lastmod>";
            }
            else
            {
                $content .= "<lastmod>".date( "Y-m-d" )."</lastmod>";
            }
            $content .= "<changefreq>".$_key[3]."</changefreq>";
            $num = number_format( $array[$i][$_key[2]] / $tmpdata['num_g'], 1 );
            if ( 1 < $num )
            {
                $num = number_format( 1, 1 );
            }
            $content .= "<priority>".$num."</priority>";
            $content .= "</url>";
        }
        return $content;
    }

    function generatecatalog( $count )
    {
        $devide = 1000;
        $i = 0;
        for ( ; $i <= ceil( $count / $devide ); ++$i )
        {
            $content .= "<sitemap>";
            $content .= "<loc>".$this->system->mkurl( "sitemaps", "index", array(
                $i
            ), "xml" )."</loc>";
            $content .= "</sitemap>";
        }
        return $content;
    }

    function map( )
    {
    }

    function updateporder( $pordAry )
    {
        if ( $pordAry )
        {
            foreach ( $pordAry as $key => $val )
            {
                $sqlString = "Update sdb_sitemaps set p_order=".intval( $val )." where node_id=".intval( $key );
                $this->db->exec( $sqlString );
            }
            return true;
        }
        return false;
    }

    function setvisibility( $node_id, $status )
    {
        $sql = "update sdb_sitemaps set hidden='".( $status ? "false" : "true" )."' where node_id=".intval( $node_id );
        return $this->db->exec( $sql );
    }

}

?>
