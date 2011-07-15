<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !function_exists( "tpl_function_html_options" ) )
{
    require( CORE_DIR."/include_v5/smartyplugins/function.html_options.php" );
}
echo "<center><h5 class=\"success\">数据库已配置并连接成功，数据前缀为&nbsp;\"<i>";
echo $this->_vars['db_pre'];
echo "</i>\"</h5></center> <div style=\"width:590px;margin:0;padding:10px;margin:10px;border:1px solid #666;border-style:solid none;\"> <!-- <img style=\"float:right\" src=\"svinfo.php?img=rank_{\$svinfo.rank}.gif\" /> <h2 style=\"font-size:36px;margin:0;padding:0;line-height:100%\">{\$svinfo.level}</h2> <h3 style=\"margin:0;padding:0\">服务器特性得分:";
echo "<s";
echo "pan style=\"font-family: Georgia;font-size:24px;margin:0 10px;color:#090\">{\$svinfo.score}</span>&nbsp;&nbsp;<a href=\"svinfo.php?db_host=config\" target=\"_blank\" style=\"color:#009\">更多信息&raquo;</a></h3>--> <table width=\"100%\"> ";
$this->_env_vars['foreach'][basic] = array(
    "total" => count( $this->_vars['svinfo']['basic'] ),
    "iteration" => 0
);
foreach ( ( array )$this->_vars['svinfo']['basic'] as $Var_600 )
{
    $this->_vars['key'] = $Tmp_28;
    $this->_env_vars['foreach'][basic]['first'] = $this->_env_vars['foreach'][basic]['iteration'] == 0;
    ++$this->_env_vars['foreach'][basic]['iteration'];
    $this->_env_vars['foreach'][basic]['last'] = $this->_env_vars['foreach'][basic]['iteration'] == $this->_env_vars['foreach'][basic]['total'];
    echo " <tr";
    if ( $this->_env_vars['foreach']['basic']['iteration'] % 2 == 1 )
    {
        echo " style=\"background:#E0EAF2\"";
    }
    echo "> <td width=\"60%\">";
    echo $this->_vars['key'];
    echo "</td><td>";
    echo $this->_vars['value'];
    echo "</td> </tr> ";
}
unset( $this->foreach[basic] );
echo " </table> ";
if ( !$this->_vars['svinfo']['allow_install'] && !( defined( "SHOP_DEVELOPER" ) && SHOP_DEVELOPER ) )
{
    echo " <center><h2 style=\"background:#FAD163\">系统无法继续安装，缺少必要的服务器环境</h2></center> ";
    $this->_env_vars['foreach'][basic] = array(
        "total" => count( $this->_vars['svinfo']['require'] ),
        "iteration" => 0
    );
    foreach ( ( array )$this->_vars['svinfo']['require'] as $Var_2520 )
    {
        $this->_vars['key'] = $Tmp_108;
        $this->_env_vars['foreach'][basic]['first'] = $this->_env_vars['foreach'][basic]['iteration'] == 0;
        ++$this->_env_vars['foreach'][basic]['iteration'];
        $this->_env_vars['foreach'][basic]['last'] = $this->_env_vars['foreach'][basic]['iteration'] == $this->_env_vars['foreach'][basic]['total'];
        echo " <table width=\"100%\"> <tr";
        if ( !$this->_vars['value']['result'] )
        {
            echo " style=\"background:#A4141D;color:#fff;font-weight:bold\"";
        }
        else if ( $this->_env_vars['foreach']['basic']['iteration'] % 2 == 1 )
        {
            echo " style=\"background:#E0EAF2\"";
        }
        echo "> <td width=\"60%\">";
        echo $this->_vars['key'];
        echo "</td><td>";
        echo $this->_vars['value']['value'];
        echo "</td> </tr> ";
    }
    unset( $this->foreach[basic] );
    echo " </table> </div> ";
}
else
{
    echo " </div> <div style=\"padding-left:45px;\"><h5 class=\"success\"><br>请在下面建立商店管理员帐户：</h5></div> <form id=\"show\" method=\"post\" action=\"index.php?step=complete\"> <table> <tr> <th width=\"150px\" align=\"right\" scope=\"row\"><label for=\"ipt_uname\">管理员用户名：</label></th> <td width=\"200px\"><input type=\"text\" name=\"uname\" id=\"ipt_uname\" value=\"admin\" tabindex=\"1\"></td> <th align=\"right\">服�";
    echo "��器时区：</th> <td> ";
    echo $this->_vars['stimezone'];
    echo " </td> </tr> <tr> <th align=\"right\" scope=\"row\"><label for=\"ipt_passwd\">管理员密码：</label></th> <td><input type=\"password\" name=\"password\" id=\"ipt_passwd\" tabindex=\"2\"></td> <th align=\"right\">您当前时间：</th> <td> ";
    echo "<s";
    echo "elect style=\"width:200px\" name=\"localtime\" tabindex=\"6\" id=\"localtime\"> ";
    echo tpl_function_html_options( array(
        "options" => $this->_vars['timelist'],
        "selected" => $this->_vars['defaultHour']
    ), $this );
    echo " </select> </td> </tr> <tr> <th align=\"right\" scope=\"row\"><label for=\"ipt_re_passwd\">再输入一次密码：</label></th> <td colspan=\"3\"><input type=\"password\" name=\"re_passwd\" id=\"ipt_re_passwd\" tabindex=\"3\"></td> </tr> <tr> <td align=\"right\" valign=\"top\"><input type=\"checkbox\" id=\"use_demo\" checked=\"checked\" name=\"use_demo\" value=\"yes\" tabindex=\"4\"></td> <td colspan=\"3\"><label for=\"use_demo\">安装体验数据</";
    echo "label> <br />";
    echo "<s";
    echo "pan style=\"color:#666\">装载体验数据后，您不必进行任何系统设置，可以用模拟数据体验ShopEx网店系统的各项功能</span></td> </tr> <tr> <td align=\"right\" valign=\"top\"><input type=\"checkbox\" checked=\"checked\" id='install_stat' name=\"install_stat\" value=\"yes\" tabindex=\"4\"></td> <td colspan=\"3\"><label for=\"install_stat\">安装营销统计工具</label> <br />";
    echo "<s";
    echo "pan style=\"color:#666\">营销统计工具能够帮助商家整合营销全程数据，分析消费者行为特征，帮助店家不断优化官网网店的营销效果。</span></td> </tr> </table> </form> <div> </div> <div class=\"button\"><input type=\"image\" src=\"images/btn-install.gif\" tabindex=\"5\" onclick=\"startInstall()\"/></div> ";
}
echo " <form action=\"http://service.shopex.cn/plugins/install_errorlog/b2c_install_erlog.php\" style=\"display:none\" method=\"post\" id=\"server_form\" target=\"shopex_iframe\"> <input name=\"webserver\" id=\"webserver\" value=\"\" type=\"text\"/> <input name=\"os\" id=\"os\" value=\"\" type=\"text\"/> <input name=\"phpver\" id=\"phpver\" value=\"\" type=\"text\"/> <input name=\"mysql\" id=\"mysql\" value=\"\" type=\"text\"/> <input name=\"domain\" i";
echo "d=\"domain\" value=\"\" type=\"text\"/> <textarea name=\"error_msg\" id=\"error_msg\"></textarea> <input name=\"contact\" id=\"contact\" value=\"\" type=\"text\"/> </form> <iframe frameborder=\"0\" src=\"\" width='0' height='0' id=\"shopex_iframe\" name=\"shopex_iframe\"></iframe> ";
echo "<s";
echo "cript>\n  function check_installFrom(str){\n        if(!\$('ipt_uname').value){\n          alert('管理员用户名不能为空。');\n          return false;\n        }\n        if(\$('ipt_passwd').value !== \$('ipt_re_passwd').value){\n          alert('两次输入密码不一致。');\n          return false;\n        }\n        if(!\$('ipt_passwd').value){\n          return confirm('确定密码为空吗？";
echo "这样系统管理帐号的安全性比较低。');\n        }\n        return true;\n  }\n  \n  function startInstall(){\n     \n         if(check_installFrom()){\n        \n            \$('main').style.display='none';\n            document.body.style.background='#D3E1ED';\n            \n            \$('install_progress').style.display='';\n        \n            installing('index.php?step=install_mysql_db');\n   ";
echo "     }\n  }\n    var server,mysql;\n\n    function installing(url){\n    \n       XHR.open('post',url,true);\n       XHR.onreadystatechange=function(){\n       \n           if (XHR.readyState != 4)return;\n           \n   \n              XHR.onreadystatechange=function(){};\n           \n          if ((XHR.status >= 200) && (XHR.status < 300)){\n                  \n                  var rs=XHR.responseText;\n      ";
echo "            \n                  \n                   if(rs=='success'){\n                        \$('install_info').innerHTML+='<br/>安装成功!';\n                        sendError(\$('install_info').innerHTML);\n                        alert('安装成功,点击确定进入下一步!');                    \n                        \$('show').submit();\n                    }else{\n                        s";
echo "erver=server||XHR.getResponseHeader(\"Server\").split(/\\s/);                \n                        mysql=mysql||XHR.getResponseHeader(\"Mysql\");\n                        var step=rs.split('|');\n                        \$('install_info').innerHTML+='<br/>'+step[0];\n                        \$('now_installing').innerHTML=(step[2] ? '正在安装'+step[2]+'...' : '正在完成安装...');\n                 ";
echo "       \n                        if(step[1]!='fail'){\n                            installing(step[1]);\n                        }else{\n                            sendError(\$('install_info').innerHTML);\n                            \$('now_installing').innerHTML='安装失败';\n                        }\n                    }\n          \n          }\n           \n       \n       \n       };\n       \n       \n";
echo "       XHR.setRequestHeader('X-Requested-With','XMLHttpRequest');\n       XHR.setRequestHeader('Accept','text/javascript, text/html, application/xml, text/xml, */*');\n       XHR.setRequestHeader('Content-type', 'application/x-www-form-urlencoded charset=utf-8');\n       \n       XHR.send('password='+encodeURIComponent(\$('ipt_passwd').value)+'&uname='+encodeURIComponent(\$('ipt_uname').value)+'&timezon";
echo "e='+\$('localtime').value+'&use_demo='+\$('use_demo').checked+'&install_stat='+\$('install_stat').checked);\n       \n    }\n    function sendError(errmsg){\n         if(/<b>(Warning|Error)<\\/b>/i.test(errmsg)) {\n            var msg=\"如:email,电话,手机,qq等联系方式\";\n            var contact=prompt(\"安装时发生错误，请输入您的联系方式方便与您联系\",msg);\n            if(contact";
echo "&&contact!=msg){                        \n                    \$('webserver').value=server[0];\n                    \$('os').value=server[1];\n                    \$('phpver').value=server[2];\n                    \$('mysql').value=mysql;\n                    \$('domain').value=document.domain;\n                    \$('contact').value=contact;\n                    \$('error_msg').value=errmsg;                  ";
echo "                  \n                    \$('server_form').submit();\n                    alert(\"信息提交成功\");                            \n            }\n        }           \n    }\n</script>";
?>
