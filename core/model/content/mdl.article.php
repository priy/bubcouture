<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_article extends shopobject
{

    var $idColumn = "article_id";
    var $textColumn = "title";
    var $adminCtl = "content/articles";
    var $defaultCols = "title,uptime,ifpub,node_id,_preview,pubtime";
    var $defaultOrder = array
    (
        0 => "uptime",
        1 => "desc"
    );
    var $tableName = "sdb_articles";

    function getfilter( )
    {
        $filter['article_cat'] = $this->getarticlecat( );
        return $filter;
    }

    function getcolumns( )
    {
        $ret = array(
            "_preview" => array(
                "label" => __( "预览" ),
                "width" => 75,
                "html" => "content/article/preview.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function modifier_node_id( &$rows )
    {
        $rst = $this->db->select( "select title,node_id from sdb_sitemaps where node_id in(".implode( ",", $rows ).")" );
        foreach ( $rst as $r )
        {
            $rows[$r['node_id']] = $r['title'];
        }
    }

    function _filter( $aFilter )
    {
        if ( $aFilter['title'] != "" )
        {
            $aFilter['title'] = addslashes( $aFilter['title'] );
            $where[] = "title LIKE '".$aFilter['title']."%'";
        }
        $ndata = $this->db->select( "select node_id from sdb_sitemaps" );
        foreach ( $ndata as $key => $v )
        {
            $data[] = $v['node_id'];
        }
        $where[] = "node_id in (".implode( ",", $data ).")";
        if ( $aFilter['article_id'] )
        {
            if ( is_array( $aFilter['article_id'] ) )
            {
                foreach ( $aFilter['article_id'] as $id )
                {
                    if ( $id != "_ANY_" )
                    {
                        $aId[] = intval( $id );
                    }
                }
                if ( 0 < count( $aId ) )
                {
                    $where[] = "article_id IN (".implode( ",", $aId ).")";
                }
            }
            else
            {
                $where[] = "article_id=".$aFilter['article_id'];
            }
        }
        if ( $aFilter['node_id'] )
        {
            if ( is_array( $aFilter['node_id'] ) )
            {
                foreach ( $aFilter['node_id'] as $catid )
                {
                    if ( $catid != "_ANY_" )
                    {
                        $aCats[] = intval( $catid );
                    }
                }
                if ( 0 < count( $aCats ) )
                {
                    $where[] = "node_id IN (".implode( ",", $aCats ).")";
                }
            }
            else
            {
                $where[] = "node_id=".$aFilter['node_id'];
            }
        }
        unset( $aFilter->'node_id' );
        unset( $aFilter->'title' );
        if ( 0 < count( $where ) )
        {
            return implode( " AND ", $where )." AND ".shopobject::_filter( $aFilter );
        }
        return shopobject::_filter( $aFilter );
    }

    function get( $art_id )
    {
        $sql = "SELECT * FROM sdb_articles WHERE article_id=".intval( $art_id );
        return $aTemp = $this->db->selectrow( $sql );
    }

    function getart( $art_id )
    {
        $sql = "SELECT * FROM sdb_articles WHERE article_id=".intval( $art_id )." AND disabled = 'false'";
        return $aTemp = $this->db->selectrow( $sql );
    }

    function savearticle( $aData )
    {
        if ( $aData['goodslink'] )
        {
            $tmpInfo = array(
                "goodskeywords" => $aData['goodskeywords'],
                "goodsnums" => $aData['goodsnums'],
                "goodsid" => $aData['goodsid']
            );
        }
        $oseo =& $this->system->loadmodel( "system/seo" );
        $seoData = array(
            "keywords" => $aData['seo_keywords'],
            "descript" => $aData['seo_description'],
            "title" => $aData['seo_title']
        );
        if ( is_array( $aData['linkwords'] ) && count( $aData['linkwords'] ) )
        {
            $this->savehotlink( $aData['article_id'], $aData['linkwords'], $aData['linkurl'] );
        }
        $oseo->set_seo( "article", $aData['article_id'], $seoData );
        $tmpInfo['goodslink'] = $aData['goodslink'];
        $tmpInfo['hotlink'] = $aData['hotlink'];
        $aData['goodsinfo'] = serialize( $tmpInfo );
        $aData['uptime'] = time( );
        $aRs = $this->db->query( "SELECT * FROM sdb_articles WHERE article_id=".$aData['article_id'] );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    function addarticle( $data )
    {
        $data['pubtime'] = time( );
        $data['uptime'] = time( );
        if ( $data['goodslink'] )
        {
            $tmpInfo = array(
                "goodskeywords" => $data['goodskeywords'],
                "goodsnums" => $data['goodsnums'],
                "goodsid" => $data['goodsid']
            );
        }
        $oseo =& $this->system->loadmodel( "system/seo" );
        $seoData = array(
            "keywords" => $data['seo_keywords'],
            "descript" => $data['seo_description'],
            "title" => $data['seo_title']
        );
        $rs = $this->db->query( "SELECT * FROM sdb_articles WHERE 0=1" );
        $tmpInfo['goodslink'] = $data['goodslink'];
        $tmpInfo['hotlink'] = $data['hotlink'];
        $data['goodsinfo'] = serialize( $tmpInfo );
        $sql = $this->db->getinsertsql( $rs, $data );
        if ( $this->db->exec( $sql ) )
        {
            $articleid = $this->db->lastinsertid( );
            $oseo->set_seo( "article", $articleid, $seoData );
            if ( $data['hotlink'] && is_array( $data['linkwords'] ) && count( $data['linkwords'] ) )
            {
                return $this->savehotlink( $articleid, $data['linkwords'], $data['linkurl'] );
            }
            return true;
        }
        return false;
    }

    function modifier_node_name( &$rows )
    {
        $result = $this->db->select( "SELECT node_id,title FROM sdb_sitemaps WHERE node_id in ('".implode( $rows, "','" )."')" );
        foreach ( $result as $name => $value )
        {
            $rows[$value['node_id']] = $value['title'];
        }
        unset( $rows );
    }

    function getarticlecat( )
    {
        $sSql = "SELECT title,node_id from sdb_sitemaps where node_type=\"articles\"";
        return $this->db->select( $sSql );
    }

    function getcategorys( )
    {
        $ret = array( );
        foreach ( $this->db->select( "select node_id,title from sdb_sitemaps where node_type='articles'" ) as $cat )
        {
            $ret[$cat['node_id']] = $cat['title'];
        }
        return $ret;
    }

    function savehotlink( $articleid, $linkgoods, $linkurl )
    {
        $this->deletehotlink( $articleid );
        $sql = "INSERT INTO sdb_goods_keywords VALUES ";
        foreach ( $linkgoods as $key => $val )
        {
            $value[] = "('".$articleid."','".$val."','".$linkurl[$key]."','article')";
        }
        $sql .= implode( ",", $value ).";";
        return $this->db->exec( $sql );
    }

    function gethotlink( $id )
    {
        $row = $this->db->select( "select * from sdb_goods_keywords where goods_id=".intval( $id )." and res_type='article'" );
        return $row;
    }

    function deletehotlink( $id )
    {
        $this->db->exec( "DELETE FROM sdb_goods_keywords where goods_id=".intval( $id )." and res_type='article'" );
    }

    function getgoodsbykw( $keywords, $num )
    {
        if ( $keywords )
        {
            $tmpKW = explode( "|", $keywords );
            foreach ( $tmpKW as $key => $val )
            {
                $val = addslashes( $val );
                $tmp[] = "g.name LIKE '%".$val."%'";
            }
            $wh = "(";
            $wh .= "(".implode( " OR ", $tmp ).") OR ";
            $inkeyword = str_replace( "|", "','", $keywords );
            $inkeyword = $this->db->quote( $inkeyword );
            $wh .= "(gk.keyword IN (".$inkeyword.") AND gk.res_type='goods')) AND ";
        }
        $sql = "select g.goods_id,g.name,gg.thumbnail,gg.gimage_id,g.price from sdb_goods as g LEFT JOIN sdb_goods_keywords as gk ON g.goods_id=gk.goods_id LEFT JOIN sdb_gimages as gg on gg.gimage_id=g.image_default where ".$wh." g.marketable='true' AND g.disabled='false' group by g.goods_id order by g.goods_id desc";
        $row = $this->db->select( $sql );
        return $row;
    }

    function modifier_uptime( &$rows )
    {
        foreach ( $rows as $key => $value )
        {
            $rows[$key] = date( "Y-m-d", $value );
        }
    }

    function getgoods( $id = "", $num = null )
    {
        $objGoods =& $this->system->loadmodel( "goods/products" );
        if ( !empty( $num ) )
        {
            $sql = "select g.*,gm.thumbnail from sdb_goods g LEFT JOIN sdb_gimages gm ON gm.gimage_id=g.image_default where g.goods_id IN (".$id.") order by g.uptime desc limit 0,".$num."";
        }
        else
        {
            $sql = "select g.*,gm.thumbnail from sdb_goods g LEFT JOIN sdb_gimages gm ON gm.gimage_id=g.image_default where g.goods_id IN (".$id.") order by g.uptime desc";
        }
        $row = $this->db->select( $sql );
        $row = $objGoods->getspareprice( $row, $runtime['member_lv'] );
        return $row;
    }

    function getvalidgoods( $goodsIds )
    {
        $sqlString = "SELECT goods_id FROM sdb_goods WHERE disabled='false' AND marketable='true' AND goods_id IN(".$goodsIds.")";
        $rs = $this->db->select( $sqlString );
        $tmp = array( );
        if ( $rs )
        {
            foreach ( $rs as $key => $item )
            {
                $tmp[] = $item['goods_id'];
            }
        }
        return implode( ",", $tmp );
    }

}

?>
