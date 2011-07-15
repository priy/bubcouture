<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* ShopEx database update script                               */\n/*                                                             */\n/*         Version:  from 12659 to 13035                       */\n/*   last Modified:  2008/07/29                                */\n/*=============================================================*/\n\nALT";
echo "ER TABLE `sdb_sitemaps` ADD COLUMN `hidden` enum('true','false') NOT NULL default 'false' ;\nALTER TABLE `sdb_sitemaps` ADD INDEX `index_1`(`hidden`);\nALTER TABLE `sdb_sitemaps` AUTO_INCREMENT = 151;\nUPDATE sdb_payment_cfg SET pay_type = 'alipay' WHERE pay_type = 'ALIPAY';\nUPDATE sdb_payment_cfg SET pay_type = 'alipaytrad' WHERE pay_type = 'ALIPAYTRAD';\nUPDATE sdb_promotion set pmts_id = substring(";
echo "pmts_id,5) where pmts_id like \"pmt_%\";";
?>
