<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_memberExperience extends modelFactory
{

    public function toUpdatelevel( $userId )
    {
        $oMember =& $this->system->loadModel( "member/member" );
        $nExperience = $this->getMemberExperience( $userId );
        $aTmp = $oMember->getLevelByExperience( $nExperience, $levelId );
        $aData['member_lv_id'] = $aTmp['member_lv_id'];
        $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
        $sSql = $this->db->GetUpdateSQL( $rRs, $aData );
        if ( $sSql )
        {
            $this->db->exec( $sSql );
        }
    }

    public function getMemberExperience( $userId, &$levelId )
    {
        $sSql = "SELECT experience,member_lv_id FROM sdb_members WHERE member_id=".intval( $userId );
        $aUserExp = $this->db->selectrow( $sSql );
        $levelId = $aUserExp['member_lv_id'];
        return intval( $aUserExp['experience'] );
    }

    public function _chgExperience( $userId, $nCheckExperience )
    {
        if ( $nCheckExperience < 0 )
        {
            $nExperience = $this->getMemberExperience( $userId );
            if ( abs( $nCheckExperience ) <= $nExperience )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    public function chgExperience( $userId, $nExperience, $sReason, $relatedId = null, $type = 0 )
    {
        if ( $nExperience == 0 )
        {
            return true;
        }
        else if ( !$this->_chgExperience( $userId, $nExperience ) )
        {
            $nExperience = 0 - $this->getMemberExperience( $userId );
        }
        $oMember =& $this->system->loadModel( "member/member" );
        $aExperience = $oMember->getFieldById( $userId, array( "experience" ) );
        $oLv =& $this->system->loadModel( "member/level" );
        if ( $userId && $oLv->checkMemLvType( $userId ) != "wholesale" )
        {
            $userId = intval( $userId );
            $aUserExperience['Experience'] = $this->getMemberExperience( $userId );
            $aUserExperience['Experience'] += $nExperience;
            $rRs = $this->db->query( "select * from sdb_members where member_id=".$userId );
            $sSql = $this->db->GetUpdateSQL( $rRs, $aUserExperience );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            if ( intval( $nExperience ) != 0 && !$type )
            {
                $shopObject = new shopObject( );
                $shopObject->modelName = "member/account";
                $data['member_id'] = $userId;
                unset( $shopObject );
            }
            if ( $this->system->getConf( "site.level_switch" ) )
            {
                $this->toUpdatelevel( $userId );
            }
        }
        return true;
    }

    public function payAllConsumeExperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadModel( "trading/order" );
        $aTmp = $oOrder->getFieldById( $orderId, array( "score_e" ) );
        $nPayExperience = 0 - intval( $aTmp['score_e'] );
        return $this->chgExperience( $userId, $nPayExperience, "order_pay_use", $orderId );
    }

    public function refundAllConsumeExperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadModel( "trading/order" );
        $aTmp = $oOrder->getFieldById( $orderId, array( "score_e" ) );
        $nPayExperience = $aTmp['score_e'];
        return $this->chgExperience( $userId, $nPayExperience, "order_refund_use", $orderId );
    }

    public function payAllGetExperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadModel( "trading/order" );
        $aTmp = $oOrder->getFieldById( $orderId, array( "score_e" ) );
        $nPayExperience = $aTmp['score_e'];
        return $this->chgExperience( $userId, $nPayExperience, "order_pay_get", $orderId );
    }

    public function refundPartGetExperience( $userId, $orderId, $nExperience )
    {
        $this->chgExperience( $userId, $nExperience, "order_refund_get", $orderId );
    }

    public function refundAllGetExperience( $userId, $orderId )
    {
        $oOrder =& $this->system->loadModel( "trading/order" );
        $aTmp = $oOrder->getFieldById( $orderId, array( "score_e" ) );
        $nPayExperience = 0 - $aTmp['score_e'];
        return $this->chgExperience( $userId, $nPayExperience, "order_refund_get", $orderId );
    }

    public function cancelOrderRefundConsumeExperience( $userId, $orderId )
    {
        $oExperienceHistory =& $this->system->loadModel( "trading/PointHistory" );
        return $this->chgExperience( $userId, $oExperienceHistory->getOrderConsumeExperience( $orderId ), "order_cancel_refund_consume_gift", $orderId );
    }

}

?>
