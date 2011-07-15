<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !class_exists( "pageFactory" ) )
{
    require( CORE_DIR."/include/pageFactory.php" );
}
class shopex_stat_modifiers extends pageFactory
{

    public function shopex_stat_modifiers( )
    {
        parent::pagefactory( );
        $this->system =& $GLOBALS['GLOBALS']['system'];
    }

    public function print_footer( &$content )
    {
        $this->system->setConf( "site.rsc_rpc", "1" );
        if ( $this->system->getConf( "site.rsc_rpc" ) && defined( "RSC_RPC" ) && ( $certificate = $this->system->getConf( "certificate.id" ) ) )
        {
            $p = "";
            if ( isset( $_COOKIE['SHOPEX_STATINFO'] ) )
            {
                foreach ( unserialize( $_COOKIE['SHOPEX_STATINFO'] ) as $k => $v )
                {
                    $p .= "&_".$k."=".urlencode( $v );
                }
            }
            if ( $this->system->getConf( "site.orderinfo" ) )
            {
                $orderinfo = unserialize( $this->system->getConf( "site.orderinfo" ) );
                foreach ( $orderinfo as $key => $value )
                {
                    $p .= "&".$key."=".urlencode( $value );
                }
            }
            if ( $this->system->getConf( "site.addmenbyadmin" ) )
            {
                $userinfo = unserialize( $this->system->getConf( "site.addmenbyadmin" ) );
                foreach ( $userinfo as $key => $value )
                {
                    $p .= "&".$key."=".urlencode( $value );
                }
            }
            if ( $this->system->getConf( "site.payinfo" ) )
            {
                $payinfo = unserialize( $this->system->getConf( "site.payinfo" ) );
                foreach ( $payinfo as $key => $value )
                {
                    $p .= "&".$key."=".urlencode( $value );
                }
            }
            if ( $this->system->getConf( "site.goods_status" ) )
            {
                $goods_status = unserialize( $this->system->getConf( "site.goods_status" ) );
                foreach ( $goods_status as $key => $value )
                {
                    $p .= "&".$key."=".urlencode( $value );
                }
            }
            if ( isset( $_COOKIE['SHOPEX_STATINFO_GOODS'] ) )
            {
                foreach ( unserialize( $_COOKIE['SHOPEX_STATINFO_GOODS'] ) as $k => $v )
                {
                    $p .= "&_".$k."=".urlencode( $v );
                }
            }
            $result = setcookie( COOKIE_PFIX."[SHOPEX_STATINFO]", "", 0, "/" );
            $result = setcookie( COOKIE_PFIX."[SHOPEX_STATINFO_GOODS]", "", 0, "/" );
            if ( preg_match( "/<title>([\\x00-\\xFF]*)<\\/title>/", $content, $titleinfo ) )
            {
                $p .= "&_pagetitle=".urlencode( $titleinfo[1] );
            }
            $RSC_RPC_STR = "<script>\n            withBroswerStore(function(store){\n               function randomChar(l)  {\n                    var  x=\"0123456789qwertyuioplkjhgfdsazxcvbnm\";\n                    var  tmp=\"\";\n                    for(var  i=0;i<  l;i++)  {\n                        tmp  +=  x.charAt(Math.ceil(Math.random()*100000000)%x.length);\n                    }\n                    return  tmp;\n               }\n               var lf = decodeURI(window.location.href);\n\n              var new_hs = \"\";\n              var pos = lf.indexOf(\"#r-\") ;\n              var pos2 = lf.indexOf(\"%23r-\") ;\n              if(pos!=-1||pos2!=-1){\n                if(pos2!=-1){\n                    pos=pos2+2;\n                }\n                new_hs=lf.substr(pos+1);\n            }\n               var old_hs = Cookie.get(\"S[SHOPEX_ADV_HS]\");\n               if(new_hs && old_hs!=new_hs){\n                    Cookie.set(\"S[SHOPEX_ADV_HS]\",new_hs);\n               }\n               store.get(\"jsapi\",function(data){\n                       var script = document.createElement(\"script\");\n                       var sessionid = Cookie.get(\"JS_SESSIONID\")\n                        if(sessionid == null){\n                            sessionid=randomChar(32)\n                            Cookie.set(\"JS_SESSIONID\",sessionid)\n                        }\n\n                       var _src = \"".RSC_RPC."/jsapi?certi_id=".$certificate."&_dep=\"+sessionid+\"&pt=".urlencode( $this->system->request['action']['controller'] ).":".urlencode( $this->system->request['action']['method'] )."&app=shopex(".$this->system->_app_version.")&uid=\"+(encodeURIComponent(Cookie.get(\"S[MEMBER]\") || \"\").split(\"-\")[0])+\"&ref=\"+encodeURIComponent(document.referrer)+\"&sz=\"+JSON.encode(window.getSize())+\"&hs=\"+encodeURIComponent(Cookie.get(\"S[SHOPEX_ADV_HS]\") || new_hs)+\"&rt=".time( )."".$p."\";\n\n                       if(data){\n                            try{\n                               data = JSON.decode(data);\n                            }catch(e){}\n                              if(\$type(data)==\"object\"){\n                                 _src +=\"&\"+Hash.toQueryString(data);\n                              }else if(\$type(data)==\"string\"){\n                                 _src +=\"&\"+data;\n                              }\n                          }\n\n                      script.setAttribute(\"src\",_src);\n                      document.head.appendChild(script);\n\n               });\n\n            });\n            </script>";
        }
        $result = setcookie( COOKIE_PFIX."[SHOPEX_STATINFO]", "", 0, "/" );
        $result = setcookie( COOKIE_PFIX."[SHOPEX_STATINFO_GOODS]", "", 0, "/" );
        $this->system->setConf( "site.orderinfo", "" );
        $this->system->setConf( "site.addmenbyadmin", "" );
        $this->system->setConf( "site.goods_status", "" );
        $this->system->setConf( "addmoney", "" );
        $this->system->setConf( "site.payinfo", "" );
        return str_replace( "</body>", $RSC_RPC_STR."</body>", $content );
    }

}

?>
