<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_nochek extends paymentPlugin
{

    public $name = "NOCHEX在线支付";
    public $logo = "NOCHEK";
    public $version = 20070902;
    public $charset = "GB2312";
    public $submitUrl = "https://www.nochex.com/nochex.dll/checkout";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "GBP" => "GBP"
    );
    public $supportArea = array
    (
        0 => "AREA_GBP"
    );
    public $desc = "NOCHEX在线支付";
    public $orderby = 45;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $return['email'] = $merId;
        $return['ordernumber'] = $payment['M_OrderId'];
        $return['amount'] = $payment['M_Amount'];
        $return['responderurl'] = $this->callbackUrl;
        $return['description'] = $payment['M_Remark'];
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $req = "";
        foreach ( $in as $key => $value )
        {
            $value = urlencode( stripslashes( $value ) );
            $req .= "&{$key}={$value}";
        }
        $req = ltrim( $req, "&" );
        $header .= "POST /nochex.dll/apc/apc HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen( $req )."\r\n\r\n";
        $fp = fsockopen( "www.nochex.com", 80, $errno, $errstr, 10 );
        if ( !$fp )
        {
            echo "succ0=N";
            $succ = "N";
        }
        else
        {
            echo "<br />succ1=Y";
            fputs( $fp, $header.$req );
            $res = "";
            $headerdone = FALSE;
            while ( !feof( $fp ) )
            {
                $line = fgets( $fp, 1024 );
                if ( strcmp( $line, "\r\n" ) == 0 )
                {
                    $headerdone = TRUE;
                }
                else if ( $headerdone )
                {
                    $res .= $line;
                }
            }
            echo "<br />res=".$res;
            $lines = explode( "\n", $res );
            $keyarray = array( );
            echo "<br />lines0=".$lines[0];
            if ( strcmp( $lines[0], "AUTHORISED" ) == 0 )
            {
                $i = 1;
                for ( ; $i < count( $lines ); ++$i )
                {
                    list( $key, $val ) = explode( "=", $lines[$i] );
                    $keyarray[urldecode( $key )] = urldecode( $val );
                }
                echo "<br />to_email=".( $to_email = $keyarray['to_email'] );
                echo "<br />from_email=".( $from_email = $keyarray['from_email'] );
                echo "<br />transaction_id=".( $transaction_id = $keyarray['transaction_id'] );
                echo "<br />amount=".( $amount = $keyarray['amount'] );
                echo "<br />transaction_date=".( $mydate = $keyarray['transaction_date'] );
                echo "<br />order_id=".( $payid = $keyarray['order_id'] );
                echo "<br />security_key=".( $security_key = $keyarray['security_key'] );
                echo "<br />status=".( $status = $keyarray['status'] );
                $succ = "Y";
            }
            else if ( strcmp( $lines[0], "DECLINED" ) == 0 )
            {
                $succ = "N";
            }
            echo "succ2=".$succ;
        }
        fclose( $fp );
        switch ( $succ )
        {
        case "Y" :
            $paymentId = $keyarray['order_id'];
            $money = $keyarray['amount'];
            return PAY_SUCCESS;
            break;
        case "N" :
            $message = "支付失败,请立即与商店管理员联系";
            return PAY_FAILED;
            break;
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
