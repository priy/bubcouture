<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* ShopEx database update script                               */\n/*                                                             */\n/*         Version:  from 11639 to 11705                       */\n/*   last Modified:  2008/07/09                                */\n/*=============================================================*/\n\nALT";
echo "ER TABLE `sdb_gift` CHANGE COLUMN `insert_time` `insert_time` int(10) NOT NULL default '0' ;\nALTER TABLE `sdb_gift` CHANGE COLUMN `update_time` `update_time` int(10) NOT NULL default '0' ;";
?>
