<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "<iframe frameborder=\"0\" style=\"width: 600px; height: 400px;\" src=\"http://addons.shopex.cn/data.php?server=";
echo urlencode( "http://".$_SERVER['HTTP_HOST'].dirname( dirname( $_SERVER['PHP_SELF'] ) )."/index.php?ctl=service/demo_data&act=install_demo_data" );
echo "\"/>";
?>
