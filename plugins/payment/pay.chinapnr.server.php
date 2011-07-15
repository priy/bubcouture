<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_chinapnr extends paymentPlugin
{

    public function pay_chinapnr_callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $PgKeyFile = $this->getConf( $in['OrdId'], "key2" );
        $p_MerId = $in['MerId'];
        $p_MerDate = $in['MerDate'];
        $p_OrdId = $in['OrdId'];
        $money = $p_TransAmt = $in['TransAmt'];
        $p_TransType = $in['TransType'];
        $p_GateId = $in['GateId'];
        $p_TransStat = $in['TransStat'];
        $p_MerPriv = $in['MerPriv'];
        $p_SysDate = $in['SysDate'];
        $tradeno = $p_SysSeqId = $in['SysSeqId'];
        $p_ChkValue = $in['ChkValue'];
        $paymentId = $p_OrdId;
        ( "ChinaPnr.SecureLink" );
        $pnrObj = new COM( );
        if ( strtolower( substr( $_ENV['OS'], 0, 7 ) ) == "windows" )
        {
            $checkout = $pnrObj->VeriSingOrder0( $p_MerId, $PgKeyFile, $p_OrdId, $p_TransAmt, $p_MerDate, $p_TransType, $p_TransStat, $p_GateId, $p_MerPriv, $p_SysDate, $p_SysSeqId, $p_ChkValue );
        }
        else
        {
            $checkout = $pnrObj->VeriSingOrder( $p_MerId, $PgKeyFile, $p_OrdId, $p_TransAmt, $p_MerDate, $p_TransType, $p_TransStat, $p_GateId, $p_MerPriv, $p_SysDate, $p_SysSeqId, $p_ChkValue );
        }
        if ( $checkout == 0 && $p_TransStat == "S" )
        {
            return PAY_SUCCESS;
            echo "RECV_ORD_ID_".$p_OrdId;
        }
    }

    public function pay_CHINAPNR_relay( $status )
    {
        switch ( $status )
        {
        case PAY_FAILED :
            break;
        case PAY_TIMEOUT :
            break;
        case PAY_SUCCESS :
            echo "RECV_ORD_ID_".$p_OrdId;
            break;
        case PAY_CANCEL :
            break;
        case PAY_ERROR :
            break;
        case PAY_PROGRESS :
            break;
        }
    }

}

?>
