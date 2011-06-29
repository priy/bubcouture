<?php
if(!class_exists('pageFactory')){
    require(CORE_DIR.'/include/pageFactory.php');
}

class shopex_stat_modifiers extends pageFactory{

    function shopex_stat_modifiers(){
        parent::pageFactory();
        $this->system = &$GLOBALS['system'];
    }


    function print_footer( &$content ){

        $status = &$this->system->loadModel('system/status');
        $status->set('site.rsc_rpc','1');

        if($status->get('site.rsc_rpc')&&defined('RSC_RPC') && ($certificate = $this->system->getConf('certificate.id'))){
            $p = '';

            if (isset($_COOKIE["SHOPEX_STATINFO"])){
                foreach(unserialize($_COOKIE["SHOPEX_STATINFO"]) as $k=>$v){
                     $p .= '&_'.$k.'='.urlencode($v);
                }
            }
             //后台订单
            if($status->get('site.orderinfo')){
                $orderinfo = unserialize($status->get('site.orderinfo'));
                foreach ($orderinfo as $key=>$value){
                 $p .= '&'.$key."=".urlencode($value);
                }
            }

             //后台添加用户
            if($status->get('site.addmenbyadmin')){
                $userinfo = unserialize($status->get('site.addmenbyadmin'));
                foreach ($userinfo as $key=>$value){
                 $p .= '&'.$key."=".urlencode($value);
                }
            }

            if($status->get('site.payinfo')){
              $payinfo = unserialize($status->get('site.payinfo'));
              foreach ($payinfo as $key=>$value){
                  $p .= '&'.$key."=".urlencode($value);
                }
            }

            if($status->get('site.goods_status')){
              $goods_status = unserialize($status->get('site.goods_status'));
              foreach ($goods_status as $key=>$value){
                   $p .= '&'.$key."=".urlencode($value);
               }
            }

            if (isset($_COOKIE["SHOPEX_STATINFO_GOODS"])){
                foreach(unserialize($_COOKIE["SHOPEX_STATINFO_GOODS"]) as $k=>$v){
                     $p .= '&_'.$k.'='.urlencode($v);
                     //echo 11;exit;
                }
            }

            //echo $_COOKIE["SHOPEX_STATINFO_GOODS"];
            //echo $_COOKIE["SHOPEX_STATINFO"];
            $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", "",0,"/");
            $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO_GOODS]", "",0,"/");

            if (preg_match('/<title>([\\x00-\\xFF]*)<\/title>/', $content,$titleinfo)) {
                $p .= '&_pagetitle='.urlencode($titleinfo[1]);
            }

            $RSC_RPC_STR='<script>
            withBroswerStore(function(store){
               function randomChar(l)  {
                    var  x="0123456789qwertyuioplkjhgfdsazxcvbnm";
                    var  tmp="";
                    for(var  i=0;i<  l;i++)  {
                        tmp  +=  x.charAt(Math.ceil(Math.random()*100000000)%x.length);
                    }
                    return  tmp;
               }
               var lf = decodeURI(window.location.href);

              var new_hs = "";
              var pos = lf.indexOf("#r-") ;
              var pos2 = lf.indexOf("%23r-") ;
              if(pos!=-1||pos2!=-1){
                if(pos2!=-1){
                    pos=pos2+2;
                }
                new_hs=lf.substr(pos+1);
            }
               var old_hs = Cookie.get("S[SHOPEX_ADV_HS]");
               if(new_hs && old_hs!=new_hs){
                    Cookie.set("S[SHOPEX_ADV_HS]",new_hs);
               }
               store.get("jsapi",function(data){
                       var script = document.createElement("script");
                       var sessionid = Cookie.get("JS_SESSIONID")
                        if(sessionid == null){
                            sessionid=randomChar(32)
                            Cookie.set("JS_SESSIONID",sessionid)
                        }

                       var _src = "'.RSC_RPC.'/jsapi?certi_id='.$certificate.'&_dep="+sessionid+"&pt='.urlencode($this->system->request['action']['controller']).':'.urlencode($this->system->request['action']['method']).'&app=shopex('.$this->system->_app_version.')&uid="+(encodeURIComponent(Cookie.get("S[MEMBER]") || "").split("-")[0])+"&ref="+encodeURIComponent(document.referrer)+"&sz="+JSON.encode(window.getSize())+"&hs="+encodeURIComponent(Cookie.get("S[SHOPEX_ADV_HS]") || new_hs)+"&rt='.time().''.$p.'";

                       if(data){
                            try{
                               data = JSON.decode(data);
                            }catch(e){}
                              if($type(data)=="object"){
                                 _src +="&"+Hash.toQueryString(data);
                              }else if($type(data)=="string"){
                                 _src +="&"+data;
                              }
                          }

                      script.setAttribute("src",_src);
                      document.head.appendChild(script);

               });

            });
            </script>';
        }


        $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO]", "",0,"/");
        $result = setcookie(COOKIE_PFIX."[SHOPEX_STATINFO_GOODS]", "",0,"/");

        $status->set('site.orderinfo','');
        $status->set('site.addmenbyadmin','');
        $status->set('site.goods_status','');
        $status->set('addmoney','');
        $status->set('site.payinfo','');
        return str_replace('</body>',$RSC_RPC_STR.'</body>',$content);
        //$p = '';
    }
}

?>