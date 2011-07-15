<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_delivery extends shopobject
{

    var $idColumn = "dt_id";
    var $textColumn = "dt_name";
    var $defaultCols = "dt_name,dt_status,protect,ordernum,ship_area";
    var $adminCtl = "trading/delivery";
    var $defaultOrder = array
    (
        0 => "ordernum",
        1 => "asc"
    );
    var $tableName = "sdb_dly_type";

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "delivery/command.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function getnewnumber( $type )
    {
        if ( $type == "return" )
        {
            $sign = "9".date( "Ymd" );
        }
        else
        {
            $sign = "1".date( "Ymd" );
        }
        $sqlString = "SELECT MAX(delivery_id) AS maxno FROM sdb_delivery WHERE delivery_id LIKE '".$sign."%'";
        $aRet = $this->db->selectrow( $sqlString );
        if ( is_null( $aRet['maxno'] ) )
        {
            $aRet['maxno'] = 0;
        }
        $maxno = substr( $aRet['maxno'], -6 ) + 1;
        if ( $maxno == 1000000 )
        {
            $maxno = 1;
        }
        return $sign.substr( "00000".$maxno, -6 );
    }

    function tocreate( &$data )
    {
        if ( !$data['delivery_id'] )
        {
            $data['delivery_id'] = $this->getnewnumber( $data['type'] );
        }
        $rs = $this->db->query( "SELECT * FROM sdb_delivery WHERE 0=1" );
        $sqlString = $this->db->getinsertsql( $rs, $data );
        if ( $this->db->exec( $sqlString ) )
        {
            return $data['delivery_id'];
        }
        return false;
    }

    function gettopdelivery( $limit )
    {
        $sql = "SELECT * FROM sdb_delivery order by t_begin DESC";
        return $this->db->selectlimit( $sql, $limit, 0 );
    }

    function toinsertitem( &$data, $hasPhysical = false, $type = "delivery", $status = "progress" )
    {
        if ( $data['item_type'] != "gift" )
        {
            if ( $type == "delivery" )
            {
                switch ( $status )
                {
                case "succ" :
                    $dly_status = "customer";
                    break;
                case "failed" :
                case "cancel" :
                    $dly_status = "storage";
                    break;
                case "lost" :
                case "ready" :
                    if ( $this->system->getconf( "system.fast_delivery_as_progress" ) )
                    {
                        break;
                    }
                case "progress" :
                    $this->db->exec( "UPDATE sdb_order_items SET sendnum = sendnum + ".intval( $data['number'] )." WHERE item_id = ".intval( $data['order_item_id'] ) );
                    $dly_status = "shipping";
                    $aDelivery =& $this->system->loadmodel( "goods/products" );
                    $aDelivery->toupdatestore( $data['product_id'], 0, $data['number'], $data['item_type'] );
                    if ( !count( $data['adjunct'] ) )
                    {
                        break;
                    }
                    foreach ( $data['adjunct'] as $pid => $num )
                    {
                        $aDelivery->toupdatestore( $pid, 0, $data['number'] * $num, $data['item_type'] );
                    }
                }
                else
                {
                    if ( !$hasPhysical )
                    {
                        break;
                    }
                    $this->db->exec( "UPDATE sdb_order_items SET sendnum = sendnum - ".intval( $data['number'] )." WHERE item_id = ".intval( $data['order_item_id'] ) );
                    $dly_status = "return";
                }
            }
            $this->db->exec( "UPDATE sdb_order_items SET dly_status=".$this->db->quote( $dly_status )." WHERE item_id = ".intval( $data['order_item_id'] ) );
        }
        else
        {
            $this->db->exec( "UPDATE sdb_gift_items SET sendnum = sendnum + ".intval( $data['number'] )." WHERE order_id = ".$data['order_id'] );
        }
        $rs = $this->db->query( "SELECT * FROM sdb_delivery_item where 0=1" );
        $sqlString = $this->db->getinsertsql( $rs, $data );
        if ( $this->db->exec( $sqlString ) )
        {
            return true;
        }
        return false;
    }

    function detail( $nDeliveryID )
    {
        return $this->db->selectrow( "select * from sdb_delivery where delivery_id=".$nDeliveryID );
    }

    function edit( $aDetail )
    {
        $rDelivery = $this->db->query( "select * from sdb_delivery where delivery_id=".$aDetail['delivery_id'] );
        unset( $aDetail->'delivery_id' );
        $sSQL = $this->db->getupdatesql( $rDelivery, $aDetail );
        if ( !$sSQL && $this->db->exec( $sSQL ) )
        {
            return true;
        }
        return false;
    }

    function getconsignlist( $order_no )
    {
        if ( !empty( $order_no ) )
        {
            $tmpsql .= " AND order_id ='".$order_no."'";
        }
        $sql = "select * from sdb_delivery where type=\"delivery\" ".$tmpsql;
        return $this->db->select( $sql );
    }

    function getreshiplist( $order_no )
    {
        if ( !empty( $order_no ) )
        {
            $tmpsql .= " AND order_id ='".$order_no."'";
        }
        $sql = "select * from sdb_delivery where type = \"return\" ".$tmpsql;
        return $this->db->select( $sql );
    }

    function consign( $nStart, $nLimit, $aParame )
    {
        if ( !$limit )
        {
            $limit = 20;
        }
        foreach ( $aParame as $k => $v )
        {
            if ( $k == "t_begin" && $v != "" )
            {
                $sTmp .= " and ".$k.">=\"".$v."\"";
            }
            else if ( $k == "t_end" && $v != "" )
            {
                $sTmp .= " and ".$k."<=\"".$v."\"";
            }
            else if ( $v != "" )
            {
                $sTmp .= " and ".$k."=\"".$v."\"";
            }
        }
        $aData = $this->db->selectrow( "select count(*) as total from sdb_delivery where type=\"delivery\"".$sTmp );
        $aData['main'] = $this->db->selectlimit( "select * from sdb_delivery where type=\"delivery\"".$sTmp, intval( $nLimit ), intval( $nStart ), false, true );
        return $aData;
    }

    function getdltypelist( )
    {
        return $this->db->select( "SELECT * FROM sdb_dly_type where disabled=\"false\" ORDER BY ordernum desc,dt_id" );
    }

    function getdltypebyarea( $areaid, $weight = 0, $method_id = null )
    {
        if ( $method_id )
        {
            $where = " and t.dt_id = ".intval( $method_id );
        }
        if ( substr( $areaid, 0, 8 ) == "mainland" )
        {
            $aTmp = explode( ":", $areaid );
            $areaid = $aTmp[2];
        }
        $rsall = array( );
        $rs1 = $this->db->select( "SELECT t.dt_id,t.dt_name, t.protect, t.detail ,a.config AS dt_config, t.minprice,t.protect_rate,a.expressions, a.has_cod AS pad, t.ordernum\n        FROM sdb_dly_type t INNER JOIN sdb_dly_h_area a ON t.dt_id = a.dt_id\n        WHERE t.disabled = 'false' AND t.dt_status = '1' AND a.areaid_group like '%,".intval( $areaid ).",%' ".$where." ORDER BY t.ordernum ASC , a.dha_id ASC" );
        foreach ( $rs1 as $val1 )
        {
            if ( !$rsall[$val1['dt_id']] )
            {
                $rsall[$val1['dt_id']] = $val1;
            }
        }
        $rs2 = $this->db->select( "SELECT t.dt_id,t.dt_name, t.has_cod AS pad, t.protect, t.dt_config,\n                            t.dt_expressions AS expressions ,t.detail,t.minprice,t.protect_rate, t.ordernum\n                            FROM sdb_dly_type t  WHERE t.disabled = 'false' AND t.dt_status = '1'\n                            AND ( dt_config LIKE '%\"setting\";s:11:\"setting_hda\"%'\n                            OR ( dt_config LIKE '%\"defAreaFee\";i:1%'  AND dt_config LIKE '%\"setting\";s:11:\"setting_sda\"%') ) ".$where." ORDER BY t.ordernum" );
        foreach ( $rs2 as $val2 )
        {
            $tpConf = unserialize( $val2['dt_config'] );
            if ( $rsall[$val2['dt_id']] && !( $tpConf['setting'] == "setting_hda" ) )
            {
                $rsall[$val2['dt_id']] = $val2;
            }
        }
        $rsall1 = array( );
        foreach ( $rsall as $rsv )
        {
            $rsall1[$rsv['ordernum']][] = $rsv;
        }
        ksort( $rsall1 );
        $rs = array( );
        foreach ( $rsall1 as $rsorderv )
        {
            foreach ( $rsorderv as $rsallv )
            {
                $rs[] = $rsallv;
            }
        }
        return $rs;
    }

    function gethascod( $shipping_id )
    {
        return $this->db->selectrow( "SELECT has_cod FROM sdb_dly_type WHERE dt_id=".intval( $shipping_id ) );
    }

    function getdltypebyid( $nDlid )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_dly_type WHERE dt_id=".intval( $nDlid ) );
    }

    function savedltype( $aData )
    {
        $config = array(
            "firstprice" => $aData['firstprice'],
            "firstunit" => $aData['firstunit'],
            "continueprice" => $aData['continueprice'],
            "continueunit" => $aData['continueunit'],
            "confexpressions" => $aData['confexpressions'],
            "setting" => $aData['setting'],
            "dt_useexp" => intval( $aData['dt_useexp'] ),
            "has_cod" => $aData['has_cod']
        );
        if ( $aData['protect'] )
        {
            $config['protectrate'] = $aData['protectrate'];
            $config['minprotectprice'] = $aData['minprotectprice'];
        }
        $aData['price'] = "0";
        $aData['dt_expressions'] = "{{w-0}-0.4}*{{{".$aData['firstunit']."-w}-0.4}+1}*".$aData['firstprice']."+ {{w-".$aData['firstunit']."}-0.6}*[(w-".$aData['firstunit'].")/".$aData['continueunit']."]*".$aData['continueprice']."";
        $aData['minprice'] = $aData['minprotectprice'] ? $aData['minprotectprice'] : 0;
        $aData['protect_rate'] = $aData['protectrate'] ? $aData['protectrate'] / 100 : 0;
        if ( !$aData['ordernum'] )
        {
            $aData['ordernum'] = 50;
        }
        if ( intval( $aData['dt_useexp'] ) )
        {
            $aData['dt_expressions'] = $aData['confexpressions'];
        }
        if ( $aData['dt_id'] )
        {
            $tmpRow = $this->db->selectrow( "select dt_config,has_cod from sdb_dly_type where dt_id=".intval( $aData['dt_id'] ) );
            $tmpConfig = unserialize( $tmpRow['dt_config'] );
            $config['firstprice'] = is_numeric( $aData['firstprice'] ) ? $aData['firstprice'] : $tmpConfig['firstprice'];
            $config['continueprice'] = is_numeric( $aData['continueprice'] ) ? $aData['continueprice'] : $tmpConfig['continueprice'];
            $config['confexpressions'] = !empty( $aData['confexpressions'] ) ? $aData['confexpressions'] : $tmpConfig['confexpressions'];
            $aData['protect'] = intval( $aData['protect'] );
            if ( $aData['setting'] == "setting_hda" )
            {
                $config['defAreaFee'] = $tmpConfig['defAreaFee'];
            }
            else
            {
                $config['defAreaFee'] = intval( $aData['defAreaFee'] );
            }
            $aData['dt_config'] = serialize( $config );
            $rs = $this->db->exec( "select * from sdb_dly_type where dt_id=".intval( $aData['dt_id'] ) );
            $sSql = $this->db->getupdatesql( $rs, $aData );
            if ( $this->db->exec( $sSql ) )
            {
                if ( $this->savedeliverarea( $aData['dt_id'], $aData ) )
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            $config['defAreaFee'] = intval( $aData['defAreaFee'] );
            $aData['dt_config'] = serialize( $config );
            $rs = $this->db->exec( "select * from sdb_dly_type where 0" );
            $sSql = $this->db->getinsertsql( $rs, $aData );
            if ( $this->db->exec( $sSql ) )
            {
                $dt_id = $this->db->lastinsertid( );
                if ( $this->savedeliverarea( $dt_id, $aData ) )
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
    }

    function checkdltype( &$data )
    {
        if ( $data['dt_id'] )
        {
            $sql = " AND dt_id != ".$data['dt_id'];
        }
        $aTemp = $this->db->selectrow( "SELECT dt_id FROM sdb_dly_type WHERE dt_name='".$data['dt_name']."'".$sql );
        return $aTemp['dt_id'];
    }

    function savedeliverarea( $dt_id, $aData )
    {
        if ( is_array( $aData['areaGroupId'] ) && 0 < count( $aData['areaGroupId'] ) )
        {
            $dArea =& $this->system->loadmodel( "trading/deliveryarea" );
            $toSaveId = array( );
            foreach ( $aData['areaGroupId'] as $key => $val )
            {
                $tmp = explode( ",", $val );
                unset( $tmpGroupId );
                if ( is_array( $tmp ) && 0 < count( $tmp ) )
                {
                    foreach ( $tmp as $k => $v )
                    {
                        if ( $v )
                        {
                            if ( strstr( $v, "|" ) )
                            {
                                $regionId = substr( $v, 0, strpos( $v, "|" ) );
                                $dArea->getallchild( $regionId );
                                if ( is_array( $dArea->IdGroup ) )
                                {
                                    $group = $this->db->selectrow( "select areaid_group from sdb_dly_h_area where dt_id =".$dt_id." and dha_id = ".$key );
                                    $area = explode( ",", $group['areaid_group'] );
                                    foreach ( $dArea->IdGroup as $dk => $dv )
                                    {
                                        foreach ( $area as $ak => $av )
                                        {
                                            if ( $dv == $av )
                                            {
                                                $tmpGroupId[] = $dv;
                                            }
                                        }
                                    }
                                    if ( !in_array( $regionId, $tmpGroupId ) )
                                    {
                                        $return = $dArea->getallchild_ex( $regionId );
                                        foreach ( $return as $dk_ex => $dv_ex )
                                        {
                                            $tmpGroupId[] = $dv_ex;
                                        }
                                    }
                                    unset( $dArea->'IdGroup' );
                                }
                            }
                            else
                            {
                                $tmpGroupId[] = $v;
                            }
                        }
                    }
                }
                $config = array(
                    "firstFee" => $aData['firstFee'][$key],
                    "continueFee" => $aData['continueFee'][$key],
                    "hasCod" => $aData['hasCod'][$key],
                    "expressions" => $aData['expressions'][$key],
                    "useexp" => $aData['useexp'][$key]
                );
                $tmpData = array(
                    "dt_id" => $dt_id,
                    "areaname_group" => $aData['areaGroupName'][$key],
                    "areaid_group" => ",".implode( ",", $tmpGroupId ).",",
                    "expressions" => $expressions,
                    "config" => serialize( $config ),
                    "has_cod" => intval( $aData['has_cod'] )
                );
                if ( $aData['useexp'][$key] )
                {
                    $tmpData['expressions'] = $aData['expressions'][$key];
                }
                else
                {
                    $tmpData['expressions'] = "{{w-0}-0.4}*{{{".$aData['firstunit']."-w}-0.4}+1}*".$aData['firstFee'][$key]."+ {{w-".$aData['firstunit']."}-0.6}*[(w-".$aData['firstunit'].")/".$aData['continueunit']."]*".$aData['continueFee'][$key];
                }
                $tRs = $this->db->selectrow( "select dt_id from sdb_dly_h_area where dt_id=".intval( $dt_id )." and dha_id=".intval( $key ) );
                if ( is_array( $tRs ) && 0 < count( $tRs ) )
                {
                    $rs = $this->db->exec( "select * from sdb_dly_h_area where dha_id=".intval( $key ) );
                    $sSql = $this->db->getupdatesql( $rs, $tmpData );
                    $this->db->exec( $sSql );
                }
                else
                {
                    $rs = $this->db->exec( "select * from sdb_dly_h_area where 0" );
                    $sSql = $this->db->getinsertsql( $rs, $tmpData );
                    $this->db->exec( $sSql );
                }
            }
        }
        $this->toremoveunarea( $dt_id, $aData['delidgroup'] );
        return true;
    }

    function toremoveunarea( $dt_id, $delidgroup )
    {
        if ( $delidgroup )
        {
            $sql = "DELETE FROM sdb_dly_h_area where dha_id in(".$delidgroup.")";
            if ( !$this->db->exec( $sql ) )
            {
                return false;
            }
        }
        return true;
    }

    function saverelation( $nDid, $aData )
    {
        foreach ( $aData as $val )
        {
            $val['dt_id'] = $nDid;
            $aRs = $this->db->query( "SELECT * FROM sdb_dly_h_area WHERE dt_id = ".intval( $val['dt_id'] )." AND area_id =".intval( $val['area_id'] ) );
            $sSql = $this->db->getupdatesql( $aRs, $val, true );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            $areaId[] = $val['area_id'];
        }
        $areaId[] = 0;
        $this->db->query( "DELETE FROM sdb_dly_h_area WHERE dt_id = ".intval( $nDid )." AND area_id NOT IN(".implode( ",", $areaId ).")" );
        return true;
    }

    function deletedltype( $aId )
    {
        if ( $aId )
        {
            $sSql = "DELETE FROM sdb_dly_type WHERE dt_id IN (".$aId.")";
            $this->db->exec( $sSql );
            $sSql = "DELETE FROM sdb_dly_h_area WHERE dt_id IN (".$aId.")";
            return $this->db->exec( $sSql );
        }
        return false;
    }

    function getcroplist( )
    {
        return $this->db->select( "SELECT * FROM sdb_dly_corp WHERE 1 order by ordernum desc" );
    }

    function getcorpbyid( $nCorpId )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_dly_corp WHERE corp_id=".$nCorpId );
    }

    function checkcorp( $sName )
    {
        $aTemp = $this->db->selectrow( "SELECT corp_id FROM sdb_dly_corp WHERE name = ".$this->db->quote( $sName ) );
        return $aTemp['corp_id'];
    }

    function insertcorp( $aData, &$msg )
    {
        if ( $this->checkcorp( $aData['name'] ) )
        {
            $msg = __( "该物流公司已经存在！" );
            return false;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_corp WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    function updatecorp( $aData, &$msg )
    {
        if ( !$aData['corp_id'] )
        {
            $msg = __( "参数丢失！" );
            return false;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_dly_corp WHERE corp_id=".$aData['corp_id'] );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        return !$sSql || $this->db->exec( $sSql );
    }

    function deletecorp( $sId )
    {
        if ( $sId )
        {
            $sSql = "DELETE FROM sdb_dly_corp WHERE corp_id IN (".$sId.")";
            return $this->db->exec( $sSql );
        }
        return false;
    }

    function getitemlist( $nDeliveryID )
    {
        return $this->db->select( "SELECT * FROM sdb_delivery_item WHERE delivery_id = '".$nDeliveryID."'" );
    }

    function checkdltypepay( $dt_id, $areaName )
    {
        $area_id = $this->checkdlarea( $areaName );
        $aRet = $this->getdltypebyarea( $area_id, 0, $dt_id );
        return $aRet[0];
    }

    function getcorpbyshipid( $shipId )
    {
        return $this->db->selectrow( "select corp_id from sdb_dly_type where dt_id='".$shipId."'" );
    }

    function getcorpinfobyshipid( $shipId )
    {
        $sql = "select dc.name,dc.website from sdb_dly_corp as dc LEFT JOIN sdb_dly_type as dt on dt.corp_id=dc.corp_id where dt.dt_id='".$shipId."'";
        return $this->db->selectrow( $sql );
    }

    function getregionbyid( $parent_id )
    {
        $sql = "select r.region_id,r.p_region_id,r.local_name,count(p.region_id) as child_count from sdb_regions as r\n                left join sdb_regions as p on r.region_id=p.p_region_id\n                where r.p_region_id".( $parent_id ? "=".intval( $parent_id ) : " is null" )." and r.package='".$this->system->getconf( "system.location" )."'\n                group by(r.region_id)\n                order by r.ordernum asc,r.region_id";
        return $this->db->select( $sql );
    }

    function getareabydtid( $dt_id )
    {
        return $this->db->select( "select * from sdb_dly_h_area where dt_id=".intval( $dt_id )." order by dha_id asc" );
    }

    function delete( $aData )
    {
        $res = shopobject::delete( $aData );
        if ( $res )
        {
            if ( $aData['dt_id'] )
            {
                $sql = "DELETE FROM sdb_dly_h_area where dt_id in (".implode( ",", $aData['dt_id'] ).")";
            }
            else
            {
                $sql = "DELETE FROM sdb_dly_h_area";
            }
            if ( !$this->db->exec( $sql ) )
            {
                return false;
            }
            return true;
        }
    }

    function getdlarealist( )
    {
        return $this->db->select( "SELECT * FROM sdb_regions where package=\"mainland\" and disabled=\"false\" order by ordernum desc" );
    }

}

?>
