<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_memberpoint extends modelfactory
{

    function toupdatelevel( $userId )
    {
        $oMember =& $this->system->loadmodel( "member/member" );
        $nPoint = $this->getmemberpoint( $userId );
        $aTmp = $oMember->getlevelbypoint( $nPoint, $levelId );
        $aData['member_lv_id'] = $aTmp['member_lv_id'];
        $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
        $sSql = $this->db->getupdatesql( $rRs, $aData );
        if ( $sSql )
        {
            $this->db->exec( $sSql );
            if ( $levelId != $aTmp['member_lv_id'] )
            {
                $shopObject = new shopobject( );
                $shopObject->modelName = "member/account";
                $data['member_id'] = $userId;
                $shopObject->fireevent( "changelevel", $data, $userId );
                unset( $shopObject );
            }
        }
    }

    function getmemberpoint( $userId, &$levelId )
    {
        $sSql = "SELECT point,member_lv_id FROM sdb_members WHERE member_id=".intval( $userId );
        $aUserPoint = $this->db->selectrow( $sSql );
        $levelId = $aUserPoint['member_lv_id'];
        return intval( $aUserPoint['point'] );
    }

    function _chgpoint( $userId, $nCheckPoint )
    {
        if ( $nCheckPoint < 0 )
        {
            $nPoint = $this->getmemberpoint( $userId );
            if ( abs( $nCheckPoint ) <= $nPoint )
            {
                return true;
            }
            return false;
        }
        return true;
    }

    function chgpoint( $userId, $nPoint, $sReason, $relatedId = null, $type = 0 )
    {
        if ( $nPoint == 0 )
        {
            return true;
        }
        if ( !$this->_chgpoint( $userId, $nPoint ) )
        {
            $nPoint = 0 - $this->getmemberpoint( $userId );
            trigger_error( __( "积分扣除超过会员已有积分" ), E_USER_ERROR );
            return false;
        }
        $oMember =& $this->system->loadmodel( "member/member" );
        $aPoint = $oMember->getfieldbyid( $userId, array( "point" ) );
        $oLv =& $this->system->loadmodel( "member/level" );
        if ( $userId && $oLv->checkmemlvtype( $userId ) != "wholesale" )
        {
            $userId = intval( $userId );
            $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
            $aUserPoint['point'] = $this->getmemberpoint( $userId );
            $aUserPoint['point'] += $nPoint;
            $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
            $sSql = $this->db->getupdatesql( $rRs, $aUserPoint );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            if ( intval( $nPoint ) != 0 && !$type )
            {
                $shopObject = new shopobject( );
                $shopObject->modelName = "member/account";
                $data['member_id'] = $userId;
                $shopObject->fireevent( "changepoint", $data, $userId );
                unset( $shopObject );
            }
            if ( !$this->system->getconf( "site.level_switch" ) )
            {
                $this->toupdatelevel( $userId );
            }
            $aPointHistory = array(
                "member_id" => $userId,
                "point" => $nPoint,
                "reason" => $sReason,
                "related_id" => $relatedId
            );
            $oPointHistory->addhistory( $aPointHistory );
        }
        return true;
    }

    function payallconsumepoint( $userId, $orderId )
    {
        $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_u" ) );
        $nPayPoint = 0 - intval( $aTmp['score_u'] );
        return $this->chgpoint( $userId, $nPayPoint, "order_pay_use", $orderId );
    }

    function refundallconsumepoint( $userId, $orderId )
    {
        $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_u" ) );
        $nPayPoint = $aTmp['score_u'];
        return $this->chgpoint( $userId, $nPayPoint, "order_refund_use", $orderId );
    }

    function payallgetpoint( $userId, $orderId )
    {
        $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_g" ) );
        $nPayPoint = $aTmp['score_g'];
        return $this->chgpoint( $userId, $nPayPoint, "order_pay_get", $orderId );
    }

    function refundpartgetpoint( $userId, $orderId, $nPoint )
    {
        $this->chgpoint( $userId, $nPoint, "order_refund_get", $orderId );
    }

    function refundallgetpoint( $userId, $orderId )
    {
        $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_g" ) );
        $nPayPoint = 0 - $aTmp['score_g'];
        return $this->chgpoint( $userId, $nPayPoint, "order_refund_get", $orderId );
    }

    function cancelorderrefundconsumepoint( $userId, $orderId )
    {
        $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
        return $this->chgpoint( $userId, $oPointHistory->getorderconsumepoint( $orderId ), "order_cancel_refund_consume_gift", $orderId );
    }

}

?>
