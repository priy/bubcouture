<?PHP
    $in=array_merge($_GET,$_POST);
    $key="LHLEF8EA4ZY853NF";
    $version=$in['version'];
    $signType=$in['signType'];
    $merchantMbrCode=$in['merchantMbrCode'];
    $requestId=$in['requestId'];
    $userId=$in['userId'];
    $userEmail=$in['userEmail'];
    $userName=$in['userName'];
    $orgName=$in['orgName'];
    $ext1=$in['ext1'];
    $ext2=$in['ext2'];
    $applyResult=$in['applyResult'];
    $errorCode=$in['errorCode'];
    $signMsg=$in['signMsg'];
    Function appendParam($returnStr,$paramId,$paramValue){
        if($returnStr!=""){
            if($paramValue!=""){
                $returnStr.="&".$paramId."=".$paramValue;
            }
        }else{
            If($paramValue!=""){
                $returnStr=$paramId."=".$paramValue;
            }
        }
        return $returnStr;
    }
    $$signMsgVal="";
    $signMsgVal=appendParam($signMsgVal,"version",$version);
    $signMsgVal=appendParam($signMsgVal,"signType",$signType);
    $signMsgVal=appendParam($signMsgVal,"merchantMbrCode",$merchantMbrCode);
    $signMsgVal=appendParam($signMsgVal,"requestId",$requestId);
    $signMsgVal=appendParam($signMsgVal,"userId",$userId);
    $signMsgVal=appendParam($signMsgVal,"userEmail",$userEmail);
    $signMsgVal=appendParam($signMsgVal,"userName",urlencode($userName));
    $signMsgVal=appendParam($signMsgVal,"orgName",urlencode($orgName));
    $signMsgVal=appendParam($signMsgVal,"ext1",urlencode($ext1));
    $signMsgVal=appendParam($signMsgVal,"ext2",urlencode($ext2));
    $signMsgVal=appendParam($signMsgVal,"applyResult",$applyResult);
    $signMsgVal=appendParam($signMsgVal,"errorCode",$errorCode);
    $signMsgVal=appendParam($signMsgVal,"key",$key);
    $mysignMsg=strtoupper(md5($signMsgVal));
    if($mysignMsg==$signMsg){
        $status="1";
        $signMsgVal="";
        $signMsgVal=appendParam($signMsgVal,"version",$version);
        $signMsgVal=appendParam($signMsgVal,"signType",$signType);
        $signMsgVal=appendParam($signMsgVal,"merchantMbrCode",$merchantMbrCode);
        $signMsgVal=appendParam($signMsgVal,"requestId",$requestId);
        $signMsgVal=appendParam($signMsgVal,"userId",$userId);
        $signMsgVal=appendParam($signMsgVal,"status",$status);
        $reParam=$signMsgVal;
        $signMsgVal=appendParam($signMsgVal,"key",key);

        $signMsg=strtoupper(md5($signMsgVal));
        $reParam .="&signMsg=".$signMsg;
        echo $reParam; 
    }else{
        echo "验证错误";
    }
?>