<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_level extends shopobject
{

    var $idColumn = "member_lv_id";
    var $adminCtl = "member/level";
    var $textColumn = "name";
    var $defaultCols = "name,lv_type,dis_count,pre_id,default_lv,experience";
    var $defaultOrder = array
    (
        0 => "point",
        1 => "ASC",
        2 => ",dis_count",
        3 => "DESC"
    );
    var $tableName = "sdb_member_lv";

    function modifier_dis_count( &$rows )
    {
        foreach ( $rows as $k => $v )
        {
            $rows[$k] = ( $v * 100 )."%";
        }
    }

    function modifier_point( &$row )
    {
        if ( is_array( $row ) )
        {
            foreach ( $rows as $k => $v )
            {
                $row[$k] = intval( $v );
            }
        }
        else
        {
            $row = intval( $row );
        }
    }

    function getmlevel( $sLv = null )
    {
        $aTemp = $aLevel = array( );
        if ( $sLv == null || $sLv == "" )
        {
            $aTemp = $this->db->select( "SELECT member_lv_id,name FROM sdb_member_lv WHERE disabled = 'false'" );
            return $aTemp;
        }
        $aTemp = $this->db->select( "SELECT member_lv_id,name FROM sdb_member_lv WHERE disabled = 'false' AND member_lv_id in(".$sLv.")" );
        return $aTemp;
    }

    function getfieldbyid( $nLvId )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_member_lv WHERE member_lv_id=".intval( $nLvId ) );
    }

    function recycle( $filter )
    {
        $data = $this->db->select( "select member_id from sdb_members where member_lv_id in(".implode( ",", $filter['member_lv_id'] ).")" );
        if ( 0 < count( $data ) )
        {
            echo __( "系统发现有会员使用该会员等级，请调整会员等级后再删除" );
            exit( );
        }
        return shopobject::recycle( $filter );
    }

    function savelevel( $aData )
    {
        $aData['default_lv'] = empty( $aData['default_lv'] ) ? 0 : 1;
        if ( $aData['lv_type'] == "wholesale" )
        {
            $aData['point'] = 0;
        }
        $nLvId = $aData['member_lv_id'];
        $aRs = $this->db->query( "SELECT * FROM sdb_member_lv WHERE member_lv_id=".intval( $nLvId ) );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        return !$sSql || $this->db->query( $sSql );
    }

    function checklevel( $aData, $action, $returnlv = 0 )
    {
        if ( $action == "INSERT" )
        {
            $sql = "select member_lv_id from sdb_member_lv where name=".$this->db->quote( $aData['lv_name'] );
        }
        if ( $action == "UPDATE" )
        {
            $sql = "select member_lv_id from sdb_member_lv where name=".$this->db->quote( $aData['name'] )." and member_lv_id <> ".intval( $aData['member_lv_id'] );
        }
        if ( $row = $this->db->selectrow( $sql ) )
        {
            if ( $returnlv )
            {
                return $row['member_lv_id'];
            }
            return true;
        }
        return false;
    }

    function checkmlevel( $aData, $action )
    {
        if ( !empty( $aData['default_lv'] ) )
        {
            if ( $action == "INSERT" )
            {
                $sql = "select member_lv_id from sdb_member_lv WHERE default_lv='1' ";
            }
            if ( $action == "UPDATE" )
            {
                $sql = "select member_lv_id from sdb_member_lv WHERE default_lv='1' and member_lv_id <> ".intval( $aData['member_lv_id'] );
            }
            if ( $this->db->selectrow( $sql ) )
            {
                return true;
            }
            return false;
        }
        return false;
    }

    function insertlevel( $aData, &$message )
    {
        if ( $aData['lv_type'] == "wholesale" )
        {
            $aData['point'] = 0;
        }
        $aData['name'] = $aData['lv_name'];
        $aRs = $this->db->query( "SELECT * FROM sdb_member_lv WHERE member_lv_id=0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        return !$sSql || $this->db->query( $sSql );
    }

    function getdefaulelv( )
    {
        $aTemp = $this->db->selectrow( "SELECT member_lv_id FROM sdb_member_lv WHERE default_lv='1'" );
        if ( $aTemp )
        {
            return $aTemp['member_lv_id'];
        }
        return "";
    }

    function checkfield( $sField, $sTable, $sWhere = "" )
    {
        return $this->db->selectrow( "SELECT ".$sField." FROM ".$sTable." ".$sWhere );
    }

    function dellevel( $aLvId )
    {
        $sSql = "DELETE FROM sdb_member_lv";
        if ( 0 < count( $aLvId ) )
        {
            $sSql .= " WHERE member_lv_id IN (".implode( ",", $aLvId ).")";
            $this->db->exec( "DELETE FROM sdb_goods_lv_price WHERE level_id IN (".implode( ",", $aLvId ).")" );
            return $this->db->exec( $sSql );
        }
        return false;
    }

    function checkmemlvtype( $mMemId )
    {
        $aRs = $this->db->selectrow( "SELECT lv.lv_type FROM sdb_members m\n                                                        LEFT JOIN sdb_member_lv lv\n                                                        ON m.member_lv_id=lv.member_lv_id\n                                                        WHERE m.member_id=".intval( $mMemId ) );
        if ( $aRs )
        {
            return $aRs['lv_type'];
        }
        return $aRs;
    }

}

?>
