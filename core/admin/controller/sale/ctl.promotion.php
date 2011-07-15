<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_promotion extends objectPage
{

    public $workground = "sale";
    public $object = "trading/promotion";
    public $finder_action_tpl = "sale/promotion/finder_action.html";
    public $finder_filter_tpl = "sale/promotion/finder_filter.html";
    public $allowImport = FALSE;
    public $allowExport = FALSE;
    public $noRecycle = TRUE;

    public function welcome( )
    {
        $this->page( "sale/welcome.html" );
    }

    public function index( $active_id = NULL )
    {
        if ( $active_id )
        {
            parent::index( array(
                "params" => array(
                    "pmta_id" => $active_id
                )
            ) );
        }
        else
        {
            parent::index( );
        }
    }

    public function addPromotion( $pmtId = NULL, $pmtaId = NULL )
    {
        $this->path[] = array(
            "text" => __( "促销规则选择" )
        );
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        $oPromotionActivity =& $this->system->loadModel( "trading/promotionActivity" );
        if ( !$pmtaId )
        {
            $aTmp = $oPromotion->getPromotionFieldById( $pmtId, array( "pmta_id" ) );
            $pmtaId = $aTmp['pmta_id'];
        }
        $_SESSION['SWP_PROMOTION'] = NULL;
        $_SESSION['SWP_PROMOTION']['activityInfo'] = $oPromotionActivity->getActivityById( $pmtaId );
        $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_begin'] = dateformat( $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_begin'] );
        $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_end'] = dateformat( $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_end'] );
        if ( $pmtId )
        {
            $aData = $oPromotion->getPromotionFieldById( $pmtId, array( "*" ) );
            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = array(
                "pmt_solution" => unserialize( $aData['pmt_solution'] ),
                "pmt_ifcoupon" => $aData['pmt_ifcoupon'],
                "pmt_time_begin" => dateformat( $aData['pmt_time_begin'] ),
                "pmt_time_end" => dateformat( $aData['pmt_time_end'] ),
                "pmt_describe" => $aData['pmt_describe']
            );
            $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'] = $aData['pmts_id'];
            $_SESSION['SWP_PROMOTION']['selectProduct']['pmt_bond_type'] = $aData['pmt_bond_type'];
            switch ( $aData['pmt_bond_type'] )
            {
            case 1 :
                $_SESSION['SWP_PROMOTION']['selectProduct']['bind_goods'] = $oPromotion->getBondGoods( $pmtId );
                break;
            case 2 :
                break;
            case 0 :
            default :
                break;
            }
        }
        $_SESSION['SWP_PROMOTION']['basic']['pmta_id'] = $pmtaId;
        $_SESSION['SWP_PROMOTION']['basic']['pmt_id'] = $pmtId;
        $this->selectPromotionRule( $pmtId );
    }

    public function selectPromotionRule( $pmtId = NULL )
    {
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        $oPromotionScheme =& $this->system->loadModel( "trading/promotionScheme" );
        $this->pagedata['scheme']['list'] = $oPromotionScheme->getList( array( "pmts_type" => 0 ), FALSE );
        if ( !empty( $_SESSION['SWP_PROMOTION']['selectPromotionRule'] ) )
        {
            $this->pagedata['scheme'] = array_merge( $this->pagedata['scheme'], $_SESSION['SWP_PROMOTION']['selectPromotionRule'] );
        }
        else
        {
            list( $pmtsId ) = each( $this->pagedata['scheme']['list'] );
            $this->pagedata['scheme']['pmts_id'] = $pmtsId;
        }
        $this->display( "sale/promotion/selectPromotionRule.html" );
    }

    public function doSelectPromotionRule( )
    {
        $this->path[] = array(
            "text" => __( "促销规则配置" )
        );
        $this->checkInput( );
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        if ( !empty( $_POST['pmts_id'] ) && $_POST['pmts_id'] != $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'] )
        {
            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = NULL;
            $_SESSION['SWP_PROMOTION']['selectProduct'] = NULL;
        }
        $_SESSION['SWP_PROMOTION']['selectPromotionRule'] =& $GLOBALS['_POST'];
        $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmt_type'] = 0;
        $this->writePromotionRule( );
    }

    public function writePromotionRule( )
    {
        $pmtsId = $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'];
        if ( !empty( $_SESSION['SWP_PROMOTION']['writePromotionRule'] ) )
        {
            $pmtSolution = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'];
            $this->pagedata['pmt']['pmt_ifcoupon'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_ifcoupon'];
            $this->pagedata['pmt']['pmt_time_begin'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'];
            $this->pagedata['pmt']['pmt_time_end'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'];
            $this->pagedata['pmt']['pmt_describe'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'];
        }
        else
        {
            $this->pagedata['pmt']['pmt_ifcoupon'] = "1";
            $this->pagedata['pmt']['pmt_time_begin'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_begin'];
            $this->pagedata['pmt']['pmt_time_end'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_end'];
            $oPromotionScheme =& $this->system->loadModel( "trading/promotionScheme" );
            $pmtSolution = $oPromotionScheme->getParams( $pmtsId, FALSE );
            $pmtSolution = $pmtSolution['pmts_solution'];
        }
        $this->pagedata['solution']['type'] = $pmtSolution['type'];
        foreach ( $pmtSolution['condition'] as $condition )
        {
            $this->pagedata['solution']['condition'][$condition[0]] = TRUE;
            switch ( $condition[0] )
            {
            case "mLev" :
                $oMember =& $this->system->loadModel( "member/level" );
                $aMemberLevelList = $oMember->getList( "member_lv_id,name" );
                foreach ( $aMemberLevelList as $k => $v )
                {
                    $aTmpMList[$v['member_lv_id']] = $v['name'];
                }
                $this->pagedata['mLev'] = $aTmpMList;
                $this->pagedata['pmt']['mLev'] = $condition[1];
                break;
            default :
                if ( $condition[0] == "orderMoney_to" && !$condition[1] )
                {
                    $condition[1] = 9999999;
                }
                $this->pagedata['pmt'][$condition[0]] = $condition[1];
                break;
            }
        }
        foreach ( $pmtSolution['method'] as $method )
        {
            $this->pagedata['solution']['method'][$method[0]] = TRUE;
            $this->pagedata['pmt'][$method[0]] = $method[1];
            switch ( $method[0] )
            {
            case "giveGift" :
                $this->pagedata['pmt']['gift_filter'] = array( "shop_iffb" => 1, "time_ifvalid" => 1, "storage_ifenough" => 1 );
                break;
            case "generateCoupon" :
                $this->pagedata['pmt']['coupon_filter'] = array(
                    "cpns_type" => array( 1 ),
                    "ifvalid" => 1
                );
                break;
            default :
                break;
            }
        }
        $this->display( "sale/promotion/writePromotionRule.html" );
    }

    public function doWritePromotionRule( )
    {
        $this->path[] = array(
            "text" => __( "选择促销对象" )
        );
        $this->checkInput( );
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_ifcoupon'] = $_POST['pmt_ifcoupon'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'] = $_POST['pmt_time_begin'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'] = $_POST['pmt_time_end'];
        $pmtsId = $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'];
        $oPromotionScheme =& $this->system->loadModel( "trading/promotionScheme" );
        $aTmp = $oPromotionScheme->getParams( $pmtsId, FALSE );
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'] = $aTmp['pmts_solution'];
        $pmtSolution =& $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution'];
        foreach ( $pmtSolution['condition'] as $k => $condition )
        {
            $pmtSolution['condition'][$k][1] = $_POST[$pmtSolution['condition'][$k][0]];
        }
        foreach ( $pmtSolution['method'] as $k => $method )
        {
            $pmtSolution['method'][$k][1] = $_POST[$pmtSolution['method'][$k][0]];
        }
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'] = $_POST['pmt_describe'];
        $this->selectProduct( );
    }

    public function selectProduct( )
    {
        $pmtType = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution']['type'];
        if ( !empty( $_SESSION['SWP_PROMOTION']['selectProduct'] ) )
        {
            $this->pagedata['pmt'] = $_SESSION['SWP_PROMOTION']['selectProduct'];
        }
        if ( $pmtType == "goods" )
        {
            $this->pagedata['pmt']['pmt_bond_type'] = 1;
        }
        else if ( $pmtType == "order" )
        {
            $this->pagedata['pmt']['pmt_bond_type'] = 0;
        }
        $this->pagedata['pmt']['type'] = $pmtType;
        $this->display( "sale/promotion/selectProduct.html" );
    }

    public function doSelectProduct( )
    {
        $this->path[] = array(
            "text" => __( "促销规则配置确认" )
        );
        $this->checkInput( );
        $_SESSION['SWP_PROMOTION']['selectProduct'] =& $GLOBALS['_POST'];
        $_SESSION['SWP_PROMOTION']['selectProduct']['pmt_basic_type'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_solution']['type'];
        $this->publish( );
    }

    public function publish( )
    {
        $this->pagedata['pmta']['pmta_name'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_name'];
        $this->pagedata['pmta']['pmta_time_begin'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_begin'];
        $this->pagedata['pmta']['pmta_time_end'] = $_SESSION['SWP_PROMOTION']['activityInfo']['pmta_time_end'];
        $this->pagedata['pmt']['pmt_time_begin'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'];
        $this->pagedata['pmt']['pmt_time_end'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'];
        $this->pagedata['pmt']['pmt_describe'] = $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_describe'];
        $this->display( "sale/promotion/publish.html" );
    }

    public function doPublish( )
    {
        $this->begin( "index.php?ctl=sale/activity&act=index" );
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        $aPromotion = array_merge( ( array )$_SESSION['SWP_PROMOTION']['selectPromotionRule'], ( array )$_SESSION['SWP_PROMOTION']['writePromotionRule'], ( array )$_SESSION['SWP_PROMOTION']['selectProduct'], ( array )$_SESSION['SWP_PROMOTION']['basic'] );
        $this->end( $oPromotion->addPromotion( $aPromotion ), __( "促销规则保存成功" ) );
    }

    public function checkInput( )
    {
        if ( !$_POST )
        {
            $this->index( );
        }
    }

}

?>
