<?php
define('MANUAL_SEND','MANUAL_SEND');
class ctl_messenger extends adminPage {

    var $workground = 'member';

    function index(){
        $this->path[] = array('text'=>__('邮件短信配置'));
        $messenger = &$this->system->loadModel('system/messenger');
        $action = $messenger->actions();
        foreach($action as $act=>$info){
            $list = $messenger->getSenders($act);
            foreach($list as $msg){
                $this->pagedata['call'][$act][$msg] = true;
            }
        }

        $this->pagedata['actions'] = $action;
        $this->_show('messenger/index.html');
    }

    function edTmpl($action,$msg){

        $messenger = &$this->system->loadModel('system/messenger');
        $info = $messenger->getParams($msg);

        if($this->pagedata['hasTitle'] = $info['hasTitle']){
            $this->pagedata['title'] = $messenger->loadTitle($action,$msg);
        }

        $this->pagedata['body'] = $messenger->loadTmpl($action,$msg);
        $this->pagedata['type'] = $info['isHtml']?'html':'textarea';
        $this->pagedata['messenger'] = $msg;
        $this->pagedata['action'] = $action;

        $actions = $messenger->actions();
        $this->pagedata['varmap'] = $actions[$action]['varmap'];
        $this->pagedata['action_desc'] = $actions[$action]['label'];
        $this->pagedata['msg_desc'] = $info['name'];

        $this->page('messenger/edtmpl.html');
    }
    function toRemove($sd_id,$sender){

        $this->begin('index.php?ctl=member/messenger&act=outbox&p[0]='.$sender);
        $mdl = &$this->system->loadModel('resources/message');
        $this->end($mdl->removeSendBox($sd_id),__('删除成功'));
    }
    function saveTmpl(){
        $messenger = &$this->system->loadModel('system/messenger');
        $ret = $messenger->saveContent($_POST['actdo'],$_POST['messenger'],array(
            'content'=>$_POST['content'],
            'title'=>$_POST['title']
        ));
        if($ret){
            $this->splash('success','index.php?ctl=member/messenger&act=index');
        }else{
            $this->splash('failed','index.php?ctl=member/messenger&act=index');
        }
    }

    function save(){
        $messenger = &$this->system->loadModel('system/messenger');
        if ($messenger->saveActions($_POST['actdo'])) {
            $this->splash('success', 'index.php?ctl=member/messenger&act=index');
        }else{
            $this->splash('failed','index.php?ctl=member/messenger&act=index');
        }
    }

    function outbox($sender,$current=1){
        $this->path[] = array('text'=>__('发件箱'));
        $messenger = &$this->system->loadModel('system/messenger');
		$aData = $messenger->outbox($sender);
        $this->pagedata['oubox'] = $aData;
        $page_data = $this->pagination($current,$aData['page']);
        $this->pagedata['sender']=$sender;
        $this->_show('messenger/outbox.html');
    }

    function _show($tmpl){
        $messenger = &$this->system->loadModel('system/messenger');
        $this->pagedata['messenger'] = $messenger->getList();
        $this->pagedata['__show_page__'] = $tmpl;
        $this->page('messenger/page.html');
    }

    function send($sender){
        $messenger = &$this->system->loadModel('system/messenger');
        $member  = &$this->system->loadModel('member/member');
        $senderInfo = $messenger->getParams($sender);

        $systmpl = &$this->system->loadModel('content/systmpl');
        $tmpl_name = md5(time());
        $column = 'member_id,uname,'.($senderInfo['dataname']?$senderInfo['dataname']:'custom');

        if($systmpl->set($tmpl_name,$_POST['content'])){
            if($_POST['targets']){

                $count = count($_POST['targets']);

                foreach($member->getList($column,array('member_id'=>array_keys($_POST['targets'])),0,-1) as $info){
                    $info['title'] = $_POST['title'];
                    $messenger->addQueue($sender,$_POST['targets'][$info['member_id']],$info['title'],$info,$tmpl_name,5,MANUAL_SEND,'');
                }

            }elseif($_POST['filter']){


                parse_str($_POST['filter'],$filter);
                $step = 10; //节省内存，10个一组
                $offset = 0;
                do{
                    $count = $member->count($filter);
                    foreach($member->getList($column,$filter,$offset,$step) as $info){
                        $target = null;
                        if($senderInfo['dataname']){
                            $target = $info[$senderInfo['dataname']];
                        }elseif(($custom = $info['custom']) && ($custom = unserialize($custom))){
                            $target = $custom['contact'][$sender];
                        }
                        $info['title'] = $_POST['title'];
                        if($target){
                            $messenger->addQueue($sender,$target,$info['title'],$info,$tmpl_name,5,MANUAL_SEND);
                        }else{
                            continue;
                        }
                    }
                }while($count>($offset+=$step));
            }

            if($count>0)

                $messenger->addSendBox( array(
                    'content'=>substr(strip_tags(substr($_POST['content'],0,200)),0,180),
                    'subject'=>substr(strip_tags($_POST['title']),0,100),
                    'sender'=>$sender,
                    'sendcount'=>intval($count),
                    'tmpl_name'=>$tmpl_name,
                    'target'=>array('targets'=>$_POST['targets'],'filter'=>$_POST['filter'])
                ));
            $this->splash('success','index.php?ctl=member/messenger&act=outbox&p[0]='.$sender);
        }else{
            $this->splash('failed','index.php?ctl=member/member&act=index');
        }
    }

