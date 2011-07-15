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
class plugin extends shopObject
{

    public $plugin_type = null;
    public $plugin_name = null;
    public $_plugin_obj = null;

    public function getType( )
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

    public function getFile( $item )
    {
        $file_name = $this->plugin_type == "dir" ? PLUGIN_DIR."/".$this->plugin_name."/".$item."/".( $this->prefix !== false ? $this->prefix : $this->plugin_name ).$item.".php" : PLUGIN_DIR."/".$this->plugin_name."/".( $this->prefix !== false ? $this->prefix : $this->plugin_name ).$item.".php";
        if ( is_file( $file_name ) )
        {
            return $file_name;
        }
        else
        {
            return false;
        }
    }

    public function _getClassName( $item )
    {
        return preg_replace( "/[\\.-]+/", "_", ( $this->prefix ? $this->prefix : $this->plugin_name ).$item );
    }

    public function &load( $item )
    {
        if ( !$this->_plugin_obj[$item] )
        {
            if ( $file_name = $this->getFile( $item ) )
            {
                include_once( $file_name );
                $className = $this->_getClassName( $item );
                $obj = new $className( );
                return $obj;
            }
            else
            {
                trigger_error( "plugin file error", E_USER_ERROR );
            }
        }
        return $this->_plugin_obj[$item];
    }

    public function getHeader( $file )
    {
        if ( ( $code = file_get_contents( $file ) ) !== false )
        {
            $tokens = token_get_all( $code );
            foreach ( $tokens as $token )
            {
                if ( is_array( $token ) )
                {
                    list( $type, $text ) = $token;
                    switch ( $type )
                    {
                    case T_VARIABLE :
                    case T_FUNCTION :
                    case T_NEW :
                    case T_CLASS :
                    case T_VAR :
                        return $result;
                    case T_STRING :
                        break;
                    case T_WHITESPACE :
                        break;
                    case T_COMMENT :
                    case T_ML_COMMENT :
                    case 366 :
                        $result .= $text;
                        break;
                    }
                }
            }
            return $result;
        }
    }

    public function getParams( $item, $ifMethods = true, $withDesc = false )
    {
        $t = array(
            "name" => $item
        );
        $file = $this->getFile( $item );
        include_once( $file );
        $className = $this->_getClassName( $item );
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
                $t = array_merge( $t, $obj->extraVars( ) );
            }
        }
        if ( $withDesc )
        {
            $t['desc'] = $this->getHeader( $file );
        }
        return $t;
    }

    public function filter( $el )
    {
        foreach ( $this->_filter as $k => $v )
        {
            settype( $v, "array" );
            foreach ( $v as $v1 )
            {
                if ( isset( $el[$k] ) && $el[$k] == $v1 )
                {
                    return true;
                }
            }
        }
        return false;
    }

    public function getList( $filter = array( ), $ifMethods = true, $withDesc = false )
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
                        continue;
                    }
                }
                else if ( $this->plugin_case == UPPER_CASE && $file != strtoupper( $file ) )
                {
                    continue;
                }
                $params = null;
                if ( $this->plugin_type == "dir" )
                {
                    $item = $file;
                    if ( is_dir( PLUGIN_DIR."/".$this->plugin_name."/".$file ) && $this->getFile( $item ) )
                    {
                        $params = $this->getParams( $item, $ifMethods, $withDesc );
                    }
                }
                else
                {
                    if ( preg_match( "/^".( $this->prefix !== false ? str_replace( ".", "\\.", $this->prefix ) : $this->plugin_name )."([a-z0-9\\_]+)\\.php/i", $file, $match ) )
                    {
                        $item = $match[1];
                        $params = $this->getParams( $item, $ifMethods, $withDesc );
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
        else
        {
            return $t;
        }
    }

    public function getOptions( $item, $valueOnly = false )
    {
        $obj = $this->load( $item );
        if ( method_exists( $obj, "getOptions" ) || method_exists( $obj, "getoptions" ) )
        {
            $options = $obj->getOptions( );
            foreach ( $options as $key => $value )
            {
                $v = $this->system->getConf( "plugin.".$this->plugin_name.".".$item.".config.".$key );
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

    public function saveCfg( $type, $data )
    {
        foreach ( $data as $key => $value )
        {
            $this->system->setConf( "plugin.".$this->plugin_name.".".$type.".config.".$key, $value );
        }
        return true;
    }

}

?>
