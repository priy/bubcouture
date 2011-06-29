<?php
class ctl_default extends adminPage{

    function index(){
        $this->pagedata['statusId'] = $this->system->getConf('shopex.wss.enable');
        if(!IN_AJAX){
            foreach($_GET as $k=>$v){
                if(substr($k,0,1)=='_' && strlen($k)>1){
                    $setting[substr($k,1)] = $v;
                }
            }
            if(constant('SAAS_MODE')){
                $saas = &$this->system->loadModel('service/saas');
                if($shopinfo = $saas->native_svc('host.getinfo',array('host_id'=>HOST_ID))){
                    if($shopinfo['response_code']>0){
                        $this->pagedata['shop_service_info'] = $shopinfo['response_error'];
                    }else{
                        $this->pagedata['shop_service_info'] .= $shopinfo['service_name'];
                        $this->pagedata['shop_service_info'] .= $shopinfo['status']=='tryout'?__('(试用)'):'';
                        $this->pagedata['shop_service_info'] .= '['.date('y/m/d',$shopinfo['add_time'])
                                                        .'-'.date('y/m/d',$shopinfo['finish_time']).']';
                    }
                }
            }

            $titlename = $this->system->getConf('system.shopname');
            $this->pagedata['title'] = $titlename.' - Powered By ShopEx';
            $this->pagedata['shopname'] = (empty($titlename) ? __("点此设置商店名称") : $titlename);
            $this->pagedata['session_id'] = $this->system->sess_id;
            $this->pagedata['status_url'] = urlencode($_SERVER['PHP_SELF'].'?ctl=default&act=status&sess_id='.$this->system->sess_id);
            $this->pagedata['shopadmin_dir']=dirname($_SERVER['PHP_SELF']).'/';
            $this->pagedata['shop_base']=$this->system->base_url();
            $this->pagedata['uname'] = $this->system->op_name;


            if(!function_exists('admin_menu_filter')){
                require(CORE_INCLUDE_DIR.'/shop/admin.menu_filter.php');
            }
            $this->pagedata['menu'] = &admin_menu_filter($this->system,null);
            $this->_fetchM($this->pagedata['menu'],$menus,array());

            $menus = array_values($menus);
            foreach($menus as $i=>$m){
                foreach($menus[$i]['key'] as $k=>$v){
                    $mkey[]=array($k,$i);
                }
                unset($menus[$i]['key']);
            }

            $i = count($menus);
            foreach($mlist as $k=>$v){
                $menus[$i] = $v;
                $mkey[] = array($k,$i);
                $i++;
            }
            $this->pagedata['guide']=$this->system->getConf('system.guide');

            $this->pagedata['scripts'] = find(dirname($_SERVER['SCRIPT_FILENAME']).'/js_src','js');
            $this->pagedata['mlist'] =array('menus'=>&$menus,'key'=>&$mkey);
            $this->display('index.html');
        }else{
            $this->system->error(401);
        }
    }

    function getAppChange(){
        $center = $this->system->loadModel('service/app_center');
        $data = $center->get_tools_status();
        $appmgr = $this->system->loadModel('system/appmgr');
        $app_data = $appmgr->getList();
        $output['update_count'] = $app_data['update_count'];
        $output['status'] = $data['result'];
        echo json_encode($output);
    }

    function _fetchM($menu,&$arr,$p){
        foreach($menu as $m){
            if($m['link']){
                if(isset($arr[$m['link']])){
                    $arr[$m['link']]['key'][$m['label']]=1;
                }else{
                    $arr[$m['link']] = array('link'=>$m['link'],'path'=>((count($p)>0?implode('/',$p).'/':'').$m['label']),'key'=>array($m['label']=>1));
                }
                if($m['keywords']){
                    foreach($m['keywords'] as $k){
                        $arr[$m['link']]['key'][$k]=1;
                    }
                }
            }
            if($m['items']){
                $np = array_slice($p,0);
                $np[]=$m['label'];
                $this->_fetchM($m['items'],$arr,$np);
            }
        }
    }

    function tnode($model,$id,$depth){
        $o = &$this->system->loadModel($model);
        $this->pagedata['item'] = $options = $o->treeOptions();
        $this->pagedata['item']['items']=$o->getNodes($id);
        $this->pagedata['item']['model']=$model;
        $this->pagedata['depth'] = $depth+1;
        $this->display('treeNode.html');
    }

    function uploadSplash(){
        foreach($_POST as $k=>$v) {
            if ($v=='null') {
                unset($_POST[$k]);
            }
        }
        echo '<script>top.$("loadMask").hide();top.MODALPANEL.hide();</script>';
        call_user_func_array(array(&$this,'splash'),$_POST);
    }

