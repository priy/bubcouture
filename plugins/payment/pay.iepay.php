<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_iepay extends paymentPlugin
{

    public $name = "IEPAY";
    public $logo = "IEPAY";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://www.epay.cc/creditcard/cardfinance.php";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "TWD" => "TWD"
    );
    public $supportArea = array
    (
        0 => "AREA_TWD"
    );
    public $desc = "";
    public $orderby = 25;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $msign = md5( $ikey.":".$this->M_Amount.",".$merId.$this->orderid.",".$merId.",".$card.",".$scard.",".$actioncode.",".$actionParameter.",".$ver );
        $msign = strtolower( $msign );
        $return['storeid'] = $merId;
        $return['password'] = $ikey;
        $return['account'] = $payment['M_Amount'];
        $return['remark'] = $payment['M_Remark'];
        $return['orderid'] = $payment['M_OrderId'];
        $return['storename'] = $this->getConf( "system.shopname" );
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $v_oid = trim( $in['orderid'] );
        $v_amount = trim( $in['account'] );
        $v_date = trim( $in['authdate'] );
        $v_status = trim( $in['status'] );
        $paymentId = $v_oid;
        $money = $v_amount;
        $ikey = $this->getConf( $v_oid, "PrivateKey" );
        if ( $v_status == "0" )
        {
            return PAY_SUCCESS;
        }
        else
        {
            $message = "交易失败";
            return PAY_FAILED;
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
