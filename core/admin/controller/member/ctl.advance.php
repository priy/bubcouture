<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_advance extends objectPage
{

    public $workground = "analytics";
    public $object = "member/advance";
    public $finder_action_tpl = "member/advance_finder_action.html";
    public $deleteAble = FALSE;
    public $allowImport = FALSE;
    public $allowExport = FALSE;
    public $filterUnable = TRUE;

    public function index( )
    {
        $this->pagedata['sfinddate'] = date( "Y-m-", time( ) )."1";
        $this->pagedata['efinddate'] = date( "Y-m-d", time( ) );
        $this->_finder_common( $_POST['sdtime'] );
        parent::index( $options );
    }

    public function _finder_common( $options )
    {
        $sdtime = explode( "/", $options );
        $oAdv =& $this->system->loadModel( "member/advance" );
        $advanceStatistics = $oAdv->getAdvanceStatistics( $sdtime[0], $sdtime[1] );
        $statusStr = __( "当前共" ).$advanceStatistics['count'].__( "笔 总转入" ).$advanceStatistics['import_money'].__( "元 总转出" ).$advanceStatistics['explode_money'].__( "元 店内总余额" ).$oAdv->getShopAdvance( ).__( "元 " );
        $this->pagedata['finder']['statusStr'] = $statusStr;
        $this->pagedata['finder']['select'] = FALSE;
    }

    public function finder( $type, $view, $cols, $finder_id, $limit )
    {
        $sdtime = explode( "/", $_GET['sdtime'] );
        $oAdv =& $this->system->loadModel( "member/advance" );
        $advanceStatistics = $oAdv->getAdvanceStatistics( $sdtime[0], $sdtime[1] );
        $statusStr = __( "当前共" ).$advanceStatistics['count'].__( "笔 总转入" ).$advanceStatistics['import_money'].__( "元 总转出" ).$advanceStatistics['explode_money'].__( "元 店内总余额" ).$oAdv->getShopAdvance( ).__( "元 " );
        $GLOBALS['_GET']['_finder']['statusStr'] = $statusStr;
        parent::finder( $type, $view, $cols, $finder_id, $limit );
    }

    public function advancelist( $nMId, $nPage = 1 )
    {
        $oAdv =& $this->system->loadModel( "member/advance" );
        if ( $_GET['member_id'] )
        {
            $nMId = $_GET['member_id'];
        }
        if ( $_GET['log_id'] )
        {
            $rs = $oAdv->getAdvanceLogByLogId( $_GET['log_id'] );
            $nMId = $rs['member_id'];
        }
        $advList = $oAdv->getFrontAdvList( $nMId, $nPage - 1, 10 );
        $advanceStatistics = $oAdv->getMemberAdvanceStatistics( $nMId );
        $statusStr = __( "<span class=\"colborder\">当前共" ).$advanceStatistics['count'].__( "笔</span> <span class=\"colborder\">总转入" ).$advanceStatistics['import_money'].__( "元</span> <span class=\"colborder\">总转出" ).$advanceStatistics['explode_money'].__( "元</span> 余额" ).$oAdv->get( $nMId ).__( "元" );
        $this->pagedata['items'] = $advList['data'];
        $this->pagedata['page'] = $nPage;
        $this->pagedata['totalpage'] = $advList['page'];
        $this->pagedata['member_id'] = $nMId;
        $this->pagedata['statusStr'] = $statusStr;
        $this->display( "member/advancelist.html" );
    }

}

?>
