<?php
class messenger_sms{

    var $name = '手机短信'; //名称
    var $iconclass="sysiconBtn sms"; //操作区图标
    var $name_show = '发短信'; //列表页操作区名称
    var $version='$ver$'; //版本
    var $updateUrl=false;  //新版本检查地址
    var $isHtml = false; //是否html消息
    var $hasTitle = false; //是否有标题
//    var $maxtitlelength =300; //最多字符
    var $maxtime = 300; //发送超时时间 ,单位:秒
    var $maxbodylength =300; //最多字符
    var $allowMultiTarget=false; //是否允许多目标
//  var $targetSplit = ','; //多目标分隔符
    var $withoutQueue = false;
    var $dataname='mobile';
    var $sms_service_ip='124.74.193.222';
    var $sms_service='http://idx.sms.shopex.cn/service.php';


    /**
     * send
     * 必有方法,发送时调用
     *
     * config参数为getOptions取得的所有项的配置结果
     *
     * @param mixed $to
     * @param mixed $message
     * @param mixed $config
     * @access public
     * @return void
     */
    function messenger_sms(){
        $this->system = &$GLOBALS['system'];
        $this->net=&$this->system->loadModel('utility/http_client');

    }
    function send($to,$message,$config,$sms_type){
        /**
        $server_url        申请的服务器地址
        $smsid            smsid
        $ex_type        消费类型
        $mobile            需要发送的手机
		$sms_type          	短信类型 1为106接口 空为电信通接口
        $active            发送的激活码,即发送信息
        $token            手机私钥
        **/
        if(!$this->checkL()){
            return 'license error';
        }
        $result=$this->apply($this->sms_service,$this->version);
        if(!$result){
            return  'you must to active the code';
        }
        $this->content = $message;
        $this->mobile = $to;
        $result=$this->send_info($result,$this->ex_type,$this->version,$sms_type);
        $msg=$this->msg($result);
        return $msg;
    }
    function checkL(){
        if(!$this->getCerti() || !$this->getToken()){
            return false;
        }else{
            return true;
        }
    }
    function apply(){
        /**
        $certi_id    license_id
        $token        手机私钥
        **/
        $submit_str['certi_id'] = $this->getCerti();
        $submit_str['ac'] = md5($this->getCerti().$this->getToken());
        $submit_str['version']=$this->version;
        $results = $this->net->post($this->sms_service,$submit_str);
        $result = explode('|',$results);
        if($result[0] == '0'){
            return $result[1];
        }
        if($result[0] == '1'){
            return false;
        }
        if($result[0] == '2'){
            return false;
        }
    }

    function send_info($url,$ex_type,$version,$sms_type=false){
		if(($sms_type==true)||($sms_type==1)){
           $sms_type=1;
		}else{
           $sms_type='';
		}
        /**
        $url        申请的服务器地址
        $ex_type        消费类型
        $mobile            需要发送的手机
		$sms_type          	短信类型 1为106接口 空为电信通接口
        **/
        $send_arr =    Array(
            0 => Array(
                    0 => $this->mobile,        //发送的手机号码
                    1 => $this->content,    //发送信息
                    2 => 'Now'                //发送的时间
                )
        );
        $send_str['certi_id'] = $this->getCerti();
        $send_str['ex_type'] = $ex_type;
        $send_str['content'] = json_encode($send_arr);
		$send_str['sms_type'] = $sms_type;
        $send_str['encoding'] = 'utf8';
        $send_str['version'] = $version;
        $send_str['ac'] = md5($send_str['certi_id'].$send_str['ex_type'].$send_str['content'].$send_str['encoding'].$this->getToken());
        $results = $this->net->post($url,$send_str);
        $result = explode('|' ,$results);
        if($result[0] == 'true'){
            //发送成功
            return 200;
        }elseif($result[0] == 'false'){
            //发送失败
            return $result[1];
        }
    }

    function getCerti(){
        if($this->system->getConf('certificate.id')){
            return $this->system->getConf('certificate.id');
        }else{
            return false;
        }
    }
    function getToken(){
        if($this->system->getConf('certificate.token')){
            return $this->system->getConf('certificate.token');
        }else{
            return false;
        }
    }
    function msg($index){
        $aMsg=array(
            '200'=>'true',
            '1'=>'Security check can not pass!',
            '2'=>'Phone number format is not correct.',
            '3'=>'Lack of content or content coding error.',
            '4'=>'Lack of balance.',
            '5'=>'Information packets over limited.',
            '6'=>'You must recharge before write message!',
            '901'=>'Write sms_log error!',
            '902'=>'Write sms_API error!'
            );
        return $aMsg[$index];
    }

    /**
     * ready
     * 可选方法，准备发送时触发
     *
     * @param mixed $config
     * @access public
     * @return void
     */
    function ready($config){
        $this->url = $this->apply($this->sms_service,$this->version);
        return $this->url;
    }

    /**
     * finish
     * 可选方法，结束发送时触发
     *
     * @param mixed $config
     * @access public
     * @return void
     */
    function finish($config){

    }

    function extraVars(){
        if(!$this->getCerti()){
            $certi_id='error';
        }else{
            $certi_id=$this->getCerti();
        }
        $sess_id = $this->system->session->sess_id;
        $auth = mktime();
        $ac = md5($certi_id.'SHOPEX_SMS'.$auth);
        $url = 'http://service.shopex.cn/sms/index.php?certificate_id='.$certi_id.'&sess_id='.$sess_id.'&auth='.$auth.'&ac='.$ac;
        return array('outgoingOptions'=>$url);
    }
}
?>
