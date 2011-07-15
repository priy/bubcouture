<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_tenpaytrad extends paymentPlugin
{

    public function pay_tenpaytrad_callback( $in, &$paymentId, &$money, &$message, &$tradeno )
    {
        $cmdno = $in['cmdno'];
        $version = $in['version'];
        $retcode = $in['retcode'];
        $status = $in['status'];
        $seller = $in['seller'];
        $total_fee = $in['total_fee'];
        $trade_price = $in['trade_price'];
        $transport_fee = $in['transport_fee'];
        $buyer_id = $in['buyer_id'];
        $chnid = $in['chnid'];
        $cft_tid = $in['cft_tid'];
        $smch_vno = $in['mch_vno'];
        $attach = $in['attach'];
        $version = $in['version'];
        $sign = $in['sign'];
        $ikey = $this->getConf( $smch_vno, "PrivateKey" );
        $buffer = $this->AddParameter( $buffer, "attach", $attach );
        $buffer = $this->AddParameter( $buffer, "buyer_id", $buyer_id );
        $buffer = $this->AddParameter( $buffer, "cft_tid", $cft_tid );
        $buffer = $this->AddParameter( $buffer, "chnid", $chnid );
        $buffer = $this->AddParameter( $buffer, "cmdno", $cmdno );
        $buffer = $this->AddParameter( $buffer, "mch_vno", $smch_vno );
        $buffer = $this->AddParameter( $buffer, "retcode", $retcode );
        $buffer = $this->AddParameter( $buffer, "seller", $seller );
        $buffer = $this->AddParameter( $buffer, "status", $status );
        $buffer = $this->AddParameter( $buffer, "total_fee", $total_fee );
        $buffer = $this->AddParameter( $buffer, "trade_price", $trade_price );
        $buffer = $this->AddParameter( $buffer, "transport_fee", $transport_fee );
        $buffer = $this->AddParameter( $buffer, "version", $version );
        $strLocalSign = strtoupper( md5( $buffer."&key=".$ikey ) );
        $tradeno = $in['cft_tid'];
        $paymentId = $attach;
        $money = $total_fee / 100;
        if ( $strLocalSign == $sign )
        {
            if ( $retcode == "0" )
            {
                echo "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">";
                switch ( $status )
                {
                case 1 :
                    return PAY_PROGRESS;
                    break;
                case 2 :
                    break;
                case 3 :
                    return PAY_SUCCESS;
                    break;
                case 4 :
                    return PAY_PROGRESS;
                    break;
                case 5 :
                    return PAY_SUCCESS;
                    break;
                case 6 :
                    break;
                case 7 :
                    break;
                case 8 :
                    break;
                case 9 :
                    return PAY_REFUND_SUCCESS;
                    break;
                case 10 :
                    break;
                default :
                    return PAY_ERROR;
                    break;
                }
            }
            else
            {
                $message = $retcode;
                return PAY_FAIL;
            }
        }
        else
        {
            $message = "qianming";
            return PAY_ERROR;
        }
    }

    public function AddParameter( $buffer, $parameterName, $parameterValue )
    {
        if ( $parameterValue == "" )
        {
            return $buffer;
        }
        if ( empty( $buffer ) )
        {
            $buffer = $parameterName."=".$parameterValue;
        }
        else
        {
            $buffer = $buffer."&".$parameterName."=".$parameterValue;
        }
        return $buffer;
    }

}

?>
