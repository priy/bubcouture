<?php

class admin_tb_notify{
    function notify(){
        if($_GET['action']){
            include_once("tb_notify.php");
            new taobao_action();
        }else{
            header('HTTP/1.1 404 Not Found',true,'404');
        }
    }
}

?>