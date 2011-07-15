<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_deliverycorp extends shopobject
{

    var $idColumn = "corp_id";
    var $textColumn = "corp_id";
    var $defaultCols = "name,website,ordernum";
    var $adminCtl = "trading/deliverycorp";
    var $defaultOrder = array
    (
        0 => "ordernum",
        1 => "desc"
    );
    var $tableName = "sdb_dly_corp";

    function getcorplist( )
    {
        $sql = "select corp_id,name from sdb_dly_corp where disabled='false' order by ordernum desc";
        return $this->db->select( $sql );
    }

}

?>
