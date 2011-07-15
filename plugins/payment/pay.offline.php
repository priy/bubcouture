<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_offline extends paymentPlugin
{

    public $name = "线下支付";
    public $logo = "";
    public $version = 200080519;
    public $charset = "gb2312";
    public $supportCurrency = array
    (
        "ALL" => "1"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY"
    );
    public $desc = "线下支付";
    public $orderby = 6;

    public function getfields( )
    {
        return array( );
    }

}

?>
