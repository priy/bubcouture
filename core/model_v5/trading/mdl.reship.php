<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( dirname( __FILE__ )."/mdl.delivery.php" );
class mdl_reship extends mdl_delivery
{

    public $idColumn = "delivery_id";
    public $adminCtl = "order/reship";
    public $textColumn = "delivery_id";
    public $defaultCols = "delivery_id,order_id,t_begin,member_id,money,is_protect,ship_name,delivery,logi_name,logi_no";
    public $defaultOrder = "t_begin DESC";
    public $tableName = "sdb_delivery";

    public function toCreate( &$data )
    {
        $data['delivery_id'] = $this->getNewNumber( $data['type'] );
        $rs = $this->db->query( "select * from sdb_delivery where 0=1" );
        $sqlString = $this->db->GetInsertSQL( $rs, $data );
        return $this->db->exec( $sqlString ) ? $data['delivery_id'] : false;
    }

    public function toInsertItem( &$data )
    {
        if ( $data['type'] == "delivery" )
        {
            switch ( $data['status'] )
            {
            case "succ" :
                $dly_status = "customer";
                break;
            case "ready" :
            case "failed" :
            case "cancel" :
                $dly_status = "storage";
                break;
            case "lost" :
            case "porgress" :
                $dly_status = "shipping";
                break;
            }
        }
        $this->db->exec( "update sdb_order_items set dly_status=\"{$dly_status}\" where order_id = ".$this->db->quote( $data['order_id'] )." and product_id = ".intval( $data['product_id'] ) );
        $rs = $this->db->query( "select * from sdb_delivery_item where 0=1" );
        $sqlString = $this->db->GetInsertSQL( $rs, $data );
        return $this->db->query( $sqlString );
    }

    public function getColumns( )
    {
        $data = parent::getcolumns( );
        unset( $data['_cmd'] );
        $data['delivery_id']['label'] = __( "退货单号" );
        $data['ship_name']['label'] = __( "退货人" );
        $data['ship_area']['label'] = __( "退货地区" );
        $data['ship_addr']['label'] = __( "退货地址" );
        $data['ship_zip']['label'] = __( "退货邮编" );
        $data['ship_tel']['label'] = __( "退货人电话" );
        $data['ship_email']['label'] = __( "退货人Email" );
        return $data;
    }

    public function searchOptions( )
    {
        $arr = parent::searchoptions( );
        $arr['delivery_id'] = __( "退货单号" );
        return array_merge( $arr, array(
            "uname" => __( "会员用户名" )
        ) );
    }

    public function _filter( $filter )
    {
        $filter['type'] = "return";
        $where = array( 1 );
        if ( isset( $filter['delivery_id'] ) )
        {
            if ( is_array( $filter['delivery_id'] ) )
            {
                if ( $filter['delivery_id'][0] != "_ALL_" )
                {
                    if ( !isset( $filter['delivery_id'][1] ) )
                    {
                        $where[] = "delivery_id = ".$this->db->quote( $filter['delivery_id'][0] )."";
                    }
                    else
                    {
                        $aOrder = array( );
                        foreach ( $filter['delivery_id'] as $delivery_id )
                        {
                            $aOrder[] = "delivery_id=".$this->db->quote( $delivery_id )."";
                        }
                        $where[] = "(".implode( " OR ", $aOrder ).")";
                        unset( $aOrder );
                    }
                }
            }
            else
            {
                $where[] = "delivery_id = ".$this->db->quote( $filter['delivery_id'] )."";
            }
            unset( $filter['delivery_id'] );
        }
        if ( array_key_exists( "uname", $filter ) && trim( $filter['uname'] ) != "" )
        {
            $user_data = $this->db->select( "select member_id from sdb_members where uname = '".addslashes( $filter['uname'] )."'" );
            foreach ( $user_data as $tmp_user )
            {
                $now_user[] = $tmp_user['member_id'];
            }
            $where[] = "member_id IN ('".implode( "','", $now_user )."')";
            unset( $filter['uname'] );
        }
        else if ( isset( $filter['uname'] ) )
        {
            unset( $filter['uname'] );
        }
        return parent::_filter( $filter )." and ".implode( " AND ", $where );
    }

    public function getNewNumber( $type )
    {
        if ( $type == "return" )
        {
            $sign = 9;
        }
        else
        {
            $sign = 1;
        }
        $sqlString = "SELECT MAX(delivery_id) AS maxno FROM sdb_delivery\n                WHERE type='delivery' AND delivery_id like '".$sign.date( "Ymd", time( ) )."%'";
        $aRet = $this->db->selectrow( $sqlString );
        if ( is_null( $aRet['maxno'] ) )
        {
            $aRet['maxno'] = 0;
        }
        $orderid = substr( $aRet['maxno'], -6 ) + 1;
        if ( $orderid == 1000000 )
        {
            $orderid = 1;
        }
        return $sign.date( "Ymd" ).substr( "00000".$orderid, -6 );
    }