    function write($sender){
        $this->workground = 'member';
        $messenger = &$this->system->loadModel('system/messenger');
        $this->pagedata['messenger'] = $sender;
        $this->pagedata['sender'] = $messenger->getParams($sender);
        $this->pagedata['dataname'] = $this->pagedata['sender']['dataname'];
		if($_POST['member_id'][0]=='_ALL_'){
             $_POST = '';
		}
        $member = &$this->system->loadModel('member/member');
        if($this->pagedata['sender']['dataname']){
               $memberList = $member->getList('member_id,uname,'.$this->pagedata['sender']['dataname'].' as target ',$_POST,0,-1);
                foreach($memberList as $k=>$v){
                    if(!$v['target']){
                        $badList[] = $v;
                        unset($memberList[$k]);
                    }
                }
            }else{
                $memberList = $member->getList('member_id,uname,custom',$_POST,0,-1);
                foreach($memberList as $k=>$v){
                    if(($custom = unserialize($v['custom'])) && $custom['contact'][$sender]){
                        $memberList[$k]['target'] = $custom['contact'][$sender];
                    }else{
                        $badList[] = $v;
                        unset($memberList[$k]);
                    }
                }
            }
        $this->pagedata['members'] = $memberList;
        $this->pagedata['badList'] = $badList;
        $this->pagedata['badListCount'] = count($badList);

        $this->pagedata['type'] = $this->pagedata['sender']['isHtml']?'html':'textarea';
        $this->page('messenger/write.html');
    }

    function config($name){
        $this->path[] = array('text'=>__('配置'));
        $messenger = &$this->system->loadModel('system/messenger');
        $this->pagedata['options'] = $messenger->getOptions($name);
        $this->pagedata['messengername'] = $name;
        $this->_show('messenger/config.html');
    }

    function saveCfg(){
        $this->begin('index.php?ctl=member/messenger&act=config&p[0]='.$_POST['messenger']);
        $messenger = &$this->system->loadModel('system/messenger');
        $this->end($messenger->saveCfg($_POST['messenger'],$_POST['config']),__('配置保存成功'));
    }

    function queue($sender,$current=1){
        $this->path[] = array('text'=>__('待发队列'));
        $objMessage = &$this->system->loadModel('system/messenger');
        $aData = $objMessage->getQueue($sender,$current);
        $page_data = $this->pagination($current,$aData['page']);
        $this->pagedata['queue'] = $aData['data'];
        $this->pagedata['sender'] = $objMessage->getParams($sender);
        $this->_show('messenger/queue.html');
    }
	function pagination($current,$totalPage,$param = 'queue'){ 
        $this->pagedata['pageData'] = array(
            'current'=>$current,
            'total'=>$totalPage,
            'link'=>'index.php?ctl=member/messenger&act='.$param.'&p[0]=email&p[1]=orz1',
            'token'=>'orz1'
            );
    }
    function testEmail(){
        $this->pagedata['options'] = $_GET['config'];
        if ($_GET['config']['sendway']=="mail")
            $this->pagedata['acceptor']=$_GET['config']['usermail'];
        $this->display('messenger/testemail.html');
    }
    function doTestemail(){
        $usermail = $_POST['usermail'];     //发件账户
        $smtpport = $_POST['smtpport'];     //端口号
        $smtpserver = $_POST['smtpserver']; //邮件服务器
        $smtpuname = $_POST['smtpuname'];   //账户名称
        $smtppasswd  = $_POST['smtppasswd'];//账户密码
        $acceptor = $_POST['acceptor'];     //收件人邮箱
        $subject = __("来自[").$this->system->getConf('system.shopname').__("]网店的测试邮件");
        $body = __("这是一封测试邮箱配置的邮件，您的网店能正常发送邮件。");
        switch ($_POST['sendway']){
            case 'smtp':
                $email = &$this->system->loadModel('system/email');
                $loginfo = __("无法发送测试邮件，下面是出错信息：");
                if ($email->ready($_POST)){
                    $res = $email->send($acceptor,$subject,$body,$_POST);
                    if ($res)
                        $loginfo = __("已成功发送一封测试邮件，请查看接收邮箱。");
                    if ($email->errorinfo){
                    	$err=$email->errorinfo;
                    	$loginfo .= "<br>".$err['error'];
                    }
                }
                else{
                    $loginfo .= "<br>".var_export($email->smtp->error,true);
                }
                echo $loginfo;
                break;
            case 'mail':
                ini_set('SMTP',$smtpserver);
                ini_set('smtp_port',$smtpport);
                ini_set('sendmail_from',$usermail);
                $email=&$this->system->loadModel('system/email');
                $subject=$email->inlineCode($subject);
                $header = array(
                    'Return-path'=>'<'.$usermail.'>',
                    'Date'=>date('r'),
                    'From'=>$email->inlineCode($this->system->getConf('system.shopname')).'<'.$usermail.'>',
                    'MIME-Version'=>'1.0',
                    'Subject'=>$subject,
                    'To'=>$acceptor,
                    'Content-Type'=>'text/html; charset=UTF-8; format=flowed',
                    'Content-Transfer-Encoding'=>'base64'
                );
                $body=chunk_split(base64_encode($body));
                $header=$email->buildHeader($header);
                if(mail($acceptor, $subject, $body, $header)){
                    echo __("发送成功！");
                }
                else{
                    echo __("发送失败，请检查邮箱配置！");
                }
                break;
        }
    }
}
?>
