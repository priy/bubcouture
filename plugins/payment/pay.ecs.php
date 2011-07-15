<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_ecs extends paymentPlugin
{

    public $name = "ECS Payment Gateway ";
    public $logo = "ECS";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://secure.cps.lv/scripts/rprocess.dll?authorize";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "USD" => "USD",
        "EUR" => "EUR"
    );
    public $supportArea = array
    (
        0 => "AREA_USD",
        1 => "AREA_EUR"
    );
    public $desc = "";
    public $orderby = 44;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $tmp_url = $this->callbackUrl;
        $mac = "goodsTitle".$payment['M_OrderId']."goodsBid".$payment['M_Amount']."ordinaryFee0.00expressFee0.00sellerEmail".$merId."no".$payment['M_OrderId']."memo".$ikey;
        $mac = md5( $mac );
        $return['Merchant'] = $merId;
        $return['Account'] = $ikey;
        $return['Service'] = $this->getConf( $payment['M_OrderId'], "SecondPrivateKey" );
        $return['OrderID'] = $payment['M_OrderNO'];
        $return['ReferenceID'] = $payment['M_OrderId'];
        $return['Amount'] = $payment['M_Amount'];
        $return['Currency'] = $payment['M_Currency'];
        $return['Description'] = $payment['M_Remark'];
        $return['Customer'] = $payment['R_Name'];
        $return['Email'] = $payment['R_Email'];
        $return['IP'] = $_SERVER['REMOTE_ADDR'];
        $return['Site'] = $tmp_url;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $merid = $in['merid'];
        $payid = $in['ReferenceID'];
        $succ = $in['Status'];
        $ErrorCode = $in['ErrorCode'];
        $Message = $in['Message'];
        $orderno = $in['OrderID'];
        $paymentId = $payid;
        $money = "";
        switch ( $succ )
        {
        case "1" :
            return PAY_SUCCESS;
            break;
        case "0" :
            $message = "支付失败,请立即与商店管理员联系";
            return PAY_FAILED;
            break;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" ),
            "SecondPrivateKey" => array( "label" => "第二私钥", "type" => "string" )
        );
    }

    public function intString( $intvalue, $len )
    {
        $intstr = strval( $intvalue );
        $i = 1;
        for ( ; $i <= $len - strlen( $intstr ); ++$i )
        {
            $tmpstr .= "0";
        }
        return $tmpstr.$intstr;
    }

}

?>
