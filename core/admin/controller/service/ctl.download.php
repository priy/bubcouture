<?php
class ctl_download extends adminPage{

    var $left_domain='shopex.cn';
    var $workground='setting';
    var $max_runtime = 5;
    var $safebytes = 10;
    var $set;

    function start(){
        $this->clear_unused_fold();
        $ident = date("Ymd").substr(md5(time().rand(0,9999)),0,5);
        $this->workdir = HOME_DIR.'/tmp/'.$ident;

//        $_POST['succ_url'] = 'http://google.com';

        //两种模式：
//        $_POST['list'] = array(
//            'http://mirrors.163.com/archlinux/archlinux/iso/latest/archlinux-2009.02-core-i686.img',
//            'http://flaboy.dev.shopex.cn/dbaron-1.wmv',
//            'http://mirror.nus.edu.sg/iso/F-7-i386-rescuecd.iso',
//            'http://flaboy.dev.shopex.cn/fcgi-2.4.0.tar.gz',
//            'http://flaboy.dev.shopex.cn/libtool-1.5.26.tar.gz',
//            'http://flaboy.dev.shopex.cn/flist.php',
//            'http://flaboy.dev.shopex.cn/m4-1.4.11.tar.bz2'
//            );

        //key_as_name形式，可以设置下载的路径
//        $_POST['key_as_name'] = true;
//        $_POST['list'] = array(
//            'path/to/img/i686.img'=>'http://mirrors.163.com/archlinux/archlinux/iso/latest/archlinux-2009.02-core-i686.img',
//            'path/to/dbaron-1.wmv'=>'http://flaboy.dev.shopex.cn/dbaron-1.wmv',
//            'fcgi.tgz' => 'http://flaboy.dev.shopex.cn/fcgi-2.4.0.tar.gz',
//            'tools/libtool.tgz' => 'http://flaboy.dev.shopex.cn/libtool-1.5.26.tar.gz',
//            'flist.php' => 'http://flaboy.dev.shopex.cn/flist.php',
//            'm4.tar.bz2' => 'http://flaboy.dev.shopex.cn/m4-1.4.11.tar.bz2'
//            );

        $this->taskinfo = $_POST;
        $this->ident = $ident;
        if(!is_dir($this->workdir)){
            mkdir_p($this->workdir);
        }
        file_put_contents($this->workdir.'/task.php',serialize($this->taskinfo));
        $this->pagedata['request_url'] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?ctl=service/download&act=run&p[0]={$this->ident}&p[1]=0";

        //$this->pagedata('service/download_progress.html');
        //$this->_run(key($this->taskinfo['download_list'])); //获取第一个对象
        if($this->set == 'true')
            $this->topage('service/download_progress.html');
        else
            $this->singlepage('service/download_progress.html');
    }
    function clear_unused_fold(){
        $path=HOME_DIR.'/tmp';
        if(($handle = opendir($path))){
            while (false !==($file = readdir($handle))){
                $file_name=substr($file,0,8);
                if(is_int($file_name) && strlen($file_name)==8){
                    if((strtotime($file_name)+86400)<time()){
                        remove_floder($path.'/'.$file);
                    }
                }
            }
        }
    }
    function run($ident,$file_id){
        $this->ident = $ident;
        $this->workdir = HOME_DIR.'/tmp/'.$ident;
        $this->taskinfo = unserialize(file_get_contents($this->workdir.'/task.php'));
        $this->_run($file_id);
    }

    function _run($file_id){
        $this->system->__session_close(false);
        $this->cur_file_id = $file_id;
        $result=explode("|",$this->taskinfo['download_list'][$file_id]);
        $file_url =  $result[0];

        if($this->taskinfo['key_as_name']){
            $file = $this->workdir.'/'.$file_id;
        }else{
            $file = $this->workdir.'/'.basename($file_url);
        }

        if(!is_dir($dir = dirname($file))){
            mkdir_p($dir);
        }
        touch($file);
        $this->file_rs = fopen($file,'rb+') or exit(__('Error: 无法创建文件:').$file);
        fseek($this->file_rs,0,SEEK_END);

        $cur_size = ftell($this->file_rs);
        $header = $cur_size?array('Range'=>'bytes='.$cur_size.'-'):null;

        set_time_limit($this->max_runtime + 3);
        $this->starttime = time();
        register_shutdown_function(array(&$this,'_next_request'));
        ob_start();
        $this->_next_request();
        $netcore = &$this->system->loadModel('utility/http_client');
        $netcore->get($file_url,$header, array(&$this,'_runner_handle'));
        ob_end_clean();

        while(key($this->taskinfo['download_list'])!=$file_id){
            next($this->taskinfo['download_list']);
        }
        if(next($this->taskinfo['download_list'])){
            $this->cur_file_id = key($this->taskinfo['download_list']);
        }else{
            $this->cur_file_id = -1;
        }

    }

    function _runner_handle(&$netcore , &$content){
        if($netcore->responseCode{0}==2){
            fputs($this->file_rs,$content);
            if(time() - $this->starttime > $this->max_runtime){
                ob_end_clean();
                exit;
            }
            return true;
        }else{
            ob_end_clean();
            $this->cur_file_id = -1;
            $this->_finish = true;
            echo $content;
            exit;
        }
    }

    function _next_request(){
        if(!$this->_finish){
            $base_url=$this->system->base_url();
            $link="http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?ctl=service/download&act=run&p[0]={$this->ident}&p[1]={$this->cur_file_id}";
            if($this->cur_file_id!==-1){
                echo "<script>download('".$link."');</script>";
            }else{
                echo "<script>success('".$this->taskinfo['succ_url'].'&download='.$this->ident."');</script>";
            }
        }
    }

}
