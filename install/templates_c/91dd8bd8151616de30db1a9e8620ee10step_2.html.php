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
echo "<h5 style=\"boder:1px #FFA43D; color:#FFA43D;padding-left:5%;line-height:150%\">请填写数据库的相关信息：<br />首先请向主机空间商确认您的数据库在服务器上已建立。</h5> <form method=\"post\" action=\"index.php?step=ready\" id=\"db_setting\"> <table width=\"100%\" border=\"0\"> <tr> <th width=\"22%\" align=\"right\"><label for=\"db_host\">数据库主机:</label></th> <td width=\"30%\"><input cla";
echo "ss=\"txt\" id=\"db_host\" name=\"dbhost\" size=\"25\" value=\"";
echo isset( $this->_vars['host'] ) && "" !== $this->_vars['host'] ? $this->_vars['host'] : "localhost";
echo "\" type=\"text\" onchange='checkdbInfo()'></td> <td width=\"48%\">如果数据库服务器与WEBSERVER不在同一台主机上,请设置为数据库服务器的地址。</td> </tr> <tr> <th align=\"right\" scope=\"row\"><label for=\"db_uname\">数据库用户名:</label></th> <td><input id=\"db_uname\" class=\"txt\" name=\"uname\" size=\"25\" value=\"\" type=\"text\" onchange='checkdbInfo()'></td> <td rowspan=\"2\" id=\"db_check_result";
echo "\" style=\"display:none\"> <img src=\"images/db_succ.gif\" /> </td> </tr> <tr> <th align=\"right\" scope=\"row\"><label for=\"db_passwd\">数据库密码:</label></th> <td><input id=\"db_passwd\" class=\"txt\" type=\"password\" name=\"pwd\" size=\"25\" value=\"\" type=\"text\" onchange='checkdbInfo()'></td> </tr> <tr> <th align=\"right\" scope=\"row\"><label for=\"db_name\">数据库名:</label></th> <td id=\"db_selector\"><input class=\"txt\" style=";
echo "\"width:120px\" id=\"db_name\" name=\"dbname\" size=\"25\" value=\"\" type=\"text\"> ";
echo "<s";
echo "pan id=\"btn_check_db\" onclick=\"checkdbInfo()\">";
echo "<s";
echo "pan style=\"text-decoration: underline; cursor: pointer;color:#00f;\">测试连接&raquo;</span></span> </td> <td></td> </tr> <tr> <th align=\"right\" scope=\"row\"><label for=\"db_prefix\">安装数据表前缀:</label></th> <td><input class=\"txt\" id=\"db_prefix\" name=\"prefix\" id=\"prefix\" value=\"sdb_\" size=\"25\" type=\"text\"></td> <td>一般您不需要修改数据表前缀。</td> </tr> <tr> <th align=\"right\" scope=\"row\"><lab";
echo "el for=\"db_PREFIX\">选择您的服务器时区</label></th> <td colspan=\"2\"> ";
echo "<s";
echo "elect name=\"stimezone\" style=\"width:300px\"> ";
echo tpl_function_html_options( array(
    "options" => $this->_vars['timezone'],
    "selected" => $this->_vars['default_timezone']
), $this );
echo " </select> </td> </tr> </table> <center> <input style=\"margin:10px;\" name=\"submit\" value=\"下一步：创建配置文件(config.php) &raquo;\" type=\"submit\"> </center> </form> ";
echo "<s";
echo "cript>\nvoid function(){\n   \n   var dbNameInput=_\$('db_selector').innerHTML;\n   \n    checkdbInfo=function(){\n       var bakvalue =_\$('db_name').value;\n       var dbHost=_\$('db_host').value;\n       var dbUname=_\$('db_uname').value;\n       var dbPass=_\$('db_passwd').value;\n       if(!XHR)return;\n       if(\$('btn_check_db'))\$('btn_check_db').innerHTML='<img src=\"images/ajax-loader.gif\" />';\n       XHR.";
echo "open('post','index.php?step=checkdb',true);\n       XHR.onreadystatechange=function(){\n       \n           if (XHR.readyState != 4)return;\n           \n   \n           XHR.onreadystatechange=function(){};\n           \n          if ((XHR.status >= 200) && (XHR.status < 300)){\n                 _\$('db_selector').innerHTML=XHR.responseText;\n                 _\$('db_check_result').style.display='';\n          ";
echo "\n          }else{\n             \n               _\$('db_check_result').style.display='none';\n               _\$('db_selector').innerHTML=dbNameInput;\n               _\$('db_name').value = bakvalue;\n             \n          }\n           \n       \n       \n       };\n       \n       XHR.setRequestHeader('X-Requested-With','XMLHttpRequest');\n       XHR.setRequestHeader('Accept','text/javascript, text/html, ap";
echo "plication/xml, text/xml, */*');\n       XHR.setRequestHeader('Content-type', 'application/x-www-form-urlencoded charset=utf-8');\n       XHR.send('dbhost='+dbHost+'&uname='+encodeURIComponent(dbUname)+'&pwd='+encodeURIComponent(dbPass));\n    };\n\n}();\n\n\n\n</script>";
?>
