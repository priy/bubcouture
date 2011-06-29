<?php
function payCallBack($return){
    require(dirname(__FILE__).'/../loader.php');
    $oPay = &$system->loadModel('trading/payment');
    $file = basename($_SERVER["PHP_SELF"]);
    $fileArr = explode('.',$file);
    $gateWayId = $fileArr[1];
//    $o = $oPay->loadMethod($gateWayId);
    $serverCall = preg_match("/^pay\.([^\.]+)\.server\.php$/i",$file,$matches)?$matches[1]:false;
    if($serverCall){//需要在server.php处理
        require('pay.'.$gateWayId.'.server.php');
        $func_name="pay_".$serverCall."_callback";//处理函数
        $className="pay_".$serverCall;
        $o=new $className($system);
        //$status = $func_name($return,$paymentId,$tradeno);
        $status = $o->$func_name($return,$paymentId,$money,$message,$tradeno);
        $info =  array('money'=>$money,'memo'=>$message,'trade_no'=>$tradeno);
        $result = $oPay->setPayStatus($paymentId,$status,$info);    //可以传入支付单返回信息，如出错原因
    }else{
        require('pay.'.$gateWayId.'.php');
        $money = null;
        $status = null;
        $className = 'pay_'.$gateWayId;
        $o = new $className($system);
        $status = $o->callback($return,$paymentId,$money,$message,$tradeno);
        $result = $oPay->progress($paymentId,$status,array('money'=>$money,'memo'=>$message,'trade_no'=>$tradeno));
    }
}
payCallBack(array_merge($_GET,$_POST));
exit();
?>
