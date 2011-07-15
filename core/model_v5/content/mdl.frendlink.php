<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_frendlink extends shopObject
{

    public $idColumn = "link_id";
    public $textColumn = "link_name";
    public $adminCtl = "content/frendlink";
    public $defaultCols = "link_name,href,image_url,orderlist";
    public $defaultOrder = array
    (
        0 => "orderlist",
        1 => "desc"
    );
    public $tableName = "sdb_link";

    public function getFieldById( $link_id, $aPara )
    {
        $sqlString = "SELECT ".implode( ",", $aPara )." FROM sdb_link WHERE link_id = ".intval( $link_id );
        return $this->db->selectrow( $sqlString );
    }

    public function save( $aData, &$msg )
    {
        $storager =& $this->system->loadModel( "system/storager" );
        if ( $_FILES )
        {
            $aData['image_url'] = $storager->save_upload( $_FILES['link_logo'], "link" );
            if ( !$aData['image_url'] )
            {
                unset( $aData['image_url'] );
            }
        }
        if ( $aData['link_id'] )
        {
            $rs = $this->db->query( "SELECT * FROM ".$this->tableName." WHERE link_id=".intval( $aData['link_id'] ) );
            $sql = $this->db->getUpdateSql( $rs, $aData );
        }
        else
        {
            unset( $aData['link_id'] );
            $rs = $this->db->query( "SELECT * FROM ".$this->tableName." WHERE 0=1" );
            $sql = $this->db->getInsertSql( $rs, $aData );
        }
        if ( !$sql || $this->db->exec( $sql ) )
        {
            $msg = __( "保存成功" );
            return true;
        }
        else
        {
            $msg = __( "保存失败" );
            return false;
        }
    }

}

?>
