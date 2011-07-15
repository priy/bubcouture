<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_ipay extends paymentPlugin
{

    public $name = "IPAY在线支付";
    public $logo = "IPAY";
    public $version = 20070615;
    public $charset = "gb2312";
    public $submitUrl = "http://www.ipay.cn/4.0/bank.shtml";
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
    public $orderby = 23;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $v_mobile = "13800138000";
        $md5string = md5( $merId.$payment['M_OrderId'].$payment['M_Amount'].$payment['R_Email'].$v_mobile.$this->getConf( $payment['M_OrderId'], "PrivateKey" ) );
        $payment['P_Name'] = $payment['P_Name'] ? $payment['P_Name'] : $payment['R_Name'];
        $return['v_mid'] = $merId;
        $return['v_oid'] = $payment['M_OrderId'];
        $return['v_amount'] = $payment['M_Amount'];
        $return['v_date'] = date( "Ymd", $payment['M_Time'] );
        $return['v_name'] = $payment['R_Name'];
        $return['v_email'] = $payment['R_Email'];
        $return['v_mobile'] = $v_mobile;
        $return['v_tel'] = $payment['R_Telephone'];
        $return['v_rpost'] = $payment['R_PostCode'];
        $return['v_address'] = $payment['R_Address'];
        $return['v_rnote'] = $payment['M_Remark'];
        $return['v_payname'] = $payment['P_Name'];
        $return['v_url'] = $this->callbackUrl;
        $return['v_md5'] = $md5string;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $v_mid = trim( $in['v_mid'] );
        $v_oid = trim( $in['v_oid'] );
        $v_pamount = trim( $in['v_amount'] );
        $v_pmode = trim( $in['v_pmode'] );
        $v_pstatus = trim( $in['v_status'] );
        $v_pstring = trim( $in['v_pstring'] );
        $v_pdate = trim( $in['v_date'] );
        $v_pmd5 = trim( $in['v_md5'] );
        $v_phpmd5 = trim( $in['v_phpmd5'] );
        $paymentId = $orderid = substr( $v_oid, -6 );
        $money = $v_pamount;
        $content = $v_pdate.$v_mid.$v_oid.$v_pamount.$v_pstatus.$this->getConf( $payment['M_OrderId'], "PrivateKey" );
        if ( $v_phpmd5 == md5( $content ) )
        {
            if ( $v_pstatus == 0 || $v_pstatus == 20 )
            {
                return PAY_SUCCESS;
            }
            else if ( $v_pstatus == 12 )
            {
                $message = "交易失败";
                return PAY_FAILED;
            }
            else if ( $v_pstatus == 99 )
            {
                $message = "交易进行中";
                return PAY_PROGRESS;
            }
        }
        else
        {
            $message = "交易异常，Md5摘要认证错误";
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
