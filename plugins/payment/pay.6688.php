<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_6688 extends paymentPlugin
{

    public $name = "6688网上支付";
    public $logo = "6688";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "http://pay.6688.com/paygate/frame.asp";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => ""
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "";
    public $orderby = 30;

    public function toSubmit( $payment )
    {
        $this->payment = "";
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $md5string = md5( "tmbrid=".$merId."&tsummoney=".$this->M_Amount."&tcontent1=".$this->M_Remark."&todrid=".$this->M_OrderId."&tpwd=".$this->getConf( $payment['M_OrderId'], "PrivateKey" ) );
        $tSupperComRegflag = 0;
        $return['tmbrid'] = $merId;
        $return['toname'] = $this->payment;
        $return['tsummoney'] = $payment['M_Amount'];
        $return['trname'] = $payment['R_Name'];
        $return['traddress'] = $payment['R_Address'];
        $return['todrid'] = $payment['M_OrderId'];
        $return['temail'] = $payment['R_Email'];
        $return['trphone'] = $payment['R_Telephone'];
        $return['trzipcode'] = $payment['R_PostCode'];
        $return['tuserurl'] = $this->callbackUrl;
        $return['tcontent1'] = $payment['M_Remark'];
        $return['tSupperComRegflag'] = $tSupperComRegflag;
        $return['mac'] = $md5string;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $billNo = $_POST['billNo'];
        $amount = $_POST['amount'];
        $succ = $_POST['succ'];
        $Mac = $_POST['Mac'];
        $paymentId = $billNo;
        $money = $amount;
        $message = "";
        $ikey = $this->getConf( $billNo, "PrivateKey" );
        $content = "billNo=".$billNo."&amount=".$amount."&succ=".$succ."&pwd=".$ikey;
        if ( $Mac == md5( $content ) )
        {
            switch ( $succ )
            {
            case "Y" :
                return PAY_SUCCESS;
                break;
            case "N" :
                $message = "支付失败";
                return PAY_FAILED;
                break;
            }
            else
            {
                $message = "验证失败";
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
