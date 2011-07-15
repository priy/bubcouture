<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_roles extends objectPage
{

    public $workground = "setting";
    public $finder_action_tpl = "admin/roles_action.html";
    public $finder_default_cols = "_cmd,role_name,role_memo";
    public $object = "admin/adminroles";
    public $filterUnable = TRUE;

    public function add( )
    {
        $this->pagedata['actions'] = $this->model->getAllActions( );
        $this->page( "admin/roles_item.html" );
    }

    public function edit( $role_id )
    {
        $this->pagedata['actions'] = $this->model->getAllActions( );
        $this->pagedata['role'] = $this->model->instance( $role_id );
        $this->pagedata['role']['actions'] = array_flip( $this->pagedata['role']['actions'] );
        $this->page( "admin/roles_item.html" );
    }

}

?>
