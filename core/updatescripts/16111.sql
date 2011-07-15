<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "/*=============================================================*/\n/* Manual ever                                                  */\n/*=============================================================*/\nUPDATE `sdb_products` SET goods_id = 0 WHERE goods_id IS NULL;\nUPDATE `sdb_members` SET addr = CONCAT(province,city,addr);\nUPDATE `sdb_member_addrs` SET addr = CONCAT(country,province,city,addr);";
?>
