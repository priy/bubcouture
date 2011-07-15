<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_enets extends paymentPlugin
{

    public $name = "eNETS Payment Services";
    public $logo = "ENETS";
    public $version = 20070902;
    public $charset = "big5";
    public $submitUrl = "https://www.enetspayments.com.sg/masterMerchant/collectionPage.jsp";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "USD" => "USD",
        "SGD" => "SGD"
    );
    public $supportArea = array
    (
        0 => "AREA_USD",
        1 => "AREA_SGD"
    );
    public $desc = "Website: https://www.enetspayments.com.sg/";
    public $orderby = 42;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $return['mid'] = $merId;
        $return['amount'] = $payment['M_Amount'];
        $return['txnRef'] = $payment['M_OrderId'];
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        if ( $_SERVER['REQUEST_METHOD'] != "POST" )
        {
            $message = "Illegal request 1";
            return PAY_ERROR;
        }
        $shopexac = $in['shopexac'];
        $ikey = $this->getConf( $in['TxnRef'], "PrivateKey" );
        if ( $shopexac != $ikey )
        {
            $message = "Illegal request 2";
            return PAY_ERROR;
        }
        $mydate = substr( $orderno, 0, 8 );
        $succ = $in['txnStatus'];
        $payid = $in['TxnRef'];
        $paymentId = $payid;
        $money = $in['amount'];
        $errcode = $in['errorCode'];
        switch ( $succ )
        {
        case "succ" :
            return PAY_SUCCESS;
            break;
        case "fail" :
            $message = "支付失败,请立即与商店管理员联系(".$errcode.")";
            return 交易失败;
            break;
        default :
            $message = "Illegal request 3";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

}

?>
