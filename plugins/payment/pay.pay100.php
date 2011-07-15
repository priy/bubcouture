<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_pay100 extends paymentPlugin
{

    public $name = "PAY100.COM 百付通";
    public $logo = "PAY100";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://www.pay100.com/interface/Professional/paypre.aspx";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "1001"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "PAY100.COM";
    public $orderby = 26;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $tmp_url = $this->url."index.php?gOo=pay100_reply.do&";
        $orderdate = date( "Y-m-d H:i:s", $payment['M_Time'] );
        $strRnote = strtoupper( $payment['M_Remark'] );
        $StrContent = "1001".$merId.$payment['M_OrderId'].$payment['M_Amount'].$payment['M_Currency'].$orderdate.$payment['M_OrderNO'].$strRnote."1"."1"."1".$this->callbackUrl.$$this->callbackUrl.$ikey;
        $return['OrderType'] = "1001";
        $return['CoagentID'] = "";
        $return['InceptUserName'] = $merId;
        $return['OrderNumber'] = $payment['M_OrderId'];
        $return['Amount'] = $payment['M_Amount'];
        $return['MoneyCode'] = $payment['M_Currency'];
        $return['TransDateTime'] = $orderdate;
        $return['Title'] = $payment['M_OrderNO'];
        $return['Content'] = $payment['M_Remark'];
        $return['CompleteReturn'] = "1";
        $return['FailReturn'] = "1";
        $return['ReturnValidate'] = "1";
        $return['ReturnUrl'] = $this->callbackUrl;
        $return['RedirectUrl'] = $this->callbackUrl;
        $return['SignCode'] = strtoupper( md5( $StrContent ) );
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $OrderType = $in['OrderType'];
        $InceptUserName = $in['InceptUserName'];
        $PayUserName = $in['PayUserName'];
        $OrderNumber = $in['OrderNumber'];
        $StateCode = $in['StateCode'];
        $Amount = $in['Amount'];
        $MoneyCode = $in['MoneyCode'];
        $TransDateTime = $in['TransDateTime'];
        $TransCompleteDateTime = $in['TransCompleteDateTime'];
        $TransType = $in['TransType'];
        $PledgeDay = $in['PledgeDay'];
        $Memo1 = $in['Memo1'];
        $Memo2 = $in['Memo2'];
        $SignCode = $in['SignCode'];
        $paymentId = $OrderNumber;
        $money = $Amount;
        $key = $this->getConf( $OrderNumber, "PrivateKey" );
        $strText = $OrderType.$InceptUserName.$PayUserName.$OrderNumber.$StateCode.$Amount.$MoneyCode.$TransDateTime.$TransCompleteDateTime.$TransType.$PledgeDay.$Memo1.$Memo2.$key;
        $mac = md5( $strText );
        if ( strtoupper( $mac ) == strtoupper( $SignCode ) )
        {
            switch ( $StateCode )
            {
            case "1001" :
                return PAY_SUCCESS;
                break;
            case "1002" :
                $message = "支付失败,请立即与商店管理员联系";
                return PAY_FAILED;
                break;
            }
            else
            {
                $message = "签名认证失败,请立即与商店管理员联系";
                return PAY_ERROR;
            }
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