    function status(){
        $status = &$this->system->loadModel('system/status');
        $storeless = intval($this->system->getConf('system.product.alert.num'));
        $this->pagedata['allstatus'] = array(
            'ORDER_NEW'=>array('label'=>__('未处理订单'),'url'=>'index.php?ctl=order/order&act=index&view=1&filter='.urlencode(serialize(array('pay_status'=>array(
                            'v'=>'0',
                            't'=>'未处理'
                        ))))),
            'GOODS_ALERT'=>array('label'=>__('库存报警'),'url'=>'index.php?ctl=goods/product&act=index&filter='.urlencode(serialize(array('storeless'=>array(
                            'v'=>$storeless,
                            't'=>'库存小于等于'.$storeless
                        ))))),
            'GNOTIFY'=>array('label'=>__('缺货通知'),'url'=>'index.php?ctl=goods/product&act=index'),
            'GDISCUSS'=>array('label'=>__('商品评论'),'url'=>'index.php?ctl=goods/discuss&act=index&filter='.urlencode(serialize(array('adm_read_status'=>array(
                            'v'=>'false',
                            't'=>'未阅读'
                        ))))),
            'GASK'=>array('label'=>__('购买咨询'),'url'=>'index.php?ctl=member/gask&act=index'),
            'GOODS_ONLINE'=>array('label'=>__('上架商品'),'url'=>'index.php?ctl=goods/product&act=index'),
            'ORDER_MESSAGE'=>array('label'=>__('新留言订单'),'url'=>'index.php?ctl=order/order&act=new_order_message_list'),
            );
        $status_data = $status->getList();
        $oBbs = $this->system->loadModel('resources/shopbbs');
        $status_data['ORDER_MESSAGE'] = $oBbs->getNewOrderMessage();

        $oProduct = $this->system->loadModel('goods/finderPdt');
        $filter_p['store_alarm'] = $this->system->getConf('system.product.alert.num');
        foreach($oProduct->getList('goods_id', $filter_p, 0, 1000) as $row){
            $filter['goods_id'][] = $row['goods_id'];
        }
        $appmgr = &$this->system->loadModel('system/appmgr');
        foreach(unserialize($this->system->getConf("system.crontab_queue")) as $k =>$v ){
            list($objCtl,$act_method) = $appmgr->get_func($v);
            if(method_exists($objCtl,$act_method)){
                $objCtl->$act_method();
            }
        }
        if(empty($filter['goods_id'])) $filter['goods_id'][] = -1;
        unset($filter_p);

        $oGoods = &$this->system->loadModel('goods/products');
        $alert_count = $oGoods->count($filter);
        $status_data['GOODS_ALERT'] = $alert_count;
        $messenger = &$this->system->loadModel('system/messenger');
        $messenger->runQueue();

        $this->pagedata['status'] = $status_data;
        echo $this->fetch('status.html');
        flush();
        set_time_limit(0);
        foreach($_POST['events'] as $event=>$detail){
            if(method_exists($this,$action = '_action_'.$event)){
                $this->$action($detail);
            }
        }
        $this->system->__session_close(1);
    }

    function _action_finder_colset($params){
        foreach($params as $ctl=>$list){
            echo 'colwith.'.$ctl."\n";
            if($set = $this->system->get_op_conf('colwith.'.$ctl)){
                $this->system->set_op_conf('colwith.'.$ctl,array_merge($set,$list));
            }else{
                $this->system->set_op_conf('colwith.'.$ctl,$list);
            }
        }
    }

    function sel_region($path,$depth){
         header('Content-type:text/html;charset=utf-8');
        $local = &$this->system->loadModel('system/local');
        if($ret = $local->get_area_select($path)){
            echo '&nbsp;-&nbsp;'.$local->get_area_select($path,array('depth'=>$depth));
        }else{
            echo '';
        }
    }

    function get_menulist($searchPanel){
       header('Content-type:text/html;charset=utf-8');
      require('adminSchema.php');
      if (is_array($menu)){
        foreach($menu as $key => $val){
            foreach($val as $skey => $sval){
                foreach($sval as $sskey=>$ssval){
                    if ($ssval['type']=="group"){
                        foreach($ssval['items'] as $ssskey =>$sssval){
                            if ($sssval['type'] == "menu"){
                                $tmpMenu[]=array(
                                    "label"=>$sssval['label'],
                                    "link"=>$sssval['link']
                                );
                            }
                        }
                    }
                }
            }
        }
      }

      if($searchPanel){
         $this->display('menuSearch.html');
         exit;
      }
    }

    function check_api_maintenance(){
        $notice = get_http(PLATFORM_HOST,PLATFORM_PORT,SERVER_PLATFORM_NOTICE);
        if(strlen($notice) == 0){   //没有维护
            $this->system->setConf('site.api.maintenance.is_maintenance',false,true);
            $this->system->setConf('site.api.maintenance.notify_msg','',true);
        }else{
            $this->system->setConf('site.api.maintenance.is_maintenance',true,true);
            $this->system->setConf('site.api.maintenance.notify_msg',$notice,true);
        }

        echo $notice;
    }


    function shownewtools(){
        $this->display('appTaobaoIntro.html');
    }

    function getcertidandurl(){
        $cet_ping = ping_url("http://guide.ecos.shopex.cn/index.php");
        if(!strstr($cet_ping,'HTTP/1.1 200 OK')){
            echo $this->system->base_url().'error.html';
        }else{
            $certi_model = $this->system->loadModel("service/certificate");
            $cert_id = $this->system->getConf("certificate.id");
            $base_url = urldecode($this->system->base_url());
            $sess_id = $certi_model->get_sess();
            $confirmkey = md5($sess_id.'ShopEx@License'.$cert_id);
            $center_url = "http://guide.ecos.shopex.cn/index.php?certi_id=".$cert_id.'&url='.urlencode($base_url).'&confirmkey='.$confirmkey.'&sess_id='.$sess_id;
            echo $center_url;
        }
    }

    function frame_include(){
        echo "<script>
        (function getHash(){
          var url=decodeURIComponent(location.hash);
        
          var param=url.substr(1).split('=');
          switch (param[1]){
            case 'close':
                top.$('user_guide_iframe').getParent('.dialog').retrieve('instance').close();
                break;
            case 'checked':
                new top.XHR({method:'post',data:'check='+param[0]}).send('index.php?ctl=system/setting&act=guide_status');
            break;    
            default:
              if(url=='#../') url = '../'
              top.location.href='".$this->system->base_url()."shopadmin/'+url;     
              top.$('user_guide_iframe').getParent('.dialog').retrieve('instance').close();
            break;
    }    
        })();
        
        </script>";
    }
}

?>
