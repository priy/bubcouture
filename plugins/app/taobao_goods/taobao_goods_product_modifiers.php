<?php
class taobao_goods_product_modifiers extends pageFactory{
	function product_add( &$content )
	{
        require('mdl.taobao.php');
        $model = new mdl_taobao();
        $nick = $model->get_tb_nick();
		$base_url = $this->app->base_url;
		$find = array(
			'<span>相关商品</span></li>',
			'</form>',
			'subGoodsForm(3,this)',
			'subGoodsForm(2,this)',
			'subGoodsForm(1,this)',
			'</head>',
			'<h3>详细介绍</h3>'
		);

		$replace[] = $find[0].'<li id="send_to_taobao"><span>发布到淘宝</span></li>';
		$replace[] = '
<div class="spage-main-boxs">
	<h3>发布到淘宝</h3>
	<div id="taobao_block"></div>
</div>
'.$find[1];
		$replace[] = 'subGoodsFormEx(3)';
		$replace[] = 'subGoodsFormEx(2)';
		$replace[] = 'subGoodsFormEx(1)';
		$replace[]='<script>
				
						var is_clicked = false;
						window.addEvent("domready",function(){
						$("send_to_taobao").addEvents({"click":function(e){
                            if("'.$nick.'"!=false){
                                if(is_clicked==false){
                                    var wysiwyg_body_editor_id = $E(".wysiwyg_body").get("id");
                                    var wysiwyg_body_editor = wysiwyg_body_editor_id.replace("mce_body_","");
                                    var mec = window[wysiwyg_body_editor];
                                    var html_edit_value="";
                                    var obj=this;
                                    if(mec.inc) html_edit_value=mec.inc.getValue();
                                    var local_data = $("gEditor").toQueryString()+"&goods[intro]="+html_edit_value;
                                    W.page("index.php?ctl=plugins/ctl_taobao_goods&act=product_add", {method:"post",update:"taobao_block",onComplete:function(rsp){
                                        obj.fireEvent("appendTag");	
                                    },data:local_data});
                                    is_clicked = true;
                                }
                                this.fireEvent("show");
                            }else{
                                new Dialog("index.php?ctl=plugins/ctl_taobao_goods&act=sess_timeout",{width:550,height:200,title:"淘宝登陆",onShow:function(e){
                                    this.dialog_body.id="dialogContent";
                                },onClose:function(){

                                }});
                            }
						},"show":function(){
							$("taobao_block").getParent(".spage-main-boxs").show();
							this.addClass("cur");
							$ES(".spage-main-box").each(function(item){ if(!item.hasClass("spage-app-box")) item.hide();});
							$ES(".spage-side-nav .l-handle").each(function(item){item.removeClass("cur");});
						},"hide":function(item){
							if(item) if(item.hasClass("all")){
								item.addClass("cur");
								$ES("#gEditor-Body .spage-main-box").each(function(item){item.show();});
							}
							$("taobao_block").getParent(".spage-main-boxs").hide();
                            if(this.hasClass("cur"))
							    this.removeClass("cur");
						},"appendTag":function(){
							var tag_name="淘宝";						
							if(!winTag.tagmain.getElements("li").some(function(el){return el.get("text").trim()==tag_name})){
									var creatElement=$E("ul[class=theme_tag] li[class=selected_none]").clone();
									creatElement.appendText(tag_name);						
									creatElement.inject(winTag.tagmain);	
							}else{
								winTag.tagmain.getElements("li").each(function(el){
									if(el.get("text").trim()==tag_name){
										el.className="selected_all";	
										el.innerHTML=$E("ul[class=theme_tag] li[class=selected_none]").innerHTML;
										el.appendText(tag_name);
									}
								})
							}
						}
						});
						$("send_to_taobao").fireEvent("hide");
						$ES(".spage-side-nav .l-handle").each(function(item){
                            if(item)
							item.addEvent("click",function(){                               
								$("send_to_taobao").fireEvent("hide",this);
							});
						});
					})

					var _form=$("gEditor");
					var _formActionURL=_form.get("action");
					_form.set("target","{ure:\"messagebox\",update:\"messagebox\"}");
					var _goodsIdHidden=_form.getElement("input[name^=goods[goods_id]");
					var reloadPicAction=function(){
							if( !$E("#action-pic-bar input[name^=goods[image_file]"))return;
							new XHR({
								onSuccess:function(picrs){
									$E("#action-pic-bar .pic-area").set("html",picrs);
									
								}
							}).send("index.php?ctl=goods/product&act=clone_goods_img");
													
					};
					var pre_img_id = -1;
					var images_hash;
					var iid;
					var sign;
					function image_callback(goods_id,iid){
							var image_keys = images_hash.getKeys();
							var image_vals = images_hash.getValues();
							if(image_keys.length>0){
								var image_id = image_keys[0];
								var image_val = image_vals[0];
								if(image_val==-1){
									new Request.JSON({onComplete:function(rsp){
										pre_img_id = rsp.itemimg_id;
										images_hash.erase(image_id);
										image_callback(goods_id,iid,image_id,pre_img_id);
									}}).get("index.php?ctl=plugins/ctl_taobao_goods&act=taobao_item_img_upload&p[0]="+goods_id+"&p[1]="+iid+"&p[2]="+image_id+"&p[3]="+pre_img_id);
								}else{
									images_hash.erase(image_id);
									new Request.JSON({method:"get",onComplete:function(rsp){
									}}).get("index.php?ctl=plugins/ctl_taobao_goods&act=taobao_item_img_delete&p[0]="+goods_id+"&p[1]="+iid+"&p[2]="+image_id);
								}
							}
							if(images_hash.getLength()<=0){
								submit_process(sign);
							}
					}
                    clearOldBn = function(bool){
                        if($E("input[name^=old_bn[]",_form)) { /* 多规格 */
                            $ES("input[name^=old_bn[]",_form).each(function(item){
                                if(bool) { /* 是否修改 */
                                    $(item).set("value",$E("input[name^=bn[]",$(item).getParent("tr")).get("value"));
                                } else {
                                    $(item).set("value","");
                                }
                            });
                        }else if($E("input[name^=old_bn]",_form)) {/* 单规格 */
                            if(bool) { /* 是否修改 */
                                $E("input[name^=old_bn]",_form).set("value",$E("input[name^=goods[product_bn]",_form).get("value"));
                            } else {
                                $E("input[name^=old_bn]",_form).set("value","");
                            }
                        }
                    }


					var subGoodsFormEx_ = function (sign_){
						sign = sign_;
						 window.MessageBoxOnShow=function(box,success){
							 if(MODALPANEL)MODALPANEL.hide();
							 if(!success)return;
								 try{
								  var goodsid=box.getElement("input[type=hidden]").getValue();
								  _goodsIdHidden.set("value",goodsid);
                                  
                                  if($E("#gEditor input[name^=goods[pub_taobao]")) {
                                      if($E("#gEditor input[name^=goods[pub_taobao]").getValue()==1)pub_taobao=true;else pub_taobao=false;
                                    }
								  if(pub_taobao==true){
										new Request.JSON({onSuccess:function(rsp){
                                                                    if(rsp["error_rsp"]){
                                                                        alert(rsp["error_rsp"]);
                                                                    }else{
                                                                        pre_img_id = -1;
                                                                        images_hash = new Hash(rsp["images"]);
                                                                        iid = rsp.iid;
                                                                        if(images_hash.getLength()>0){
                                                                          image_callback(goodsid,iid);
                                                                        }else{
                                                                            submit_process(sign);
                                                                        }
                                                                    }
                                                                
																}}).get("index.php?ctl=plugins/ctl_taobao_goods&act=taobao_item_add&p[0]="+goodsid);
									}else{
                                        submit_process(sign);
                                    }

							  }catch(e){console.info(e)}
												 
						  };
						_form.set("action",_formActionURL+"&but="+sign).fireEvent("submit");
					};
					function subGoodsFormEx(ctl){
						if($E("#gEditor input[name^=goods[pub_taobao]")){
							new Ajax("index.php?ctl=plugins/ctl_taobao_goods&act=get_postages", {method:"get",onComplete:function(rsp){
								if(rsp=="fail"){
									login_taobao();
								}else{
									subGoodsFormEx_(ctl);
								}
							}}).request();
						}else{
							subGoodsFormEx_(ctl);
						}
					}
                     var pub_taobao = false;
					function submit_process(sign){
						  switch (sign){
								case 1:
								 _goodsIdHidden.set("value","");
								   $E("input[name^=goods[name]",_form).value="";
								   $E("input[name^=goods[bn]",_form).set("value","");
								   if(pbn=$E("input[name^=goods[product_bn]",_form)){
										pbn.value="";
								   }
								   if(pbns=$$("input[name^=bn[]",_form)){
									  if(pbns.length){
									   pbns.set("value","");
									   }
								   }
								    if($E("#gEditor input[name^=goods[pub_taobao]")){
                                       try{ $E(".spage-side-nav .l-handle").fireEvent("click");}catch(e){}
                                    }
								   reloadPicAction();
                                    clearOldBn(false);

								break;
								case 2:
								window.close();		
								break;
								case 3:
                                clearOldBn(true);
								default:
								break;
						  }
                          if($chk($("g_id"))&&window.winTag)window.winTag.finderTag.fireEvent("apply",$("g_id").value);
					}
					</script>
					<style>
					#send_to_taobao span { background:url('.$base_url.'/images/pdt_tb.gif) no-repeat 80px 7px; color:#ff721d;background-position:0;}
					.cur#send_to_taobao span { color:#fff}
					</style>
					</head>';
					$replace[] = '<h3>详细介绍</h3><input type="hidden" class="_x_ipt" vtype="g-intro">';
		$content = str_replace($find,$replace,$content);
		return $content;
    }
    
