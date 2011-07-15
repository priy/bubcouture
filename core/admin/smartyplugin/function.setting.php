<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_setting( $params, &$smarty )
{
    if ( !$GLOBALS['_settingList'] )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        $GLOBALS['GLOBALS']['_settingList'] =& $system->__setting->source( );
    }
    $system =& $GLOBALS['GLOBALS']['system'];
    $params = array_merge( $params, $GLOBALS['_settingList'][$params['key']] );
    $params['value'] = $system->getConf( $params['key'] );
    if ( $params['key'] == "site.tax_ratio" )
    {
        $params['value'] *= 100;
    }
    if ( $params['type'] == SET_T_INT )
    {
        $params['type'] = "number";
    }
    else if ( $params['type'] == SET_T_ENUM )
    {
        $params['type'] = "select";
        $params['required'] = TRUE;
    }
    else if ( $params['type'] == SET_T_BOOL )
    {
        $params['type'] = "bool";
    }
    else if ( $params['type'] == SET_T_TXT )
    {
        $params['type'] = "textarea";
    }
    else if ( $params['type'] == SET_T_FILE )
    {
        $params['type'] = "file";
    }
    else if ( $params['type'] == SET_T_DIGITS )
    {
        $params['type'] = "digits";
    }
    else
    {
        $params['type'] = "text";
    }
    if ( !$params['id'] )
    {
        $params['id'] = $smarty->new_dom_id( );
    }
    $params['name'] = ( $params['namespace'] ? $params['namespace'] : "setting" )."[".$params['key']."]";
    $key = $params['key'];
    unset( $params['desc'] );
    unset( $params['key'] );
    if ( $params['backend'] == "public" )
    {
        if ( !$GLOBALS['storager'] )
        {
            $system =& $GLOBALS['GLOBALS']['system'];
            $GLOBALS['GLOBALS']['storager'] = $system->loadModel( "system/storager" );
        }
        $storager =& $GLOBALS['GLOBALS']['storager'];
        $url = $storager->getUrl( $params['value'] );
        $html = "<img src=\"".$url."?".time( )."\" style=\"float:none\" />";
    }
    else
    {
        $html = "";
    }
    $func = "tpl_input_".$params['type'];
    if ( function_exists( $func ) )
    {
        echo $func( $params, $smarty );
    }
    else
    {
        if ( file_exists( CORE_INCLUDE_DIR."/smartyplugins/input.".$params['type'].".php" ) )
        {
            require( CORE_INCLUDE_DIR."/smartyplugins/input.".$params['type'].".php" );
            $smarty->_plugins['input'][$params['type']] = $func;
            echo $func( $params, $smarty );
        }
        else
        {
            if ( !function_exists( "tpl_input_default" ) )
            {
                require( CORE_INCLUDE_DIR."/smartyplugins/input.default.php" );
            }
            echo $html.tpl_input_default( $params, $smarty );
        }
    }
    unset( $func );
    unset( $params );
}

?>
