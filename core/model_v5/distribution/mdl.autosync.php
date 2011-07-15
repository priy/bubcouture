<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_autosync extends shopObject
{

    public $idColumn = "rule_id";
    public $textColumn = "rule_name";
    public $defaultCols = "_cmd,rule_name,supplier_op_id,local_op_id,memo";
    public $adminCtl = "dustribution/autosync";
    public $defaultOrder = array
    (
        0 => "rule_id",
        1 => "desc"
    );
    public $tableName = "sdb_autosync_rule";
    public $rule = null;

    public function getColumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "distribution/auto_command.html"
            ),
            "rule_id" => array(
                "label" => __( "配置编号" ),
                "class" => "span-3"
            ),
            "rule_name" => array(
                "label" => __( "对象" ),
                "class" => "span-5"
            ),
            "supplier_op_id" => array(
                "label" => __( "供应商操作" ),
                "class" => "span-5"
            ),
            "local_op_id" => array(
                "label" => __( "本地操作" ),
                "class" => "span-5"
            ),
            "memo" => array(
                "label" => __( "备注" ),
                "class" => "span-5"
            )
        );
        return array_merge( parent::getcolumns( ), $ret );
    }

    public function getSupplierOPList( )
    {
        return array(
            1 => array(
                "name" => "商品上架",
                "op_items" => array( 0, 1 ),
                "checked" => 1
            ),
            2 => array(
                "name" => "货品库存变更",
                "op_items" => array( 0, 4 ),
                "checked" => 4
            ),
            3 => array(
                "name" => "商品图片更新",
                "op_items" => array( 0, 6 ),
                "checked" => 6
            ),
            4 => array(
                "name" => "商品更新",
                "op_items" => array( 0, 7 ),
                "checked" => 7
            ),
            5 => array(
                "name" => "货品更新",
                "op_items" => array( 0, 5 ),
                "checked" => 5
            ),
            6 => array(
                "name" => "商品新增",
                "op_items" => array( 0, 8 ),
                "checked" => 8
            ),
            7 => array(
                "name" => "商品删除",
                "op_items" => array( 0, 2, 3 ),
                "checked" => 2
            ),
            8 => array(
                "name" => "商品下架",
                "op_items" => array( 0, 2 ),
                "checked" => 2
            ),
            9 => array(
                "name" => "货品库存为0",
                "op_items" => array( 0, 4 ),
                "checked" => 4
            ),
            10 => array(
                "name" => "中断产品线分销权限",
                "op_items" => array( 0, 2, 3 ),
                "checked" => 0
            )
        );
    }

    public function getSupplierOP( $supplier_op_id )
    {
        $aList = $this->getSupplierOPList( );
        return $aList[$supplier_op_id];
    }

    public function getLocalOPList( )
    {
        return array( 0 => "手动操作", 1 => "自动商品上架", 2 => "自动商品下架", 3 => "自动删除商品", 4 => "自动同步库存", 5 => "自动更新货品", 6 => "自动更新图片", 7 => "自动更新商品", 8 => "自动新增商品" );
    }

    public function getLocalOP( $local_sop_id )
    {
        $aList = $this->getLocalOPList( );
        return $aList[$local_sop_id];
    }

    public function getRuleInfo( $rule_id, $columns = "*" )
    {
        return $this->db->selectrow( "SELECT ".$columns." FROM sdb_autosync_rule WHERE rule_id = '".$rule_id."' and disabled = 'false'" );
    }

    public function getRuleRelationInfo( $rule_id )
    {
        $aList = $this->db->select( "SELECT * FROM sdb_autosync_rule_relation WHERE rule_id='".$rule_id."'" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        foreach ( $aList as $key => $row )
        {
            if ( empty( $row['supplier_id'] ) )
            {
                continue;
            }
            $aInfo = $oSupplier->getSupplierInfo( $row['supplier_id'] );
            if ( $aInfo['supplier_pline'] )
            {
                $aPline = unserialize( $aInfo['supplier_pline'] );
                if ( !empty( $aPline ) )
                {
                    foreach ( $aPline as $key1 => $row1 )
                    {
                        if ( $row1['pline_name'] )
                        {
                            break;
                        }
                        $aPline[$key1]['pline_name'] = $this->generatePlineName( $row1 );
                    }
                }
                $aList[$key]['pline_list'] = $aPline;
            }
            else
            {
                $aList[$key]['pline_id'] = 0;
            }
        }
        return $aList;
    }

    public function insert( $data )
    {
        $rule = $data['rule'];
        unset( $data['rule'] );
        unset( $data['__'] );
        $data['rule_name'] = $this->generateRuleName( $rule );
        addslashes_array( $data );
        if ( !( $rule_id = $this->_insert( $data ) ) )
        {
            trigger_error( "添加失败!", E_USER_ERROR );
        }
        return $this->insertRuleRelation( $rule_id, $rule );
    }

    public function _insert( $data )
    {
        $rs = $this->db->exec( "select * from ".$this->tableName." where 0=1" );
        $this->_checkColumns( $data );
        $sql = $this->db->GetInsertSQL( $rs, $data );
        if ( $sql && $this->db->exec( $sql ) )
        {
            return $this->db->lastInsertId( );
        }
        else
        {
            return false;
        }
    }

    public function _checkColumns( &$data )
    {
        foreach ( $data as $k => $v )
        {
            $data[$k] = trim( $data[$k] );
        }
        $cols = $this->getColumns( );
        $cols[$this->textColumn]['required'] = true;
        foreach ( $cols as $k => $p )
        {
            if ( !$p['required'] && $data[$k] )
            {
                trigger_error( "<b>".$p['label']."</b> 不能为空！", E_USER_ERROR );
            }
        }
    }

    public function generateRuleName( $rule )
    {
        return $this->_generateRuleName( $this->_changeRuleDataFormat( $rule ) );
    }

    public function _changeRuleDataFormat( $rule )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $aResult = array( );
        foreach ( $rule['supplier_id'] as $key => $row )
        {
            if ( empty( $row ) )
            {
                $aResult[$row]['name'] = "所有";
                $aResult[$row]['items'] = array( 0 => "所有" );
                continue;
            }
            if ( empty( $aResult[$row] ) )
            {
                $aInfo = $oSupplier->getSupplierInfo( $row );
                $aResult[$row]['name'] = $aInfo['supplier_brief_name'];
                $aResult[$row]['supplier_pline'] = unserialize( $aInfo['supplier_pline'] );
            }
            $aResult[$row]['items'][$rule['pline_id'][$key]] = empty( $rule['pline_id'][$key] ) ? "所有" : $aResult[$row]['supplier_pline'][$rule['pline_id'][$key]]['pline_name'];
            if ( empty( $aResult[$row]['items'][$rule['pline_id'][$key]] ) )
            {
                $aResult[$row]['items'][$rule['pline_id'][$key]] = $this->generatePlineName( $aResult[$row]['supplier_pline'][$rule['pline_id'][$key]] );
            }
        }
        return $aResult;
    }

    public function _generateRuleName( $aData )
    {
        $aResult = array( );
        foreach ( $aData as $key => $row )
        {
            $sTemp = $row['name']."/";
            $aTemp = array_values( $row['items'] );
            $aResult[] = $sTemp.implode( ",", $aTemp );
        }
        return implode( " & ", $aResult );
    }

    public function generatePlineName( $data )
    {
        return "产品线 ".$data['cat_id']." | ".$data['brand_id'];
    }

    public function deleteRuleRelation( $rule_id )
    {
        if ( is_array( $rule_id ) )
        {
            $rule_id = implode( "','", $rule_id );
        }
        return $this->db->exec( "DELETE FROM sdb_autosync_rule_relation WHERE rule_id IN ('".$rule_id."')" );
    }

    public function insertRuleRelation( $rule_id, $data )
    {
        $rule = array( );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        foreach ( $data['supplier_id'] as $key => $row )
        {
            if ( !$this->_tempPline[$row] )
            {
                $aTemp = $oSupplier->getSupplierInfo( $row );
                $this->_tempPline[$row] = unserialize( $aTemp['supplier_pline'] );
            }
            if ( $data['pline_id'][$key] )
            {
                $aTemp = $this->_tempPline[$row][$data['pline_id'][$key]];
            }
            else
            {
                $aTemp = array( );
            }
            $sDesc = serialize( $aTemp );
            $rule[] = "'".$rule_id."','".$row."','".$data['pline_id'][$key]."','".$sDesc."'";
        }
        if ( empty( $rule ) )
        {
            return false;
        }
        $values = implode( "),(", $rule );
        return $this->db->exec( "INSERT INTO sdb_autosync_rule_relation(`rule_id`,`supplier_id`,`pline_id`,`memo`) VALUES(".$values.")" );
    }

    public function update( $data, $filter )
    {
        $rule = $data['rule'];
        unset( $data['rule_id'] );
        unset( $data['rule'] );
        unset( $data['__'] );
        $data['rule_name'] = $this->generateRuleName( $rule );
        addslashes_array( $data );
        if ( !$this->_update( $data, $filter ) )
        {
            trigger_error( "修改失败!", E_USER_ERROR );
        }
        $this->deleteRuleRelation( $filter['rule_id'] );
        $this->insertRuleRelation( $filter['rule_id'], $rule );
        return true;
    }

    public function _update( $data, $filter )
    {
        $rs = $this->db->exec( "SELECT * from ".$this->tableName." WHERE rule_id='".$filter['rule_id']."'" );
        $this->_checkColumns( $data );
        $sql = $this->db->GetUpdateSQL( $rs, $data );
        if ( $sql )
        {
            if ( $this->db->exec( $sql ) )
            {
                return $this->db->affect_row( );
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    public function generateAutoSyncConfigFile( )
    {
        $sSql = "SELECT r.supplier_op_id,r.local_op_id,sp.supplier_pline,re.supplier_id,re.pline_id,re.memo \n                       FROM sdb_autosync_rule_relation AS re  \n                       LEFT JOIN sdb_autosync_rule AS r ON r.rule_id = re.rule_id \n                       LEFT JOIN sdb_supplier AS sp ON sp.supplier_id = re.supplier_id \n                       WHERE r.disabled = 'false' \n                       ORDER BY r.rule_id ASC";
        $aRule = $this->db->select( $sSql );
        $aRule = $this->_changeArray( $aRule );
        $filename = HOME_DIR."/cache/autosync.php";
        $fp = fopen( $filename, "w" );
        $flag = fwrite( $fp, "<?php exit();?>".serialize( $aRule ) );
        fclose( $fp );
        return $flag;
    }

    public function _changeArray( $data )
    {
        $aRule = array( );
        if ( empty( $data ) || !is_array( $data ) )
        {
            return $aRule;
        }
        foreach ( $data as $row )
        {
            if ( $row['supplier_id'] == 0 )
            {
                $aRule[$row['supplier_op_id']][0][0] = array(
                    "local_op_id" => $row['local_op_id']
                );
                continue;
            }
            if ( $row['pline_id'] == 0 || empty( $row['supplier_pline'] ) )
            {
                $aRule[$row['supplier_op_id']][$row['supplier_id']][0] = array(
                    "local_op_id" => $row['local_op_id']
                );
            }
            else
            {
                if ( $row['memo'] )
                {
                    $pline = unserialize( $row['memo'] );
                }
                else
                {
                    $pline = unserialize( $row['supplier_pline'] );
                    $pline = $pline[$row['pline_id']];
                }
                if ( !empty( $pline ) )
                {
                    $aRule[$row['supplier_op_id']][$row['supplier_id']][$row['pline_id']] = array(
                        "local_op_id" => $row['local_op_id'],
                        "cat_id" => $pline['cat_id'],
                        "brand_id" => $pline['brand_id']
                    );
                }
                else if ( empty( $row['supplier_pline'] ) )
                {
                    $aRule[$row['supplier_op_id']][$row['supplier_id']][0] = array(
                        "local_op_id" => $row['local_op_id']
                    );
                }
            }
        }
        return $aRule;
    }

    public function isNeedAutoSync( $data, $supplier_id = 0 )
    {
        if ( !is_array( $data ) )
        {
            if ( !$supplier_id )
            {
                return 0;
            }
            $data = $this->getCommandDetail( $data, $supplier_id );
            if ( empty( $data ) )
            {
                return 0;
            }
        }
        if ( $data['command'] == 1 && $data['marketable'] == "false" )
        {
            $data['command'] = 8;
        }
        if ( $data['command'] == 2 && $data['store'] == 0 )
        {
            $data['command'] = 9;
        }
        return $this->_isNeedAutoSync( $data );
    }

    public function _isNeedAutoSync( $data )
    {
        if ( is_null( $this->rule ) )
        {
            $filename = HOME_DIR."/cache/autosync.php";
            if ( !file_exists( $filename ) )
            {
                return 0;
            }
            $aRule = substr( file_get_contents( $filename ), 15 );
            $aRule = unserialize( $aRule );
            $this->rule = $aRule;
        }
        else
        {
            $aRule = $this->rule;
        }
        if ( empty( $aRule ) )
        {
            return 0;
        }
        if ( $aRule[$data['command']][0] )
        {
            return $aRule[$data['command']][0][0]['local_op_id'];
        }
        if ( $aRule[$data['command']][$data['supplier_id']][0] )
        {
            return $aRule[$data['command']][$data['supplier_id']][0]['local_op_id'];
        }
        foreach ( $aRule[$data['command']][$data['supplier_id']] as $row )
        {
            if ( $row['cat_id'] == -1 && $row['brand_id'] == -1 )
            {
                return $row['local_op_id'];
            }
            if ( $row['cat_id'] == -1 && $row['brand_id'] == $data['brand_id'] )
            {
                return $row['local_op_id'];
            }
            if ( $row['brand_id'] == -1 && $row['cat_id'] == $data['cat_id'] )
            {
                return $row['local_op_id'];
            }
            if ( $row['cat_id'] == $data['cat_id'] && $row['brand_id'] = $data['brand_id'] )
            {
                return $row['local_op_id'];
            }
        }
        return 0;
    }

    public function getCommandDetail( $command_id, $supplier_id )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_data_sync_".$supplier_id." WHERE command_id = ".$command_id );
    }

    public function doSync( $supplier_id, $command_id, $local_op_id )
    {
        $data = $this->getCommandDetail( $command_id, $supplier_id );
        if ( $data['status'] == "unoperated" && !empty( $data ) )
        {
            switch ( $local_op_id )
            {
            case 1 :
                $this->_doEnMarketable( $data );
                break;
            case 2 :
                $this->_doDisMarketable( $data );
                break;
            case 3 :
                $this->_doDeleteGoods( $data );
                break;
            case 4 :
                $this->_doUpdateStore( $data );
                break;
            case 5 :
                $this->_doUpdateProducts( $data );
                break;
            case 6 :
                $this->_doUpdateImages( $data );
                break;
            case 7 :
                $this->_doUpdateGoods( $data );
                break;
            case 8 :
                $this->_doAddGoods( $data );
                break;
            }
        }
        return $this->deleteAutoSyncTask( $supplier_id, $command_id );
    }

    public function addAutoSyncTask( $supplier_id, $command_id )
    {
        if ( !( $local_op_id = $this->isNeedAutoSync( $command_id, $supplier_id ) ) )
        {
            return "pass";
        }
        $aData = array(
            "supplier_id" => $supplier_id,
            "command_id" => $command_id,
            "local_op_id" => $local_op_id
        );
        $rs = $this->db->exec( "SELECT * FROM sdb_autosync_task WHERE 0=1" );
        $sSql = $this->db->GetInsertSQL( $rs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            return "succ";
        }
        else
        {
            return "fail";
        }
    }

    public function deleteAutoSyncTask( $supplier_id, $command_id )
    {
        return $this->db->exec( "DELETE FROM sdb_autosync_task WHERE supplier_id = '".$supplier_id."' AND command_id = '".$command_id."' " );
    }

    public function getAutoSyncTaskCount( $supplier_id )
    {
        $aCount = $this->db->selectrow( "SELECT count(*) AS num FROM sdb_autosync_task WHERE supplier_id = '".$supplier_id."'" );
        return $aCount['num'];
    }

    public function getAutoSyncTask( $supplier_id )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_autosync_task WHERE supplier_id = '".$supplier_id."'" );
    }

    public function _doEnMarketable( $data )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->updateGoodsMarketable( $data['supplier_id'], $data['object_id'], "true" );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "done" );
    }

    public function _doDisMarketable( $data )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->updateGoodsMarketable( $data['supplier_id'], $data['object_id'], "false" );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "done" );
    }

    public function _doDeleteGoods( $data )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->removeGoods( $data['supplier_id'], $data['object_id'] );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "done" );
    }

    public function _doUpdateStore( $data )
    {
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oDataSync->syncProductStore( $data['supplier_id'], $data['object_id'] );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "done" );
    }

    public function _doUpdateProducts( $data )
    {
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oDataSync->updateGoodsProduct( $data['supplier_id'], $data['object_id'], $data['command_id'] );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "unmodified" );
    }

    public function _doUpdateImages( $data )
    {
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->updateGoodsImageFailed( $data['command_id'], $data['supplier_id'] );
        $oDataSync->updateGoodsImage( $data['command_id'], $data['supplier_id'], $data['object_id'] );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "done" );
    }

    public function _doUpdateGoods( $data )
    {
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oBrand = $this->system->loadModel( "goods/brand" );
        $bFlag = false;
        if ( $oDataSync->checkGoodsDownload( $data['supplier_id'], $data['object_id'] ) )
        {
            $syncInfo = $oDataSync->preDownload( $data['supplier_id'], $data['object_id'], $data['command_id'], null, false );
            $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "unmodified" );
            $oCat = $this->system->loadModel( "goods/productCat" );
            $oBrand = $this->system->loadModel( "goods/brand" );
            $oBrand->brand2json( );
            $oCat->cat2json( );
            $bFlag = true;
        }
        $aGoods = $oDataSync->getSupplierGoodsInfo( $data['supplier_id'], $data['object_id'] );
        $aData = array(
            "unit" => $aGoods['unit'],
            "brief" => $aGoods['brief'],
            "name" => $aGoods['name'],
            "weight" => $aGoods['weight'],
            "intro" => $aGoods['intro'],
            "mktprice" => $aGoods['mktprice'],
            "params" => $aGoods['params']
        );
        $aData = array_merge( $aData, $this->getSupplierGoodsProperty( $aGoods ) );
        if ( $bFlag )
        {
            $aGoods['type_id'] = $syncInfo['locals']['local_type_id'];
            $aGoods['brand_id'] = $syncInfo['locals']['local_brand_id'];
            if ( $aGoods['type_id'] )
            {
                $aData['type_id'] = $aGoods['type_id'];
            }
            if ( $aGoods['brand_id'] )
            {
                $aData['brand_id'] = $aGoods['brand_id'];
                $aTemp = $oBrand->getFieldById( $aData['brand_id'], array( "brand_name" ) );
                $aData['brand'] = $aTemp['brand_name'];
            }
        }
        else
        {
            $aData['type_id'] = $oDataSync->_getLocalTypeByPlatType( $data['supplier_id'], $aGoods['type_id'] );
            $aData['brand_id'] = $oDataSync->_getLocalBrandByPlatBrand( $data['supplier_id'], $aGoods['brand_id'] );
            $aTemp = $oBrand->getFieldById( $aData['brand_id'], array( "brand_name" ) );
            $aData['brand'] = $aTemp['brand_name'];
        }
        $rs = $this->db->exec( "SELECT * FROM sdb_goods WHERE supplier_goods_id = '".$data['goods_id']."' AND supplier_id='".$data['supplier_id']."'" );
        $sSql = $this->db->GetUpdateSQL( $rs, $aData );
        $this->db->exec( $sSql );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "unmodified" );
    }

    public function doLoosePline( $supplier_id, $pline )
    {
        $rule_info = $this->db->selectrow( "SELECT a.local_op_id FROM sdb_autosync_rule AS a \n                                                  LEFT JOIN sdb_autosync_rule_relation AS b ON a.rule_id=b.rule_id \n                                                  WHERE (b.supplier_id=".$supplier_id." or b.supplier_id=0) AND a.supplier_op_id=10 AND a.disabled='false'" );
        if ( $rule_info && $rule_info['local_op_id'] != 0 && is_array( $pline ) )
        {
            foreach ( $pline as $k => $v )
            {
                if ( $v['cat_id'] == "-1" && $v['brand_id'] == "-1" )
                {
                    return true;
                }
                else if ( $v['cat_id'] == "-1" )
                {
                    $where[] = "(d.brand_id<>".$v['brand_id'].")";
                }
                else if ( $v['brand_id'] == "-1" )
                {
                    $where[] = "(d.cat_id NOT IN (".$v['cat_id']."))";
                }
                else
                {
                    $where[] = "(d.cat_id NOT IN (".$v['cat_id'].") AND d.brand_id<>".$v['brand_id'].")";
                }
            }
            $sql = "SELECT d.goods_id FROM sdb_data_sync_".$supplier_id." AS d INNER JOIN sdb_goods AS g ON d.goods_id=g.supplier_goods_id WHERE g.supplier_id=".$supplier_id." AND d.command=6 AND (".implode( " AND ", $where ).")";
            $goods = $this->db->select( $sql );
            $goods_id = array( );
            foreach ( $goods as $v )
            {
                $goods_id[] = $v['goods_id'];
            }
            $update = "SELECT * FROM sdb_goods WHERE supplier_id=".$supplier_id." AND supplier_goods_id IN (".implode( ",", $goods_id ).")";
            switch ( $rule_info['local_op_id'] )
            {
            case 2 :
                $params = array( "marketable" => "false" );
                $update .= " AND marketable='true'";
                break;
            case 3 :
                $params = array( "disabled" => "true" );
                $update .= " AND disabled='false'";
                break;
            default :
                $params = array( );
                break;
            }
            if ( !empty( $params ) )
            {
                $rs = $this->db->query( $update );
                $sql = $this->db->GetUpdateSQL( $rs, $params );
                $this->db->exec( $sql );
            }
        }
    }

    public function getSupplierGoodsProperty( $aData )
    {
        if ( !is_array( $aData ) )
        {
            return array( );
        }
        foreach ( $aData as $key => $val )
        {
            if ( !preg_match( "/^p_\\d{1,2}\$/", $key ) )
            {
                unset( $aData[$key] );
            }
        }
        return $aData;
    }

    public function _doAddGoods( $data )
    {
        $oDataSync = $this->system->loadModel( "distribution/datasync" );
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $syncInfo = $oDataSync->downloadGoods( $data['command_id'], $data['supplier_id'], $data['object_id'] );
        $oCat = $this->system->loadModel( "goods/productCat" );
        $oBrand = $this->system->loadModel( "goods/brand" );
        $oBrand->brand2json( );
        $oCat->cat2json( );
        $oSupplier->updateSyncStatus( $data['command_id'], $data['supplier_id'], "unmodified" );
    }

    public function getAutoSyncSupplierList( )
    {
        return $this->db->select( "SELECT supplier_id FROM sdb_autosync_task GROUP BY supplier_id" );
    }

    public function isExistPlineName( $aData )
    {
        foreach ( $aData as $key => $row )
        {
            if ( isset( $row['pline_name'] ) )
            {
                return true;
            }
            return false;
        }
    }

    public function fillPlineName( $supplier_id, $aData )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $aInfo = $oSupplier->getSupplierInfo( $supplier_id );
        $aPline = unserialize( $aInfo['supplier_pline'] );
        $aData = array_change_key( $aData, "pline_id" );
        foreach ( $aPline as $key => $row )
        {
            $aPline[$key]['pline_name'] = $aData[$key]['pline_name'];
        }
        $sPline = serialize( $aPline );
        $sSql = "UPDATE sdb_supplier SET supplier_pline='".$sPline."' WHERE supplier_id ='".$supplier_id."'";
        $this->db->exec( $sSql );
        return $aPline;
    }

    public function modifier_rule_name( &$rows )
    {
        foreach ( $rows as $key => $val )
        {
            $rows[$key] = "<span title='".htmlspecialchars( $val, ENT_QUOTES )."'>".htmlspecialchars( $val, ENT_QUOTES )."</span>";
        }
    }

    public function modifier_supplier_op_id( &$rows )
    {
        foreach ( $rows as $key => $val )
        {
            if ( $val )
            {
                $aTemp = $this->getSupplierOP( $val );
                $rows[$key] = $aTemp['name'];
            }
            else
            {
                $rows[$key] = "-";
            }
        }
    }

    public function modifier_local_op_id( &$rows )
    {
        foreach ( $rows as $key => $val )
        {
            $rows[$key] = $this->getLocalOP( $val );
        }
    }

}

?>
