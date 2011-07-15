<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_widgets( $attrs, &$compiler )
{
    $solt = intval( $compiler->_wgbar[$compiler->_parent->_files[0]]++ );
    $current_file = $compiler->_parent->_files[0];
    if ( substr( $current_file, 0, 5 ) == "page:" )
    {
        $current_file = substr( $current_file, 5 );
        $tmp_where = "base_file = '".$current_file."' OR base_file = 'page:".$current_file."'";
    }
    else
    {
        $tmp_where = "base_file = '".$current_file."'";
    }
    $system =& $system;
    $id = $compiler->_cache_id[$current_file]++;
    if ( !isset( $compiler->_cache[$current_file] ) )
    {
        $db =& $system->database( );
        $c = array( );
        $all = $db->select( "select * from sdb_widgets_set where ".$tmp_where." order by widgets_order" );
        foreach ( $all as $i => $r )
        {
            if ( $r['base_id'] )
            {
                $c['id'][$r['base_id']][] =& $all[$i];
            }
            else
            {
                $c['slot'][$r['base_slot']][] =& $all[$i];
            }
        }
        $compiler->_cache[$current_file] =& $c;
    }
    if ( isset( $attrs['id'] ) )
    {
        if ( $attrs['id'][0] == "\"" || $attrs['id'][0] == "'" )
        {
            $attrs['id'] = substr( $attrs['id'], 1, -1 );
        }
        $widgets_group = $compiler->_cache[$current_file]['id'][$attrs['id']];
    }
    else
    {
        $widgets_group = $compiler->_cache[$current_file]['slot'][$solt];
    }
    if ( isset( $widgets_group[0] ) )
    {
        $wg_compiler =& $compiler;
        $return = "unset(\$this->_vars);";
        foreach ( $widgets_group as $widget )
        {
            if ( !$widget['tpl'] )
            {
                $widget['tpl'] = "default.html";
            }
            $tpl = PLUGIN_DIR."/widgets/".$widget['widgets_type']."/".$widget['tpl'];
            $params = unserialize( $widget['params'] );
            if ( $widget['widgets_type'] == "html" )
            {
                if ( $params['html'] )
                {
                    $content = $params['html'];
                }
                else
                {
                    $content = $params['usercustom'];
                }
            }
            else
            {
                $func_file = "/widgets/".$widget['widgets_type']."/widget_".$widget['widgets_type'].".php";
                $return .= "\$setting = ".var_export( $params, 1 ).";\$this->bundle_vars['setting'] = &\$setting;";
                if ( file_exists( PLUGIN_DIR.$func_file ) )
                {
                    $return .= "if(!function_exists('widget_".$widget['widgets_type']."')){require(PLUGIN_DIR.'".$func_file."');}";
                    $return .= "\$this->_vars = array('data'=>widget_".$widget['widgets_type']."(\$setting,\$GLOBALS['system']),'widgets_id'=>'".$widget['widgets_id']."');";
                }
                else
                {
                    $return .= "\$this->_vars = array('widgets_id'=>'".$widget['widgets_id']."');";
                }
                $content = file_get_contents( $tpl );
            }
            $pattern = "/(\\'|\")images/i";
            $replacement = "\$1plugins/widgets/".$widget['widgets_type']."/images/";
            $content = preg_replace( $pattern, $replacement, $content );
            $wg_compiler->bundle_vars = array(
                "setting" => $params
            );
            $return .= "ob_start();?>".$wg_compiler->_compile_file( $content )."<?"."php ";
            $wg_compiler->bundle_vars = null;
            $return .= "\$body = str_replace('%THEME%','{ENV_theme_dir}',ob_get_contents());ob_end_clean();";
            if ( file_exists( $_border = THEME_DIR."/".$compiler->_parent->theme."/".$widget['border'] ) )
            {
                $return .= "\$this->_vars = array('body'=>&\$body,'title'=>'{$widget['title']}','widgets_id'=>'{$widget['domid']}','widgets_classname'=>'{$widget['classname']}');";
                $return .= "?>".$wg_compiler->_compile_file( file_get_contents( $_border ) )."<?"."php ";
            }
            else
            {
                $return .= "echo '<div id=\"".$widget['widgets_id']."\">',\$body,'</div>';unset(\$body);";
            }
        }
        return $return."\$setting=null;\$this->_vars = &\$this->pagedata;";
    }
    else
    {
        return "";
    }
}

?>
