<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_cncard extends paymentPlugin
{

    public function pay_cncard_callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $c_order = $in['c_order'];
        $money = $c_orderamount = $in['c_orderamount'];
        $c_succmark = $in['c_succmark'];
        $c_cause = $in['c_cause'];
        $c_signstr = $in['c_signstr'];
        $tradeno = $in['c_transnum'];
        $ikey = $this->getConf( $c_order, "PrivateKey" );
        $content = md5( $in['c_mid'].$in['c_order'].$in['c_orderamount'].$in['c_ymd'].$in['c_transnum'].$in['c_succmark'].$in['c_moneytype'].$in['c_memo1'].$in['c_memo2'].$ikey );
        $paymentId = $corder;
        $tradeno = $in['c_transnum'];
        $money = $c_orderamount;
        $system =& $GLOBALS['GLOBALS']['system'];
        $sUrl = $system->base_url( );
        $url = $system->mkUrl( "paycenter", $act = "result" );
        if ( $c_signstr != $content )
        {
            echo "<result>1</result><reURL>".$sUrl.$url."?payment_id=".$paymentId."</reURL>";
            return PAY_ERROR;
        }
        else if ( $c_succmark == "Y" )
        {
            echo "<result>1</result><reURL>".$sUrl.$url."?payment_id=".$paymentId."</reURL>";
            return PAY_SUCCESS;
        }
        else
        {
            echo "<result>1</result><reURL>".$sUrl.$url."?payment_id=".$paymentId."</reURL>";
            return PAY_FAIL;
        }
    }

}

?>
