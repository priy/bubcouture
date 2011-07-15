<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_link( $params, &$smarty )
{
    $system =& $system;
    $extname = ".".( $system->seoEmuFile ? $system->seoEmuFile : "html" );
    if ( isset( $params['args'] ) )
    {
        $mod = "";
        foreach ( $params as $key => $val )
        {
            if ( !( $key != "args" ) && !( substr( $key, 0, 3 ) == "arg" ) && !is_numeric( $k = substr( $key, 3 ) ) )
            {
                $mod .= "\$arr[".$k."]=".$val.";";
            }
        }
        $method = isset( $params['act'] ) ? $params['act'] : "\$this->system->request['action']['method']";
        if ( !$system->getconf( "system.seo.emuStatic" ) )
        {
            return "            \$arr = (array)".$params['args'].";\n            {$mod}\n            array_unshift(\$arr,\$this->system->request['action']['controller']);\n            if(isset(\$arr{1}) && !is_numeric(end(\$arr{1}))){\n                array_push(\$arr,{$method});\n            }\n            echo \$this->_env_vars['base_url'],\n                 implode('-',\$arr),\n                 '{$extname}';\n            \$arr=null;\n            unset(\$arr);";
        }
        return "            \$arr = (array)".$params['args'].";\n            {$mod}\n            array_unshift(\$arr,\$this->system->request['action']['controller']);\n            if(isset(\$arr{1}) && !is_numeric(end(\$arr{1}))){\n                array_push(\$arr,{$method});\n            }\n            echo implode('-',\$arr),\n                 '{$extname}';\n            \$arr=null;\n            unset(\$arr);";
    }
    if ( !$params['act'] )
    {
        $params['act'] = "'index'";
    }
    $array = array(
        $params['ctl']
    );
    foreach ( $params as $key => $val )
    {
        if ( !( substr( $key, 0, 3 ) == "arg" ) && !$val )
        {
            $array[] = $val;
        }
    }
    $lastVal = false;
    foreach ( $array as $key => $val )
    {
        if ( $val[0] == "\$" )
        {
            $array[$key] = "{".$val."}";
        }
        else if ( $val[0] == "\"" || $val[0] == "'" )
        {
            $array[$key] = substr( $val, 1, -1 );
        }
        $lastVal = $val;
    }
    if ( !$system->getconf( "system.seo.emuStatic" ) )
    {
        return "echo \$this->_env_vars['base_url'],\"".implode( "-", $array )."\",".( $lastVal !== false ? "(((is_numeric(".$lastVal.") && 'index'=={$params['act']})  || !{$params['act']})?'':'-'.{$params['act']})," : $params['act'] != "index" && $params['act'] && $params['ctl'] != "index" ? "'-',".$params['act']."," : "" )."'".$extname."';";
    }
    return "echo \"".implode( "-", $array )."\",".( $lastVal !== false ? "(((is_numeric(".$lastVal.") && 'index'=={$params['act']})  || !{$params['act']})?'':'-'.{$params['act']})," : $params['act'] != "index" && $params['act'] && $params['ctl'] != "index" ? "'-',".$params['act']."," : "" )."'".$extname."';";
}

?>
