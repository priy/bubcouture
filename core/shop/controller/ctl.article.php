<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_article extends shopPage
{

    public $type = "articles";
    public $seoTag = array
    (
        0 => "shopname",
        1 => "article_cat",
        2 => "article_title",
        3 => "article_intro"
    );

    public function ctl_article( )
    {
        parent::shoppage( );
        $this->title = $this->system->getConf( "site.article_title" );
        $this->keywords = $this->system->getConf( "site.article_meta_key_words" );
        $this->desc = $this->system->getConf( "site.article_meta_desc" );
    }

    public function index( $articleid )
    {
        $oseo =& $this->system->loadModel( "system/seo" );
        $seo_info = $oseo->get_seo( "article", $articleid );
        $this->title = $seo_info['title'] ? $seo_info['title'] : $this->system->getConf( "site.article_title" );
        $this->keywords = $seo_info['keywords'] ? $seo_info['keywords'] : $this->system->getConf( "site.article_meta_key_words" );
        $this->desc = $seo_info['descript'] ? $seo_info['descript'] : $this->system->getConf( "site.article_meta_desc" );
        $objArticle =& $this->system->loadModel( "content/article" );
        $this->customer_template_type = "article";
        $this->customer_template_id = $articleid;
        $this->pagedata['article'] = $objArticle->getArt( $articleid );
        $this->id = array(
            "node_id" => $articleid
        );
        if ( !$this->pagedata['article'] )
        {
            $this->system->error( 404 );
            exit( );
        }
        $goodsinfo = unserialize( $this->pagedata['article']['goodsinfo'] );
        $goodsinfo['goodsid'] = $objArticle->getValidGoods( $goodsinfo['goodsid'] );
        if ( $goodsinfo['hotlink'] )
        {
            $hotlink = $objArticle->gethotlink( $articleid );
        }
        $tmpContent = preg_split( "/(<[^<>]+>)/", $this->pagedata['article']['content'], -1, PREG_SPLIT_DELIM_CAPTURE );
        if ( $tmpContent )
        {
            $standerlen = 200;
            $conlen = 0;
            if ( count( $tmpContent ) <= 1 )
            {
                if ( $goodsinfo['hotlink'] )
                {
                    $val =& $tmpContent[0];
                    foreach ( $hotlink as $hk => $hv )
                    {
                        if ( strstr( $val, $hv['keyword'] ) && !strstr( $val, ".".$hv['keyword']."." ) )
                        {
                            $val = str_replace( $hv['keyword'], "<a style=\"color:blue; text-decoration:underline;\" href=".$hv['refer']." target=_blank>".$hv['keyword']."</a>", $val );
                            $used[] = $hv['keyword'];
                        }
                    }
                }
            }
            else
            {
                foreach ( $tmpContent as $key => $v )
                {
                    $val =& $tmpContent[$key];
                    if ( trim( $val ) && !preg_match( "/<.*>/", $val ) )
                    {
                        if ( $conlen <= $standerlen )
                        {
                            if ( $goodsinfo['hotlink'] )
                            {
                                foreach ( $hotlink as $hk => $hv )
                                {
                                    if ( strstr( $val, $hv['keyword'] ) && !strstr( $val, ".".$hv['keyword']."." ) )
                                    {
                                        $val = str_replace( $hv['keyword'], "<a style=\"color:blue; text-decoration:underline;\" href=".$hv['refer']." target=_blank>".$hv['keyword']."</a>", $val );
                                        $used[] = $hv['keyword'];
                                    }
                                }
                            }
                            $conlen += $reallen;
                        }
                        else
                        {
                            unset( $used );
                            $conlen = 0;
                            $self = 0;
                        }
                    }
                }
            }
            $this->pagedata['article']['content'] = implode( "", $tmpContent );
        }
        $goodsInfo = $objArticle->get( $articleid );
        $goodsNum = unserialize( $goodsInfo['goodsinfo'] );
        if ( $goodsinfo['goodslink'] )
        {
            $this->pagedata['goods'] = $objArticle->getGoods( $goodsinfo['goodsid'], $goodsNum['goodsnums'] );
        }
        foreach ( $this->pagedata['goods'] as $key => $val )
        {
            $this->pagedata['goods'][$key]['goodspath'] = $this->system->mkUrl( "product", "index", array(
                $val['goods_id']
            ) );
        }
        $this->path = array( "title" => "" );
        $this->getGlobal( $this->seoTag, $this->pagedata );
        $this->output( );
    }

    public function strlen( $str, $encode = "utf8" )
    {
        $enlen = 0;
        if ( $encode == "utf8" )
        {
            $minchar = 128;
        }
        else if ( $encode == "gbk" )
        {
            $minchar = 0;
        }
        $maxchar = 255;
        $i = 0;
        for ( ; $i < strlen( $str ); ++$i )
        {
            if ( $minchar <= ord( $str[$i] ) && ord( $str[$i] ) <= $maxchar )
            {
                $zh[] = ord( $str[$i] );
            }
            else
            {
                ++$enlen;
            }
        }
        if ( $encode == "utf8" )
        {
            $zhlen = count( $zh ) / 3;
        }
        else
        {
            $zhlen = count( $zh ) / 2;
        }
        return $enlen + $zhlen;
    }

    public function substr( $str, $start = 0, $offset = "" )
    {
        $j = 0;
        $cn = 0;
        if ( !$offset )
        {
            $offset = strlen( $str );
        }
        while ( $cn < $start )
        {
            if ( 128 <= ord( $str[$j] ) && ord( $str[$j] ) <= 255 )
            {
                $j += 3;
            }
            else
            {
                ++$j;
            }
            ++$cn;
        }
        $i = $j;
        $exp = 0;
        while ( $exp < $offset )
        {
            if ( 128 <= ord( $str[$i] ) && ord( $str[$i] ) < 255 )
            {
                $substr .= substr( $str, $i, 3 );
                $i += 3;
            }
            else
            {
                $substr .= $str[$i];
                ++$i;
            }
            ++$exp;
        }
        return $substr;
    }

    public function get_article_cat( $result )
    {
        $sitemap = $this->system->loadModel( "content/sitemap" );
        $row = $sitemap->getNowNod( $result['article']['node_id'] );
        return $row[0]['title'];
    }

    public function get_article_title( $result )
    {
        return $result['article']['title'];
    }

    public function get_article_intro( $result )
    {
        $content = strip_tags( $result['article']['content'] );
        if ( 50 < strlen( $content ) )
        {
            $content = substr( $content, 0, 50 );
        }
        return $content;
    }

}

?>
