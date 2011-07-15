<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_frendlink extends objectPage
{

    public $workground = "site";
    public $finder_action_tpl = "content/frendlink/finder_action.html";
    public $object = "content/frendlink";
    public $filterUnable = TRUE;

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "友情链接" ),
                "tpl" => "content/frendlink/detail.html"
            )
        );
    }

    public function show_detail( $link_id )
    {
        $this->path[] = array(
            "text" => __( "友情链接" )
        );
        $link =& $this->system->loadModel( "content/frendlink" );
        $linkinfo = $link->getFieldById( $link_id, array( "*" ) );
        $this->pagedata['linkInfo'] = $linkinfo;
    }

    public function addNew( )
    {
        $this->path[] = array(
            "text" => __( "友情链接编辑" )
        );
        $this->page( "content/frendlink/detail.html" );
    }

    public function save( )
    {
        if ( $_POST['link_id'] || $_FILES )
        {
            $this->begin( "index.php?ctl=content/frendlink&act=detail&p[0]=".$_POST['link_id'] );
        }
        else
        {
            $this->begin( "index.php?ctl=content/frendlink&act=index" );
        }
        $link =& $this->system->loadModel( "content/frendlink" );
        $this->end( $link->save( $_POST, $msg ), $msg );
    }

}

?>