    public function getDlTypeList( )
    {
        return $this->db->select( "SELECT dt_id, dt_name, ordernum FROM sdb_dly_type ORDER BY ordernum desc" );
    }

    public function getDlTypeById( $nDlid )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_dly_type WHERE dt_id=".$nDlid );
    }

    public function saveRelation( $nDid, $aData )
    {
        foreach ( $aData as $val )
        {
            $val['dt_id'] = $nDid;
            $aRs = $this->db->query( "SELECT * FROM sdb_dly_h_area WHERE 0" );
            $sSql = $this->db->GetInsertSql( $aRs, $val );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
        }
        return true;
    }

    public function checkDlType( $sName )
    {
        $aTemp = $this->db->selectrow( "SELECT dt_id FROM sdb_dly_type WHERE dt_name='".$sName."' order by ordernum desc" );
        return $aTemp['dt_id'];
    }

    public function deleteDlType( $aId )
    {
        if ( $aId )
        {
            $sSql = "DELETE FROM sdb_dly_type WHERE dt_id IN (".$aId.")";
            return $this->db->exec( $sSql );
        }
        else
        {
            return false;
        }
    }

    public function checkDlArea( $sName )
    {
        $aTemp = $this->db->selectrow( "SELECT area_id FROM sdb_dly_area WHERE name='".$sName."' order by ordernum desc" );
        return $aTemp['area_id'];
    }

    public function getDlAreaList( )
    {
        return $this->db->select( "SELECT * FROM sdb_dly_area WHERE 1" );
    }

    public function getDlAreaById( $aAreaId )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_dly_area WHERE area_id=".$aAreaId );
    }

    public function insertDlArea( $aData, &$msg )
    {
        if ( !trim( $aData['name'] ) )
        {
            $msg = __( "地区名称不能为空！" );
            return false;
        }
        if ( $this->checkDlArea( $aData['name'] ) )
        {
            $msg = __( "该地区名称已经存在！" );
            return false;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_area WHERE 0" );
        $sSql = $this->db->GetInsertSql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    public function updateDlArea( $aData, &$msg )
    {
        if ( !$aData['area_id'] )
        {
            $msg = __( "参数丢失！" );
            return false;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_area WHERE area_id=".$aData['area_id'] );
        $sSql = $this->db->GetUpdateSql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    public function deleteDlArea( $sId )
    {
        $sSql = "";
        if ( $sId )
        {
            $sSql = $sId ? "DELETE FROM sdb_dly_area WHERE area_id in (".$sId.")" : "";
        }
        return !$sSql || $this->db->exec( $sSql );
    }

    public function assistantInsertArea( $aData )
    {
        if ( $this->checkDlArea( $aData['name'] ) )
        {
            return -1;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_area WHERE 0" );
        $sSql = $this->db->GetInsertSql( $aRs, $aData );
        if ( !$sSql || $this->db->exec( $sSql ) )
        {
            return $this->db->lastInsertId( );
        }
        else
        {
            return 0;
        }
    }

    public function getCropList( )
    {
        return $this->db->select( "SELECT * FROM sdb_dly_corp WHERE 1 order by ordernum desc" );
    }

    public function getCorpById( $nCorpId )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_dly_corp WHERE corp_id=".$nCorpId );
    }

    public function checkCorp( $sName )
    {
        $aTemp = $this->db->selectrow( "SELECT corp_id FROM sdb_dly_corp WHERE name='".$sName."'" );
        return $aTemp['corp_id'];
    }

    public function insertCorp( $aData, &$msg )
    {
        if ( $this->checkCorp( $aData['name'] ) )
        {
            $msg = __( "该物流公司已经存在！" );
            return false;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_corp WHERE 0" );
        $sSql = $this->db->GetInsertSql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    public function updateCorp( $aData, &$msg )
    {
        if ( !$aData['corp_id'] )
        {
            $msg = __( "参数丢失！" );
            return false;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_corp WHERE corp_id=".$aData['corp_id'] );
        $sSql = $this->db->GetUpdateSql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    public function deleteCorp( $sId )
    {
        if ( $sId )
        {
            $sSql = "DELETE FROM sdb_dly_corp WHERE corp_id IN (".$sId.")";
            return $this->db->exec( $sSql );
        }
        return false;
    }

    public function toRemove( $id )
    {
        $sqlString = "DELETE FROM sdb_delivery WHERE delivery_id='".$id."'";
        $this->db->exec( $sqlString );
        $sqlString = "DELETE FROM sdb_delivery_item WHERE delivery_id='".$id."'";
        $this->db->exec( $sqlString );
    }

}

?>
