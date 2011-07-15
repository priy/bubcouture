<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_frendlink extends shopobject
{

    var $idColumn = "link_id";
    var $textColumn = "link_name";
    var $adminCtl = "content/frendlink";
    var $defaultCols = "link_name,href,image_url,orderlist";
    var $defaultOrder = array
    (
        0 => "orderlist",
        1 => "desc"
    );
    var $tableName = "sdb_link";

    function getfieldbyid( $link_id, $aPara )
    {
        $sqlString = "SELECT ".implode( ",", $aPara )." FROM sdb_link WHERE link_id = ".intval( $link_id );
        return $this->db->selectrow( $sqlString );
    }

    function save( $aData, &$msg )
    {
        $storager =& $this->system->loadmodel( "system/storager" );
        if ( $_FILES )
        {
            $aData['image_url'] = $storager->save_upload( $_FILES['link_logo'], "link" );
            if ( !$aData['image_url'] )
            {
                unset( $aData->'image_url' );
            }
        }
        if ( $aData['link_id'] )
        {
            $rs = $this->db->query( "SELECT * FROM ".$this->tableName." WHERE link_id=".intval( $aData['link_id'] ) );
            $sql = $this->db->getupdatesql( $rs, $aData );
        }
        else
        {
            unset( $aData->'link_id' );
            $rs = $this->db->query( "SELECT * FROM ".$this->tableName." WHERE 0=1" );
            $sql = $this->db->getinsertsql( $rs, $aData );
        }
        if ( !$sql && $this->db->exec( $sql ) )
        {
            $msg = __( "保存成功" );
            return true;
        }
        $msg = __( "保存失败" );
        return false;
    }

}

?>
