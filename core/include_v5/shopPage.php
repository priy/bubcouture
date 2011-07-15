<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "pageFactory.php" );
class shopPage extends pageFactory
{

    public $noCache = false;
    public $_running = true;
    public $contentType = "text/html;charset=utf-8";
    public $member = NULL;
    public $header = "";
    public $keyWords = null;
    public $metaDesc = null;
    public $title = null;
    public $type = null;
    public $transaction_start = false;
    public $__tmpl = null;
    public $path = array( );

    public function shopPage( )
    {
        parent::pagefactory( );
        $this->system =& $system;
        $this->setError( );
        $this->header .= "<meta name=\"generator\" content=\"ShopEx ".$this->system->_app_version."\" />\n";
        if ( constant( "WITHOUT_STRIP_HTML" ) )
        {
            $this->enable_strip_whitespace = false;
        }
        $this->system->controller =& $this;
        if ( !$this->system->_base_link && ( !$this->system->getConf( "system.seo.emuStatic" ) || $this->system->getConf( "system.seo.emuStatic" ) == "false" ) )
        {
            $this->system['_base_link'] .= APP_ROOT_PHP."?";
        }
        $this->_env_vars = array(
            "base_url" => $this->system->_base_link
        );
    }

    public function error_jump( $errMsg, $tpl = "default.html" )
    {
        $this->__tmpl = "user:".TPL_ID."/".$tpl.":exception/index";
        $this->title = "error";
        $this->pagedata['errormsg'] = $errMsg;
        $this->output( );
    }

