<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_cmbc extends paymentPlugin
{

    public $name = "招商银行";
    public $logo = "CMBC";
    public $version = 20050802;
    public $charset = "gbk";
    public $submitUrl = "https://netpay.cmbchina.com/netpayment/BaseHttp.dll?PrePayC2";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "001"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $intro = "1987年，招商银行作为中国第一家由企业创办的商业银行，以及中国政府推动金融改革的试点银行，在中国改革开放的最前沿----深圳经济特区成立。2002年，招商银行在上海证券交易所上市；2006年，在香港联合交易所上市。";
    public $orderby = 35;
    public $head_charset = "gbk";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $keyPass = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $return['BranchID'] = substr( $merId, 0, 4 );
        $return['CoNo'] = substr( $merId, -6 );
        $strBillNo = substr( $payment['M_OrderId'], 0, -10 );
        $return['BillNo'] = $this->intString( $payment['M_OrderId'], 10 );
        $return['Amount'] = $payment['M_Amount'];
        $return['Date'] = date( "Ymd", $payment['M_Time'] ? $payment['M_Time'] : time( ) );
        $return['MerchantUrl'] = $this->callbackUrl;
        $md5src = $merId.$keyPass.$return['BranchID'].$return['BillNo'].$return['Amount'].$this->callbackUrl;
        $return['MerchantPara'] = strtoupper( md5( $md5src ) )."&strBillNo=".$strBillNo;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $Succeed = $in['Succeed'];
        $para = http_build_query( $in );
        $MerPk = $this->getConf( $paymentId, "MerPrk" );
        $filepath = dirname( __FILE__ )."/../../home/upload/cmbc/".$MerPrk;
        if ( !file_exists( $filepath ) )
        {
            $message = "缺少公钥文件";
            return PAY_FAILED;
        }
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) == "WIN" )
        {
            $cmd = "java -jar C:/CheckCmb.jar \"{$para}\" \"<{$filepath}\"";
        }
        else
        {
            $cmd = "java -jar /bin/CheckCmb.jar \"{$para}\" \"<{$filepath}\"";
        }
        $handle = popen( $cmd, "r" );
        $isok = fread( $handle, 8 );
        pclose( $handle );
        if ( $isok == "true" )
        {
            if ( $Succeed == "Y" )
            {
                $message = "支付成功！";
                return PAY_SUCCESS;
            }
            else
            {
                $message = "支付失败！";
                return PAY_FAILED;
            }
        }
        else
        {
            $message = "签名验证错误！";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" ),
            "MerPrk" => array( "label" => "公钥文件", "type" => "file" )
        );
    }

    public function intString( $intvalue, $len )
    {
        $intstr = intval( $intvalue );
        if ( strlen( $intvalue ) < $len )
        {
            $i = 1;
            for ( ; $i <= $len - strlen( $intstr ); ++$i )
            {
                $tmpstr .= "0";
            }
            return $tmpstr.$intstr;
        }
        else if ( $len < strlen( $intvalue ) )
        {
            return substr( $intvalue, 0 - $len );
        }
        else
        {
            return $intvalue;
        }
    }

}

echo "\n";
?>
