<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* ShopEx database update script                               */\n/*                                                             */\n/*         Version:  from 11705 to 12149                       */\n/*   last Modified:  2008/07/14                                */\n/*=============================================================*/\n\nALT";
echo "ER TABLE `sdb_advance_logs` CHANGE COLUMN `money` `money` decimal(20,3) NOT NULL default 0 ;";
?>
