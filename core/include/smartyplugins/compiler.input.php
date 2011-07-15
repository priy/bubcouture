<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_input( $params, &$compiler )
{
    if ( !$params['params'] || strpos( $params['type'], "\$" ) === false )
    {
        $type = $params['type'][0] == "\"" || $params['type'][0] == "'" ? substr( $params['type'], 1, -1 ) : $params['type'];
        if ( substr( $type, 0, 7 ) == "object:" )
        {
            do
            {
                list( , $object, $params['key'] ) = explode( ":", $type );
                $params['object'] = "'".$object."'";
                $function = $compiler->_plugin_exists( "object_".str_replace( "/", "_", $object ), "input" );
                if ( $function )
                {
                    break;
                }
                else
                {
                    $function = $compiler->_plugin_exists( "object", "input" );
                }
            }
            else
            {
                $function = $compiler->_plugin_exists( $type, "input" );
            }
            if ( $function )
            {
                break;
            }
            $function = $compiler->_plugin_exists( $type = "default", "input" );
        } while ( 0 );
        unset( $params->'type' );
        $_args = array( );
        foreach ( $params as $key => $value )
        {
            if ( is_bool( $value ) )
            {
                $value = $value ? "true" : "false";
            }
            else if ( is_null( $value ) )
            {
                $value = "null";
            }
            $_args[$key] = "'".$key."' => {$value}";
        }
        return "echo ".$function."(array(".implode( ",", ( array )$_args )."), \$this);";
    }
    $compiler->_plugin_exists( "default", "input" );
    if ( $params['params'] )
    {
        $return = "\$params = ".$params['params'].";";
    }
    else
    {
        $return = "\$params = array();";
    }
    unset( $params->'params' );
    if ( !$compiler->_included_input_func_map )
    {
        $return .= "\$this->input_func_map = ".var_export( $compiler->get_plugins_by_type( "input" ), 1 ).";";
        $compiler->_included_input_func_map = true;
    }
    foreach ( $params as $key => $value )
    {
        if ( is_bool( $value ) )
        {
            $value = $value ? "true" : "false";
        }
        else if ( is_null( $value ) )
        {
            $value = "null";
        }
        $return .= "\$params['".$key."'] = {$value};";
    }
    $return .= "if(substr(\$params['type'],0,7)=='object:'){\n    list(,\$params['object'],\$params['key']) = explode(':',\$params['type']);\n    \$obj = str_replace('/','_',\$params['object']);\n    \$func = 'tpl_input_object_'.\$obj;\n    if(!function_exists(\$func)){\n        if(isset(\$this->input_func_map['object_'.\$obj])){\n            require(CORE_DIR.\$this->input_func_map['object_'.\$obj]);\n            \$this->_plugins['input']['object_'.\$obj] = \$func;\n        }else{\n            \$func = 'tpl_input_object';\n            \$params['type'] = 'object';\n        }\n    }\n}else{\n    \$func = 'tpl_input_'.\$params['type'];\n}\nif(function_exists(\$func)){\n    echo \$func(\$params,\$this);\n}elseif(isset(\$this->input_func_map[\$params['type']])){\n    require(CORE_DIR.\$this->input_func_map[\$params['type']]);\n    \$this->_plugins['input'][\$params['type']] = \$func;\n    echo \$func(\$params,\$this);\n}else{\n    echo tpl_input_default(\$params,\$this);\n}\nunset(\$func,\$params);";
    return $return;
}

?>
