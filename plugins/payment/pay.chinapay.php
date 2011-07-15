<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_chinapay extends paymentPlugin
{

    public $name = "上海银联电子支付ChinaPay";
    public $logo = "CHINAPAY";
    public $charset = "utf8";
    public $version = 20070902;
    public $submitUrl = "https://payment.ChinaPay.com/pay/TransGet";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "156"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $M_Language = "1";
    public $orderby = 16;
    public $head_charset = "utf-8";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $MerPrk = $this->getConf( $payment['M_OrderId'], "MerPrk" );
        $PubPk = $this->getConf( $payment['M_OrderId'], "PubPk" );
        $TransType = "0001";
        if ( !$payment['M_Time'] )
        {
            $payment['M_Time'] = time( );
        }
        $ordId = $this->intString( substr( $merId, -5 ).substr( date( "YmdHis", $payment['M_Time'] ), -7 ), 16 );
        $payment['M_Amount'] = $this->intString( $payment['M_Amount'] * 100, 12 );
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) == "WIN" )
        {
            ( "CPNPC.NPC" );
            $chinapay = new COM( );
            if ( file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$PubPk ) )
            {
                $chinapay->setMerKeyFile( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk );
                $chinapay->setPubKeyFile( dirname( __FILE__ )."/../../home/upload/chinapay/".$PubPk );
            }
            else if ( file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$PubPk ) )
            {
                $chinapay->setMerKeyFile( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk );
                $chinapay->setPubKeyFile( dirname( __FILE__ )."/../../cert/chinapay/".$PubPk );
            }
            $chkvalue = $chinapay->sign( $merId, $ordId, $payment['M_Amount'], $this->supportCurrency[$payment['M_Currency']], date( "Ymd", $payment['M_Time'] ), $TransType );
        }
        else
        {
            if ( file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$PubPk ) )
            {
                setmerkeyfile( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk );
                setpubkeyfile( dirname( __FILE__ )."/../../home/upload/chinapay/".$PubPk );
            }
            else if ( file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$PubPk ) )
            {
                setmerkeyfile( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk );
                setpubkeyfile( dirname( __FILE__ )."/../../cert/chinapay/".$PubPk );
            }
            $chkvalue = signorder( $merId, $ordId, $payment['M_Amount'], $this->supportCurrency[$payment['M_Currency']], date( "Ymd", $payment['M_Time'] ), $TransType );
        }
        switch ( $chkvalue )
        {
        case "-100" :
            $errinfo = "环境变量\"NPCDIR\"未设置";
            break;
        case "-101" :
            $errinfo = "商户密钥文件不存在或无法打开";
            break;
        case "-102" :
            $errinfo = "密钥文件格式错误";
            break;
        case "-103" :
            $errinfo = "秘钥商户号和用于签名的商户号不一致";
            break;
        case "-130" :
            $errinfo = "用于签名的字符串长度为空";
            break;
        case "-111" :
            $errinfo = "没有设置秘钥文件路径，或者没有设置“NPCDIR”环境变量";
            break;
        default :
            break;
        }
        if ( $errinfo )
        {
            header( "Content-Type:text/html;charset=utf-8" );
            echo $errinfo;
            $this->_succ = TRUE;
            exit( );
        }
        $return['MerId'] = $merId;
        $return['OrdId'] = $ordId;
        $return['TransAmt'] = $payment['M_Amount'];
        $return['CuryId'] = $this->supportCurrency[$payment['M_Currency']];
        $return['TransDate'] = date( "Ymd", $payment['M_Time'] );
        $return['TransType'] = $TransType;
        $return['Version'] = "20040916";
        $return['PageRetUrl'] = $this->callbackUrl;
        $return['GateId'] = "";
        $return['Priv1'] = $payment['M_OrderId'];
        $return['ChkValue'] = $chkvalue;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $paymentId = $in['Priv1'];
        $money = intval( $in['amount'] ) / 100;
        $merId = $this->getConf( $paymentId, "member_id" );
        $MerPk = $this->getConf( $paymentId, "MerPrk" );
        $PubPk = $this->getConf( $paymentId, "PubPk" );
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) == "WIN" )
        {
            ( "CPNPC.NPC" );
            $chinapay = new COM( );
            if ( file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk ) )
            {
                $chinapay->setMerKeyFile( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk );
                $chinapay->setPubKeyFile( dirname( __FILE__ )."/../../home/upload/chinapay/".$PubPk );
            }
            else if ( file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk ) )
            {
                $chinapay->setMerKeyFile( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk );
                $chinapay->setPubKeyFile( dirname( __FILE__ )."/../../cert/chinapay/".$PubPk );
            }
            $res = $chinapay->check( $in['merid'], $in['orderno'], $in['amount'], $in['currencycode'], $in['transdate'], $in['transtype'], $in['status'], $in['checkvalue'] );
            if ( $res == "0" )
            {
                $res = TRUE;
            }
            else
            {
                $res = FALSE;
            }
        }
        else
        {
            if ( file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk ) )
            {
                setmerkeyfile( dirname( __FILE__ )."/../../home/upload/chinapay/".$MerPrk );
                setpubkeyfile( dirname( __FILE__ )."/../../home/upload/chinapay/".$PubPk );
            }
            else if ( file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk ) && file_exists( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk ) )
            {
                setmerkeyfile( dirname( __FILE__ )."/../../cert/chinapay/".$MerPrk );
                setpubkeyfile( dirname( __FILE__ )."/../../cert/chinapay/".$PubPk );
            }
            $res = verifytransresponse( $in['merid'], $in['orderno'], $in['amount'], $in['currencycode'], $in['transdate'], $in['transtype'], $in['status'], $in['checkvalue'] );
            if ( $res == 0 )
            {
                $res = TRUE;
            }
            else
            {
                $res = FALSE;
            }
        }
        if ( $res )
        {
            if ( $in['status'] == "1001" )
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
            $message = "验证签名错误！";
            return PAY_ERROR;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PubPk" => array( "label" => "公钥文件", "type" => "file" ),
            "MerPrk" => array( "label" => "私钥文件", "type" => "file" )
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
