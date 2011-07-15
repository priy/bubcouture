<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "plugin.php" );
class mdl_page extends plugin
{

    var $plugin_name = "layout";
    var $plugin_type = "dir";
    var $prefix = "layout_";

    function getexists( $title, &$needCreatePage )
    {
        $needCreatePage = false;
        if ( $this->db->selectrow( "select node_id from sdb_sitemaps where title=\"".$title."\"" ) )
        {
            return true;
        }
        $oSitemap =& $this->system->loadmodel( "content/sitemap" );
        $ident = $oSitemap->title2page( $title );
        while ( $this->db->selectrow( "select page_id from sdb_pages where page_name=\"".$ident."\"" ) )
        {
            $needCreatePage = true;
            $ident .= "1";
        }
        return $ident;
    }

    function set_tpl_content( $aData )
    {
        if ( $aData )
        {
            $aData['edittime'] = time( );
            if ( !( $rs = $this->db->selectrow( "select * from sdb_systmpl where tmpl_name=\"".$aData['tmpl_name']."\"" ) ) )
            {
                $rs = $this->db->query( "select * from sdb_systmpl where 0=1" );
                $sqlString = $this->db->getinsertsql( $rs, $aData );
                $this->db->exec( $sqlString );
            }
            else
            {
                $rs = $this->db->query( "SELECT * FROM sdb_systmpl WHERE tmpl_name='".$aData['tmpl_name']."'" );
                $sql = $this->db->getupdatesql( $rs, $aData );
                $this->db->exec( $sql );
            }
        }
        return true;
    }

    function get_tpl_content( $file_name )
    {
        if ( !( $rs = $this->db->selectrow( "select  content from sdb_systmpl where tmpl_name=\"".$file_name."\"" ) ) )
        {
            if ( file_exists( CORE_DIR."/html/pages/".$ident.".txt" ) )
            {
                return file_get_contents( CORE_DIR."/html/pages/".$file_name.".txt" );
            }
        }
        else
        {
            return $rs['content'];
        }
    }

    function editor( $ident, $layout, $theme )
    {
        if ( !$_GET['needCreatePage'] )
        {
            $rs = $this->db->exec( "select page_name,page_content,page_time,page_title from sdb_pages where page_name=\"".$ident."\"" );
            $rows = $this->db->getrows( $rs );
        }
        else
        {
            $rs = $this->db->exec( "select page_name,page_content,page_time,page_title from sdb_pages where 1=2" );
            $rows = $this->db->getrows( $rs );
        }
        if ( !$rows )
        {
            if ( file_exists( CORE_DIR."/html/pages/".$ident.".html" ) )
            {
                $sql = $this->db->getupdatesql( $rs, array(
                    "page_name" => $ident,
                    "page_time" => time( ),
                    "page_title" => $ident,
                    "page_content" => "<{widgets}>"
                ), true );
                $this->db->exec( $sql );
                $this->db->exec( "delete from sdb_widgets_set where base_file=\"page:".$ident."\"" );
                $rs = $this->db->exec( "select * from sdb_widgets_set where 0=1" );
                $html_content = file_get_contents( CORE_DIR."/html/pages/".$ident.".html" );
                $html_content = str_replace( "<{t}>", "", $html_content );
                $html_content = str_replace( "<{/t}>", "", $html_content );
                $sql = $this->db->getinsertsql( $rs, array(
                    "base_file" => $ident,
                    "base_slot" => 0,
                    "widgets_type" => "usercustom",
                    "widgets_order" => 0,
                    "border" => "__none__",
                    "tpl" => "default.html",
                    "params" => array(
                        "usercustom" => $html_content
                    ),
                    "modified" => time( )
                ) );
                $this->db->exec( $sql );
            }
            else
            {
                $sql = $this->db->getupdatesql( $rs, array(
                    "page_name" => $ident,
                    "page_time" => time( ),
                    "page_title" => $ident,
                    "page_content" => file_get_contents( PLUGIN_DIR."/layout/1-column/layout.html" )
                ), true );
                $this->db->exec( $sql );
            }
        }
        else if ( $layout && ( $sql = $this->db->getupdatesql( $rs, array(
                    "page_content" => file_get_contents( PLUGIN_DIR."/layout/".$layout."/layout.html" ),
                    "page_time" => time( )
                ), true ) ) )
        {
            $this->db->exec( $sql );
            $setting = $this->getparams( $layout );
            $setting['slotsNum'] = intval( $setting['slotsNum'] );
            if ( 0 < $setting['slotsNum'] )
            {
                --$setting['slotsNum'];
                $this->db->exec( "update sdb_widgets_set set base_slot=".$this->db->quote( $setting['slotsNum'] )." where base_slot>".intval( $setting['slotsNum'] )." and base_file='page:".$ident."'" );
            }
        }
        return true;
    }

