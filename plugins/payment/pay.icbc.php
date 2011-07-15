<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_icbc extends paymentPlugin
{

    public $name = "中国工商银行[1.0.0.3版]";
    public $logo = "ICBC";
    public $version = 20070902;
    public $charset = "gbk";
    public $submitUrl = "https://B2C.icbc.com.cn/servlet/ICBCINBSEBusinessServlet";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "001"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $intro = "中国工商银行网上银行B2C支付网关可以使用在windows主机和linux主机，请在申请工行网关接口时申请1.0.0.3版。";
    public $orderby = 8;
    public $head_charset = "gbk";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $keyPass = $this->getConf( $payment['M_OrderId'], "keyPass" );
        $icbcno = $this->getConf( $payment['M_OrderId'], "icbcno" );
        $icbcFile = $this->getConf( $payment['M_OrderId'], "icbcFile" );
        $keyFile = $this->getConf( $payment['M_OrderId'], "keyFile" );
        $certFile = $this->getConf( $payment['M_OrderId'], "certFile" );
        $charset = $this->system->loadModel( "utility/charset" );
        if ( is_dir( dirname( __FILE__ )."/../../home/upload/icbc/" ) )
        {
            $realpath = dirname( __FILE__ )."/../../home/upload/icbc/";
        }
        else if ( is_dir( dirname( __FILE__ )."/../../cert/icbc/" ) )
        {
            $realpath = dirname( __FILE__ )."/../../cert/icbc/";
        }
        $key = $realpath.$keyFile;
        $cert = $realpath.$certFile;
        $icbc = $realpath.$icbcFile;
        if ( !file_exists( $key ) )
        {
            exit( "ICBC key file not found!" );
        }
        if ( !file_exists( $cert ) )
        {
            exit( "ICBC Cert file not found!" );
        }
        $aREQ['interfaceName'] = "ICBC_PERBANK_B2C";
        $aREQ['interfaceVersion'] = "1.0.0.3";
        $aREQ['merID'] = $merId;
        $aREQ['merAcct'] = $icbcno;
        $aREQ['merURL'] = $this->callbackUrl;
        $aREQ['notifyType'] = "HS";
        $aREQ['orderid'] = $payment['M_OrderId']."-".$payment['M_Time'];
        $aREQ['amount'] = $payment['M_Amount'] * 100;
        $aREQ['curType'] = "001";
        $aREQ['resultType'] = 0;
        $aREQ['orderDate'] = date( "YmdHis", empty( $payment['M_Time'] ) ? time( ) : $payment['M_Time'] );
        $aREQ['verifyJoinFlag'] = "0";
        $aREQ['goodsID'] = "";
        $aREQ['goodsName'] = $payment['M_OrderNO'];
        $aREQ['goodsNum'] = 1;
        $aREQ['carriageAmt'] = 0;
        $aREQ['merHint'] = "";
        $aREQ['remark1'] = $charset->utf2local( $payment['rnote'], "zh" );
        $aREQ['remark2'] = "";
        $aREQ['verifyJoinFlag'] = 0;
        $tranData = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"no\"?><B2CReq><interfaceName>".$aREQ['interfaceName']."</interfaceName><interfaceVersion>".$aREQ['interfaceVersion']."</interfaceVersion><orderInfo><orderDate>".$aREQ['orderDate']."</orderDate><orderid>".$aREQ['orderid']."</orderid><amount>".$aREQ['amount']."</amount><curType>".$aREQ['curType']."</curType><merID>".$aREQ['merID']."</merID><merAcct>".$aREQ['merAcct']."</merAcct></orderInfo><custom><verifyJoinFlag>".$aREQ['verifyJoinFlag']."</verifyJoinFlag><Language>ZH_CN</Language></custom><message><goodsID>".$aREQ['goodsID']."</goodsID><goodsName>".$aREQ['goodsName']."</goodsName><goodsNum>".$aREQ['goodsNum']."</goodsNum><carriageAmt>".$aREQ['carriageAmt']."</carriageAmt><merHint>".$aREQ['merHint']."</merHint><remark1>".$aREQ['remark1']."</remark1><remark2>".$aREQ['remark2']."</remark2><merURL>".$aREQ['merURL']."</merURL><merVAR>".$payment['M_OrderId']."</merVAR></message></B2CReq>";
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) == "WIN" )
        {
            ( "ICBCEBANKUTIL.B2CUtil" );
            $bb = new COM( );
            $rc = $bb->init( $icbc, $cert, $key, $keyPass );
            $merSignMsg = $bb->signC( $tranData, strlen( $tranData ) );
        }
        else
        {
            $cmd = "/bin/icbc_sign '{$key}' '{$keyPass}' '{$tranData}'";
            $handle = popen( $cmd, "r" );
            $merSignMsg = fread( $handle, 2096 );
            pclose( $handle );
        }
        $fp = fopen( $cert, "rb" );
        $merCert = fread( $fp, filesize( $cert ) );
        $merCert = base64_encode( $merCert );
        fclose( $fp );
        $aFinalReq['interfaceName'] = $aREQ['interfaceName'];
        $aFinalReq['interfaceVersion'] = $aREQ['interfaceVersion'];
        $aFinalReq['tranData'] = base64_encode( $tranData );
        $aFinalReq['merSignMsg'] = $merSignMsg;
        $aFinalReq['merCert'] = $merCert;
        foreach ( $aFinalReq as $key => $val )
        {
            $return[$key] = $val;
        }
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $paymentId = $in['merVAR'];
        if ( is_dir( dirname( __FILE__ )."/../../home/upload/icbc/" ) )
        {
            $realpath = dirname( __FILE__ )."/../../home/upload/icbc/";
        }
        else if ( is_dir( dirname( __FILE__ )."/../../cert/icbc/" ) )
        {
            $realpath = dirname( __FILE__ )."/../../cert/icbc/";
        }
        $merId = $this->getConf( $paymentId, "member_id" );
        $icbc = $realPath.$this->getConf( $paymentId, "icbcFile" );
        $cert = $realPath.$this->getConf( $paymentId, "certFile" );
        $key = $realPath.$this->getConf( $paymentId, "keyFile" );
        $keyPass = $this->getConf( $paymentId, "keyPass" );
        $notifyData = base64_decode( $in['notifyData'] );
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) == "WIN" )
        {
            ( "ICBCEBANKUTIL.B2CUtil" );
            $bb = new COM( );
            $bb->init( $icbc, $cert, $key, $keyPass );
            $isok = $bb->verifySignC( $notifyData, strlen( $notifyData ), $in['signMsg'], strlen( $in['signMsg'] ) );
        }
        else
        {
            $cmd = "/bin/icbc_verify '{$icbcpubcert}' '{$notifyData}' '{$signMsg}'";
            $handle = popen( $cmd, "r" );
            $isok = fread( $handle, 8 );
            pclose( $handle );
        }
        if ( $isok == 0 )
        {
            preg_match( "/\\<amount\\>(.*)\\<\\/amount\\>.+\\<TranSerialNo\\>(.*)\\<\\/TranSerialNo\\>.+\\<tranStat\\>(.*)\\<\\/tranStat\\>/i", $notifyData, $rnt );
            $money = $rnt[1] / 100;
            $tradeno = $rnt[2];
            if ( $rnt[3] == 1 )
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
            "member_id" => array( "label" => "商城代码", "type" => "string", "helpMsg" => "此处填写您的支付帐号、客户号或客户id等，此帐号在支付服务提供商处取得；" ),
            "icbcno" => array( "label" => "工行帐号", "type" => "string" ),
            "keyFile" => array( "label" => "商户私钥文件", "type" => "file" ),
            "certFile" => array( "label" => "商户公钥文件", "type" => "file" ),
            "icbcFile" => array( "label" => "工行公钥文件", "type" => "file" ),
            "keyPass" => array( "label" => "私钥保护密码", "type" => "string" )
        );
    }

}

?>
