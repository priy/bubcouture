<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_deliverycorp extends shopObject
{

    public $idColumn = "corp_id";
    public $textColumn = "corp_id";
    public $defaultCols = "name,website,ordernum";
    public $adminCtl = "trading/deliverycorp";
    public $defaultOrder = array
    (
        0 => "ordernum",
        1 => "desc"
    );
    public $tableName = "sdb_dly_corp";

    public function getCorpList( )
    {
        $sql = "select corp_id,name from sdb_dly_corp where disabled='false' order by ordernum desc";
        return $this->db->select( $sql );
    }

}

?>
