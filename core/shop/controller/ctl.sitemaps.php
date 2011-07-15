<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_sitemaps extends shopPage
{

    public function view( )
    {
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        foreach ( $sitemap->getMap( ) as $item )
        {
            if ( $item['items'] )
            {
                $item['html'] = $this->_make_html( $item['items'], 0 );
                unset( $item['items'] );
                $items[] = $item;
            }
            else
            {
                $first[] = $item;
            }
        }
        if ( !$items )
        {
            $items = array( );
        }
        $title = $sitemap->getTitleByAction( "sitemaps:view" );
        $title = $title['title'] ? $title['title'] : __( "站点地图" );
        $this->path[] = array(
            "title" => $title
        );
        array_unshift( $items, array(
            "title" => __( "首页" ),
            "link" => $this->system->mkUrl( "page", "index" ),
            "html" => $this->_make_html( $first, 0 )
        ) );
        $this->pagedata['items'] =& $items;
        $this->output( );
    }

    public function _make_html( $map, $level )
    {
        foreach ( $map as $item )
        {
            $html .= ( "<ul class=\"list\" style=\"padding-left:".$level * 20 )."px\"><li ><a href=\"".$item['link']."\">".$item['title']."</a></li></ul>";
            if ( is_array( $item['items'] ) && 0 < count( $item['items'] ) )
            {
                $html .= "<div>".$this->_make_html( $item['items'], $level + 1 )."</div>";
            }
        }
        return $html;
    }

    public function _header( $etag )
    {
        if ( empty( $etag ) )
        {
            $etag = date( "Y-m-d" );
        }
        header( "Content-type: application/xml" );
        if ( $_SERVER['HTTP_IF_NONE_MATCH'] == $etag )
        {
            header( "Etag:".$etag, TRUE, 304 );
            exit( );
        }
        else
        {
            header( "Etag:".$etag );
        }
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><?xml-stylesheet type=\"text/xsl\" href=\"".$this->system->base_url( )."statics/sitemaps.xsl\"?".">";
    }

    public function catalog( )
    {
        $header = "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\nxmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\nxsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\nhttp://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd\">";
        $end = "</sitemapindex>";
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $goods =& $this->system->loadModel( "goods/products" );
        $etag = $goods->getLastModify( );
        $this->_header( $etag );
        $countNum = $goods->countNum( );
        $content = $sitemap->generateCatalog( $countNum );
        echo $header.$content.$end;
        $this->system->_succ = TRUE;
        exit( );
    }

    public function index( $page = 0 )
    {
        $getNumber = 1000;
        $header = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\nxmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\nxsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\nhttp://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">";
        $end = "</urlset>";
        if ( $page == 0 )
        {
            $sitemap =& $this->system->loadModel( "content/sitemap" );
            $goods =& $this->system->loadModel( "goods/productCat" );
            $result = $goods->getList( "cat_id,50 as rank", "", 0, 1000 );
            $_goods = $sitemap->geneList( $result, array( "cat_id", "gallery", "rank", "daily", "" ) );
            $brand =& $this->system->loadModel( "goods/brand" );
            $result = $brand->getList( "brand_id,50 as rank", "", 0, 1000 );
            $_brand = $sitemap->geneList( $result, array( "brand_id", "brand", "rank", "daily", "" ) );
            $article =& $this->system->loadModel( "content/article" );
            $result = $article->getList( "article_id,20 as rank", array( "ifpub" => 1 ), 0, 1000 );
            $_article = $sitemap->geneList( $result, array( "article_id", "article", "rank", "daily", "" ) );
            $gift =& $this->system->loadModel( "trading/gift" );
            $result = $gift->getList( "gift_id,10 as rank", array( "shop_iffb" => 1 ), 0, 1000 );
            $_gift = $sitemap->geneList( $result, array( "gift_id", "gift", "rank", "daily", "" ) );
            $geneList = $_goods.$_brand.$_article.$_gift;
        }
        else
        {
            $sitemap =& $this->system->loadModel( "content/sitemap" );
            $goods =& $this->system->loadModel( "goods/products" );
            $result = $goods->getList( "goods_id,d_order,last_modify", array( "marketable" => "true" ), ( $page - 1 ) * $getNumber, $page * $getNumber );
            $geneList = $sitemap->geneList( $result, array( "goods_id", "product", "d_order", "daily", "last_modify" ) );
            $etag = $goods->getLastModify( array(
                ( $page - 1 ) * $getNumber,
                $page * $getNumber
            ) );
        }
        $this->_header( $etag );
        echo $header.$geneList.$end;
        $this->system->_succ = TRUE;
        exit( );
    }

}

?>
