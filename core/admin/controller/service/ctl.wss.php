<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_wss extends adminPage
{

    public $workground = "analytics";
    public $WSS_REG_DOMAIN = "http://wss.cnzz.com/user/companion/shopex.php?";
    public $WSS_LOGIN_DOMAIN = "http://wss.cnzz.com/user/companion/shopex_login.php?";
    public $WSS_IFRAME_DOMAIN = "http://wss.cnzz.com/oem/udmin.php?";
    public $JS = "<script src='http://pw.cnzz.com/c.php?id=###&l=2' language='JavaScript' charset='gb2312'></script>";
    public $aError = array
    (
        -1 => "验证KEY错误",
        -2 => "域名长度错误",
        -3 => "域名输入错误",
        -4 => "域名开通错误",
        -5 => "IP限制"
    );
    public $path = array
    (
        0 => array
        (
            "text" => "统计报表"
        )
    );

    public function ctl_wss( )
    {
        parent::adminpage( );
        $this->ENCODESTR = "A34dfwfF";
    }

    public function show( )
    {
        $this->path[] = array(
            "text" => __( "统计功能配置" )
        );
        $this->pagedata['action'] = $this->getWss( );
        if ( $this->getWss( ) )
        {
            $this->pagedata['url'] = $this->apply( );
            $this->pagedata['wss_id'] = $this->getUserName( );
            $this->pagedata['wss_password'] = $this->getPassword( );
        }
        if ( $this->getShowIndex( ) )
        {
            $this->pagedata['str'] = __( "关闭统计功能" );
        }
        else
        {
            $this->pagedata['str'] = __( "开启统计功能" );
        }
        $this->page( "service/wss.html" );
    }

    public function register( )
    {
        set_time_limit( 0 );
        $net =& $this->system->loadModel( "utility/http_client" );
        $result = $net->get( $this->getLoginDomain( ) );
        $r = $this->response( $result );
        if ( $r )
        {
            $this->splash( "failed", "index.php?ctl=service/wss&act=show", __( "申请统计失败:" ).$this->aError[$r] );
            exit( );
        }
        if ( $this->setStatus( $result ) )
        {
            $this->splash( "success", "index.php?ctl=service/wss&act=show", "申请统计成功" );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=service/wss&act=show", "申请统计失败:参数错误" );
            exit( );
        }
    }

    public function clear( )
    {
        $this->system->setConf( "shopex.wss.enable", 0 );
        $this->splash( "success", "index.php?ctl=service/wss&act=show", __( "清除统计成功" ) );
    }

    public function getShowIndex( )
    {
        return $this->system->getConf( "shopex.wss.show" );
    }

    public function setShowIndex( )
    {
        if ( $this->getShowIndex( ) )
        {
            $this->system->setConf( "shopex.wss.show", 0 );
            $str = __( "关闭" );
        }
        else
        {
            $this->system->setConf( "shopex.wss.show", 1 );
            $str = __( "开启" );
            $this->setJs( );
        }
        $this->splash( "success", "index.php?ctl=service/wss&act=show", $str.__( "前台统计成功" ) );
    }

    public function apply( )
    {
        return $this->url = $this->WSS_LOGIN_DOMAIN."site_id=".$this->getUserName( )."&password=".$this->getPassword( );
    }

    public function setJs( )
    {
        $content = str_replace( "###", $this->getUserName( ), $this->JS );
        $this->system->setConf( "shopex.wss.js", $content );
    }

    public function getUserName( )
    {
        return $this->system->getConf( "shopex.wss.username" );
    }

    public function getPassword( )
    {
        return $this->system->getConf( "shopex.wss.password" );
    }

    public function setStatus( $r )
    {
        $tmp = explode( "@", $r );
        if ( $tmp[0] < 0 )
        {
            return FALSE;
        }
        $this->system->setConf( "shopex.wss.username", $tmp[0] );
        $this->system->setConf( "shopex.wss.password", $tmp[1] );
        $this->system->setConf( "shopex.wss.enable", 1 );
        return TRUE;
    }

    public function response( $result )
    {
        if ( isset( $this->aError[$result] ) )
        {
            return $result;
        }
        else
        {
            return FALSE;
        }
    }

    public function getLoginDomain( )
    {
        $domain = $_SERVER['HTTP_HOST'];
        $key = md5( $domain.$this->ENCODESTR );
        return $this->WSS_REG_DOMAIN."domain=".$domain."&key=".$key;
    }

    public function getWss( )
    {
        return $this->system->getConf( "shopex.wss.enable" );
    }

    public function logininx( )
    {
        $this->path[] = array(
            "text" => __( "访问统计" )
        );
        if ( $this->getWss( ) )
        {
            $this->pagedata['url'] = $this->apply( );
            $this->page( "service/wssframe.html" );
        }
        else
        {
            $this->show( );
        }
    }

    public function welcome( )
    {
        $this->page( "service/welcome.html" );
    }

}

?>