	function product_update( &$content )
	{
		$find = '<placeholder style="display:none;">taobao_block</placeholder>';
		$replace1 = <<<EOF
<div class="spage-main-box">
	<h3>发布到淘宝</h3>
	<div class="division">
		<div id="taobao_block"></div>
	</div>
</div>
EOF;
		$content = str_replace($find,$replace1,$content);
		return $content;
    }
    
    function product_edit( &$content )
    {
        return $content = $this->product_add($content);
    }
	function product_index(&$content){
		$append = '

<script>
window.addEvent("domready", function(){
	var goods_id = [];
	var goods_nodes = {};
	$ES("#main input[name^=goods_id[]").each(function(item,key){
		goods_id[key] = "goods_id[]="+item.get("value");
		goods_nodes[item.get("value")] = item;
	});
	var post_data = (goods_id.join("&"));
	new Request.JSON({data:post_data,onComplete:function(rsp){
		rsp = new Hash(rsp);
		rsp.each(function(item,index){
		});
	}}).post("index.php?ctl=plugins/ctl_taobao_goods&act=product_index");
	
});
</script>

		';
		return $content;
	}
	function detail(&$content){
		//input type="text" onclick="this.focus();this.select();" style="color: rgb(0, 102, 0); font-size: 10px; font-family: Arial; width: 55%;" class="shadow" value="http://localhost:8080/485/src/?product-466.html"/>
		preg_match('/.*value=[\'|"]?([^"|\']*)[\'|"]?\s/',$content,$match);
		$url = $match[1];
		preg_match('/.*product-(\d+)/',$url,$a);
		$gid = $a[1];
		include('mdl.taobao.php');
		$objTaobao = new mdl_taobao();
		$row = $objTaobao->get_goodslist_by_id($gid); 
        
        if($row['outer_id']){
            $outer_key = $row['outer_key'];
            $outer_id = $row['outer_id'];
            $url = 'http://item.taobao.com/auction/item_detail.jhtml?item_id='.$outer_id;
            $output .= '<a target="_blank" class="sysiconBtnNoIcon" href="'.$url.'">'.'访问'.$outer_key.'上的商品</a>';
        }
        $find[] = "访问此链接</a>";
		$replace[] = $find[0].$output;
		$content = str_replace($find,$replace,$content);
		return $content;
	}
}
