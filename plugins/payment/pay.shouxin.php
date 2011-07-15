<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_shouxin extends paymentPlugin
{

    public $name = "首信易在线支付";
    public $logo = "SHOUXIN";
    public $version = 20070615;
    public $charset = "gb2312";
    public $submitUrl = "http://pay.beijing.com.cn/prs/user_payment.checkit";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "0",
        "USD" => "1"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_USA"
    );
    public $desc = "";
    public $orderby = 11;
    public $head_charset = "gb2312";
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $o = $this->system->loadModel( "utility/charset" );
        if ( $payment['M_Currency'] == "CNY" )
        {
        }
        else
        {
            $this->submitUrl = "http://pay.beijing.com.cn/prs/e_user_payment.checkit";
        }
        $payment['M_Time'] = $payment['M_Time'] ? $payment['M_Time'] : time( );
        $oid = date( "Ymd", $payment['M_Time'] )."-".$merId."-".$payment['M_OrderId'];
        $sourcedata = $o->utf2local( $this->supportCurrency[$payment['M_Currency']].date( "Ymd", $payment['M_Time'] ).$payment['M_Amount'].$payment['R_Name'].$oid.$merId.$this->callbackUrl, "zh" );
        $payment['P_Name'] = $payment['P_Name'] ? $payment['P_Name'] : $payment['R_Name'];
        $return['version'] = "3.0";
        $return['v_oid'] = $oid;
        $return['v_mid'] = $merId;
        $return['v_rcvname'] = $payment['R_Name'];
        $return['v_rcvaddr'] = $payment['R_Address'];
        $return['v_rcvtel'] = $payment['R_Telephone'];
        $return['v_rcvpost'] = $payment['R_PostCode'];
        $return['v_amount'] = $payment['M_Amount'];
        $return['v_ymd'] = date( "Ymd", $payment['M_Time'] );
        $return['v_orderstatus'] = "0";
        $return['v_ordername'] = $payment['P_Name'];
        $return['v_moneytype'] = $this->supportCurrency[$payment['M_Currency']];
        $return['v_url'] = $this->callbackUrl;
        $return['v_md5info'] = $this->shopex_hmac_md5( $sourcedata, $this->getConf( $payment['M_OrderId'], "PrivateKey" ) );
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $v_oid = $in['v_oid'];
        $v_amount = $in['v_amount'];
        $v_pmode = $in['v_pmode'];
        $v_pstatus = $in['v_pstatus'];
        $v_pstring = $in['v_pstring'];
        $v_md5info = $in['v_md5info'];
        $v_moneytype = $in['v_moneytype'];
        $v_md5money = $in['v_md5money'];
        $v_sign = $in['v_sign'];
        $tmp_order = explode( "-", $v_oid );
        $paymentId = $tmp_order[2];
        $money = $v_amount;
        $ikey = $this->getConf( $paymentId, "PrivateKey" );
        $md5str = $v_oid.$v_pstatus.$v_pstring.$v_pmode;
        $md5moneystr = $v_amount.$v_moneytype;
        $md5moneystring = $this->shopex_hmac_md5( $md5moneystr, $ikey );
        $md5string = $this->shopex_hmac_md5( $md5str, $ikey );
        if ( $v_md5info == $md5string && $v_md5money == $md5moneystring )
        {
            if ( $v_pstatus == 20 )
            {
                return PAY_SUCCESS;
            }
            else
            {
                $message = "交易失败";
                return PAY_FAILED;
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

    public function shopex_hmac_md5( $data, $key )
    {
        if ( 64 < strlen( $key ) )
        {
            $key = pack( "H32", md5( $key ) );
        }
        if ( strlen( $key ) < 64 )
        {
            $key = str_pad( $key, 64, chr( 0 ) );
        }
        $_ipad = substr( $key, 0, 64 ) ^ str_repeat( chr( 54 ), 64 );
        $_opad = substr( $key, 0, 64 ) ^ str_repeat( chr( 92 ), 64 );
        return md5( $_opad.pack( "H32", md5( $_ipad.$data ) ) );
    }

}

?>
