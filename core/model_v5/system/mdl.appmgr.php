<?php
class mdl_appmgr extends modelFactory{

    function register_controller($ctl,$call){
        list($thispkg,$class,$method) = explode(':',$map[$ctl]);
        $map = $this->system->getConf('system.ctlmap');
        if($map[$ctl]){
            list($pkg,$class,$method) = explode(':',$map[$ctl]);
            if($pkg!=$thispkg){
                $this->disable($pkg);
            }
        }
        $map[strtolower($ctl)] = strtolower($call);
        $this->system->setConf('system.ctlmap',$map);
    }
    
   function login_refer($row){
      $return = $this->db->selectrow('select member_id from sdb_trust_login where member_refer ="'.$row['open_type'].'" and uname ="'.$row['open_id'].'"');
    
      return $return;
     
   }

    function info($ident){
        $app = &$this->load($ident);
        $info['prop'] = get_object_vars($app);

        unset($info['prop']['system']
            ,$info['prop']['db'],$info['prop']['charset']);

        $info['listener'] = $app->listener();
        $info['dbtables'] = $app->dbtables();
        $info['output_modifiers'] = $app->output_modifiers();
        if(method_exists($app,'setting')) $info['setting'] = $app->setting();
        return $info;
    }

    function restore_controller($ctl=null){
        $map = &$this->system->getConf('system.ctlmap');
        unset($map[strtolower($ctl)]);
        return $this->system->setConf('system.ctlmap',$map);
    }

