<?php
class messenger_email{

    var $name = '电子邮件'; //名称
    var $iconclass="sysiconBtn email"; //操作区图标
    var $name_show = '发邮件'; //列表页操作区名称
    var $version='$ver$'; //版本
    var $updateUrl='';  //新版本检查地址
    var $isHtml = true; //是否html消息
    var $hasTitle = true; //是否有标题
    var $maxtime = 300; //发送超时时间 ,单位:秒
    var $maxbodylength =300; //最多字符
    var $allowMultiTarget=false; //是否允许多目标
    var $targetSplit = ',';
    var $dataname='email';
    var $debug = false;

    function ready($config){
        $system = &$GLOBALS['system'];
        $this->email = &$system->loadModel('system/email');
        if($config['sendway']=='smtp'){
            $this->email->smtp = &$system->loadModel('utility/smtp');;
            if(!$this->email->SmtpConnect($config)) return false;
        }
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
        if($config['sendway']=='smtp'){
            $this->email->SmtpClose();
        }
    }
    


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
    function send($to, $subject, $body, $config){ 
        $system = &$GLOBALS['system'];
        if ($config['sendway']=="mail"){
            $this->email = &$system->loadModel('system/email');
        } 
        $this->Sender = $config['usermail'];
        $this->Subject = $this->email->inlineCode($subject);
        $this->email->Sender = $this->Sender;
        $this->email->Subject = $this->Subject;
        
        $header = array(
            'Return-path'=>'<'.$config['usermail'].'>',
            'Date'=>date('r'),
            'From'=>$this->email->inlineCode($system->getConf('system.shopname')).'<'.$config['usermail'].'>',
            'MIME-Version'=>'1.0',
            'Subject'=>$this->Subject,
            'To'=>$to,
            'Content-Type'=>'text/html; charset=UTF-8; format=flowed',
            'Content-Transfer-Encoding'=>'base64'
        );
      
        $body = chunk_split(base64_encode($body));
        $config['sendway']=($config['sendway'])?$config['sendway']:'smtp';
        if($config['sendway'] == "mail"){
            unset($header['To']);
            unset($header['Subject']);
        }
        $header = $this->email->buildHeader($header);
        switch($config['sendway'])
        {
            case "sendmail":
                $result = $this->email->SendmailSend($to,$header, $body);
                break;
            case "mail":
                $result = $this->email->MailSend($to,$header, $body);
                break;
            case "smtp":
                $result = $this->email->SmtpSend($to,$header, $body,$config);
                break;
            default:
                trigger_error('mailer_not_supported',E_ERROR);
                $result = false;
                break;
        }
        return $result;
    }

    function getOptions(){
        return array(
            'sendway'=>array('label'=>'发送方式','type'=>'radio','options'=>array('mail'=>"使用本服务器发送",'smtp'=>"使用外部SMTP发送"),'value'=>"mail"),
            'usermail'=>array('label'=>'发信人邮箱','type'=>'input','value'=>'yourname@domain.com'),
            'smtpserver'=>array('label'=>'smtp服务器地址','type'=>'input','value'=>'mail.domain.com'),
            'smtpport'=>array('label'=>'smtp服务器端口','type'=>'input','value'=>'25'),
            'smtpuname'=>array('label'=>'smtp用户名','type'=>'input','value'=>''),
            'smtppasswd'=>array('label'=>'smtp密码','type'=>'password','value'=>'')
        );
    }
}
?>
