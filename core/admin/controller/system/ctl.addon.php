<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "objectPage.php" );
class ctl_addon extends objectPage
{

    public $workground = "tools";
    public $object = "system/addons";
    public $allowImport = FALSE;
    public $allowExport = FALSE;
    public $finder_action_tpl = "system/addons/finder_action.html";
    public $deleteAble = FALSE;
    public $filterUnable = TRUE;

    public function _views( )
    {
        $views = array( );
        foreach ( $this->model->alltypes as $k => $v )
        {
            $views[$v] = array(
                "plugin_type" => $k
            );
        }
        return $views;
    }

    public function refresh( )
    {
        $this->begin( "index.php?ctl=system/addon&act=index" );
        $this->model =& $this->system->loadModel( "system/addons" );
        $this->end( $this->model->refresh( ) );
    }

    public function plugin( $type = "payment" )
    {
        $GLOBALS['_GET']['p'][0] = $type;
        $this->pagedata['allow_disable'] = FALSE;
        $model =& $this->system->loadModel( "system/addons" );
        $tpList = $model->getType( );
        $this->path[] = array(
            "text" => __( "插件" )
        );
        $this->path[] = array(
            "text" => $tpList[$type]['text']
        );
        $model->plugin_type = $tpList[$type]['type'];
        $model->prefix = $tpList[$type]['prefix'];
        $model->plugin_name = $type;
        $model->plugin_case = $tpList[$type]['case'];
        $this->pagedata['type'] =& $tpList;
        $list = $model->getList( NULL, NULL, TRUE );
        $this->pagedata['items'] =& $list;
        $this->pagedata['infoPage'] = "system/addons/{$_GET['act']}-{$_GET['p'][0]}.html";
        $this->page( "system/addons/page.html" );
    }

    public function widget( )
    {
        $this->path[] = array(
            "text" => __( "板块" )
        );
        $model =& $this->system->loadModel( "content/widgets" );
        $items = $model->getLibs( );
        foreach ( $items as $key => $item )
        {
            $items[$key]['name'] = $item['label'];
            $items[$key]['file'] = "plugins/widgets/".$key;
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['allow_disable'] = FALSE;
        $this->pagedata['infoPage'] = "system/addons/widgets.html";
        $this->page( "system/addons/page.html" );
    }

    public function package( )
    {
        $this->path[] = array(
            "text" => __( "功能包" )
        );
        $this->pagedata['allow_disable'] = TRUE;
        $this->page( "system/addons/page.html" );
    }

}

?>
