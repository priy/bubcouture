<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$shopex47 = array( "/^catalog_([0-9]+)\\.html/" => "gallery|index|\$1", "/^catalog\\.html/" => "gallery|index", "/^list_([0-9]+)\\.html/" => "gallery|index|\$1", "/^list\\.html/" => "gallery|index", "/^member\\.html/" => "member|index", "/^feedback\\.html/" => "message|index", "/^feedback_([0-9]+)\\.html/" => "message|index|\$1", "/^product_([0-9]+)\\.html/" => "product|index|\$1", "/^bulletin_([0-9]+)\\.html/" => "article|index|\$1", "/^message_([0-9]+)\\.html/" => "article|index|\$1", "/^product\\/([0-9]+)\\.html/" => "product|index|\$1", "/^catalog_([0-9]+)_([0-9]+)\\.html/" => "gallery|index|\$1||0||\$2", "/^([0-9]+)\\.html/" => "product|index|\$1", "/^([0-9]+)_([^.]*)\\.html/" => "product|index|\$1", "/^bulletin\\.html/" => "artlist|index|1" );
$map =& $shopex47;
?>
