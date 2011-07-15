<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_link extends shopPage
{

    public function showList( $page = 1 )
    {
        $sitemapts =& $this->system->loadModel( "content/sitemap" );
        $title = $sitemapts->getTitleByAction( "link:showList" );
        $title = $title['title'] ? $title['title'] : __( "友情链接" );
        $this->path[] = array(
            "title" => $title
        );
        $this->title = $title;
        $pageLimit = 10;
        $oLink =& $this->system->loadModel( "content/frendlink" );
        $result = $oLink->getList( "*", "", ( $page - 1 ) * $pageLimit, $pageLimit );
        $linkCount = $oLink->count( );
        $this->pagedata['pager'] = array(
            "current" => $page,
            "total" => ceil( $linkCount / $pageLimit ),
            "link" => $this->system->mkUrl( "link", "showList", array(
                $tmp = time( )
            ) ),
            "token" => $tmp
        );
        if ( $this->pagedata['pager']['total'] < $page )
        {
            trigger_error( __( "查询数为空" ), E_USER_NOTICE );
        }
        $this->pagedata['data'] = $result;
        $this->output( );
    }

}

?>
