<?php
function widget_member($setting,&$system){
    $aMember = $system->request['member'];
    $appmgr = $system->loadModel('system/appmgr');
    $login_plugin = $appmgr->getloginplug();
    foreach($login_plugin as $key =>$value){
        $object = $appmgr->instance_loginplug($value);
        if(method_exists($object,'getWidgetsHtml')){
            $aMember['login_content'][] = $object->getWidgetsHtml();
        }
    }
    if($appmgr->openid_loglist()){
        $aMember['open_id_open'] = true; 
    }
    $aMember['valideCode'] = $system->getConf('site.login_valide');
    return $aMember;
}
?>