    function _widgets_bar( $tag_args, &$smarty )
    {
        $s = $this->_current_file;
        $i = intval( $smarty->_wgbar[$s]++ );
        $args = $tag_args;
        return "echo '<div class=\"shopWidgets_panel\" base_file=\"".$s."\" base_slot=\"".$i."\" base_id=\"".substr( $args['id'], 1, -1 )."\"  >';\$system = &\$GLOBALS['system'];\n        if(!\$GLOBALS['_widgets_mdl'])\$GLOBALS['_widgets_mdl'] = \$system->loadModel('content/widgets');\n        \$widgets = &\$GLOBALS['_widgets_mdl'];\n        \$widgets->adminLoad(\"".$s."\",".( $args['id'] ? $i.",".$args['id'] : $i ).");echo '</div>'";
    }

    function _header( $theme )
    {
        $ret = "<base href=\"".$this->system->base_url( )."\"/>";
        if ( constant( "DEBUG_CSS" ) )
        {
            $ret .= "<link rel=\"stylesheet\" href=\"statics/framework.css\" type=\"text/css\" />";
            $ret .= "<link rel=\"stylesheet\" href=\"statics/shop.css\" type=\"text/css\" />";
            $ret .= "<link rel=\"stylesheet\" href=\"statics/widgets.css\" type=\"text/css\" />";
            $ret .= "<link rel=\"stylesheet\" href=\"statics/widgets_edit.css\" type=\"text/css\" />";
        }
        else if ( constant( "GZIP_CSS" ) )
        {
            $ret .= "<link rel=\"stylesheet\" href=\"statics/style.zcss\" type=\"text/css\" />";
            $ret .= "<link rel=\"stylesheet\" href=\"statics/widgets_edit.css\" type=\"text/css\" />";
        }
        else
        {
            $ret .= "<link rel=\"stylesheet\" href=\"statics/style.css\" type=\"text/css\" />";
            $ret .= "<link rel=\"stylesheet\" href=\"statics/widgets_edit.css\" type=\"text/css\" />";
        }
        $tmp_path = "http://".$_SERVER['HTTP_HOST']."/".dirname( $_SERVER['PHP_SELF'] );
        if ( constant( "DEBUG_JS" ) )
        {
            $ret .= "<script src=\"".$tmp_path."/js_src/moo.js\"></script>\n                    <script src=\"".$tmp_path."/js_src/moomore.js\"></script>\n                    <script src=\"".$tmp_path."/js_src/mooadapter.js\"></script>\n                    <script src=\"".$tmp_path."/js_src/jstools.js\"></script>\n                    <script src=\"".$tmp_path."/js_src/coms/dragdropplus.js\"></script>\n                    <script src=\"".$tmp_path."/js_src/coms/shopwidgets.js\"></script>";
            return $ret;
        }
        if ( constant( "GZIP_JS" ) )
        {
            $ret .= "<script src=\"".$tmp_path."/js/package/tools.jgz\"></script>\n                     <script src=\"".$tmp_path."/js/package/widgetsedit.jgz\"></script>";
            return $ret;
        }
        $ret .= "<script src=\"".$tmp_path."/js/package/tools.js\"></script>\n                     <script src=\"".$tmp_path."/js/package/widgetsedit.js\"></script>";
        return $ret;
    }

    function _footer( )
    {
        return __( "<div id='drag_operate_box' class='drag_operate_box' style='visibility:hidden;'>\n       <div class='drag_handle_box'>\n             <table cellpadding='0' cellspacing='0' width='100%'>\n                                           <tr>\n                                           <td><span class='dhb_title'>标题</span></td>\n                                           <td width='40'><span class='dhb_edit'>编辑</span></td>\n                                           <td width='40'><span class='dhb_del'>删除</span></td>\n                                           </tr>\n              </table>\n              </div>\n          </div>\n\n          <div id='drag_ghost_box' class='drag_ghost_box' style='visibility:hidden'>\n\n          </div>" );
    }

}

?>
