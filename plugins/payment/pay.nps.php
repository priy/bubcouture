<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_nps extends paymentPlugin
{

    public $name = "NPS网上支付－内卡";
    public $logo = "NPS";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://payment.nps.cn/PHPReceiveMerchantAction.do";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "CNY"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "";
    public $orderby = 19;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $state = 0;
        $language = 1;
        $payment['M_Currency'] = 1;
        if ( $payment['R_Name'] == "" )
        {
            $payment['R_Name'] = "NA";
        }
        if ( $payment['R_Address'] == "" )
        {
            $payment['R_Address'] = "NA";
        }
        if ( $payment['R_PostCode'] == "" )
        {
            $payment['R_PostCode'] = "NA";
        }
        if ( $payment['R_Telephone'] == "" )
        {
            $payment['R_Telephone'] = "NA";
        }
        if ( $payment['R_Email'] == "" )
        {
            $payment['R_Email'] = "NA";
        }
        $m_info = $merId."|".$payment['M_OrderId']."|".$payment['M_Amount']."|".$payment['M_Currency']."|".$this->callbackUrl."|".$language;
        $s_info = $payment['P_Name']."|".$payment['P_Address']."|".$payment['P_PostCode']."|".$payment['P_Telephone']."|".$payment['P_Email'];
        $r_info = $payment['R_Name']."|".$payment['R_Address']."|".$payment['R_PostCode']."|".$payment['R_Telephone']."|".$payment['R_Email']."|".$payment['M_Remark']."|".$state."|".date( "Y-m-d H:i:s", $payment['M_Time'] );
        $OrderInfo = $m_info."|".$s_info."|".$r_info;
        $charset = $this->system->loadModel( "utility/charset" );
        $OrderInfo = $charset->utf2local( $OrderInfo, "zh" );
        $OrderInfo = $this->StrToHex( $OrderInfo );
        $digest = strtoupper( md5( $OrderInfo.$ikey ) );
        $return['M_ID'] = $merId;
        $return['digest'] = $digest;
        $return['OrderMessage'] = $OrderInfo;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $m_id = $in['m_id'];
        $m_orderid = $in['m_orderid'];
        $m_oamount = $in['m_oamount'];
        $State = $in['m_status'];
        $OrderInfo = $in['OrderMessage'];
        $signMsg = $in['Digest'];
        $newmd5info = $in['newmd5info'];
        $paymentId = $m_orderid;
        $money = $m_oamount;
        $key = $this->getConf( $m_orderid, "PrivateKey" );
        $digest = strtoupper( md5( $OrderInfo.$key ) );
        $newtext = $m_id.$m_orderid.$m_oamount.$key.$State;
        $newMd5digest = strtoupper( md5( $newtext ) );
        if ( $digest == $signMsg )
        {
            $OrderInfo = $this->HexToStr( $OrderInfo );
            if ( $newmd5info == $newMd5digest )
            {
                if ( $State == 2 )
                {
                    return PAY_SUCCESS;
                }
                else
                {
                    $message = "更新数据库，支付失败。";
                    return PAY_FAILED;
                }
            }
            else
            {
                $message = "支付信息不正确，可能被篡改。";
                return PAY_ERROR;
            }
        }
        else
        {
            $message = "支付信息不正确，可能被篡改。";
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

    public function stringToHex( $s )
    {
        $r = "";
        $hexes = array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f" );
        $i = 0;
        for ( ; $i < strlen( $s ); ++$i )
        {
            $r .= $hexes[ord( $s[$i] ) >> 4].$hexes[ord( $s[$i] ) & 15];
        }
        return $r;
    }

    public function HexToStr( $hex )
    {
        $string = "";
        $i = 0;
        for ( ; $i < strlen( $hex ) - 1; $i += 2 )
        {
            $string .= chr( hexdec( $hex[$i].$hex[$i + 1] ) );
        }
        return $string;
    }

    public function StrToHex( $string )
    {
        $hex = "";
        $i = 0;
        for ( ; $i < strlen( $string ); ++$i )
        {
            $hex .= dechex( ord( $string[$i] ) );
        }
        $hex = strtoupper( $hex );
        return $hex;
    }

}

?>
