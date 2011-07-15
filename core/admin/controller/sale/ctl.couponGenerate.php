<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_couponGenerate extends objectPage
{

    public $name = "ÓÅ»ÝÈ¯";
    public $workground = "sale";
    public $object = "trading/couponGenerate";
    public $finder_filter_tpl = "sale/coupon/generate/finder_filter.html";
    public $deleteAble = FALSE;
    public $allowImport = FALSE;
    public $allowExport = FALSE;

    public function index( $cpnsId = NULL )
    {
        if ( $cpnsId )
        {
            parent::index( array(
                "params" => array(
                    "cpns_id" => $cpnsId
                )
            ) );
        }
        else
        {
            parent::index( );
        }
    }

    public function addCouponGenerate( $cpnsId, $pmtId = NULL )
    {
        $_SESSION['SWP_PROMOTION'] = NULL;
        $oCoupon =& $this->system->loadModel( "trading/coupon" );
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        $_SESSION['SWP_PROMOTION']['couponInfo'] = $oCoupon->getCouponById( $cpnsId );
        if ( $pmtId != NULL )
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
            $_SESSION['SWP_PROMOTION']['basic']['pmt_id'] = $pmtId;
        }
        $_SESSION['SWP_PROMOTION']['basic']['cpns_id'] = $cpnsId;
        $this->selectPromotionRule( );
    }

    public function doSelectPromotionRule( )
    {
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        if ( !empty( $_POST['pmts_id'] ) && $_POST['pmts_id'] != $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmts_id'] )
        {
            $_SESSION['SWP_PROMOTION']['writePromotionRule'] = NULL;
            $_SESSION['SWP_PROMOTION']['selectProduct'] = NULL;
        }
        $aData = $oPromotion->getSchemeFieldById( "pmts_type", $_POST['pmts_id'] );
        $_SESSION['SWP_PROMOTION']['selectPromotionRule'] =& $GLOBALS['_POST'];
        $_SESSION['SWP_PROMOTION']['selectPromotionRule']['pmt_type'] = $aData['pmts_type'];
        $this->writePromotionRule( );
    }

    public function doWritePromotionRule( )
    {
        $this->_filterPost( );
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_ifcoupon'] = $_POST['pmt_ifcoupon'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_begin'] = $_POST['pmt_time_begin'];
        $_SESSION['SWP_PROMOTION']['writePromotionRule']['pmt_time_end'] = $_POST['pmt_time_end'];
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

    public function doSelectProduct( )
    {
        $_SESSION['SWP_PROMOTION']['selectProduct'] =& $GLOBALS['_POST'];
        $this->publish( );
    }

    public function doPublish( )
    {
        $oPromotion =& $this->system->loadModel( "trading/promotion" );
        $aPromotion = array_merge( $_SESSION['SWP_PROMOTION']['selectPromotionRule'], $_SESSION['SWP_PROMOTION']['writePromotionRule'], $_SESSION['SWP_PROMOTION']['selectProduct'], $_SESSION['SWP_PROMOTION']['basic'] );
        $oPromotion->addPromotion( $aPromotion, 2 );
        $this->splash( "success", "index.php?ctl=sale/coupon&act=index" );
    }

    public function _filterPost( )
    {
        if ( is_array( $_POST ) )
        {
            foreach ( $_POST as $k => $v )
            {
                if ( substr( $k, 0, 4 ) == "ext-" )
                {
                    unset( $_POST[$k] );
                }
            }
        }
    }

}

?>
