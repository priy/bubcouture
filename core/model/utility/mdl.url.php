<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_url extends modelfactory
{

    function map( $url )
    {
        $map = null;
        if ( include( PLUGIN_DIR."/functions/urlmap.php" ) )
        {
            if ( is_array( $map ) && $url != ( $result = preg_replace( array_keys( $map ), $map, $url ) ) )
            {
                $result = explode( "|", $result );
                if ( 2 < count( $result ) )
                {
                    $ctl = array_shift( $result );
                    $act = array_shift( $result );
                    return $this->system->mkurl( $ctl, $act, $result );
                }
                return $this->system->mkurl( $result[0], $result[1] );
            }
            return false;
        }
        return false;
    }

    function oldversionshopex( $action )
    {
        if ( $ret = $this->getgoo( $action ) )
        {
            return $ret;
        }
        $action['gOo'] = base64_decode( $action['gOo'] );
        if ( $ret = $this->getgoo( $action ) )
        {
            return $ret;
        }
        return $this->system->mkurl( "sitemap", "index" );
    }

    function getgoo( $action )
    {
        switch ( $action['gOo'] )
        {
        case "goods_search_list.dwt" :
        case "goods_category.dwt" :
            return $this->system->mkurl( "gallery", $this->system->getconf( "gallery.default_view" ), array(
                $action['gcat'],
                null,
                0,
                null,
                $action['p']
            ) );
        case "goods_details.dwt" :
            return $this->system->mkurl( "product", "index", array(
                $action['goodsid']
            ) );
        case "article_list.dwt" :
            return $this->system->mkurl( "artlist", "index", array(
                $action['acat']
            ) );
        case "article_details.dwt" :
            return $this->system->mkurl( "article", "index", array(
                $action['articleid']
            ) );
        case "register.dwt" :
            return $this->system->mkurl( "passport", "signup" );
        case "logout_act.do" :
            return $this->system->mkurl( "passport", "logout" );
        case "forget.dwt" :
            return $this->system->mkurl( "passport", "forget" );
        case "discuz_reply.do" :
            include_once( CORE_DIR."/func_ext.php" );
            return $this->system->mkurl( "passport", "callback", array( "discuz" ) )."?action=".http_build_query( $action );
        case "logout_act.do" :
            return $this->system->mkurl( "passport", "logout" );
        default :
        }
        return false;
    }

}

if ( !function_exists( "http_build_query" ) )
{
    function http_build_query( $formdata, $numeric_prefix = null )
    {
        if ( is_object( $formdata ) )
        {
            $formdata = get_object_vars( $formdata );
        }
        if ( !is_array( $formdata ) )
        {
            user_error( "http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.", E_USER_WARNING );
            return false;
        }
        if ( empty( $formdata ) )
        {
            return;
        }
        $separator = ini_get( "arg_separator.output" );
        $tmp = array( );
        foreach ( $formdata as $key => $val )
        {
            if ( is_integer( $key ) && $numeric_prefix != null )
            {
                $key = $numeric_prefix.$key;
            }
            if ( is_scalar( $val ) )
            {
                array_push( $tmp, urlencode( $key )."=".urlencode( $val ) );
            }
            else if ( is_array( $val ) )
            {
                array_push( $tmp, __http_build_query( $val, urlencode( $key ) ) );
            }
        }
        return implode( $separator, $tmp );
    }
    function __http_build_query( $array, $name )
    {
        $tmp = array( );
        foreach ( $array as $key => $value )
        {
            if ( is_array( $value ) )
            {
                array_push( $tmp, __http_build_query( $value, sprintf( "%s[%s]", $name, $key ) ) );
            }
            else if ( is_scalar( $value ) )
            {
                array_push( $tmp, sprintf( "%s[%s]=%s", $name, urlencode( $key ), urlencode( $value ) ) );
            }
            else if ( is_object( $value ) )
            {
                array_push( $tmp, __http_build_query( get_object_vars( $value ), sprintf( "%s[%s]", $name, $key ) ) );
            }
        }
        $separator = ini_get( "arg_separator.output" );
        return implode( $separator, $tmp );
    }
}
?>
