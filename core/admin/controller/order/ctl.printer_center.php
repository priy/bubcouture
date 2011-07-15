<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_printer_center extends objectPage
{

    public $workground = "order";
    public $object = "trading/dly_centers";
    public $finder_action_tpl = "order/dly_center_action.html";

    public function printer_select( )
    {
        $path = "/pxml";
        $handle = opendir( "../../pxml" );
        $ct = array( );
        while ( FALSE !== ( $file = readdir( $handle ) ) )
        {
            if ( preg_match( "/.*\\.xml+/", $file ) )
            {
                $ct[] = $file;
            }
        }
        $this->pagedata['pdate'] = $ct;
        $this->display( "order/printertest.html" );
    }

    public function add_center( )
    {
        $this->page( "order/dly_center_editor.html" );
    }

    public function save_data( )
    {
        $this->begin( "index.php?ctl=order/delivery_centers&act=index" );
        $this->end( $this->model->insert( $_POST ), __( "发货信息添加成功" ) );
    }

}

?>
