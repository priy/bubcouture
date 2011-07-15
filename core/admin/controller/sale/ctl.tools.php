<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_tools extends adminPage
{

    public $workground = "sale";

    public function &defaultseo( )
    {
        return array(
            "index_title" => array(
                "value" => "",
                "name" => __( "无" )
            ),
            "meta_key_words" => array(
                "value" => "",
                "name" => __( "无" )
            ),
            "meta_desc" => array(
                "value" => "",
                "name" => __( "无" )
            ),
            "homepage_title" => array(
                "value" => "{ENV_shopname}",
                "name" => __( "{商店名称}" )
            ),
            "homepage_meta_key_words" => array(
                "value" => "",
                "name" => __( "无" )
            ),
            "homepage_meta_desc" => array(
                "value" => "{ENV_shopname}",
                "name" => __( "{商店名称}" )
            ),
            "goods_title" => array(
                "value" => "{ENV_goods_name}_{ENV_shopname}",
                "name" => __( "{商品名称}_{商店名称}" )
            ),
            "goods_meta_key_words" => array(
                "value" => "{ENV_goods_kw}",
                "name" => __( "{商品关键词}" )
            ),
            "goods_meta_desc" => array(
                "value" => __( "{ENV_goods_name}现价{ENV_goods_price};{ENV_goods_intro}" ),
                "name" => __( "{商品名称}现价{商品价格};{商品描述}" )
            ),
            "list_title" => array(
                "value" => "{ENV_path}_{ENV_goods_cat_p}_{ENV_shopname}",
                "name" => __( "{商品类别路径}_{商品父分类}_{商店名称}" )
            ),
            "list_meta_key_words" => array(
                "value" => "{ENV_brand}",
                "name" => __( "{品牌}" )
            ),
            "list_meta_desc" => array(
                "value" => __( "{ENV_path},{ENV_shopname}共找到{ENV_goods_amount}个商品" ),
                "name" => __( "{商品类别路径},{商店名称}共找到{商品数量}个商品" )
            ),
            "brand_index_title" => array(
                "value" => __( "品牌专区_{ENV_shopname}" ),
                "name" => __( "品牌专区_{商店名称}" )
            ),
            "brand_index_meta_key_words" => array(
                "value" => "{ENV_brand}",
                "name" => __( "{品牌}" )
            ),
            "brand_index_meta_desc" => array(
                "value" => __( "{ENV_shopname}提供{ENV_brand}等品牌的商品。" ),
                "name" => __( "{商店名称}提供{品牌列表}等品牌的商品" )
            ),
            "brand_list_title" => array(
                "value" => "{ENV_brand}_{ENV_shopname}",
                "name" => __( "{品牌}_{商店名称}" )
            ),
            "brand_list_meta_key_words" => array(
                "value" => "{ENV_brand_kw}",
                "name" => __( "{品牌关键词}" )
            ),
            "brand_list_meta_desc" => array(
                "value" => "{ENV_brand_intro}",
                "name" => __( "{品牌描述}" )
            ),
            "article_list_title" => array(
                "value" => "{ENV_article_cat}_{ENV_shopname}",
                "name" => __( "{文章类别}_{商店名称}" )
            ),
            "article_list_meta_key_words" => array(
                "value" => "",
                "name" => __( "无" )
            ),
            "article_list_meta_desc" => array(
                "value" => "{ENV_shopname}{ENV_article_cat}",
                "name" => __( "{商店名称}{文章类别}" )
            ),
            "article_title" => array(
                "value" => "{ENV_article_title}_{ENV_shopname}{ENV_article_cat}",
                "name" => __( "{文章标题}_{商店名称}_{文章类别}" )
            ),
            "article_meta_key_words" => array(
                "value" => "",
                "name" => __( "无" )
            ),
            "article_meta_desc" => array(
                "value" => "{ENV_article_intro}",
                "name" => __( "{文章描述}" )
            )
        );
    }

    public function &defaulttag( )
    {
        return array(
            "homepage" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                )
            ),
            "goods" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                ),
                array(
                    "value" => "{ENV_brand}",
                    "name" => __( "品牌" )
                ),
                array(
                    "value" => "{ENV_goods_name}",
                    "name" => __( "商品名称" )
                ),
                array(
                    "value" => "{ENV_goods_cat}",
                    "name" => __( "商品类别" )
                ),
                array(
                    "value" => "{ENV_goods_intro}",
                    "name" => __( "商品介绍" )
                ),
                array(
                    "value" => "{ENV_goods_brief}",
                    "name" => __( "商品简介" )
                ),
                array(
                    "value" => "{ENV_brand_kw}",
                    "name" => __( "品牌关键词" )
                ),
                array(
                    "value" => "{ENV_goods_kw}",
                    "name" => __( "商品关键词" )
                ),
                array(
                    "value" => "{ENV_goods_price}",
                    "name" => __( "商品价格" )
                ),
                array(
                    "value" => "{ENV_update_time}",
                    "name" => __( "商品更新时间" )
                ),
                array(
                    "value" => "{ENV_goods_bn}",
                    "name" => __( "商品编号" )
                )
            ),
            "list" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                ),
                array(
                    "value" => "{ENV_goods_amount}",
                    "name" => __( "搜到的商品个数" )
                ),
                array(
                    "value" => "{ENV_goods_cat}",
                    "name" => __( "商品类别" )
                ),
                array(
                    "value" => "{ENV_goods_cat_p}",
                    "name" => __( "商品分类路径" )
                ),
                array(
                    "value" => "{ENV_goods_type}",
                    "name" => __( "商品类型" )
                ),
                array(
                    "value" => "{ENV_brand}",
                    "name" => __( "品牌" )
                ),
                array(
                    "value" => "{ENV_path}",
                    "name" => __( "渐进式搜索路径" )
                )
            ),
            "brand" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                ),
                array(
                    "value" => "{ENV_goods_amount}",
                    "name" => __( "找到的商品个数" )
                ),
                array(
                    "value" => "{ENV_brand}",
                    "name" => __( "品牌" )
                ),
                array(
                    "value" => "{ENV_brand_intro}",
                    "name" => __( "品牌介绍" )
                ),
                array(
                    "value" => "{ENV_brand_kw}",
                    "name" => __( "品牌关键词" )
                )
            ),
            "brandlist" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                ),
                array(
                    "value" => "{ENV_brand}",
                    "name" => __( "品牌" )
                )
            ),
            "article" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                ),
                array(
                    "value" => "{ENV_article_title}",
                    "name" => __( "文章标题" )
                ),
                array(
                    "value" => "{ENV_article_intro}",
                    "name" => __( "文章介绍" )
                ),
                array(
                    "value" => "{ENV_article_cat}",
                    "name" => __( "文章类别" )
                )
            ),
            "articlelist" => array(
                array(
                    "value" => "{ENV_shopname}",
                    "name" => __( "商店名称" )
                ),
                array(
                    "value" => "{ENV_article_cat}",
                    "name" => __( "文章类别" )
                )
            )
        );
    }

    public function sitemaps( )
    {
        $this->path[] = array(
            "text" => __( "搜索引擎优化" )
        );
        $this->pagedata['url'] = $this->system->realUrl( "sitemaps", "catalog", NULL, "xml", $this->system->base_url( ) );
        $this->page( "system/tools/sitemaps.html" );
    }

    public function createLink( )
    {
        $this->path[] = array(
            "text" => __( "站外推广链接" )
        );
        $timer = $this->system->getConf( "site.refer_timeout" );
        $this->pagedata['base_url'] = $this->system->base_url( );
        $this->pagedata['validtime'] = $timer;
        $this->page( "system/tools/createlink.html" );
    }

    public function seo( )
    {
        $this->path[] = array(
            "text" => __( "SEO设置" )
        );
        $this->pagedata['expseo'] = array(
            "seo" => __( "统一SEO设置" ),
            "homepage" => __( "首页" ),
            "goods" => __( "商品" ),
            "list" => __( "列表" ),
            "brand" => __( "品牌" ),
            "article" => __( "文章" )
        );
        $this->pagedata['name'] = "seo";
        $this->page( "system/tools/seo.html" );
    }

    public function seoedit( )
    {
        if ( $_POST['pagetype'] == "seo" )
        {
            $this->begin( "index.php?ctl=sale/tools&act=seo" );
        }
        else
        {
            $this->begin( "index.php?ctl=sale/tools&act=exceptseo&p[0]=".$_POST['pagetype'] );
        }
        $GLOBALS['_POST']['setting']['site.tax_ratio'] = $_POST['setting']['site.tax_ratio'] / 100;
        $storager =& $this->system->loadModel( "system/storager" );
        $this->clear_all_cache( );
        $this->system->cache->clear( );
        $this->end( $this->settingEdit( ), __( "修改成功" ) );
    }

    public function exceptseo( $name )
    {
        $defaultTag =& $this->defaulttag( );
        switch ( $name )
        {
        case "goods" :
            $pathname = __( "商品页设置" );
            $tmpTag = $defaultTag['goods'];
            foreach ( $tmpTag as $k => $v )
            {
                if ( $v['value'] == "{ENV_goods_cat}" )
                {
                    unset( $this->goods[$k] );
                    break;
                }
            }
            unset( $tmpTag );
            break;
        case "list" :
            $pathname = __( "列表页设置" );
            break;
        case "virtualcat" :
            $pathname = __( "虚拟分类列表设置" );
            break;
        case "brand" :
            $pathname = __( "品牌页设置" );
            break;
        case "article" :
            $pathname = __( "文章页设置" );
            break;
        case "homepage" :
            $pathname = __( "首页设置" );
            break;
        default :
            $this->splash( "failed", "index.php?ctl=sale/tools&act=seo", __( "路径有误" ) );
            break;
        }
        $seomode =& $this->defaultseo( );
        foreach ( $seomode as $key => $val )
        {
            $seoprefix = substr( $key, 0, strpos( $key, "_" ) );
            if ( $name == $seoprefix )
            {
                $this->pagedata[$key."_default"] = $val['value'];
                $this->pagedata[$key."_default_name"] = $val['name'];
            }
        }
        $this->pagedata[$name."_defTag"] = $defaultTag[$name];
        if ( $defaultTag[$name."list"] )
        {
            $this->pagedata[$name."_list_defTag"] = $defaultTag[$name."list"];
        }
        $this->pagedata['expseo'] = array(
            "seo" => __( "统一SEO设置" ),
            "homepage" => __( "首页" ),
            "goods" => __( "商品" ),
            "list" => __( "列表" ),
            "brand" => __( "品牌" ),
            "article" => __( "文章" )
        );
        $this->path[] = array(
            "text" => __( "SEO设置" )
        );
        $this->path[] = array(
            "text" => $pathname
        );
        $this->pagedata['name'] = $name;
        $this->page( "system/tools/".$name.".html" );
    }

    public function editValidtime( )
    {
        $timer = intval( $_POST['validtime'] );
        $this->begin( "index.php?ctl=sale/tools&act=createLink" );
        if ( $this->system->setConf( "site.refer_timeout", $timer ) )
        {
            $this->end( TRUE, __( "修改成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "修改失败" ) );
        }
    }

    public function get_wltx_info( )
    {
        $url = OUTER_SERVICE_URL."/api.php";
        $query = "certi_id=".$this->system->getConf( "certificate.id" );
        $response_url = $url."?".$query;
        $results = file_get_contents( $response_url );
        return $results;
    }

    public function wltx_exp_pop( )
    {
        echo "<iframe src='".OUTER_SERVICE_URL."/pop.html' width='100%' height='100%'></iframe>\n            <!-----.mainFoot-----<div class='textcenter'><img src='images/wltx_botton.gif' style=\"cursor:pointer\" isclosedialogbtn=\"true\"/></div>-----.mainFoot----->";
    }

    public function set_wltx( $status = 0 )
    {
        $this->begin( "index.php?ctl=sale/tools&act=wltx" );
        $function = $status == 0 ? "co.close_se" : "co.open_se";
        $this->sendwltx( $function );
        $this->end( TRUE, $status == 0 ? __( "网罗天下服务已暂停" ) : __( "网罗天下服务已开启" ) );
    }

    public function save_wltx_exp( )
    {
        $url = OUTER_SERVICE_URL."/api.php";
        $query = "certi_id=".$this->system->getConf( "certificate.id" );
        $response_url = $url."?".$query;
        $results = file_get_contents( $response_url );
        $this->pagedata['status'] = $results;
        $allow_max = 30;
        $count = count( $_POST['goods_id'] );
        if ( $count && $count <= $allow_max )
        {
            $this->system->setConf( "utility.wltx", implode( ",", $_POST['goods_id'] ) );
            $this->splash( "success", "index.php?ctl=sale/tools&act=wltx_exp", __( "成功提交".$count."条商品" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=sale/tools&act=wltx_exp", __( "提交失败，您的商品数超过了30条或者您没有提交商品" ) );
        }
        exit( );
    }

    public function wltx_exp_apply( $open = FALSE )
    {
        $url = OUTER_SERVICE_URL."/iapp_wltx.php";
        $this->pagedata['goods_count'] = count( $info );
        $results = $this->get_wltx_info( );
        if ( !$open && $results == "sans" )
        {
            $this->wltx_exp( );
        }
        else
        {
            $certi_id = $this->system->getConf( "certificate.id" );
            $istime = time( );
            $ac = md5( $certi_id.$istime."alliweb" );
            $o = $this->system->loadModel( "trading/goods" );
            $goods_count = $o->getAllGoods( );
            $url = $url."?goods_count=".$goods_count."&certi_id=".$certi_id."&istime=".$istime."&ac=".$ac;
            $this->pagedata['url'] = $url;
            $this->page( "sale/wltx/wltx_exp_aply.html" );
        }
    }

    public function wltx_exp( $page = 1, $pagenumber = 5 )
    {
        $results = $this->get_wltx_info( );
        $this->pagedata['status'] = $results;
        $allow_max = 30;
        $this->pagedata['max_count'] = $allow_max;
        $this->path[] = array(
            "text" => __( "网络天下免费版" )
        );
        if ( $wltx_info = $this->system->getConf( "utility.wltx" ) )
        {
            $info = @explode( ",", $wltx_info );
        }
        $this->pagedata['goods_count'] = count( $info );
        $this->pagedata['selected_goods'] = $info;
        $this->page( "sale/wltx/wltx_exp.html" );
    }

    public function wltx( )
    {
        $cer = $this->system->loadModel( "service/certificate" );
        $url = OUTER_SERVICE_URL."/zhuanye_f.php";
        $certi_id = $cer->getCerti( );
        $istime = time( );
        $ac = md5( $certi_id.$istime."alliweb" );
        $url = $url."?certi_id=".$certi_id."&istime=".$istime."&ac=".$ac;
        $this->pagedata['url'] = $url;
        $this->page( "sale/wltx/wltx_exp_aply.html" );
    }

    public function showwltx( )
    {
        $data = $this->sendwltx( "co.show_se" );
        if ( !is_array( $data ) )
        {
            return __( "数据获取错误" );
        }
        return $data['info']['se'];
    }

    public function sendwltx( $function )
    {
        $params['certi_app'] = $function;
        $cer =& $this->system->loadModel( "service/certificate" );
        $params['certificate_id'] = $cer->getCerti( );
        $token = $cer->getToken( );
        if ( ( !$token || !$params['certificate_id'] ) && $function != "co.show_se" )
        {
            return array( );
        }
        $params['app_id'] = APP_WLTX_ID;
        $params['version'] = APP_WLTX_VERSION;
        $params['certi_url'] = $this->system->base_url( );
        $params['certi_ac'] = $this->make_shopex_ac( $params, $token );
        $net =& $this->system->loadModel( "utility/http_client" );
        $data = $net->post( APP_WLTX_URL, $params );
        $data = json_decode( $data, TRUE );
        if ( !is_array( $data ) )
        {
            return FALSE;
        }
        return $data;
    }

    public function licenseService( )
    {
        $this->path[] = array( "text" => "绿卡服务" );
        $this->pagedata['type'] = "licenseService";
        $cert = $this->system->loadModel( "service/certificate" );
        $this->pagedata['certificate_id'] = $cert->getCerti( );
        $this->pagedata['certificate_token'] = $cert->getToken( );
        $this->pagedata['sess_id'] = $cert->get_sess( );
        $this->pagedata['url'] = $this->system->base_url( );
        $this->page( "sale/wltx/index.html" );
    }

    public function settingEdit( )
    {
        foreach ( $_POST['_set_'] as $key => $type )
        {
            if ( $type == "bool" )
            {
                $GLOBALS['_POST']['setting'][$key] = $_POST['setting'][$key] ? TRUE : FALSE;
            }
        }
        if ( $this->_modified( $_POST['setting'], "site.stripHtml" ) )
        {
            $frontend =& $this->system->loadModel( "system/frontend" );
            $frontend->clear_all_cache( );
        }
        $this->system->setConf( "readingGlass", $_POST['readingGlass'] ? 1 : 0 );
        if ( isset( $_POST['setting']['system.seo.emuStatic'] ) && $_POST['setting']['system.seo.emuStatic'] == "true" )
        {
            $svinfo =& $this->system->loadModel( "utility/tools" );
            $svinfo->test_fake_html( FALSE, $msg );
            if ( $msg )
            {
                $this->system->setConf( "system.seo.emuStatic", "false" );
                trigger_error( __( $msg ), E_USER_ERROR );
                return FALSE;
            }
        }
        foreach ( $_POST['setting'] as $k => $v )
        {
            if ( !$this->system->setConf( $k, $v ) )
            {
                trigger_error( $k.__( "设置错误" ), E_USER_ERROR );
                return FALSE;
            }
        }
        return TRUE;
    }

    public function _modified( $src, $key )
    {
        if ( isset( $src[$key] ) && $src[$key] != $this->system->getConf( $key ) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function make_shopex_ac( $temp_arr, $token )
    {
        ksort( $temp_arr );
        $str = "";
        foreach ( $temp_arr as $key => $value )
        {
            if ( $key != "certi_ac" )
            {
                $str .= $value;
            }
        }
        return md5( $str.$token );
    }

    public function recoverallseo( )
    {
        $this->begin( "index.php?ctl=sale/tools&act=seo" );
        $this->end( $this->seomanage( "default" ), __( "全站页面SEO模板恢复成功！" ) );
    }

    public function backupseo( )
    {
        $this->seomanage( "backuptocsv", "", $message );
    }

    public function recoverseo( )
    {
        $this->begin( "index.php?ctl=sale/tools&act=selectcsv" );
        $this->end( $this->seomanage( "recoverfromcsv", "", $message ), $message );
    }

    public function recoveroneseo( $key )
    {
        $p0 = substr( $key, 0, strpos( $key, "_" ) );
        $this->begin( "index.php?ctl=sale/tools&act=exceptseo&p[0]=".$p0 );
        $this->end( $this->seomanage( "special", $key, $message ), __( "成功恢复" ).$message.__( "默认值" ) );
    }

    public function seomanage( $act, $key = "", &$message )
    {
        $seomode =& $this->defaultseo( );
        if ( $act == "default" )
        {
            foreach ( $seomode as $key => $val )
            {
                $this->system->setConf( "site.".$key, $val['value'] );
            }
        }
        else if ( $act == "special" )
        {
            $this->system->setConf( "site.".$key, $seomode[$key]['value'] );
            $message = $seomode[$key]['name'];
        }
        else if ( $act == "backuptocsv" )
        {
            $addons = $this->system->loadModel( "system/addons" );
            $exporter = $addons->load( "csv", "io" );
            $exporter->charset = $this->system->loadModel( "utility/charset" );
            foreach ( $seomode as $key => $val )
            {
                $seovalue[] = array(
                    $key,
                    $this->system->getConf( "site.".$key )
                );
            }
            if ( method_exists( $exporter, "export_begin" ) )
            {
                $exporter->export_begin( $headCols = array( ), "", $count = "", date( "Ymd" )."_seo_template" );
            }
            $exporter->export_rows( $seovalue );
            if ( method_exists( $exporter, "export_finish" ) )
            {
                $exporter->export_finish( );
            }
        }
        else if ( $act == "recoverfromcsv" )
        {
            $addons = $this->system->loadModel( "system/addons" );
            $exporter = $addons->load( "csv", "io" );
            $exporter->charset = $this->system->loadModel( "utility/charset" );
            $csvName = $_FILES['seomoudle']['name'];
            if ( strtoupper( substr( $csvName, strpos( $csvName, "." ) + 1 ) ) !== "CSV" )
            {
                $message = __( "文件格式错误，应该上传CSV文件！" );
                return FALSE;
            }
            else
            {
                $handle = fopen( $_FILES['seomoudle']['tmp_name'], "r" );
                $data = $exporter->import_rows( $handle );
                if ( $data )
                {
                    foreach ( $data as $key => $val )
                    {
                        if ( $val )
                        {
                            $this->system->setConf( "site.".$val[0], $val[1] );
                        }
                    }
                }
                echo "<script>parent.\$('backupseotpl').retrieve('dialog').close();";
                echo "parent.W.page.call(parent.W,'index.php?ctl=sale/tools&act=seo');parent.MessageBox.success()</script>";
            }
        }
        return TRUE;
    }

    public function selectcsv( )
    {
        $this->page( "system/tools/selectcsv.html" );
    }

}

?>
