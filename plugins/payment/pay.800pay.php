<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_800pay extends paymentPlugin
{

    public $name = "八佰付在线支付";
    public $logo = "800PAY";
    public $version = 20070615;
    public $charset = "utf-8";
    public $submitUrl = "https://www.800-pay.com/PayAction/ReceivePayOrder.aspx";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "RMB",
        "USD" => "USD",
        "KRW" => "KRW"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_USD",
        2 => "AREA_KRW"
    );
    public $status = NULL;
    public $desc = "";
    public $orderby = 28;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        switch ( $order->M_Language )
        {
        case "zh_CN" :
            $payment['M_Language'] = "cn";
            break;
        case "en_US" :
            $payment['M_Language'] = "en";
            break;
        case "zh_TW" :
            $payment['M_Language'] = "tw";
            break;
        }
        $info = array(
            "M_id" => $this->getConf( $payment['M_OrderId'], "member_id" ),
            "M_OrderID" => $payment['M_OrderId'],
            "M_OAmount" => $payment['M_Amount'],
            "M_OCurrency" => $this->supportCurrency[0],
            "M_URL" => $this->callbackUrl,
            "M_Language" => $payment['M_Language'],
            "T_TradeName" => $payment['T_TradeName'],
            "T_Unit" => $payment['T_Unit'],
            "T_UnitPrice" => $payment['T_UnitPrice'],
            "T_quantity" => $payment['T_quantity'],
            "T_carriage" => $payment['T_carriage'],
            "S_Name" => "",
            "S_Address" => "",
            "S_PostCode" => "",
            "S_Telephone" => "",
            "S_Email" => "",
            "R_Name" => "",
            "R_Address" => "",
            "R_PostCode" => "",
            "R_Telephone" => "",
            "R_Email" => "",
            "M_OComment" => "",
            "State" => 0,
            "M_ODate" => date( "Y-m-d h:i:s" )
        );
        $return = array( );
        $return['OrderMessage'] = implode( "|", $info );
        $return['digest'] = strtoupper( md5( $return['OrderMessage'].$this->getConf( $payment['M_OrderId'], "PrivateKey" ) ) );
        $return['M_ID'] = $this->getConf( $payment['M_OrderId'], "member_id" );
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $digest = trim( md5( $in['OrderMessage'] ) );
        $info = explode( "|", $in['OrderMessage'] );
        $paymentId = $info[1];
        $money = $info[2];
        if ( $in['Digest'] == $digest )
        {
            switch ( $info[22] )
            {
            case 0 :
                $message = "未支付";
                return PAY_CANCEL;
                break;
            case 2 :
                return PAY_SUCCESS;
                break;
            case 3 :
                $message = "交易失败";
                return PAY_FAILED;
                break;
            default :
                $message = "交易出现错误";
                return PAY_ERROR;
                break;
            }
        }
        else
        {
            $message = "交易出现错误";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "800pay用户ID", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

}

?>