    function getList($nocompare=false){
        $list = $this->db->select('select plugin_id,plugin_ident,
           plugin_name,plugin_type,plugin_base,plugin_version,plugin_author,plugin_package,plugin_website,plugin_hasopts,plugin_path,status,disabled,plugin_mtime,plugin_struct from sdb_plugins where plugin_type="app"');
        $base_url = $this->system->base_url();
        $list = array_values($list);
        foreach($list as $k=>$item){
            if(substr($item['plugin_ident'],0,4) == 'pay_'){
                unset($list[$k]);
                continue;
            }
            $plugin_compare[] = $item['plugin_ident'];
            if($item['plugin_base']=='0'){
                if(file_exists(PLUGIN_DIR.
                    dirname($item['plugin_path']).'/icon.png')){
                        $list[$k]['icon'] = $base_url.PLUGIN_BASE_URL.
                            dirname($item['plugin_path']).'/icon.png';
                }
            }
        }
        if(!$nocompare){
            $app_center = $this->system->loadModel("service/app_center");
            $result = $app_center->get_app_compare(implode(",",$plugin_compare));

            $count = 0;
            $app_key = array_keys($result['result_msg']);
            if($result['result']=='succ'){
                foreach($list as $key=>$value){
                    if(in_array($list[$key]['plugin_ident'],$app_key)){
                        if($list[$key]['plugin_version'] < $result['result_msg'][$list[$key]['plugin_ident']]['ver']){
                            $list[$key]['new_version'] =$result['result_msg'][$list[$key]['plugin_ident']]['ver'];
                            $list[$key]['download_url'] = $result['result_msg'][$list[$key]['plugin_ident']]['download'];
                            $count++;
                        }
                    }
                }
            }
            $list['update_count'] = $count;
        }

        return $list;
    }

   function getPaymentApp(){
        $list = $this->db->select('select plugin_id,plugin_ident,
           plugin_name,plugin_type,plugin_base,plugin_version,plugin_author,plugin_package,plugin_website,plugin_hasopts,plugin_path,status,disabled,plugin_mtime,plugin_struct from sdb_plugins where plugin_type="app"');
        foreach($list as $k=>$item){
             if(substr($item['plugin_ident'],0,4) != 'pay_')
             unset($list[$k]);
        }
        return $list;
    }

    function getNameByIdents($idents){
        if(!$idents)return array();
        foreach($this->db->select('select plugin_name from
            sdb_plugins where plugin_type="app" and plugin_ident in("'
            .implode('"',$idents).'")') as $row){
            $return[$row['plugin_ident']] = $row['plugin_name'];
        }
        return $return;
    }

    function getAppName($idents){
         return $this->db->selectrow('select plugin_name from sdb_plugins where plugin_type="app" and plugin_ident in("'
            .$idents.'")');
    }

    function getNameBydis($idents){
        foreach($this->db->select('select plugin_ident from
            sdb_plugins where plugin_type="app" and disabled="false" and plugin_ident  ="'
            .$idents.'"') as $row){
            $return = $row['plugin_ident'];
        }
        return $return;
    }

    function getPaydata($data){
        if(is_array($data)){
            for($i=0;$i<count($data);$i++){
                foreach($data as $key=>$val){

                    if($val['status'] =='false')  unset($data[$key]);
                    if($data[$key]['sort']>$data[$key+1]['sort']){
                        if(!empty($data[$key+1])){
                            $arg = $data[$key+1];
                            $data[$key+1] = $data[$key];
                            $data[$key] = $arg;
                        }
                    }
                }
            }
            foreach($data as $key=>$val){
                $type = substr($val['pay_ident'],4);
                $dis = $this->db->selectrow('select disabled  from sdb_plugins where plugin_type="app" and plugin_ident  ="'.$val['pay_ident'].'"');
                $setId = $this->db->selectrow('select id  from sdb_payment_cfg where disabled="false" and pay_type  ="'.$type.'"');
                if(isset($setId))
                    $data[$key]['set'] = 'true';
                if($dis['disabled'] == 'true'||empty($dis['disabled'])){
                    $data[$key]['disable'] = 'true';
                }else{
                    $data[$key]['disable'] = 'false';
                }
                $data[$key]['count'] = $key+1;
            }
        }
        return $data;
    }

   function getUseapp($data){
        $updatePay = array();
        $return = array();
        $appmgr = &$this->system->loadModel('system/appmgr');
        $payment = &$this->system->loadModel('trading/payment');
        $apps = $appmgr->paymentList();
        $pay = $payment->getAllMethods();
        foreach($apps as $k=>$v){
           if(isset($v['new_version'])&&isset($v['download_url'])){
             $updatePay[] = $v['plugin_ident'];
           }
        }
        $bcount = 1;
        if(is_array($data)){
            foreach($data as $key=>$val){
                    if(in_array($val['pay_ident'],$updatePay)) $val['update'] = 'true';
                    if(!$this->checkAppFile($val['pay_ident'])) $val['exist'] = 'true';
                    $val['count'] = $bcount;
                    array_push($return,$val);
                    $bcount++;
            }
        }
        foreach($pay as $pk=>$pv){
            foreach($return as $key=>$val){
                if('pay_'.$pv['pay_type'] == $val['pay_ident']||$pv['pay_type']== $val['pay_ident']){
                    $result[$pk] = $val;
                    $result[$pk]['id'] = $pv['id'];
                    $result[$pk]['custom_name'] = $pv['custom_name'];
                    $result[$pk]['dis'] = $pv['disabled'];
                }
            }
        }
        return $result;
    }

    function load($ident){
        if(!class_exists('app')){
            require('app.php');
        }
        $addons = &$this->system->loadModel('system/addons');
        $data = $this->db->selectrow('select * from sdb_plugins where plugin_type="app" and plugin_ident="'.$ident.'"');
        if($data){
            $obj = $addons->plugin_instance($data);
            if($data['plugin_base']=='0'){
                $obj->base_url = $this->system->base_url().'plugins'.dirname($data['plugin_path']).'/';
            }
            return $obj;
        }else{
            return false;
        }
    }

    function install($ident,$is_update=false){
        $app = $this->load($ident);
        if(!$app&&$_GET['download']){
            echo '安装出错,请检查网络环境,或重新安装';
            exit;
        }
        if($is_update){
            $result = $app->update();
        }else{
            $result = $app->install();
        }
        if($result){
            $this->db->exec('update sdb_plugins set `status`=\'used\' where plugin_id='.$app->plugin_id);
            return $this->enable($ident);
        }else{
            return false;
        }
    }

    function get_app_diff($dir_name){
         $schema=&$this->system->loadModel('utility/schemas');
         $dbtables = $schema->get_system_schemas($dir_name);
         foreach($dbtables as $tbname=>$struct){
            if($diff = $schema->diff($dir_name.'_'.$tbname,$struct,$dir_name)){
                $ret[$this->system->db->prefix.$dir_name.'_'.$tbname] = $diff;
            }
         }
         return $ret;
    }

    function uninstall($ident){
        $this->disable($ident);
        $app = $this->load($ident);
        $this->db->exec('update sdb_plugins set `status`=\'unused\' where plugin_id='.$app->plugin_id);
        return $app->uninstall();
    }

    function register_crontab_queue($value){
        $setting_params = unserialize($this->system->getConf("system.crontab_queue"));
        $setting_params[$value] = $value;
        $this->system->setConf('system.crontab_queue',serialize($setting_params),1);
    }

    function restore_crontab_queue($value){
        $setting_params = unserialize($this->system->getConf("system.crontab_queue"));
        unset($setting_params[$value]);
        $this->system->setConf('system.crontab_queue',serialize($setting_params),1);
    }

    function enable($ident){
        $app = $this->load($ident);
        $ident = strtolower($ident);
        foreach($app->ctl_mapper() as $k=>$v){
            $this->register_controller($k,$ident.':'.$v);
        }
        foreach($app->crontab_queue() as $ek =>$ev){
            $this->register_crontab_queue($ident.':'.$ev);
        }

        $all = $this->system->getConf('system.event_listener');
        foreach($app->listener() as $k=>$v){
            $k = strtolower($k);
            $v = strtolower($v);
            $all[$k][$ident.':'.$v] = $ident.':'.$v;
        }
        $this->system->setConf('system.event_listener',$all);

        $all = $this->system->getConf('system.output_modifiers');
        foreach($app->output_modifiers() as $k=>$v){
            $k = strtolower($k);
            $v = strtolower($v);
            $all[$k][$ident.':'.$v] = $ident.':'.$v;
        }
        $this->system->setConf('system.output_modifiers',$all);
        return $this->db->exec('update sdb_plugins set `disabled`=\'false\' where plugin_package=\''.$ident.'\'');
    }

    function disable($ident){
        $app = $this->load($ident);
        $ident = strtolower($ident);
        $map = $app->ctl_mapper();
        if($map){
            foreach($map as $k=>$v){
                $this->restore_controller($k);
            }
        }else{
            $this->restore_controller(null);
        }
        $crontab_queue = $app->crontab_queue();
        foreach($crontab_queue as $ke=>$ve){
            $this->restore_crontab_queue($ident.':'.$ve);
        }

        $all = $this->system->getConf('system.event_listener');
        $len = strlen($ident)+1;
        foreach($all as $k=>$m){
            foreach($m as $v){
                if(substr($v,0,$len)==$ident.':'){
                    unset($all[$k][$v]);
                }
            }
            if(!$all[$k]){
                unset($all[$k]);
            }
        }
        $this->system->setConf('system.event_listener',$all);

        $all = $this->system->getConf('system.output_modifiers');
        foreach($all as $k=>$m){
            foreach($m as $v){
                if(substr($v,0,$len)==$ident.':'){
                    unset($all[$k][$v]);
                }
            }
            if(!$all[$k]){
                unset($all[$k]);
            }
        }
        $this->system->setConf('system.output_modifiers',$all);
        $this->db->exec('delete from sdb_payment_cfg where pay_type ="'.substr($ident,4).'"');
        return $this->db->exec('update sdb_plugins set `disabled`=\'true\' where plugin_package=\''.$ident.'\'');
    }

    function get_func($position){
        list($package,$class,$method) = explode(':',$position);
        $class = $package.'_'.$class;
        $app = &$this->load($package);
        if(!$app){
            return false;
        }
        if(!class_exists($class)){
            require(dirname($app->plugin_path).'/'.$class.'.php');
        }
        $obj = new $class;
        $obj->app = &$app;
        return array(&$obj,$method);
    }

    function getloginplug(){
        return $this->db->select("SELECT plugin_id,plugin_path,plugin_struct,plugin_ident,plugin_type FROM sdb_plugins WHERE disabled = 'false' AND status = 'used' AND plugin_type = 'app'");

    }

    function getlgplugbyname($name){
         return $this->db->selectrow("SELECT plugin_id,plugin_path,plugin_struct,plugin_ident,plugin_type FROM sdb_plugins WHERE disabled = 'false' AND status = 'used' AND plugin_type = 'app' AND plugin_ident = '".$name."'");
    }


    function getappByident($ident){
        foreach($ident as $key=>$value){
            $txt_sql .='"'.$key.'",';
        }
        $txt_sql = substr($txt_sql,0,-1);
        $result = $this->db->select("SELECT plugin_ident FROM sdb_plugins WHERE plugin_type='app' AND status = 'used' AND plugin_ident IN(".$txt_sql.")");
        foreach($result as $k => $v){
            if(isset($ident[$v['plugin_ident']])){
                unset($ident[$v['plugin_ident']]);
            }
        }
        $str= implode(",",$ident);
        return $str;
    }



    function instance_loginplug($data){
        if(!class_exists('app')) require('app.php');
        $path = PLUGIN_DIR.substr($data['plugin_path'],0,strrpos($data['plugin_path'],'/')).'/passport.'.$data['plugin_ident'].'.php';
        if(file_exists($path)){
            require_once($path);
            $classname = 'passport_'.$data['plugin_ident'];
            $object = new $classname;
            return $object;
        }else{
            return false;
        }
    }


    function instal_ol_app($file_path,$dir_name,&$msg,$update=false){
        $app_path = PLUGIN_DIR.'/app/';
        if(!$update&&is_dir($app_path.$dir_name)){
            $msg = __('您已经安装过此应用，请返回已安装应用列表，点击应用后面的开启按钮即可使用。');
            return false;
        }
        $tar = $this->system->loadModel('utility/tar');

        if($tar->openTAR($file_path)){
            foreach($tar->files as $id => $file) {
                $fpath = $app_path.$file['name'];
                if(!is_dir(dirname($fpath))){
                    if(mkdir_p(dirname($fpath))){
                        file_put_contents($fpath,$tar->getContents($file));
                    }else{
                        $msg = __('权限不允许');
                        return false;
                    }
                }else{
                    file_put_contents($fpath,$tar->getContents($file));
                }
            }
            $addon  = $this->system->loadModel('system/addons');
            $addon->refresh();
        }
        return true;

    }

    function openid_loglist(){
        if($this->db->select("SELECT * FROM sdb_plugins WHERE plugin_type='app' AND disabled='false' AND status ='used' AND plugin_ident LIKE 'openid%'")){
            return true;
        }else{
            return false;
        }
    }

    function checkAppFile($ident){
        $dir = PLUGIN_DIR.'/app/';
        if(file_exists($dir.$ident)) return true;
        else return false;
    }


    function paymentList($nocompare=false){
        $list = $this->db->select('select plugin_id,plugin_ident,
           plugin_name,plugin_type,plugin_base,plugin_version,plugin_author,plugin_package,plugin_website,plugin_hasopts,plugin_path,status,disabled,plugin_mtime,plugin_struct from sdb_plugins where plugin_type="app"');
        $base_url = $this->system->base_url();
        foreach($list as $k=>$item){
            if(substr($item['plugin_ident'],0,4) != 'pay_')
            unset($list[$k]);
        }
        $list = array_values($list);
        foreach($list as $k=>$item){

            $plugin_compare[] = $item['plugin_ident'];
            if($item['plugin_base']=='0'){
                if(file_exists(PLUGIN_DIR.
                    dirname($item['plugin_path']).'/icon.png')){
                        $list[$k]['icon'] = $base_url.PLUGIN_BASE_URL.
                            dirname($item['plugin_path']).'/icon.png';
                }
            }
        }

        if(!$nocompare){
            $app_center = $this->system->loadModel("service/app_center");
            $result = $app_center->get_payment_app_compare(implode(",",$plugin_compare));
            $count = 0;
            if($result['result']='succ'){
                $i=0;
                foreach($result['result_msg'] as $key=>$value){
                    if($list[$i]['plugin_version'] < $value['ver']){
                        $list[$i]['new_version'] = $value['ver'];
                        $list[$i]['download_url'] = $value['download'];
                        $count++;
                    }
                       $i++;
                }
            }
            $list['update_count'] = $count;
        }

        return $list;
    }

    function getPluginInfoByident($ident, $columns="*"){
        return $this->db->selectrow('SELECT '.$columns.' FROM sdb_plugins WHERE plugin_type=\'app\' AND plugin_ident=\''.$ident.'\'');
    }

}
