<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_deliveryarea extends shopobject
{

    var $idColumn = "area_id";
    var $textColumn = "area_id";
    var $defaultCols = "name,ordernum";
    var $adminCtl = "trading/deliveryarea";
    var $defaultOrder = array
    (
        0 => "ordernum",
        1 => "desc"
    );
    var $tableName = "sdb_dly_area";
    var $IdGroup = array( );

    function gettreesize( )
    {
        $sql = "select count(region_id) as rcount from sdb_regions";
        $row = $this->db->selectrow( $sql );
        if ( 100 < $row['rcount'] )
        {
            return true;
        }
        return false;
    }

    function getbyid( $regionId = "" )
    {
        return $this->db->selectrow( "select local_name from sdb_regions where region_id=".intval( $regionId ) );
    }

    function getregionbyid( $regionId = "" )
    {
        $sql = "select region_id,p_region_id,local_name,ordernum,region_path from sdb_regions as r where r.p_region_id".( $regionId ? "=".intval( $regionId ) : " is null" )." order by ordernum asc,region_id asc";
        $aTemp = $this->db->select( $sql );
        if ( is_array( $aTemp ) && 0 < count( $aTemp ) )
        {
            foreach ( $aTemp as $key => $val )
            {
                $aTemp[$key]['p_region_id'] = intval( $val['p_region_id'] );
                $aTemp[$key]['step'] = intval( substr_count( $val['region_path'], "," ) ) - 1;
                $aTemp[$key]['child_count'] = $this->getchildcount( $val['region_id'] );
            }
        }
        return $aTemp;
    }

    function getchildcount( $region_id )
    {
        $row = $this->db->selectrow( "select count(*) as childCount from sdb_regions where p_region_id=".intval( $region_id ) );
        return $row['childCount'];
    }

    function getregionbyparentid( $parentId )
    {
        $sql = "select region_id,local_name from sdb_regions where region_id=".intval( $parentId );
        return $this->db->selectrow( $sql );
    }

    function toremovearea( $regionId )
    {
        $tmpRow = $this->db->selectrow( "select region_path from sdb_regions where region_id=".intval( $regionId ) );
        $this->db->exec( "DELETE FROM sdb_regions where region_id=".intval( $regionId ) );
        $this->toremovesubarea( $tmpRow['region_path'] );
        return true;
    }

    function toremovesubarea( $path )
    {
        if ( $path )
        {
            return $this->db->exec( "DELETE FROM sdb_regions where region_path LIKE '%".$path."%'" );
        }
    }

    function getallchild_ex( $regionId )
    {
        $sql = "select region_path from sdb_regions where region_id=".intval( $regionId );
        $tmpRow = $this->db->selectrow( $sql );
        $sql = "select region_id from sdb_regions where region_path like '%".$tmpRow['region_path']."%'";
        $row = $this->db->select( $sql );
        if ( is_array( $row ) && 0 < count( $row ) )
        {
            foreach ( $row as $key => $val )
            {
                $region_Id[] = $val['region_id'];
            }
        }
        return $region_Id;
    }

    function getallchild( $regionId )
    {
        unset( $this->'IdGroup' );
        $sql = "select region_path from sdb_regions where region_id=".intval( $regionId );
        $tmpRow = $this->db->selectrow( $sql );
        $sql = "select region_id from sdb_regions where region_path like '%".$tmpRow['region_path']."%'";
        $row = $this->db->select( $sql );
        if ( is_array( $row ) && 0 < count( $row ) )
        {
            foreach ( $row as $key => $val )
            {
                $this->IdGroup[] = $val['region_id'];
            }
        }
    }

    function updateordernum( $param )
    {
        if ( is_array( $param ) && 0 < count( $param ) )
        {
            foreach ( $param as $key => $val )
            {
                $val = $val ? $val : "50";
                $this->db->exec( "UPDATE sdb_regions set ordernum=".intval( $val )." where region_id=".intval( $key ) );
            }
        }
        return true;
    }

    function insertdlarea( $aData, &$msg )
    {
        if ( !trim( $aData['local_name'] ) )
        {
            $msg = __( "地区名称不能为空！" );
            return false;
        }
        $aData['ordernum'] = $aData['ordernum'] ? $aData['ordernum'] : "50";
        if ( $this->checkdlarea( $aData['local_name'], $aData['p_region_id'] ) )
        {
            $msg = __( "该地区名称已经存在！" );
            return false;
        }
        $tmp = $this->db->selectrow( "select region_path from sdb_regions where region_id=".intval( $aData['p_region_id'] ) );
        if ( !$tmp )
        {
            $tmp['region_path'] = ",";
        }
        $aData = array_filter( $aData );
        $aRs = $this->db->query( "SELECT * FROM sdb_regions WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            $regionId = $this->db->lastinsertid( );
            $tmp['region_path'] = $tmp['region_path'].$regionId.",";
            $tmp['region_grade'] = count( explode( ",", $tmp['region_path'] ) ) - 2;
            $this->db->exec( $sql = "UPDATE sdb_regions set region_path=".$this->db->quote( $tmp['region_path'] ).",region_grade=".intval( $tmp['region_grade'] )." where region_id=".intval( $regionId ) );
            return true;
        }
        return false;
    }

    function getgroupregionid( $regionId )
    {
        $row = $this->db->selectrow( $sql = "select region_path from sdb_regions where region_id=".intval( $regionId ) );
        $path = $row['region_path'];
        $rows = $this->db->select( $sql = "select region_id from sdb_regions where region_path like '%".$path."%' and region_id<>".intval( $regionId ) );
        if ( $rows )
        {
            foreach ( $rows as $key => $val )
            {
                $idGroup[] = $val['region_id'];
            }
            return $idGroup;
        }
    }

    function updatedlarea( $aData, &$msg )
    {
        if ( $aData['region_id'] == $aData['p_region_id'] )
        {
            $msg = __( "上级地区不能为本地区！" );
            return false;
        }
        $idGroup = $this->getgroupregionid( $aData['region_id'] );
        if ( in_array( $aData['p_region_id'], $idGroup ) )
        {
            $msg = __( "上级地区不能为本地区的子地区！" );
            return false;
        }
        if ( !$aData['region_id'] )
        {
            $msg = __( "参数丢失！" );
            return false;
        }
        $cPath = $this->db->selectrow( "select region_path from sdb_regions where region_id=".intval( $aData['region_id'] ) );
        if ( !trim( $aData['local_name'] ) )
        {
            $msg = __( "地区名称不能为空！" );
            return false;
        }
        if ( intval( $aData['p_region_id'] ) )
        {
            $tmp = $this->db->selectrow( "select region_path from sdb_regions where region_id=".intval( $aData['p_region_id'] ) );
            $aData['region_path'] = $tmp['region_path'].$aData['region_id'].",";
        }
        else
        {
            $aData['region_path'] = ",".$aData['region_id'].",";
        }
        $aData['ordernum'] = $aData['ordernum'] ? $aData['ordernum'] : "50";
        $aData['region_grade'] = count( explode( ",", $aData['region_path'] ) ) - 2;
        $aData = array_filter( $aData );
        $aRs = $this->db->query( "SELECT * FROM sdb_regions WHERE region_id=".$aData['region_id'] );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        $this->updatesubpath( $cPath['region_path'], $aData['region_path'] );
        return !$sSql || $this->db->exec( $sSql );
    }

    function updatesubpath( $Opath, $Npath )
    {
        $offset = count( explode( ",", $Npath ) ) - count( explode( ",", $Opath ) );
        return $this->db->exec( "update sdb_regions set region_path=replace(region_path,".$this->db->quote( $Opath ).",".$this->db->quote( $Npath )."),region_grade=region_grade + ".intval( $offset )." where region_path LIKE '%".$Opath."%'" );
    }

    function getdlareabyid( $aRegionId )
    {
        $sql = "select c.region_id,c.local_name,c.p_region_id,c.ordernum,p.local_name as parent_name from sdb_regions as c LEFT JOIN sdb_regions as p ON p.region_id=c.p_region_id where c.region_id=".intval( $aRegionId );
        return $this->db->selectrow( $sql );
    }

    function checkdlarea( $name, $p_region_id )
    {
        $aTemp = $this->db->selectrow( "SELECT region_id FROM sdb_regions WHERE local_name='".$name."' and p_region_id".( $p_region_id ? "=".intval( $p_region_id ) : " is null" ) );
        return $aTemp['region_id'];
    }

    function getmap( $prId = "" )
    {
        if ( $prId )
        {
            $sql = "select region_id,region_grade,local_name,ordernum,(select count(*) from sdb_regions where p_region_id=r.region_id) as child_count from sdb_regions as r where r.p_region_id=".intval( $prId )." order by ordernum asc,region_id";
        }
        else
        {
            $sql = "select region_id,region_grade,local_name,ordernum,(select count(*) from sdb_regions where p_region_id=r.region_id) as child_count from sdb_regions as r where r.p_region_id is null order by ordernum asc,region_id";
        }
        $row = $this->db->select( $sql );
        foreach ( $row as $key => $val )
        {
            $this->regions[] = array(
                "local_name" => $val['local_name'],
                "region_id" => $val['region_id'],
                "region_grade" => $val['region_grade'],
                "ordernum" => $val['ordernum']
            );
            if ( $val['child_count'] )
            {
                $this->getmap( $val['region_id'] );
            }
        }
    }

}

?>
