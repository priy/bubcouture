<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_pages extends adminPage
{

    public $workground = "site";

    public function index( $ident, $node_id )
    {
        $ident = urldecode( $ident );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $this->path[] = array(
            "text" => __( "编辑单独页面-[" ).$ident."]"
        );
        $this->pagedata['ident'] = $ident;
        $this->pagedata['node_id'] = $node_id;
        $this->pagedata['path'] = $sitemap->getPathById( $node_id );
        $this->pagedata['themes'] = $this->system->getConf( "system.ui.current_theme" );
        if ( $this->pagedata['path'][count( $this->pagedata['path'] ) - 1]['title'] )
        {
            $this->path[] = array(
                "text" => $this->pagedata['path'][count( $this->pagedata['path'] ) - 1]['title']
            );
        }
        return $this->singlepage( "content/page_edit.html" );
        $this->page( "content/page_edit.html" );
    }

    public function create_page( $ident, $p_node_id = 0, $template )
    {
        $oTemplate = $this->system->loadModel( "system/template" );
        $oTemplate->update_template( "page", $aData['brand_id'], $aData['brand_template'], "page" );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $page =& $this->system->loadModel( "content/page" );
        $exists = $page->getExists( $ident, $needCreatePage );
        header( "Content-Type: text/html;charset=utf-8" );
        if ( $exists === TRUE )
        {
            echo "该页面的名称已经存在, <a href=\"javascript:void()\" onclick=\"window.close()\">按此关闭本页</a>";
            exit( );
        }
        if ( preg_match( "/[\\/&\\\\]/", $ident ) )
        {
            echo "页面的名称中含有特殊符号, <a href=\"javascript:void()\" onclick=\"window.close()\">按此关闭本页</a>";
            exit( );
        }
        $page_id = $exists;
        $node = $sitemap->newNode( array(
            "node_type" => "page",
            "p_node_id" => $p_node_id,
            "title" => $ident,
            "action" => "page:".$page_id
        ) );
        $ident = urldecode( $ident );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $this->path[] = array(
            "text" => __( "编辑单独页面-[" ).$ident."]"
        );
        $this->pagedata['ident'] = $page_id;
        $this->pagedata['node_id'] = $node_id;
        $this->pagedata['path'] = $sitemap->getPathById( $node_id );
        $this->pagedata['themes'] = $this->system->getConf( "system.ui.current_theme" );
        $this->pagedata['needCreatePage'] = $needCreatePage;
        if ( $this->pagedata['path'][count( $this->pagedata['path'] ) - 1]['title'] )
        {
            $this->path[] = array(
                "text" => $this->pagedata['path'][count( $this->pagedata['path'] ) - 1]['title']
            );
        }
        if ( $template )
        {
            $node_id = $this->system->db->lastInsertId( );
            $oTemplate->update_template( "page", $node_id, $template, "page" );
        }
        return $this->singlepage( "content/page_edit.html" );
        $this->page( "content/page_edit.html" );
    }

    public function editor( $ident, $layout = NULL )
    {
        $ident = urldecode( $ident );
        header( "Content-type: text/html;charset=utf-8" );
        $page =& $this->system->loadModel( "content/page" );
        $page->editor( $ident, $layout, $_GET['theme'] );
        $this->pagedata['header'] = $page->_header( );
        $this->pagedata['footer'] = $page->_footer( );
        $this->pagedata['include'] = "page:".$ident;
        $this->_plugins['compiler']['widgets'] = array(
            $this,
            "_widgets_bar"
        );
        $this->display( "content/page_frame.html" );
    }

    public function _widgets_bar( $tag_args, &$smarty )
    {
        $s = $this->_current_file;
        $i = intval( $smarty->_wgbar[$s]++ );
        $args = $tag_args;
        return "echo '<div class=\"shopWidgets_panel\" base_file=\"".$s."\" base_slot=\"".$i."\" base_id=\"".substr( $args['id'], 1, -1 )."\"  >';\$system = &\$GLOBALS['system'];\n        if(!\$GLOBALS['_widgets_mdl'])\$GLOBALS['_widgets_mdl'] = \$system->loadModel('content/widgets');\n        \$widgets = &\$GLOBALS['_widgets_mdl'];\n        \$widgets->adminLoad(\"".$s."\",".( $args['id'] ? $i.",".$args['id'] : $i ).");echo '</div>';";
    }

    public function save( )
    {
        if ( $_POST['ident'] && $_POST['node_id'] )
        {
            $systmpl =& $this->system->loadModel( "content/systmpl" );
            $systmpl->set( "pages/".$_POST['ident'], $_POST['body'] );
            $sitemap =& $this->system->loadModel( "content/sitemap" );
            $setTitle = $sitemap->setTitle( $_POST['node_id'], $_POST['title'] );
            $this->splash( "success", "index.php?ctl=content/sitemaps", __( "页面成功保存" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=content/sitemaps", __( "页面保存失败" ) );
        }
    }

    public function view( $style )
    {
        $this->pagedata['page'] = "systmpl:frames/".$style;
        $this->system->loadModel( "content/systmpl" );
        $this->display( "content/page.html" );
    }

    public function layout( $ident )
    {
        $page =& $this->system->loadModel( "content/page" );
        $this->pagedata['layouts'] =& $page->getList( );
        $this->pagedata['ident'] = $ident;
        $this->display( "content/layout.html" );
    }

    public function widgetinfo( $type )
    {
        $widgets =& $this->system->loadModel( "content/widgets" );
        $this->pagedata['widgets'] = $widgets->getWidgetsInfo( $type );
        $this->display( "content/widgets/info.html" );
    }

}

?>