    public function output( )
    {
        if ( $this->action['type'] == "xml" || $this->action['type'] == "json" )
        {
            require( CORE_INCLUDE_DIR."/shop/shop.front_api.php" );
            shop_front_api( $this, $this->action['type'] );
        }
        if ( $this->system->getConf( "system.seo.emuStatic" ) || $this->system->getConf( "system.seo.emuStatic" ) == "true" )
        {
            $this->pagedata['emuenable'] = true;
        }
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        if ( !isset( $this->type ) )
        {
            $type = "action";
        }
        if ( !isset( $this->id ) )
        {
            $this->id = array(
                "action" => $this->action['controller'].":".urldecode( $this->action['method'] )
            );
        }
        if ( $this->title && $this->path[count( $this->path ) - 1]['title'] != $this->title && count( $this->path ) )
        {
            $this->path[] = array(
                "title" => $this->title
            );
        }
        if ( $this->cat_type )
        {
            $runtime['path'] = array_merge( $sitemap->getPath( $this->type, $this->cat_type, $this->action['method'] ), $this->path );
        }
        else
        {
            $runtime['path'] = array_merge( $sitemap->getPath( $this->type, $this->id, $this->action['method'] ), $this->path );
        }
        $this->pagedata['request'] =& $this->system->request;
        $this->pagedata['member'] =& $this->member;
        if ( !$this->title && $runtime['path'] )
        {
            array_shift( $runtime['path'] );
            $shopTitle = $runtime['path'];
            krsort( $shopTitle );
            foreach ( $shopTitle as $tk => $tl )
            {
                if ( $tk == intval( count( $shopTitle ) - 1 ) )
                {
                    $this->title .= trim( $tl['title'] )." ";
                }
                else
                {
                    $this->title .= trim( $tl['title'] )." ";
                }
            }
        }
        if ( $titleFormat = $this->system->getConf( "site.title_format" ) )
        {
            $this->pagedata['title'] = $this->system->sprintf( $titleFormat, $this->title );
        }
        else
        {
            $this->pagedata['title'] = $this->title;
        }
        if ( DEBUG_TEMPLETE )
        {
            $o =& $this->system->loadModel( "system/template" );
            $theme = $this->system->getConf( "system.ui.current_theme" );
            $o->resetTheme( $theme );
        }
        if ( !$this->keywords )
        {
            $this->keywords = $this->system->getConf( "site.meta_key_words" );
        }
        if ( !$this->desc )
        {
            $this->desc = $this->system->getConf( "site.meta_desc" );
        }
        $oTemplate =& $this->system->loadModel( "system/template" );
        $theme = $oTemplate->applyTheme( constant( "TPL_ID" ) );
        $this->theme = $theme['theme'];
        if ( is_array( $theme['config'] ) )
        {
            foreach ( $theme['config']['config'] as $c )
            {
                if ( isset( $c['key'] ) )
                {
                    $this->pagedata['theme'][$c['key']] = $c['value'];
                }
            }
        }
        if ( !isset( $this->pagedata['_MAIN_'] ) )
        {
            $this->pagedata['_MAIN_'] = $this->action['controller']."/".$this->action['method'].".html";
        }
        if ( $this->customer_template_type && $this->customer_template_id )
        {
            $oTemplate = $this->system->loadModel( "system/template" );
            $tpl = $oTemplate->get_customer_template( $this->customer_template_type, $this->customer_template_id, $this->customer_source_type );
            if ( $tpl )
            {
                $this->__tmpl = $oTemplate->get_template_path( $this->theme, $tpl );
            }
        }
        if ( !$this->__tmpl )
        {
            if ( $this->customer_template_type == "page" && $this->customer_template_id == "index" )
            {
                $this->customer_template_type = "index";
            }
            if ( !$this->customer_template_type )
            {
                $this->customer_template_type = $this->action['controller'];
            }
            $this->__tmpl = $this->system->getConf( "system.custom_template_".$this->customer_template_type ) ? $this->system->getConf( "system.custom_template_".$this->customer_template_type ) : $this->system->getConf( "system.custom_template_default" );
            if ( $this->customer_template_type == "artlist" )
            {
                $this->__tmpl = $this->system->getConf( "system.custom_template_article" );
            }
            if ( !file_exists( THEME_DIR."/".$this->theme."/".$this->__tmpl ) )
            {
            }
        }
        if ( !isset( $this->__tmpl ) )
        {
            $tmpl_file = $this->_get_view( TPL_ID, $ctl = $this->system->request['action']['controller'], $act = $this->system->request['action']['method'] );
            $this->__tmpl = "user:".$this->theme."/".$tmpl_file;
        }
        else if ( !$tpl )
        {
            $this->__tmpl = $this->template_exists( THEME_DIR."/".$this->theme."/".$this->__tmpl ) ? "user:".$this->theme."/".$this->__tmpl : "shop:".$this->__tmpl;
        }
        $this->system->_debugger['log'] = ob_get_contents( );
        ob_clean( );
        $output = $this->fetch( $this->__tmpl, false );
        $runtime['theme_dir'] = $runtime['base_url']."themes/".$this->theme;
        if ( preg_match_all( "/\\{([a-z][a-z0-9_]+)\\}/i", $output, $matches ) )
        {
            $to_fetchv = null;
            $to_replace = null;
            foreach ( $matches[1] as $v )
            {
                if ( substr( $v, 0, 4 ) == "ENV_" )
                {
                    $v = substr( $v, 4 );
                    if ( array_key_exists( $v, $runtime ) )
                    {
                        $to_replace["{ENV_".$v."}"] = $runtime[$v];
                    }
                    else
                    {
                        $to_replace["{ENV_".$v."}"] = "";
                    }
                }
                else
                {
                    $to_fetchv[] = $v;
                }
            }
            $magic_value =& $this->system->loadModel( "system/magicvars" );
            $magic_value->filter = null;
            foreach ( $magic_value->getList( "var_name,var_value", array(
                "var_name" => $matches[0]
            ) ) as $r )
            {
                $to_replace[$r['var_name']] = $r['var_value'];
            }
            $output = str_replace( array_keys( $to_replace ), array_values( $to_replace ), $output );
            $re = array( "/(\\s+name=[\"'][a-z0-9]+[\"']\\s+content=')(.*?)('>\\s*<meta)/is", "/(\\<title\\>)(.*?)(\\<\\/title\\>)/s" );
            $output = preg_replace_callback( $re, array(
                $this,
                "_fix_header"
            ), $output );
            $this->system->apply_modifiers( $output, "shop" );
            echo $output;
        }
        else
        {
            echo $output;
        }
    }

