<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_paymentcfg extends shopobject
{

    var $adminCtl = "order/payment";
    var $idColumn = "id";
    var $textColumn = "custom_name";
    var $defaultCols = "custom_name,pay_type,orderlist";
    var $defaultOrder = array
    (
        0 => "orderlist",
        1 => "desc",
        2 => ",",
        3 => "id",
        4 => "ASC"
    );
    var $tableName = "sdb_payment_cfg";

}

?>
