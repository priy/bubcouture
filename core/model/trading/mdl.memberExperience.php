<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_memberexperience extends modelfactory
{

    function toupdatelevel( $userId )
    {
        $oMember =& $this->system->loadmodel( "member/member" );
        $nExperience = $this->getmemberexperience( $userId );
        $aTmp = $oMember->getlevelbyexperience( $nExperience, $levelId );
        $aData['member_lv_id'] = $aTmp['member_lv_id'];
        $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
        $sSql = $this->db->getupdatesql( $rRs, $aData );
        if ( $sSql )
        {
            $this->db->exec( $sSql );
        }
    }

    function getmemberexperience( $userId, &$levelId )
    {
        $sSql = "SELECT experience,member_lv_id FROM sdb_members WHERE member_id=".intval( $userId );
        $aUserExp = $this->db->selectrow( $sSql );
        $levelId = $aUserExp['member_lv_id'];
        return intval( $aUserExp['experience'] );
    }

    function _chgexperience( $userId, $nCheckExperience )
    {
        if ( $nCheckExperience < 0 )
        {
            $nExperience = $this->getmemberexperience( $userId );
            if ( abs( $nCheckExperience ) <= $nExperience )
            {
                return true;
            }
            return false;
        }
        return true;
    }

    function chgexperience( $userId, $nExperience, $sReason, $relatedId = null, $type = 0 )
    {
        if ( $nExperience == 0 )
        {
            return true;
        }
        if ( !$this->_chgexperience( $userId, $nExperience ) )
        {
            $nExperience = 0 - $this->getmemberexperience( $userId );
        }
        $oMember =& $this->system->loadmodel( "member/member" );
        $aExperience = $oMember->getfieldbyid( $userId, array( "experience" ) );
        $oLv =& $this->system->loadmodel( "member/level" );
        if ( $userId && $oLv->checkmemlvtype( $userId ) != "wholesale" )
        {
            $userId = intval( $userId );
            $aUserExperience['Experience'] = $this->getmemberexperience( $userId );
            $aUserExperience['Experience'] += $nExperience;
            $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
            $sSql = $this->db->getupdatesql( $rRs, $aUserExperience );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            if ( intval( $nExperience ) != 0 && !$type )
            {
                $shopObject = new shopobject( );
                $shopObject->modelName = "member/account";
                $data['member_id'] = $userId;
                unset( $shopObject );
            }
            if ( $this->system->getconf( "site.level_switch" ) )
            {
                $this->toupdatelevel( $userId );
            }
        }
        return true;
    }

    function payallconsumeexperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_e" ) );
        $nPayExperience = 0 - intval( $aTmp['score_e'] );
        return $this->chgexperience( $userId, $nPayExperience, "order_pay_use", $orderId );
    }

    function refundallconsumeexperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_e" ) );
        $nPayExperience = $aTmp['score_e'];
        return $this->chgexperience( $userId, $nPayExperience, "order_refund_use", $orderId );
    }

    function payallgetexperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_e" ) );
        $nPayExperience = $aTmp['score_e'];
        return $this->chgexperience( $userId, $nPayExperience, "order_pay_get", $orderId );
    }

    function refundpartgetexperience( $userId, $orderId, $nExperience )
    {
        $this->chgexperience( $userId, $nExperience, "order_refund_get", $orderId );
    }

    function refundallgetexperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $aTmp = $oOrder->getfieldbyid( $orderId, array( "score_e" ) );
        $nPayExperience = 0 - $aTmp['score_e'];
        return $this->chgexperience( $userId, $nPayExperience, "order_refund_get", $orderId );
    }

    function cancelorderrefundconsumeexperience( $userId, $orderId )
    {
        $oExperienceHistory =& $this->system->loadmodel( "trading/PointHistory" );
        return $this->chgexperience( $userId, $oExperienceHistory->getorderconsumeexperience( $orderId ), "order_cancel_refund_consume_gift", $orderId );
    }

}

?>