    public function _fix_header( $match )
    {
        return $match[1].htmlspecialchars( preg_replace( "/\\s+/s", " ", strip_tags( $match[2] ) ) ).$match[3];
    }

    public function splash( $status = "success", $jumpto = null, $msg = null, $links = array( ), $wait = false, $js = null )
    {
        if ( !$msg )
        {
            $msg = __( "操作成功" );
        }
        $this->system->_succ = true;
        $this->pagedata['_MAIN_'] = "splash/".$status.".html";
        $this->pagedata['msg'] = $msg;
        $this->pagedata['jumpto'] = $jumpto;
        $this->pagedata['links'] = $links;
        $this->pagedata['js'] = $js;
        if ( $wait )
        {
            $this->pagedata['wait'] = $wait;
        }
        else if ( $status = "success" )
        {
            $this->pagedata['wait'] = 1;
        }
        else
        {
            $this->pagedata['wait'] = 10;
        }
        $this->pagedata['error_info'] =& $this->system->_err;
        header( "Content-type: ".$this->contentType );
        $this->title = $status == "success" ? __( "执行成功" ) : __( "执行失败" );
        $this->output( );
        exit( );
    }

    public function redirect( $ctl = null, $act = "index", $args = null, $jsJump = false )
    {
        if ( !$ctl )
        {
            $ctl = $this->system->request['action']['controller'];
        }
        $url = $this->system->mkUrl( $ctl, $act, $args );
        $this->system->_succ = true;
        if ( $jsJump )
        {
            echo "<header><meta http-equiv=\"refresh\" content=\"0; url={$url}\"></header>";
        }
        else
        {
            header( "Location: ".$url );
        }
        exit( );
    }

    public function _verifyMember( $member_id = true )
    {
        if ( $_COOKIE['MEMBER'] )
        {
            $member = explode( "-", $_COOKIE['MEMBER'] );
        }
        else
        {
            $member = array( 0 );
        }
        $memberObj =& $this->system->loadModel( "member/account" );
        $memberInfo = $memberObj->verify( $member[0], $member[2] );
        if ( $member_id !== false && ( !$member[0] || !$memberInfo ) )
        {
            $this->system->setCookie( "MEMBER", "", time( ) - 1000 );
            $this->system->setCookie( "MLV", "", time( ) - 1000 );
            $this->system->setCookie( "UNAME", "", time( ) - 1000 );
            $this->system->_succ = true;
            $this->system->location( $this->system->mkUrl( "passport", "login", array(
                base64_encode( str_replace( array(
                    "+",
                    "/",
                    "="
                ), array(
                    "_",
                    ",",
                    "~"
                ), $this->system->mkUrl( $this->system->request['action']['controller'], $this->system->request['action']['method'], $this->system->request['action']['args'] ) ) )
            ) ) );
        }
        else
        {
            $this->member =& $memberInfo;
            if ( $member_id !== true && $memberInfo['member_id'] != $member_id && is_numeric( $member_id ) )
            {
                $this->system->error( 404 );
                return false;
            }
            $runtime['member_lv'] = $this->member['member_lv_id'];
        }
    }

