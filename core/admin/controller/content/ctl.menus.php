<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_menus extends adminPage
{

    public $workground = "site";

    public function menus( )
    {
        $oMenus =& $this->system->loadModel( "content/menus" );
        $this->pagedata['menus'] = $oMenus->menusList( );
        $this->page( "content/menus.html" );
    }

    public function menusDel( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( !$o->menusDel( $_POST['id'] ) )
        {
            $this->splash( "failed", "index.php?ctl=content/menus&act=menus", __( "对不起,菜单删除失败!" ) );
        }
        $this->splash( "success", "index.php?ctl=content/menus&act=menus", __( "菜单删除成功!" ) );
    }

    public function menusDetail( )
    {
        $oMenus =& $this->system->loadModel( "content/menus" );
        $this->pagedata['menus'] = $oMenus->menusDetailList( $_POST['id'] );
        $this->pagedata['id'] = $_POST['id'];
        $this->page( "content/menusDetail.html" );
    }

    public function menusDetailEditPage( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        $data = $o->menusDetail( $_POST['id'] );
        $this->pagedata['menu_id'] = $_POST['id'];
        $this->pagedata['label'] = $data['label'];
        $this->pagedata['type'] = $data['type'];
        $this->pagedata['res_id'] = $data['res_id'];
        $this->pagedata['setting'] = unserialize( $data['setting'] );
        $this->page( "content/menusDetailEdit.html" );
    }

    public function menusDetialEdit( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( $_POST['type'] == 0 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['link'];
        }
        else if ( $_POST['type'] == 1 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['browser'];
        }
        else if ( $_POST['type'] == 2 )
        {
            $GLOBALS['_POST']['res_id'] = $_POST['product'];
        }
        else if ( $_POST['type'] == 3 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['article'];
        }
        else if ( $_POST['type'] == 4 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['art_cat'];
        }
        else if ( $_POST['type'] == 5 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['tag'];
        }
        if ( !$o->menusDetialEdit( $_POST ) )
        {
            $this->splash( "failed", "index.php?ctl=content/menus&act=menusDetailEditPage&id=".$_POST['menu_id'], __( "对不起,操作失败" ) );
        }
        $this->splash( "success", "index.php?ctl=content/menus&act=menusDetailEditPage&id=".$_POST['menu_id'], __( "操作成功" ) );
    }

    public function menusDetailAddPage( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( empty( $_POST['id'] ) )
        {
            $GLOBALS['_POST']['id'] = $o->menusAdd( );
        }
        $this->pagedata['menu_grp_id'] = $_POST['id'];
        $this->page( "content/menusDetailAdd.html" );
    }

    public function menusDetailAdd( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( $_POST['type'] == 0 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['link'];
        }
        else if ( $_POST['type'] == 1 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['browser'];
        }
        else if ( $_POST['type'] == 2 )
        {
            $GLOBALS['_POST']['res_id'] = $_POST['product'];
        }
        else if ( $_POST['type'] == 3 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['article'];
        }
        else if ( $_POST['type'] == 4 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['art_cat'];
        }
        else if ( $_POST['type'] == 5 )
        {
            $GLOBALS['_POST']['setting'] = $_POST['tag'];
        }
        if ( !$o->menusDetailAdd( $_POST ) )
        {
            $this->splash( "failed", "index.php?ctl=content/menus&act=menusDetail&id=".$_POST['menu_grp_id'], __( "对不起,操作失败" ) );
        }
        $this->splash( "success", "index.php?ctl=content/menus&act=menusDetail&id=".$_POST['menu_grp_id'], __( "操作成功" ) );
    }

    public function menusDetailDel( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( !$o->menusDetailDel( $_POST['id'] ) )
        {
            $this->splash( "failed", "index.php?ctl=content/menus&act=menusDetail&id=".$_POST['menu_grp_id'], __( "对不起,操作失败" ) );
        }
        $this->splash( "success", "index.php?ctl=content/menus&act=menusDetail&id=".$_POST['menu_grp_id'], __( "操作成功" ) );
    }

    public function toRemove( $id )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( $o->toRemoveDefineMenus( $id, $msg ) )
        {
            $this->splash( "success", "index.php?ctl=content/menus&act=defineMenus", __( "操作成功" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=content/menus&act=defineMenus", __( "对不起,操作失败" ) );
        }
    }

    public function doAdd( )
    {
        $o =& $this->system->loadModel( "content/menus" );
        if ( $o->addDefinemenus( $_POST ) )
        {
            $this->splash( "success", "index.php?ctl=content/menus&act=defineMenus", __( "操作成功" ) );
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=content/menus&act=defineMenus", __( "对不起,操作失败" ) );
        }
    }

}

?>
