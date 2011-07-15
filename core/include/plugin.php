<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
define( LOWER_CASE, 1 );
define( UPPER_CASE, 2 );
class plugin extends shopobject
{

    var $plugin_type = null;
    var $plugin_name = null;
    var $_plugin_obj = null;

    function gettype( )
    {
        return array(
            "payment" => array(
                "text" => __( "支付方式" ),
                "type" => "file",
                "prefix" => "pay.",
                "case" => LOWER_CASE
            ),
            "dataio" => array(
                "text" => __( "导入导出" ),
                "type" => "file",
                "prefix" => "io."
            ),
            "messenger" => array(
                "text" => __( "联系会员" ),
                "type" => "dir",
                "prefix" => "messenger."
            ),
            "passport" => array(
                "text" => __( "登录整合" ),
                "type" => "file",
                "prefix" => "passport."
            ),
            "pmtScheme" => array(
                "text" => __( "促销规则" ),
                "type" => "file",
                "prefix" => "pmt."
            ),
            "schema" => array(
                "text" => __( "商品插件" ),
                "type" => "dir",
                "prefix" => "schema."
            ),
            "functions" => array(
                "text" => __( "行为扩展" ),
                "type" => "func",
                "prefix" => ""
            )
        );
    }

    function getfile( $item )
    {
        $file_name = $this->plugin_type == "dir" ? PLUGIN_DIR."/".$this->plugin_name."/".$item."/".( $this->prefix !== false ? $this->prefix : $this->plugin_name ).$item.".php" : PLUGIN_DIR."/".$this->plugin_name."/".( $this->prefix !== false ? $this->prefix : $this->plugin_name ).$item.".php";
        if ( is_file( $file_name ) )
        {
            return $file_name;
        }
        return false;
    }

    function _getclassname( $item )
    {
        return preg_replace( "/[\\.-]+/", "_", ( $this->prefix ? $this->prefix : $this->plugin_name ).$item );
    }

    function &load( $item )
    {
        if ( !$this->_plugin_obj[$item] )
        {
            if ( $file_name = $this->getfile( $item ) )
            {
                include_once( $file_name );
                $className = $this->_getclassname( $item );
                $obj = new $className( );
                return $obj;
            }
            trigger_error( "plugin file error", E_USER_ERROR );
        }
        return $this->_plugin_obj[$item];
    }

    function getheader( $file )
    {
        if ( ( $code = file_get_contents( $file ) ) !== false )
        {
            $tokens = token_get_all( $code );
        default :
            switch ( $type )
            {
                foreach ( $tokens as $token )
                {
                    if ( is_array( $token ) )
                    {
                        list( $type, $text ) = $token;
                    case T_VARIABLE :
                    case T_FUNCTION :
                    case T_NEW :
                    case T_CLASS :
                    case T_VAR :
                    }
                }
                return $result;
            case T_STRING :
            case T_WHITESPACE :
            case T_COMMENT :
            case T_ML_COMMENT :
            case 366 :
                $result .= $text;
            }
            return $result;
        }
    }

    function getparams( $item, $ifMethods = true, $withDesc = false )
    {
        $t = array(
            "name" => $item
        );
        $file = $this->getfile( $item );
        include_once( $file );
        $className = $this->_getclassname( $item );
        $t['class'] = $className;
        if ( class_exists( $className ) )
        {
            $o = new $className( );
            $t = array_merge( $t, get_object_vars( $o ) );
            if ( $ifMethods )
            {
                $t['methods'] = get_class_methods( $className );
            }
            $t['hasOptions'] = in_array( "getoptions", $t['methods'] ) || in_array( "getOptions", $t['methods'] );
            if ( in_array( "extravars", $t['methods'] ) || in_array( "extraVars", $t['methods'] ) )
            {
                $obj = new $className( );
                $t = array_merge( $t, $obj->extravars( ) );
            }
        }
        if ( $withDesc )
        {
            $t['desc'] = $this->getheader( $file );
        }
        return $t;
    }

    function filter( $el )
    {
        foreach ( $this->_filter as $k => $v )
        {
            settype( $v, "array" );
            foreach ( $v as $v1 )
            {
                if ( !isset( $el[$k] ) && !( $el[$k] == $v1 ) )
                {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    function getlist( $filter = array( ), $ifMethods = true, $withDesc = false )
    {
        $handle = opendir( PLUGIN_DIR."/".$this->plugin_name );
        $t = array( );
        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file[0] != "." )
            {
                if ( $this->plugin_case == LOWER_CASE )
                {
                    if ( $file != strtolower( $file ) )
                    {
                        break;
                    }
                }
                else
                {
                    if ( $this->plugin_case == UPPER_CASE && $file != strtoupper( $file ) )
                    {
                        break;
                    }
                }
                $params = null;
                if ( $this->plugin_type == "dir" )
                {
                    $item = $file;
                    if ( is_dir( PLUGIN_DIR."/".$this->plugin_name."/".$file ) && $this->getfile( $item ) )
                    {
                        $params = $this->getparams( $item, $ifMethods, $withDesc );
                    }
                }
                else
                {
                    if ( preg_match( "/^".( $this->prefix !== false ? str_replace( ".", "\\.", $this->prefix ) : $this->plugin_name )."([a-z0-9\\_]+)\\.php/i", $file, $match ) )
                    {
                        $item = $match[1];
                        $params = $this->getparams( $item, $ifMethods, $withDesc );
                        $params['item'] = $item;
                    }
                }
                if ( $params )
                {
                    $params['file'] = "plugins/".$this->plugin_name."/".$file;
                    $t[$item] = $params;
                }
            }
        }
        closedir( $handle );
        ksort( $t );
        if ( $filter )
        {
            $this->_filter = $filter;
            return array_filter( $t, array(
                $this,
                "filter"
            ) );
        }
        return $t;
    }

    function getoptions( $item, $valueOnly = false )
    {
        $obj = $this->load( $item );
        if ( method_exists( $obj, "getOptions" ) || method_exists( $obj, "getoptions" ) )
        {
            $options = $obj->getoptions( );
            foreach ( $options as $key => $value )
            {
                $v = $this->system->getconf( "plugin.".$this->plugin_name.".".$item.".config.".$key );
                if ( $valueOnly )
                {
                    $options[$key] = is_null( $v ) ? $options[$key] : $v;
                }
                else
                {
                    $options[$key]['value'] = is_null( $v ) ? $options[$key]['value'] : $v;
                }
            }
            return $options;
        }
    }

    function savecfg( $type, $data )
    {
        foreach ( $data as $key => $value )
        {
            $this->system->setconf( "plugin.".$this->plugin_name.".".$type.".config.".$key, $value );
        }
        return true;
    }

}

?>
