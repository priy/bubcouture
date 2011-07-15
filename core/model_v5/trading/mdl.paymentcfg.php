<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_paymentcfg extends shopObject
{

    public $adminCtl = "order/payment";
    public $idColumn = "id";
    public $textColumn = "custom_name";
    public $defaultCols = "custom_name,pay_type,orderlist";
    public $defaultOrder = array
    (
        0 => "orderlist",
        1 => "desc",
        2 => ",",
        3 => "id",
        4 => "ASC"
    );
    public $tableName = "sdb_payment_cfg";

}

?>
