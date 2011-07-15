<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* ShopEx database update script                               */\n/*                                                             */\n/*         Version:  from 33184 to 34365                       */\n/*   last Modified:  2009/10/23                                */\n/*=============================================================*/\n\n/*=";
echo "============================================================*/\n/* Create tables                                               */\n/*=============================================================*/\nCREATE TABLE `sdb_autosync_rule` (\n  `rule_id` mediumint(8) unsigned NOT NULL auto_increment,\n  `supplier_op_id` tinyint(4) NOT NULL default '0',\n  `local_op_id` tinyint(4) NOT NULL default '0',\n  `disable";
echo "d` enum('false','true') NOT NULL default 'false',\n  `memo` varchar(255) default NULL,\n  `rule_name` varchar(255) NOT NULL,\n  PRIMARY KEY  (`rule_id`),\n  KEY `index_1` (`rule_id`,`local_op_id`,`disabled`)\n)type = MyISAM DEFAULT CHARACTER SET utf8;\n\nCREATE TABLE `sdb_autosync_rule_relation` (\n  `rule_id` mediumint(8) unsigned NOT NULL default '0',\n  `supplier_id` int(10) unsigned NOT NULL default '0";
echo "',\n  `pline_id` mediumint(8) unsigned NOT NULL default '0',\n  KEY `rsp_index` (`rule_id`,`supplier_id`,`pline_id`),\n  KEY `supplier_id` (`supplier_id`)\n)type = MyISAM DEFAULT CHARACTER SET utf8;\n\nCREATE TABLE `sdb_autosync_task` (\n  `supplier_id` int(10) unsigned NOT NULL default '0',\n  `command_id` int(10) unsigned NOT NULL default '0',\n  `local_op_id` tinyint(3) unsigned default '0',\n  PRIMARY K";
echo "EY  (`supplier_id`,`command_id`)\n)type = MyISAM DEFAULT CHARACTER SET utf8;\n\nCREATE TABLE `sdb_cost_sync` (\n  `supplier_id` int(10) unsigned NOT NULL,\n  `bn` varchar(30) NOT NULL,\n  `cost` decimal(20,3) NOT NULL default '0.000',\n  `version_id` int(10) unsigned NOT NULL default '0',\n  `product_id` mediumint(8) unsigned NOT NULL default '0',\n  `goods_id` mediumint(8) unsigned NOT NULL default '0',\n ";
echo " PRIMARY KEY  (`supplier_id`,`bn`),\n  KEY `spid_gid` (`supplier_id`,`goods_id`)\n)type = MyISAM DEFAULT CHARACTER SET utf8;\n\n/*=============================================================*/\n/* New columns                                                 */\n/*=============================================================*/\nALTER TABLE `sdb_goods_type` ADD COLUMN `lastmodify` int(10) unsigned default ";
echo "NULL ;\nALTER TABLE `sdb_specification` ADD COLUMN `lastmodify` int(10) unsigned default NULL ;\n\n/*=============================================================*/\n/* Modify columns                                              */\n/*=============================================================*/\n\n/*=============================================================*/\n/* Index                               ";
echo "                        */\n/*=============================================================*/\nALTER TABLE `sdb_supplier_pdtbn` ADD INDEX `sp_srcbn`(`source_bn`,`supplier_id`);\n\n/*=============================================================*/\n/* Drop tables                                                 */\n/*=============================================================*/\n\n/*=======================";
echo "======================================*/\n/* Drop fields                                                 */\n/*=============================================================*/\n\n/*=============================================================*/\n/* Drop index                                                  */\n/*=============================================================*/\n";
?>
