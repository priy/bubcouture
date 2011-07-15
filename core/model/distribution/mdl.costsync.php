<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_costsync extends modelfactory
{

    var $jobLimit = 100;

    function mdl_costsync( )
    {
        modelfactory::modelfactory( );
        $token = $this->system->getconf( "certificate.token" );
        $this->api = $this->system->api_call( PLATFORM, PLATFORM_HOST, PLATFORM_PATH, PLATFORM_PORT, $token );
    }

    function getapiparams( $supplier_id = 0, $isAdd = false )
    {
        $aApiParams = array(
            "api_name" => "getMemberPrice",
            "api_version" => API_VERSION,
            "api_params" => array( ),
            "api_action" => "distribution/costsync|doCostSyncJob",
            "limit" => $this->jobLimit
        );
        if ( $isAdd )
        {
            $version_id = $this->getmaxversionid( $supplier_id );
            $aApiParams['api_params']['version'] = $version_id;
            $aApiParams['api_params']['id'] = $supplier_id;
        }
        return $aApiParams;
    }

    function getcostsync( $supplier_id, $pages = 1, $limit = 0, $version_id = 0 )
    {
        $aApiParams = $this->getapiparams( $supplier_id, true );
        if ( empty( $limit ) )
        {
            $limit = $aApiParams['limit'];
        }
        if ( empty( $version_id ) )
        {
            $version_id = $aApiParams['api_params']['version'];
        }
        return $this->api->getapidata( $aApiParams['api_name'], $aApiParams['api_version'], array(
            "pages" => $pages,
            "counts" => $limit,
            "id" => $supplier_id,
            "version" => $version_id
        ), true, true );
    }

    function getcostsynccount( $supplier_id )
    {
        $aList = $this->getcostsync( $supplier_id, 1, 1 );
        if ( is_array( $aList ) )
        {
            return intval( $aList[0]['row_count'] );
        }
        return 0;
    }

    function generatecostsyncjob( $supplier_id )
    {
        $oSyncJob = $this->system->loadmodel( "distribution/syncjob" );
        $aApiParams = $this->getapiparams( $supplier_id, true );
        $oSyncJob->addapilistjob( $supplier_id, $aApiParams['api_name'], $aApiParams['api_params'], $aApiParams['api_version'], $aApiParams['api_action'], $aApiParams['limit'] );
    }

    function getcostsyncjob( $supplier_id )
    {
        $oSyncJob = $this->system->loadmodel( "distribution/syncjob" );
        $aApiParams = $this->getapiparams( $supplier_id );
        return $oSyncJob->doapilistjob( $supplier_id, $aApiParams['api_name'], $aApiParams['api_version'], $aApiParams['api_action'] );
    }

    function getcostsyncjobcount( $supplier_id )
    {
        $aApiParams = $this->getapiparams( $supplier_id );
        $aList = $this->db->selectrow( "SELECT count(*) AS num FROM sdb_job_apilist WHERE supplier_id = '".$supplier_id."' AND api_name='".$aApiParams['api_name']."' " );
        return $aList['num'];
    }

    function docostsyncjob( $supplier_id )
    {
        $version_id = $this->getcostsyncjobversion( $supplier_id );
        $aList = $this->getcostsyncjob( $supplier_id );
        if ( empty( $aList ) )
        {
            $this->updatecostsyncinfo( $supplier_id );
            $this->updateproductcost( $supplier_id );
            $this->updategoodscost( $supplier_id );
            return "done";
        }
        foreach ( $aList as $row )
        {
            $aData = array(
                "supplier_id" => $supplier_id,
                "bn" => $row['bn'],
                "version_id" => $row['version'],
                "cost" => doubleval( $row['price'] )
            );
            $this->addcostsync( $aData );
        }
        $this->updatecostsyncversion( $supplier_id, $version_id );
        return "continue";
    }

    function updatecostsyncversion( $supplier_id, $version_id )
    {
        $max_version_id = $this->getmaxversionid( $supplier_id );
        $rs = $this->db->exec( "SELECT * FROM sdb_cost_sync WHERE supplier_id=".$supplier_id." AND version_id > '".$version_id."'" );
        $sSql = $this->db->getupdatesql( $rs, array(
            "version_id" => $max_version_id
        ) );
        return $this->db->exec( $sSql );
    }

    function getcostsyncjobversion( $supplier_id )
    {
        $aApiParams = $this->getapiparams( );
        $sSql = "SELECT api_params FROM sdb_job_apilist WHERE api_name='".$aApiParams['api_name']."' AND supplier_id = '".$supplier_id."'";
        $aResult = $this->db->selectrow( $sSql );
        if ( empty( $aResult ) )
        {
            return 0;
        }
        $aResult = unserialize( $aResult['api_params'] );
        return $aResult['version'];
    }

    function addcostsync( $data )
    {
        $sSql = "REPLACE INTO sdb_cost_sync(`supplier_id`,`bn`,`version_id`,`cost`) \r\n                 VALUES('".$data['supplier_id']."','".$data['bn']."','".$data['version_id']."','".$data['cost']."')";
        return $this->db->exec( $sSql );
    }

    function getcostsynccols( $str )
    {
        if ( strpos( $str, "cost" ) )
        {
            return $str;
        }
        if ( strpos( $str, "mktprice" ) )
        {
            return str_replace( "mktprice", "mktprice,cost", $str );
        }
        if ( strpos( $str, "type_id" ) )
        {
            return str_replace( "type_id", "type_id,cost", $str );
        }
        if ( strpos( $str, "cat_id" ) )
        {
            return str_replace( "cat_id", "cat_id,cost", $str );
        }
        return str_replace( "name", "name,cost", $str );
    }

    function getmaxversionid( $supplier_id, $isexist = false )
    {
        $where = $isexist ? " AND goods_id <> 0" : "";
        $aList = $this->db->selectrow( "SELECT MAX(version_id) AS max_version_id FROM sdb_cost_sync WHERE supplier_id = '".$supplier_id."'".$where );
        return intval( $aList['max_version_id'] );
    }

    function getcostsyncamount( $supplier_id, $version_id = null )
    {
        $where[] = " supplier_id = '".$supplier_id."' ";
        if ( $version_id != null )
        {
            $where[] = " version_id = '".$version_id."' ";
        }
        $sSql = "SELECT count(*) AS num FROM sdb_cost_sync WHERE ".implode( " AND ", $where );
        $aResult = $this->db->selectrow( $sSql );
        return intval( $aResult['num'] );
    }

    function updatecostsyncinfo( $supplier_id, $goods_id = 0 )
    {
        $sWhere = $goods_id ? " AND p.goods_id = '".$goods_id."' " : "";
        $sSql = "UPDATE sdb_cost_sync AS c, sdb_products AS p,sdb_supplier_pdtbn AS s \r\n                 SET c.goods_id = p.goods_id,c.product_id = p.product_id \r\n                 WHERE s.supplier_id = c.supplier_id AND p.bn = s.local_bn AND c.bn = s.source_bn AND c.supplier_id = '".$supplier_id."'".$sWhere;
        return $this->db->exec( $sSql );
    }

    function updateproductcost( $supplier_id, $goods_id = 0, $version_id = 0 )
    {
        $sWhere = $goods_id ? " AND p.goods_id = '".$goods_id."' " : "";
        if ( empty( $version_id ) )
        {
            $version_id = $this->getmaxversionid( $supplier_id );
        }
        $sSql = "UPDATE sdb_products AS p,sdb_cost_sync AS c \r\n                 SET p.cost = c.cost \r\n                 WHERE p.product_id = c.product_id AND c.supplier_id = '".$supplier_id."' AND c.version_id = '".$version_id."'".$sWhere;
        return $this->db->exec( $sSql );
    }

    function updategoodscost( $supplier_id, $goods_id = 0, $version_id = 0 )
    {
        $sWhere = $goods_id ? " AND g.goods_id = '".$goods_id."' " : "";
        if ( empty( $version_id ) )
        {
            $version_id = $this->getmaxversionid( $supplier_id );
        }
        $sSql = "UPDATE sdb_cost_sync AS c,sdb_goods AS g\r\n                 SET g.cost = c.cost \r\n                 WHERE g.goods_id = c.goods_id AND c.supplier_id = '".$supplier_id."' AND c.version_id = '".$version_id."'".$sWhere;
        return $this->db->exec( $sSql );
    }

    function updatealoneproductcost( $product_id, $price )
    {
        $aResult = $this->db->selectrow( "SELECT goods_id,cost FROM sdb_products WHERE product_id='".$product_id."'" );
        if ( $aResult['cost'] == $price )
        {
            return "pass";
        }
        $sSql = "UPDATE sdb_products SET cost='".$price."' WHERE product_id='".$product_id."'";
        if ( !$this->db->exec( $sSql ) )
        {
            return "failure";
        }
        $aResult = $this->db->selectrow( "SELECT goods_id,MIN(cost) AS cost FROM sdb_products WHERE goods_id = '".$aResult['goods_id']."' GROUP BY goods_id" );
        if ( $aResult['cost'] < $price )
        {
            return "success";
        }
        $sSql = "UPDATE sdb_goods SET cost='".$price."' WHERE goods_id='".$aResult['goods_id']."'";
        if ( !$this->db->exec( $sSql ) )
        {
            return "failure";
        }
        return "success";
    }

    function getcostsyncstatus( $supplier_id )
    {
        $aResult['num'] = $this->getcostsyncamount( $supplier_id );
        return $aResult;
    }

    function getsuppliercostsyncstatus( $aSupplier )
    {
        foreach ( $aSupplier as $key => $row )
        {
            if ( !$row['sync_time_for_plat'] && !( $row['status'] == 1 ) )
            {
                $aTemp = $this->getcostsyncstatus( $row['supplier_id'] );
                if ( $this->getcostsyncjobcount( $row['supplier_id'] ) )
                {
                    $aTemp['status'] = "syncing";
                }
                else if ( $row['has_cost_new'] == "true" )
                {
                    $aTemp['status'] = "having";
                }
                else
                {
                    $aTemp['status'] = "done";
                }
                $aSupplier[$key]['costsync'] = $aTemp;
            }
        }
        return $aSupplier;
    }

    function getcostsynclist( )
    {
        $aApiParams = $this->getapiparams( );
        $sSql = "SELECT supplier_id,COUNT(*) AS num FROM sdb_job_apilist WHERE api_name='".$aApiParams['api_name']."' GROUP BY supplier_id";
        return $this->db->select( $sSql );
    }

    function getcostsyncdonecount( $supplier_id )
    {
        $max_version_id = $this->getmaxversionid( $supplier_id );
        $sSql = "SELECT count(*) AS num FROM sdb_cost_sync WHERE supplier_id='".$supplier_id."' AND goods_id <> 0 AND version_id = '".$max_version_id."' GROUP BY goods_id";
        $aResult = $this->db->selectrow( $sSql );
        return $aResult['num'];
    }

}

?>
