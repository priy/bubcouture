<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_items extends objectPage
{

    public $finder_filter_tpl = "product/finder_products_filter.html";
    public $workground = "goods";
    public $object = "goods/finderPdt";

    public function select( )
    {
        $params = unserialize( stripslashes( $_POST['data'] ) );
        if ( preg_match( "/ctl=goods\\/package&act=showAddPackage/", $_SERVER['HTTP_REFERER'] ) )
        {
            $params['is_local'] = 1;
        }
        $this->_finder_common( array(
            "params" => $params,
            "select" => "checkbox"
        ) );
        $this->pagedata['options'] = $params;
        $this->pagedata['_finder']['rowselect'] = FALSE;
        $this->setView( "finder/browser.html" );
        $this->output( );
    }

}

?>
