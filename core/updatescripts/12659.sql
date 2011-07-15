<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* ShopEx database update script                               */\n/*                                                             */\n/*         Version:  from 12175 to 12659                       */\n/*   last Modified:  2008/07/23                                */\n/*=============================================================*/\n\ndro";
echo "p table if exists sdb_package;\n\nCREATE TABLE `sdb_package` (\n  `pkg_id` varchar(100) NOT NULL,\n  `disabled` enum('true','false') NOT NULL default 'false',\n  `dbver` mediumint(8) unsigned default NULL,\n  `adminschema` text,\n  `shopaction` text,\n  `installed` enum('true','false') NOT NULL default 'false',\n  PRIMARY KEY  (`pkg_id`)\n)type = MyISAM DEFAULT CHARACTER SET utf8;";
?>
