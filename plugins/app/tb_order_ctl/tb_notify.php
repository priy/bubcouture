<?php
require_once(CORE_DIR.'/kernel.php');
require_once(CORE_DIR.'/func_ext.php');
class taobao_action extends kernel{

    function taobao_action(){
        //parent::kernel();
        echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8">';
        $method=$_GET['action'];
        $result_msg = $this->taobao_error_list($_GET['result_code']);
        if($_GET['result_code']==27){
            $this->redirect_tb($this->getCallBack($method));
            exit;
        }

        if(!$result_msg||!isset($_GET['result_code'])){
            if(method_exists($this,$method)){
                $this->db = $this->database();
                if($_GET['op']=='center'){
                    if(base64_decode($_GET['opsign'])==md5($_GET['seller_nick'].HOST_ID.$_GET['buyer_nick'])){
                        echo $this->$method();
                        exit;
                    }else{
                        echo 'sign error';
                        exit;
                    }
                }
                $func=$this->$method();
                if($func){
                    $this->close_dialog($this->getCallBack($method));
                }else{
                    echo '执行失败';
                }
            }
        }else{
            echo "执行失败:".$result_msg;
        }

    }
    function refund_create(){
        if(in_array($_GET['refund_status'],array(6,2,5,3,4))){
            $refund_status = $this->get_refund_status($_GET['refund_status']);
        }else{
            $refund_status = $this->get_refund_status($_GET['is_received_good'].$_GET['need_return']);
        }
        if(!$refund_status){
            return false;
        }
        $set_order_status = " ";
        if($refund_status==7){
            $set_order_status = "status='finish',";
        }else if($refund_status!=0){
            $set_order_status = "status='refund',";
        }else{
            $set_order_status = "status='wait',";
        }
        if($this->db->exec("UPDATE sdb_order_items SET ".$set_order_status."refund_status=".$refund_status.",refund_id=".$_GET['refund_id']." WHERE      taobao_order=".$_GET['biz_order_id'])){
            $tmpdata = $this->db->selectrow("SELECT order_id FROM sdb_order_items WHERE taobao_order=".$_GET['biz_order_id']);
            $data = $this->db->select("SELECT order_id,refund_status FROM sdb_order_items WHERE order_id=".$tmpdata['order_id']);
            foreach($data as $key=>$value){
                if($value['refund_status']!=7){
                    $nodelete = true;
                    break;
                }
            }
            if(!$nodelete){
                $this->db->exec('UPDATE sdb_orders SET status="dead" where order_id = '.$tmpdata["order_id"]);
            }
            return true;
        }else{
            return false;
        }
    }
    function set_order_price(){
        /*
        卖家修改价格/运费
        index.php?ctl=order/order&act=set_order_price
        
        */
       
        if($_GET['result_code']==11002){
            $this->close_dialog();
            exit;
        }
        $cost_freight = $_GET['transport_fee']/100?$_GET['transport_fee']/100:0;
        $total_amount = $_GET['totle_adjust_fee']/100?$_GET['totle_adjust_fee']/100:0;
        $a_result=$this->db->selectrow("select total_amount,cost_freight,discount,cost_item from sdb_orders WHERE order_id=".$_GET['biz_order_id']);
        $a_result['cost_freight']=$a_result['cost_freight']?$a_result['cost_freight']:0;
        $a_result['discount']=$a_result['discount']?$a_result['discount']:0;
        $a_result['total_amount']=$a_result['cost_item']+$cost_freight+$total_amount;
        if($_GET['close_reason']&&$_GET['is_all_closed']==='false'){
            $close_t_ids = explode(";",$_GET['need_close_ids']);
            foreach($close_t_ids as $key =>$value){
                $del_id = $_GET['biz_order_id']+$value;
                $this->db->exec("UPDATE sdb_order_items SET disabled = 'true' WHERE taobao_order =".$del_id);
            }
            if($this->db->exec("UPDATE sdb_orders SET cost_freight = ".$cost_freight.",discount=".$total_amount.",total_amount=".$a_result['total_amount'].",final_amount=".$a_result['total_amount']."  WHERE order_id=".$_GET['biz_order_id'])){
                return true;    
            }else{
                return false;    
            };
        }
        if($_GET['is_all_closed']==='true'){
            if($this->db->exec('UPDATE sdb_orders SET status="dead" WHERE order_id = '.$_GET["biz_order_id"])){
                return true;
            }else{
                return false;
            }  
        }
        if($_GET['result_code']==11000&&!$_GET['close_reason']){
            if($this->db->exec("UPDATE sdb_orders SET cost_freight = ".$cost_freight.",discount=".$total_amount.",total_amount=".$a_result['total_amount'].",final_amount=".$a_result['total_amount']." WHERE order_id=".$_GET['biz_order_id'])){
                return true; 
            }else{
                return false;
            }
        }
    }
    function delay_delivery_time(){
        if($_GET['result_code']==13002){
            $this->close_dialog();
        }
        if($_GET['delay_days']){
            $dayas=  $_GET['delay_days']*3600*24;
            if($this->db->query('UPDATE sdb_taobao_orders SET delivery_time=delivery_time+'.$dayas.' where order_id='.$_GET['biz_order_id'])){
                return true;    
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    function close_order(){
        /*
        卖家关闭订单
        index.php?ctl=order/order&act=close_order
        */
        if($_GET['result_code']=='12002'){
            $this->close_dialog();
        }
        if($_GET['result_code']=='12000'){
            if($this->db->exec('UPDATE sdb_orders SET status="dead" where order_id = '.$_GET["biz_order_id"])){
                return true;
            }else{
                return false;
            }
        }
    }





    function taobao_error_list($code){
        $error_code=array(
            '1'=>'系统错误',
            '14002'=>'查询退款信息失败',
            '14003'=>'查询物流信息失败',
            '14004'=>'卖家同意退款协议失败',
            '14005'=>'卖家拒绝退款协议失败',
            '14006'=>'卖家确认退款失败',
            '14007'=>'卖家拒绝退款失败',
            '14008'=>'卖家必须上传发货凭证',
            '14009'=>'买家又修改了退款协议',
            '14010'=>'退款已经关闭',
            '14011'=>'创建留言失败',
            '14014'=>'零元退款金额校验失败',
            '14015'=>'对不起，您已经发起了退款',
            '14016'=>'收货地址不能为空',
            '14017'=>'由于红包金额不支持部分退款，您不能进行本次操作',
            '14018'=>'时间戳丢失',
            '14019'=>'对不起，操作失败，本笔退款会导致红包拆分',
            '14020'=>'参数错误',
            '14021'=>'无法要求小二介入',
            '14101'=>'您不是交易买家',
            '14102'=>'您不是交易卖家',
            '14103'=>'目前您不能退款发货',
            '14104'=>'目前您不能发起退款',
            '14105'=>'目前您不能修改退款协议',
            '14106'=>'你不是交易双方',
            '14108'=>'客服初审完成，不能修改退款协议',
            '14109'=>'目前您不能同意或拒绝退款协议',
            '14110'=>'目前您不能同意或拒绝打款给买家',
            '14111'=>'退款已经结束，您不能再提交留言凭证',
            '14112'=>'你要求的退款金额不合法',
            '14115'=>'物流公司为空',
            '14116'=>'非法物流方式',
            '14117'=>'没有退款记录',
            '16001'=>'获取订单失败',
            '16002'=>'只有商品的买家才能确认收货',
            '16003'=>'已经确认收货，无须再次确认',
            '16004'=>'订单存在退款(主订单退款或一部分子订单有退款)',
            '16005'=>'未退款的子订单可以确认收货，前提是其父订单必须为正在退款的状态',
            '12001'=>'系统错误',
            '12003'=>'不能关闭交易',
            '12004'=>'没有权限',
            '12005'=>'用户没有选择关闭交易理由',
            '13001'=>'系统错误',
            '11001'=>'系统错误',
            '11003'=>'没有权限',
            '11004'=>'用户没有选择关闭交易理由',
            '11005'=>'关闭所有交易失败',
            '11008'=>'总价不含邮费需要大于0'
        );
        if($error_code[$code]){
            return $error_code[$code];
        }else{
            return false;
        }
    }

    function set_pay_status(){
        //to_do正式的付款时间需要改为strtotime($_GET['pay_time']);
        if(!isset($_GET['pay_time'])){
            $_GET['pay_time'] = date('Y-m-d H:i:s');
        }
        if(isset($_GET['biz_order_id'])){
            if($this->db->exec("UPDATE sdb_orders SET pay_status = 1,pay_time=".strtotime($_GET['pay_time'])." WHERE taobao_order = ".$_GET['biz_order_id'])){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    function goods_confirm(){
        $this->db->exec("UPDATE sdb_orders SET status = 'finish',consign_time = ".time()." WHERE taobao_order = '".$_GET['biz_order_id']."'");
        $tmporder  = $this->db->selectrow("SELECT order_id,member_id FROM sdb_orders WHERE taobao_order=".$_GET['biz_order_id']);
        $oMemberPoint=$this->loadModel('trading/memberPoint');
        $oMemberPoint->payAllGetPoint($tmporder['member_id'],$tmporder['order_id']);
        if($tmporder){
            $this->db->exec("UPDATE sdb_order_items SET refund_status = 0 WHERE  refund_status !=7 AND order_id = ".$tmporder['order_id']);
        }
        $this->db->exec("UPDATE sdb_order_items SET status = 'finish',refund_status=0 WHERE taobao_order = ".$_GET['biz_order_id']);
        $tmpdata = $this->db->selectrow("SELECT order_id FROM sdb_order_items WHERE taobao_order=".$_GET['biz_order_id']);
        if($tmpdata){
            if(!$this->db->select("SELECT order_id FROM sdb_order_items WHERE (status='wait' or status ='refund') AND order_id=".$tmpdata['order_id'])){
                $this->db->exec("UPDATE sdb_orders SET status = 'finish',consign_time = ".time()." WHERE order_id=".$tmpdata['order_id']);
            }
        }
        return true;
    }


    function close_dialog($op='admin'){
        if($op=='admin'){
            echo "<script> for(var f in window.finderGroup){
                if(!!window.finderGroup[f].isVisibile()){
                    window.finderGroup[f].refresh();
                }else{
                    delete (window.finderGroup[f]); 
                } 
            }         
            parent.$('dialogContent').getParent('.dialog').retrieve('instance').close();</script>";
            echo "";
        }else{
            if(isset($_GET['op'])){
                echo '<script>alert("执行成功");window.location.href="'.$this->base_url().'shopadmin/index.php"</script>';
            }else{
                echo '<script>alert("执行成功");window.location.href="'.$this->base_url().'?member.html"</script>';
            }
            
        }
    }




    function getCallBack($key){
        $list = array('goods_confirm'=>'shop',
              'close_order'=>'admin',
              'set_order_price'=>'admin',
              'delay_delivery_time'=>'admin',
              'refund_create'=>'shop',
        );
        return $list[$key];
    }



    function redirect_tb($redirect){
        if($redirect=='admin'){
            echo '登陆超时,请关闭本窗口重新登录';
        }else{
            echo '登陆超时,请返回网店端重新登录';
        }
    }


             

    function get_refund_status($num){
        $list = array('10'=>2,
                      '20'=>6,
                      '21'=>1,
                      6=>3,
                      2=>8,
                      5=>7,
                      3=>5,
                      4=>0);
        if($list[$num]){
            return $list[$num];
        }else{
            return false;
        }
    }
}


?>