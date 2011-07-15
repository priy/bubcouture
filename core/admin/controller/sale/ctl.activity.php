<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_activity extends objectPage
{

    public $workground = "sale";
    public $object = "trading/promotionActivity";
    public $finder_action_tpl = "sale/activity/finder_action.html";
    public $finder_default_cols = "_cmd,pmta_id,pmta_name,pmta_time_begin,pmta_time_end,pmta_enabled,pmta_describe";
    public $allowImport = FALSE;
    public $allowExport = FALSE;
    public $noRecycle = TRUE;
    public $filterUnable = TRUE;

    public function activityInfo( $pmtaId = NULL )
    {
        $this->path[] = array(
            "text" => __( "促销活动内容" )
        );
        $_SESSION['SWP_ACTIVITY'] = NULL;
        if ( !empty( $pmtaId ) && intval( $pmtaId ) != 0 )
        {
            $oPromotionActivity =& $this->system->loadModel( "trading/promotionActivity" );
            $this->pagedata['pmta'] = $oPromotionActivity->getActivityById( $pmtaId );
            $this->pagedata['pmta']['pmta_time_begin'] = dateformat( $this->pagedata['pmta']['pmta_time_begin'] );
            $this->pagedata['pmta']['pmta_time_end'] = dateformat( $this->pagedata['pmta']['pmta_time_end'] );
            $this->pagedata['_S']['act'] = "edit";
        }
        else
        {
            $this->pagedata['pmta']['pmta_enabled'] = "true";
            $this->pagedata['_S']['act'] = "add";
        }
        $this->page( "sale/activity/activityInfo.html" );
    }

    public function jumpTo( $act = "index", $ctl = NULL, $args = NULL )
    {
        $GLOBALS['_GET']['act'] = $act;
        if ( $ctl )
        {
            $GLOBALS['_GET']['ctl'] = $ctl;
        }
        if ( $args )
        {
            $GLOBALS['_GET']['p'] = $args;
        }
        if ( !is_null( $ctl ) )
        {
            if ( $pos = strpos( $_GET['ctl'], "/" ) )
            {
                $domain = substr( $_GET['ctl'], 0, $pos );
            }
            else
            {
                $domain = $_GET['ctl'];
            }
            $ctl =& $this->system->getController( $ctl );
            $ctl->message = $this->message;
            $ctl->pagedata =& $this->pagedata;
            $this->system->callAction( $ctl, $act, $args );
        }
        else
        {
            $this->system->callAction( $this, $act, $args );
        }
    }

    public function doActivityInfo( $action )
    {
        $this->path[] = array(
            "text" => __( "促销活动配置完成" )
        );
        if ( $action == "add" )
        {
            $oPromotionActivity =& $this->system->loadModel( "trading/promotionActivity" );
            $oPromotion =& $this->system->loadModel( "trading/promotion" );
            unset( $_POST['pmta_id'] );
            $nPmtaId = $oPromotionActivity->saveActivity( $_POST );
            $_SESSION['SWP_ACTIVITY']['pmta_id'] = $nPmtaId;
            $_SESSION['SWP_ACTIVITY']['pmta_name'] = $_POST['pmta_name'];
            $_SESSION['SWP_PROMOTION'] = NULL;
        }
        else if ( $action = "edit" )
        {
            $oPromotionActivity =& $this->system->loadModel( "trading/promotionActivity" );
            $oPromotionActivity->saveActivity( $_POST );
        }
        $this->completeActivity( $action );
    }

    public function completeActivity( $action )
    {
        $this->pagedata['pmta'] = $_SESSION['SWP_ACTIVITY'];
        $this->pagedata['action'] = $action;
        $this->page( "sale/activity/completeActivity.html" );
    }

    public function _detail( $nMId )
    {
        return array(
            "show_detail" => array(
                "label" => __( "促销规则" ),
                "tpl" => "sale/activity/promotion.html"
            )
        );
    }

    public function show_detail( $active_id )
    {
        $promotion = $this->system->loadModel( "trading/promotion" );
        $this->pagedata['active_id'] = $active_id;
        $this->pagedata['pmts'] = $promotion->getList( "pmt_id,pmt_describe,pmt_update_time,pmt_time_begin,pmt_time_end", array(
            "pmta_id" => $active_id
        ) );
    }

    public function rm_pmts( $active_id )
    {
        $promotion = $this->system->loadModel( "trading/promotion" );
        $this->pagedata['active_id'] = $active_id;
        $this->pagedata['pmts'] = $promotion->getList( "pmt_id,pmt_describe,pmt_update_time,pmt_time_begin,pmt_time_end", array(
            "pmta_id" => $active_id
        ) );
    }

}

?>
