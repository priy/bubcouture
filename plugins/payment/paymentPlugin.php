<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function payCallBack( $return )
{
    require( dirname( __FILE__ )."/../loader.php" );
    $oPay =& $system->loadModel( "trading/payment" );
    $file = basename( $_SERVER['PHP_SELF'] );
    $fileArr = explode( ".", $file );
    $gateWayId = $fileArr[1];
    $serverCall = preg_match( "/^pay\\.([^\\.]+)\\.server\\.php\$/i", $file, $matches ) ? $matches[1] : FALSE;
    if ( $serverCall )
    {
        require( "pay.".$gateWayId.".server.php" );
        $func_name = "pay_".$serverCall."_callback";
        $className = "pay_".$serverCall;
        ( $system );
        $o = new $className( );
        $status = $o->$func_name( $return, $paymentId, $money, $message, $tradeno );
        $info = array(
            "money" => $money,
            "memo" => $message,
            "trade_no" => $tradeno
        );
        $result = $oPay->setPayStatus( $paymentId, $status, $info );
    }
    else
    {
        require( "pay.".$gateWayId.".php" );
        $money = NULL;
        $status = NULL;
        $className = "pay_".$gateWayId;
        ( $system );
        $o = new $className( );
        $status = $o->callback( $return, $paymentId, $money, $message, $tradeno );
        $result = $oPay->progress( $paymentId, $status, array(
            "money" => $money,
            "memo" => $message,
            "trade_no" => $tradeno
        ) );
    }
}

paycallback( array_merge( $_GET, $_POST ) );
exit( );
?>
