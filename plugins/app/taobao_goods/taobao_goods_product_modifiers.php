<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class taobao_goods_product_modifiers extends pageFactory
{

    public function product_add( &$content )
    {
        require( "mdl.taobao.php" );
        ( );
        $model = new mdl_taobao( );
        $nick = $model->get_tb_nick( );
        $base_url = $this->app->base_url;
        $find = array( "<span>相关商品</span></li>", "</form>", "subGoodsForm(3,this)", "subGoodsForm(2,this)", "subGoodsForm(1,this)", "</head>", "<h3>详细介绍</h3>" );
        $replace[] = $find[0]."<li id=\"send_to_taobao\"><span>发布到淘宝</span></li>";
        $replace[] = "\n<div class=\"spage-main-boxs\">\n\t<h3>发布到淘宝</h3>\n\t<div id=\"taobao_block\"></div>\n</div>\n".$find[1];
        $replace[] = "subGoodsFormEx(3)";
        $replace[] = "subGoodsFormEx(2)";
        $replace[] = "subGoodsFormEx(1)";
        $replace[] = "<script>\n\t\t\t\t\n\t\t\t\t\t\tvar is_clicked = false;\n\t\t\t\t\t\twindow.addEvent(\"domready\",function(){\n\t\t\t\t\t\t\$(\"send_to_taobao\").addEvents({\"click\":function(e){\n                            if(\"".$nick."\"!=false){\n                                if(is_clicked==false){\n                                    var wysiwyg_body_editor_id = \$E(\".wysiwyg_body\").get(\"id\");\n                                    var wysiwyg_body_editor = wysiwyg_body_editor_id.replace(\"mce_body_\",\"\");\n                                    var mec = window[wysiwyg_body_editor];\n                                    var html_edit_value=\"\";\n                                    var obj=this;\n                                    if(mec.inc) html_edit_value=mec.inc.getValue();\n                                    var local_data = \$(\"gEditor\").toQueryString()+\"&goods[intro]=\"+html_edit_value;\n                                    W.page(\"index.php?ctl=plugins/ctl_taobao_goods&act=product_add\", {method:\"post\",update:\"taobao_block\",onComplete:function(rsp){\n                                        obj.fireEvent(\"appendTag\");\t\n                                    },data:local_data});\n                                    is_clicked = true;\n                                }\n                                this.fireEvent(\"show\");\n                            }else{\n                                new Dialog(\"index.php?ctl=plugins/ctl_taobao_goods&act=sess_timeout\",{width:550,height:200,title:\"淘宝登陆\",onShow:function(e){\n                                    this.dialog_body.id=\"dialogContent\";\n                                },onClose:function(){\n\n                                }});\n                            }\n\t\t\t\t\t\t},\"show\":function(){\n\t\t\t\t\t\t\t\$(\"taobao_block\").getParent(\".spage-main-boxs\").show();\n\t\t\t\t\t\t\tthis.addClass(\"cur\");\n\t\t\t\t\t\t\t\$ES(\".spage-main-box\").each(function(item){ if(!item.hasClass(\"spage-app-box\")) item.hide();});\n\t\t\t\t\t\t\t\$ES(\".spage-side-nav .l-handle\").each(function(item){item.removeClass(\"cur\");});\n\t\t\t\t\t\t},\"hide\":function(item){\n\t\t\t\t\t\t\tif(item) if(item.hasClass(\"all\")){\n\t\t\t\t\t\t\t\titem.addClass(\"cur\");\n\t\t\t\t\t\t\t\t\$ES(\"#gEditor-Body .spage-main-box\").each(function(item){item.show();});\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\$(\"taobao_block\").getParent(\".spage-main-boxs\").hide();\n                            if(this.hasClass(\"cur\"))\n\t\t\t\t\t\t\t    this.removeClass(\"cur\");\n\t\t\t\t\t\t},\"appendTag\":function(){\n\t\t\t\t\t\t\tvar tag_name=\"淘宝\";\t\t\t\t\t\t\n\t\t\t\t\t\t\tif(!winTag.tagmain.getElements(\"li\").some(function(el){return el.get(\"text\").trim()==tag_name})){\n\t\t\t\t\t\t\t\t\tvar creatElement=\$E(\"ul[class=theme_tag] li[class=selected_none]\").clone();\n\t\t\t\t\t\t\t\t\tcreatElement.appendText(tag_name);\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t\tcreatElement.inject(winTag.tagmain);\t\n\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\twinTag.tagmain.getElements(\"li\").each(function(el){\n\t\t\t\t\t\t\t\t\tif(el.get(\"text\").trim()==tag_name){\n\t\t\t\t\t\t\t\t\t\tel.className=\"selected_all\";\t\n\t\t\t\t\t\t\t\t\t\tel.innerHTML=\$E(\"ul[class=theme_tag] li[class=selected_none]\").innerHTML;\n\t\t\t\t\t\t\t\t\t\tel.appendText(tag_name);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t})\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t\t});\n\t\t\t\t\t\t\$(\"send_to_taobao\").fireEvent(\"hide\");\n\t\t\t\t\t\t\$ES(\".spage-side-nav .l-handle\").each(function(item){\n                            if(item)\n\t\t\t\t\t\t\titem.addEvent(\"click\",function(){                               \n\t\t\t\t\t\t\t\t\$(\"send_to_taobao\").fireEvent(\"hide\",this);\n\t\t\t\t\t\t\t});\n\t\t\t\t\t\t});\n\t\t\t\t\t})\n\n\t\t\t\t\tvar _form=\$(\"gEditor\");\n\t\t\t\t\tvar _formActionURL=_form.get(\"action\");\n\t\t\t\t\t_form.set(\"target\",\"{ure:\\\"messagebox\\\",update:\\\"messagebox\\\"}\");\n\t\t\t\t\tvar _goodsIdHidden=_form.getElement(\"input[name^=goods[goods_id]\");\n\t\t\t\t\tvar reloadPicAction=function(){\n\t\t\t\t\t\t\tif( !\$E(\"#action-pic-bar input[name^=goods[image_file]\"))return;\n\t\t\t\t\t\t\tnew XHR({\n\t\t\t\t\t\t\t\tonSuccess:function(picrs){\n\t\t\t\t\t\t\t\t\t\$E(\"#action-pic-bar .pic-area\").set(\"html\",picrs);\n\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t}).send(\"index.php?ctl=goods/product&act=clone_goods_img\");\n\t\t\t\t\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t};\n\t\t\t\t\tvar pre_img_id = -1;\n\t\t\t\t\tvar images_hash;\n\t\t\t\t\tvar iid;\n\t\t\t\t\tvar sign;\n\t\t\t\t\tfunction image_callback(goods_id,iid){\n\t\t\t\t\t\t\tvar image_keys = images_hash.getKeys();\n\t\t\t\t\t\t\tvar image_vals = images_hash.getValues();\n\t\t\t\t\t\t\tif(image_keys.length>0){\n\t\t\t\t\t\t\t\tvar image_id = image_keys[0];\n\t\t\t\t\t\t\t\tvar image_val = image_vals[0];\n\t\t\t\t\t\t\t\tif(image_val==-1){\n\t\t\t\t\t\t\t\t\tnew Request.JSON({onComplete:function(rsp){\n\t\t\t\t\t\t\t\t\t\tpre_img_id = rsp.itemimg_id;\n\t\t\t\t\t\t\t\t\t\timages_hash.erase(image_id);\n\t\t\t\t\t\t\t\t\t\timage_callback(goods_id,iid,image_id,pre_img_id);\n\t\t\t\t\t\t\t\t\t}}).get(\"index.php?ctl=plugins/ctl_taobao_goods&act=taobao_item_img_upload&p[0]=\"+goods_id+\"&p[1]=\"+iid+\"&p[2]=\"+image_id+\"&p[3]=\"+pre_img_id);\n\t\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\t\timages_hash.erase(image_id);\n\t\t\t\t\t\t\t\t\tnew Request.JSON({method:\"get\",onComplete:function(rsp){\n\t\t\t\t\t\t\t\t\t}}).get(\"index.php?ctl=plugins/ctl_taobao_goods&act=taobao_item_img_delete&p[0]=\"+goods_id+\"&p[1]=\"+iid+\"&p[2]=\"+image_id);\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\tif(images_hash.getLength()<=0){\n\t\t\t\t\t\t\t\tsubmit_process(sign);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t}\n                    clearOldBn = function(bool){\n                        if(\$E(\"input[name^=old_bn[]\",_form)) { /* 多规格 */\n                            \$ES(\"input[name^=old_bn[]\",_form).each(function(item){\n                                if(bool) { /* 是否修改 */\n                                    \$(item).set(\"value\",\$E(\"input[name^=bn[]\",\$(item).getParent(\"tr\")).get(\"value\"));\n                                } else {\n                                    \$(item).set(\"value\",\"\");\n                                }\n                            });\n                        }else if(\$E(\"input[name^=old_bn]\",_form)) {/* 单规格 */\n                            if(bool) { /* 是否修改 */\n                                \$E(\"input[name^=old_bn]\",_form).set(\"value\",\$E(\"input[name^=goods[product_bn]\",_form).get(\"value\"));\n                            } else {\n                                \$E(\"input[name^=old_bn]\",_form).set(\"value\",\"\");\n                            }\n                        }\n                    }\n\n\n\t\t\t\t\tvar subGoodsFormEx_ = function (sign_){\n\t\t\t\t\t\tsign = sign_;\n\t\t\t\t\t\t window.MessageBoxOnShow=function(box,success){\n\t\t\t\t\t\t\t if(MODALPANEL)MODALPANEL.hide();\n\t\t\t\t\t\t\t if(!success)return;\n\t\t\t\t\t\t\t\t try{\n\t\t\t\t\t\t\t\t  var goodsid=box.getElement(\"input[type=hidden]\").getValue();\n\t\t\t\t\t\t\t\t  _goodsIdHidden.set(\"value\",goodsid);\n                                  \n                                  if(\$E(\"#gEditor input[name^=goods[pub_taobao]\")) {\n                                      if(\$E(\"#gEditor input[name^=goods[pub_taobao]\").getValue()==1)pub_taobao=true;else pub_taobao=false;\n                                    }\n\t\t\t\t\t\t\t\t  if(pub_taobao==true){\n\t\t\t\t\t\t\t\t\t\tnew Request.JSON({onSuccess:function(rsp){\n                                                                    if(rsp[\"error_rsp\"]){\n                                                                        alert(rsp[\"error_rsp\"]);\n                                                                    }else{\n                                                                        pre_img_id = -1;\n                                                                        images_hash = new Hash(rsp[\"images\"]);\n                                                                        iid = rsp.iid;\n                                                                        if(images_hash.getLength()>0){\n                                                                          image_callback(goodsid,iid);\n                                                                        }else{\n                                                                            submit_process(sign);\n                                                                        }\n                                                                    }\n                                                                \n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t}}).get(\"index.php?ctl=plugins/ctl_taobao_goods&act=taobao_item_add&p[0]=\"+goodsid);\n\t\t\t\t\t\t\t\t\t}else{\n                                        submit_process(sign);\n                                    }\n\n\t\t\t\t\t\t\t  }catch(e){console.info(e)}\n\t\t\t\t\t\t\t\t\t\t\t\t \n\t\t\t\t\t\t  };\n\t\t\t\t\t\t_form.set(\"action\",_formActionURL+\"&but=\"+sign).fireEvent(\"submit\");\n\t\t\t\t\t};\n\t\t\t\t\tfunction subGoodsFormEx(ctl){\n\t\t\t\t\t\tif(\$E(\"#gEditor input[name^=goods[pub_taobao]\")){\n\t\t\t\t\t\t\tnew Ajax(\"index.php?ctl=plugins/ctl_taobao_goods&act=get_postages\", {method:\"get\",onComplete:function(rsp){\n\t\t\t\t\t\t\t\tif(rsp==\"fail\"){\n\t\t\t\t\t\t\t\t\tlogin_taobao();\n\t\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\t\tsubGoodsFormEx_(ctl);\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t}}).request();\n\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\tsubGoodsFormEx_(ctl);\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n                     var pub_taobao = false;\n\t\t\t\t\tfunction submit_process(sign){\n\t\t\t\t\t\t  switch (sign){\n\t\t\t\t\t\t\t\tcase 1:\n\t\t\t\t\t\t\t\t _goodsIdHidden.set(\"value\",\"\");\n\t\t\t\t\t\t\t\t   \$E(\"input[name^=goods[name]\",_form).value=\"\";\n\t\t\t\t\t\t\t\t   \$E(\"input[name^=goods[bn]\",_form).set(\"value\",\"\");\n\t\t\t\t\t\t\t\t   if(pbn=\$E(\"input[name^=goods[product_bn]\",_form)){\n\t\t\t\t\t\t\t\t\t\tpbn.value=\"\";\n\t\t\t\t\t\t\t\t   }\n\t\t\t\t\t\t\t\t   if(pbns=\$\$(\"input[name^=bn[]\",_form)){\n\t\t\t\t\t\t\t\t\t  if(pbns.length){\n\t\t\t\t\t\t\t\t\t   pbns.set(\"value\",\"\");\n\t\t\t\t\t\t\t\t\t   }\n\t\t\t\t\t\t\t\t   }\n\t\t\t\t\t\t\t\t    if(\$E(\"#gEditor input[name^=goods[pub_taobao]\")){\n                                       try{ \$E(\".spage-side-nav .l-handle\").fireEvent(\"click\");}catch(e){}\n                                    }\n\t\t\t\t\t\t\t\t   reloadPicAction();\n                                    clearOldBn(false);\n\n\t\t\t\t\t\t\t\tbreak;\n\t\t\t\t\t\t\t\tcase 2:\n\t\t\t\t\t\t\t\twindow.close();\t\t\n\t\t\t\t\t\t\t\tbreak;\n\t\t\t\t\t\t\t\tcase 3:\n                                clearOldBn(true);\n\t\t\t\t\t\t\t\tdefault:\n\t\t\t\t\t\t\t\tbreak;\n\t\t\t\t\t\t  }\n                          if(\$chk(\$(\"g_id\"))&&window.winTag)window.winTag.finderTag.fireEvent(\"apply\",\$(\"g_id\").value);\n\t\t\t\t\t}\n\t\t\t\t\t</script>\n\t\t\t\t\t<style>\n\t\t\t\t\t#send_to_taobao span { background:url(".$base_url."/images/pdt_tb.gif) no-repeat 80px 7px; color:#ff721d;background-position:0;}\n\t\t\t\t\t.cur#send_to_taobao span { color:#fff}\n\t\t\t\t\t</style>\n\t\t\t\t\t</head>";
        $replace[] = "<h3>详细介绍</h3><input type=\"hidden\" class=\"_x_ipt\" vtype=\"g-intro\">";
        $content = str_replace( $find, $replace, $content );
        return $content;
    }

    public function product_update( &$content )
    {
        $find = "<placeholder style=\"display:none;\">taobao_block</placeholder>";
        $replace1 = "<div class=\"spage-main-box\">\n\t<h3>发布到淘宝</h3>\n\t<div class=\"division\">\n\t\t<div id=\"taobao_block\"></div>\n\t</div>\n</div>";
        $content = str_replace( $find, $replace1, $content );
        return $content;
    }

    public function product_edit( &$content )
    {
        return $content = $this->product_add( $content );
    }

    public function product_index( &$content )
    {
        $append = "\n\n<script>\nwindow.addEvent(\"domready\", function(){\n\tvar goods_id = [];\n\tvar goods_nodes = {};\n\t\$ES(\"#main input[name^=goods_id[]\").each(function(item,key){\n\t\tgoods_id[key] = \"goods_id[]=\"+item.get(\"value\");\n\t\tgoods_nodes[item.get(\"value\")] = item;\n\t});\n\tvar post_data = (goods_id.join(\"&\"));\n\tnew Request.JSON({data:post_data,onComplete:function(rsp){\n\t\trsp = new Hash(rsp);\n\t\trsp.each(function(item,index){\n\t\t});\n\t}}).post(\"index.php?ctl=plugins/ctl_taobao_goods&act=product_index\");\n\t\n});\n</script>\n\n\t\t";
        return $content;
    }

    public function detail( &$content )
    {
        preg_match( "/.*value=['|\"]?([^\"|']*)['|\"]?\\s/", $content, $match );
        $url = $match[1];
        preg_match( "/.*product-(\\d+)/", $url, $a );
        $gid = $a[1];
        include( "mdl.taobao.php" );
        ( );
        $objTaobao = new mdl_taobao( );
        $row = $objTaobao->get_goodslist_by_id( $gid );
        if ( $row['outer_id'] )
        {
            $outer_key = $row['outer_key'];
            $outer_id = $row['outer_id'];
            $url = "http://item.taobao.com/auction/item_detail.jhtml?item_id=".$outer_id;
            $output .= "<a target=\"_blank\" class=\"sysiconBtnNoIcon\" href=\"".$url."\">"."访问".$outer_key."上的商品</a>";
        }
        $find[] = "访问此链接</a>";
        $replace[] = $find[0].$output;
        $content = str_replace( $find, $replace, $content );
        return $content;
    }

}

?>