    public function _get_view( $theme, $ctl, $act = "index" )
    {
        if ( $ctl == "page" && $act == "index" )
        {
            if ( file_exists( THEME_DIR."/".$theme."/index.html" ) )
            {
                return "index.html";
            }
            else if ( $cust_defalut = $this->system->getConf( "system.custom_template_default" ) )
            {
                return $cust_defalut;
            }
            else
            {
                return "default.html";
            }
        }
        if ( file_exists( THEME_DIR."/".$theme."/".$ctl."-".$act.".html" ) )
        {
            return $ctl."-".$act.".html";
        }
        else if ( file_exists( THEME_DIR."/".$theme."/".$ctl.".html" ) )
        {
            return $ctl.".html";
        }
        else if ( $cust_defalut = $this->system->getConf( "system.custom_template_default" ) )
        {
            if ( file_exists( THEME_DIR."/".$theme."/".$cust_defalut ) )
            {
                return $cust_defalut;
            }
            else
            {
                return "default.html";
            }
        }
        else
        {
            return "default.html";
        }
    }

    public function setError( $errorno = 0, $jumpto = "back", $msg = "", $links = array( ), $time = 3, $js = null )
    {
        $this->system->ErrorSet = array(
            "errorno" => $errorno,
            "message" => $msg,
            "jumpto" => $jumpto,
            "links" => $links,
            "time" => $time,
            "js" => $js
        );
    }

    public function begin( $url = null, $errAction = null, $shutHandle = null )
    {
        set_error_handler( array(
            $this,
            "_errorHandler"
        ) );
        if ( $this->transaction_start )
        {
            trigger_error( "The transaction has been started", E_USER_ERROR );
        }
        if ( !$url )
        {
            trigger_error( "The transaction has been started", E_USER_ERROR );
        }
        $this->transaction_start = true;
        $this->_shutHandle = $shutHandle ? $shutHandle : E_USER_ERROR | E_ERROR;
        $this->_action_url = $url;
        $this->_errAction = $errAction;
        $this->_err = array( );
    }

    public function end( $result = true, $message = null, $url = null, $showNotice = false )
    {
        if ( !$this->transaction_start )
        {
            trigger_error( "The transaction has not started yet", E_USER_ERROR );
        }
        $this->transaction_start = false;
        restore_error_handler( );
        if ( is_null( $url ) )
        {
            $url = $this->_action_url;
        }
        $this->splash( $result ? "success" : "failed", $url, $result ? $message : $message ? $message : __( "操作失败" ), $showNotice ? $this->_err : null );
    }

    public function end_only( )
    {
        if ( !$this->transaction_start )
        {
            trigger_error( "The transaction has not started yet", E_USER_ERROR );
        }
        $this->transaction_start = false;
        restore_error_handler( );
    }

    public function _errorHandler( $errno, $errstr, $errfile, $errline )
    {
        $errorlevels = array( 2048 => "Warning", 2048 => "Notice", 1024 => "Warning", 1024 => "Notice", 512 => "Warning", 256 => "Error", 128 => "Warning", 64 => "Error", 32 => "Warning", 16 => "Error", 8 => "Notice", 4 => "Error", 2 => "Warning", 1 => "Error" );
        $this->_err[] = array(
            "code" => $errno,
            "string" => $errstr,
            "file" => $errfile,
            "line" => $errline,
            "codeinfo" => $errorlevels[$errno]
        );
        if ( isset( $this->system->ErrorSet['errorno'], $this->_errAction[$this->system->ErrorSet['errorno']] ) )
        {
            $this->splash( "failed", $this->_errAction[$this->system->ErrorSet['errorno']], $errstr );
        }
        else
        {
            switch ( $errno )
            {
            case $errno & $this->_shutHandle :
                restore_error_handler( );
                $this->splash( "failed", $this->_action_url, $errstr, $this->_err );
            }
        }
        return true;
    }

    public function get_shopname( )
    {
        return $this->system->getConf( "system.shopname" );
    }

    public function getGlobal( $seo, &$result, $list = 0 )
    {
        foreach ( $seo as $val )
        {
            $funcName = "get_".$val;
            $runtime[$val] = $this->$funcName( $result, $list );
        }
    }

}

?>
