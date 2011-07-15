<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_dly_centers extends shopobject
{

    var $idColumn = "dly_center_id";
    var $textColumn = "name";
    var $defaultCols = "name,region,address,area_id,zip,phone,uname";
    var $adminCtl = "trading/delivery_centers";
    var $defaultOrder = array
    (
        0 => "dly_center_id",
        1 => "desc"
    );
    var $tableName = "sdb_dly_center";

    function getcolumns( $filter )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "order/dly_center_command.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

}

?>
