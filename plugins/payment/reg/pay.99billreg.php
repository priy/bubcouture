<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function appendParam( $returnStr, $paramId, $paramValue )
{
    if ( $returnStr != "" )
    {
        if ( $paramValue != "" )
        {
            $returnStr .= "&".$paramId."=".$paramValue;
        }
    }
    else if ( $paramValue != "" )
    {
        $returnStr = $paramId."=".$paramValue;
    }
    return $returnStr;
}

$in = array_merge( $_GET, $_POST );
$key = "LHLEF8EA4ZY853NF";
$version = $in['version'];
$signType = $in['signType'];
$merchantMbrCode = $in['merchantMbrCode'];
$requestId = $in['requestId'];
$userId = $in['userId'];
$userEmail = $in['userEmail'];
$userName = $in['userName'];
$orgName = $in['orgName'];
$ext1 = $in['ext1'];
$ext2 = $in['ext2'];
$applyResult = $in['applyResult'];
$errorCode = $in['errorCode'];
$signMsg = $in['signMsg'];
$$signMsgVal = "";
$signMsgVal = appendparam( $signMsgVal, "version", $version );
$signMsgVal = appendparam( $signMsgVal, "signType", $signType );
$signMsgVal = appendparam( $signMsgVal, "merchantMbrCode", $merchantMbrCode );
$signMsgVal = appendparam( $signMsgVal, "requestId", $requestId );
$signMsgVal = appendparam( $signMsgVal, "userId", $userId );
$signMsgVal = appendparam( $signMsgVal, "userEmail", $userEmail );
$signMsgVal = appendparam( $signMsgVal, "userName", urlencode( $userName ) );
$signMsgVal = appendparam( $signMsgVal, "orgName", urlencode( $orgName ) );
$signMsgVal = appendparam( $signMsgVal, "ext1", urlencode( $ext1 ) );
$signMsgVal = appendparam( $signMsgVal, "ext2", urlencode( $ext2 ) );
$signMsgVal = appendparam( $signMsgVal, "applyResult", $applyResult );
$signMsgVal = appendparam( $signMsgVal, "errorCode", $errorCode );
$signMsgVal = appendparam( $signMsgVal, "key", $key );
$mysignMsg = strtoupper( md5( $signMsgVal ) );
if ( $mysignMsg == $signMsg )
{
    $status = "1";
    $signMsgVal = "";
    $signMsgVal = appendparam( $signMsgVal, "version", $version );
    $signMsgVal = appendparam( $signMsgVal, "signType", $signType );
    $signMsgVal = appendparam( $signMsgVal, "merchantMbrCode", $merchantMbrCode );
    $signMsgVal = appendparam( $signMsgVal, "requestId", $requestId );
    $signMsgVal = appendparam( $signMsgVal, "userId", $userId );
    $signMsgVal = appendparam( $signMsgVal, "status", $status );
    $reParam = $signMsgVal;
    $signMsgVal = appendparam( $signMsgVal, "key", key );
    $signMsg = strtoupper( md5( $signMsgVal ) );
    $reParam .= "&signMsg=".$signMsg;
    echo $reParam;
}
else
{
    echo "验证错误";
}
?>
