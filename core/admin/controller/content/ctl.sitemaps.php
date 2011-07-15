<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_sitemaps extends adminPage
{

    public $workground = "site";

    public function welcome( )
    {
        $this->page( "content/welcome.html" );
    }

    public function index( $close = FALSE )
    {
        $this->path[] = array(
            "text" => __( "网站内容管理" )
        );
        $this->pagedata['time'] = time( );
        $mdl =& $this->system->loadModel( "content/sitemap" );
        $list = $mdl->update( );
        $this->pagedata['base_url'] = $this->system->base_url( );
        $this->pagedata['cloasall'] = $close;
        foreach ( $list as $k => $v )
        {
            $list[$k]['editUrl'] = $this->_edit_act( $v );
        }
        $this->pagedata['list'] = $list;
        $this->page( "content/sitemap.html" );
    }

    public function toRemove( $node_id )
    {
        $this->begin( "index.php?ctl=content/sitemaps&act=index" );
        $mdl =& $this->system->loadModel( "content/sitemap" );
        if ( $mdl->checkDel( $node_id, $string ) )
        {
            $mdl->remove( $node_id );
            $this->splash( "success", "index.php?ctl=content/sitemaps&act=index", __( "栏目删除成功!" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=content/sitemaps&act=index", __( $string ? $string : __( "该栏目还有子项不能删除" ) ) );
        }
    }

    public function doAdd( $p_node_id = 0 )
    {
        $this->begin( "index.php?ctl=content/sitemaps&act=index" );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        if ( substr( $_POST['type'], 0, 7 ) == "plugin-" )
        {
            $ident = substr( $_POST['type'], 7 );
            $node = $sitemap->newNode( array(
                "node_type" => "action",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => "action_".$ident.":index"
            ) );
            $this->end( $node, "新页面执行成功" );
        }
        switch ( $_POST['type'] )
        {
        case "custompage" :
            $node = $sitemap->newNode( array(
                "node_type" => "custompage",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => "custompage:".$p_node_id,
                "item_id" => $_POST['item_id']
            ) );
            break;
        case "articles" :
            $node = $sitemap->newNode( array(
                "node_type" => "articles",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => "artlist:index"
            ) );
            break;
        case "artlist" :
            $node = $sitemap->newNode( array(
                "node_type" => "artlist",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => "artlist:index"
            ) );
            break;
        case "pageurl" :
            $node = $sitemap->newNode( array(
                "node_type" => "pageurl",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => ""
            ) );
            break;
        case "page" :
            $page =& $this->system->loadModel( "content/page" );
            $page_id = $sitemap->title2page( $_POST['title'] );
            $exists = $page->getExists( $_POST['title'] );
            if ( $exists == TRUE )
            {
                trigger_error( __( "该页面的名称已经存在" ), E_USER_ERROR );
            }
            $node = $sitemap->newNode( array(
                "node_type" => "page",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => "page:".$page_id
            ) );
            break;
        case "goodsCat" :
            $node = $sitemap->newNode( array(
                "node_type" => "goodsCat",
                "p_node_id" => $p_node_id,
                "title" => $_POST['title'],
                "action" => ""
            ) );
            break;
        default :
            if ( substr( $_POST['type'], 0, 7 ) == "action_" )
            {
                $action = substr( $_POST['type'], 7 );
                $this->end( $sitemap->newNode( array(
                    "node_type" => "action",
                    "p_node_id" => $p_node_id,
                    "title" => $_POST['title'],
                    "action" => $action
                ) ), __( "功能节点添加成功" ) );
            }
            else
            {
                trigger_error( __( "错误的功能代码" ).$_POST['type'], E_USER_ERROR );
            }
            break;
        }
        $this->end_only( );
        $this->location( $this->_edit_act( $node, "add" ) );
    }

    public function _edit_act( $data, $type )
    {
        switch ( $data['node_type'] )
        {
        case "page" :
            $str = "ctl=content/pages&act=index&p[0]=".urlencode( substr( $data['action'], strpos( $data['action'], ":" ) + 1 ) )."&p[1]=".$data['node_id'];
            break;
        case "articles" :
            if ( $type == "add" )
            {
                $str = "ctl=content/sitemaps&act=index";
            }
            else
            {
                $str = "ctl=content/articles&act=index&p[0]=".$data['node_id'];
            }
            break;
        case "artlist" :
            $str = "ctl=content/sitemaps&act=index&p[0]=".$data['node_id'];
            break;
        case "goodsCat" :
            $str = "ctl=content/content&act=indexOfgoodsCat&p[0]=".$data['node_id'];
            break;
        case "pageurl" :
            $str = "ctl=content/content&act=urllinkIndex&p[0]=".$data['node_id'];
            break;
        case "custompage" :
            $str = "ctl=content/content&act=custompage&p[0]=".$data['node_id'];
            break;
        default :
            return FALSE;
        }
        return "index.php?".$str."&_wg=site";
    }

    public function modify( $node_id )
    {
        $mdl =& $this->system->loadModel( "content/sitemap" );
        $node = $mdl->getNode( $node_id );
        $this->pagedata['node'] =& $node;
        foreach ( $mdl->update( ) as $item )
        {
            if ( FALSE === strpos( $item['path'].$item['node_id'], $node['path'].$node['node_id'] ) )
            {
                $this->pagedata['list'][] = $item;
            }
        }
        $this->page( "content/node_info.html" );
    }

    public function addNew( $p_node_id = 0 )
    {
        $this->path[] = array(
            "text" => __( "添加栏目" )
        );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $this->pagedata['path'] = $sitemap->getPathById( $p_node_id, FALSE );
        $addons =& $this->system->loadModel( "system/addons" );
        $this->pagedata['plugins'] = $addons->getList( "plugin_struct,plugin_ident,plugin_name,plugin_id", array( "plugin_type" => "shop" ) );
        $this->pagedata['actions'] = $sitemap->getActions( );
        $this->pagedata['parent_id'] = $p_node_id;
        $this->page( "content/newnode.html" );
    }

    public function update( )
    {
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $sitemap->updatePorder( $_POST['p_order'] );
        $this->splash( "success", "index.php?ctl=content/sitemaps", __( "排序已经更新" ) );
    }

    public function save( $node_id )
    {
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $this->begin( "index.php?ctl=content/sitemaps&act=index" );
        $this->end( $sitemap->save( $node_id, $_POST ), __( "栏目已修改" ) );
    }

    public function location( $url )
    {
        header( "Location: ".$url.( $_GET['_ajax'] ? "&_ajax=".$_GET['_ajax'] : NULL ) );
    }

    public function enableNode( $node_id )
    {
        $this->begin( "index.php?ctl=content/sitemaps&act=index" );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $rst = $sitemap->setVisibility( $node_id, TRUE );
        $this->end( $rst, __( "已设置显示状态" ) );
    }

    public function disableNode( $node_id )
    {
        $this->begin( "index.php?ctl=content/sitemaps&act=index" );
        $sitemap =& $this->system->loadModel( "content/sitemap" );
        $rst = $sitemap->setVisibility( $node_id, FALSE );
        $this->end( $rst, __( "已设置关闭状态" ) );
    }

}

?>
