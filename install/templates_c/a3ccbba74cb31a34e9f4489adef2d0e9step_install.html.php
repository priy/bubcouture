<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "<h5>系统安装成功!以下是您的商店系统管理员帐户信息：</h5> <table width=\"300px;\"> <tr> <th width=\"60\">用户名:</th> <td width=\"228\">";
echo $this->_vars['uname'];
echo "</td> </tr> <tr> <th>密&nbsp;&nbsp;&nbsp;&nbsp;码:</th> <td>";
echo isset( $this->_vars['password'] ) && "" !== $this->_vars['password'] ? $this->_vars['password'] : "(空密码)";
echo "</td> </tr> </table><br /> <a href=\"../shopadmin\" target=\"_blank\">点此登录商店系统后台管理</a> <!--<h1>Already Installed</h1><p>You appear to have already installed ShopEx. To reinstall please clear your old database tables first.</p>--> <br /><br /><br /><br /><br /> <table width=\"658\" border=\"0\" style=\"border: #FFFFFF dotted 1px;\"> <tr> <td><h5>ShopEx商店系统已经安装成功，为了更好的辅�";
echo "�商店运营，您可根据需要下载并安装以下软件：</h5></td> </tr> <tr> <td>ShopEx网店助理是一个类似淘宝助理、易趣助理的客户端程序，可用来方便的在本地处理商店数据，并能够在ShopEx独立网上商店和第三方平台（比如淘宝、易趣、拍拍、有啊、阿里巴巴）之间实现数据上传与下载功能的工具。 </td> </tr> <tr> <td><a h";
echo "ref=\"http://www.shopex.cn/products/ShopExAssitant_download.html\" target=\"_blank\">最新版本ShopEx助理下载</a></td> </tr> </table> ";
?>
