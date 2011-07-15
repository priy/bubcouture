<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* ShopEx database update script                               */\n/*                                                             */\n/*         Version:  from 21246 to 27449                       */\n/*   last Modified:  2009/07/15                                */\n/*=============================================================*/\n\n/*=";
echo "============================================================*/\n/* Create tables                                               */\n/*=============================================================*/\nCREATE TABLE `sdb_globals` (\n  `glob_id` mediumint(8) unsigned NOT NULL auto_increment,\n  `glob_name` varchar(20) NOT NULL,\n  `glob_var` varchar(100) NOT NULL,\n  `glob_type` enum('system','custom') NOT NUL";
echo "L,\n  `glob_remark` varchar(100) NOT NULL,\n  `glob_value` text NOT NULL,\n  `disabled` enum('true','false') NOT NULL default 'false',\n  PRIMARY KEY  (`glob_id`)\n)type = MyISAM DEFAULT CHARACTER SET utf8;\n\n/*=============================================================*/\n/* New columns                                                 */\n/*=============================================================*/";
echo "\nALTER TABLE `sdb_orders` ADD COLUMN `extend` varchar(255) default NULL ;\n\n/*=============================================================*/\n/* Modify columns                                              */\n/*=============================================================*/\nALTER TABLE `sdb_operators` CHANGE COLUMN `lastip` `lastip` varchar(20) default NULL ;\nALTER TABLE `sdb_operators` CHANGE COLUM";
echo "N `op_no` `op_no` varchar(50) default NULL ;\nALTER TABLE `sdb_operators` CHANGE COLUMN `department` `department` varchar(50) default NULL ;\n\n/*=============================================================*/\n/* Index                                                       */\n/*=============================================================*/\n\n/*==========================================================";
echo "===*/\n/* Drop tables                                                 */\n/*=============================================================*/\n\n/*=============================================================*/\n/* Drop fields                                                 */\n/*=============================================================*/\n\n/*============================================================";
echo "=*/\n/* Drop index                                                  */\n/*=============================================================*/\n";
?>
