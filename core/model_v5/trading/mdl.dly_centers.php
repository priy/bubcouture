<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_dly_centers extends shopObject
{

    public $idColumn = "dly_center_id";
    public $textColumn = "name";
    public $defaultCols = "name,region,address,area_id,zip,phone,uname";
    public $adminCtl = "trading/delivery_centers";
    public $defaultOrder = array
    (
        0 => "dly_center_id",
        1 => "desc"
    );
    public $tableName = "sdb_dly_center";

    public function getColumns( $filter )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "order/dly_center_command.html"
            )
        );
        return array_merge( $ret, parent::getcolumns( ) );
    }

}

?>
