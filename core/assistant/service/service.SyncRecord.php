<?php
class SyncRecordService extends BaseService
{
    function init(&$server)
    {
        parent::init($server);

        $server->wsdl->addComplexType(
            'ConditionItem',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'fieldname' => array('name'=>'fieldname','type'=>'xsd:string'),
                'comparesign' => array('name'=>'comparesign','type'=>'xsd:string'),
                'fieldvalue' => array('name'=>'fieldvalue','type'=>'xsd:string'),
                'joinflag'  => array('name'=>'join','type'=>'xsd:string'),
                'groupfalg'  => array('name'=>'group','type'=>'xsd:string'),
            )
        );
        $server->wsdl->addComplexType(
            'ConditionItemArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(
                array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:ConditionItem[]')
            ),
            'tns:ConditionItem'
        );
        $server->wsdl->addComplexType(
            'OrderByItem',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'fieldname' => array('name'=>'fieldname','type'=>'xsd:string'),
                'orderby' => array('name'=>'orderby','type'=>'xsd:string')
            )
        );
        $server->wsdl->addComplexType(
            'OrderByItemArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(
                array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:OrderByItem[]')
            ),
            'tns:OrderByItem'
        );

        $server->register('GetRecordCount',
            array('table' => 'xsd:string', 'cndarr' => 'tns:ConditionItemArray'),
            array('return' => 'xsd:int'),
            'urn:shopexapi',
            'urn:shopexapi#GetRecordCount',
            'rpc',
            'encoded',
            '');
        $server->register('DownloadRecord',
            array('table' => 'xsd:string', 'fields' => 'tns:StringArray', 'cndarr' => 'tns:ConditionItemArray',
                  'orders' => 'tns:OrderByItemArray', 'limit' => 'xsd:boolean', 'offset' => 'xsd:int', 'rowcount' => 'xsd:int',
                  'delimiter' => 'xsd:string', 'enclosure' => 'xsd:string'),
            array('return' => 'xsd:int'),
            'urn:shopexapi',
            'urn:shopexapi#DownloadRecord',
            'rpc',
            'encoded',
            '');

        $server->register('UploadRecord',
            array('table' => 'xsd:string', 'fields' => 'tns:StringArray', 'guidfield' => 'xsd:string', 'idfield' => 'xsd:string', 'syncfield' => 'xsd:string',
                  'delimiter' => 'xsd:string', 'enclosure' => 'xsd:string'),
            array('return' => 'tns:SyncPack'),
            'urn:shopexapi',
            'urn:shopexapi#UploadRecord',
            'rpc',
            'encoded',
            '');
    }

    function parseConditionArray($cndarr, &$db)
    {
        $tbpre = isset($GLOBALS['_tbpre']) ? $GLOBALS['_tbpre'] : null;
        if (!$tbpre && defined('DB_PREFIX')) $tbpre = DB_PREFIX;
        $cnds = array();
        foreach ($cndarr as $cnditem)
        {
            $cnds[$cnditem['groupflag']][] = $cnditem;
        }
        $sql = array();
        foreach ($cnds as $group => $items)
        {
            $item['fieldvalue'] = str_replace('sdb_', $tbpre, $item['fieldvalue']);
            $str = '';
            foreach ($items as $item)
            {
                if ($str != '') $str .= ' '.$item['joinflag'].' ';
                $compare_sign = trim(strtolower($item['comparesign']));
                $field_value = $item['fieldvalue'];
                if ($compare_sign == 'in' || $compare_sign == 'not in'){
                    $str .= "{$item['fieldname']} in ($field_value)";
                }else if ($compare_sign == 'is' || $compare_sign == 'is not'){
                    $str .= "{$item['fieldname']} {$item['comparesign']} {$field_value}";
                }else{                                                            
                    $str .= "{$item['fieldname']} {$item['comparesign']} ".$db->quote($field_value);
                }
            }
            $sql[] = "($str)";
        }
        return join(' and ', $sql);
    }

    function parseOrderByArray($orders)
    {
        $sql = array();
        foreach ($orders as $item)
        {
            $sql[] = $item['fieldname'].' '.$item['orderby'];
        }
        return implode(',', $sql);
    }

    function makeSql(&$db, $tablename, $fields, $cndarr, $orders, $limit = false, $offset = 0, $rowcount = 20)
    {
        $tbpre = isset($GLOBALS['_tbpre']) ? $GLOBALS['_tbpre'] : null;
        if (!$tbpre && defined('DB_PREFIX')) $tbpre = DB_PREFIX;
        if (strstr($tablename, $tbpre) !== 0) $tablename = $tbpre.$tablename;
        $cndsql  = SyncRecordService::parseConditionArray($cndarr, $db);
        $orderby = SyncRecordService::parseOrderByArray($orders);
        $sql = 'select '. implode(',', $fields).' from '.$tablename;
        if ($cndsql)  $sql .= ' where '. $cndsql;
        if ($orderby) $sql .= ' order by '.$orderby;
        if ($limit)   $sql .= ' limit '.$offset.','.$rowcount;
        return $sql;
    }
}

function GetRecordCount($table, $cndarr)
{
    LogUtils::log_str('GetRecordCount Begin');
    LogUtils::log_obj(func_get_args());

    $server = &$GLOBALS['as_server'];
    $sys = &$GLOBALS['system'];
    $db = $sys->database();

    $sql = SyncRecordService::makeSql($db, $table, array('count(*) recordcount'), $cndarr, array());
    LogUtils::log_str($sql);

    $row = $db->selectrow($sql);
    $count = $row ? $row['recordcount'] : 0;

    LogUtils::log_str('GetRecordCount Return:'.$count);
    return $count;
}

function DownloadRecord($table, $fields, $cndarr, $orders, $limit = false, $offset = 0, $rowcount = 20, $delimiter = ',', $enclosure = '"')
{
    LogUtils::log_str('DownloadRecord Begin');
    LogUtils::log_obj(func_get_args());

    $server = &$GLOBALS['as_server'];
    $sys = &$GLOBALS['system'];
    $db = $sys->database();

    $sql = SyncRecordService::makeSql($db, $table, $fields, $cndarr, $orders, $limit, $offset, $rowcount);
    LogUtils::log_str($sql);
    $rows = $db->select($sql);
    if ($rows)
    {
        ob_start();
        $first = true;
        foreach ($rows as $row)
        {
            if ($first)
            {
                echo implode($delimiter, array_keys($row));
                echo "\r\n";
                $first = false;
            }
            $linefirst = true;
            foreach ($row as $k=>$v)
            {
                if ($linefirst) $linefirst = false; else echo $delimiter;
                echo $enclosure;
                echo str_replace('"','""', $v);
                echo $enclosure;
            }
            echo "\r\n";
        }
        $data = ob_get_contents();
        ob_end_clean();
        $server->addAttachment($data);

        LogUtils::log_str($data);

        $count = count($rows);
        LogUtils::log_str('DownloadRecord Return:'.$count);
        return $count;
    }

    LogUtils::log_str('DownloadRecord Return');
    return 0;
}

function UploadRecord($table, $fields, $guidfield, $idfield, $syncfield, $delimiter = ',', $enclosure = '"')
{
    LogUtils::log_str('UploadRecord Begin');
    LogUtils::log_obj(func_get_args());

    $server = &$GLOBALS['as_server'];
    $sys = &$GLOBALS['system'];
    $db = $sys->database();

    $syncitems = array();
    $atts = $server->getAttachments();
    LogUtils::log_obj($atts);
    if (count($atts) > 0)
    {
        $att = null;
        foreach ($atts as $attitem)
        {
            $att = $attitem;
            break;
        }

        $csvfile = ServerUtils::formalPath(ServerUtils::buildPath(AS_TMP_DIR,'tmpcsv'.time().'.txt'));
        file_put_contents($csvfile, $att['data']);
        LogUtils::log_str($csvfile);
        $list = TextUtils::csv2array($csvfile, $fields, $delimiter, $enclosure);
        unlink($csvfile);

        $validators = BaseValidator::loadValidators(AS_VALIDATOR_DIR, $table, $sys);

        $idcolarr = split(',', $idfield);
        foreach ($list as $row)
        {
            LogUtils::log_obj($row);

            $sync_item = array();
            $sync_item['guid'] = '';
            $sync_item['id'] = '';
            $sync_item['succ'] = false;
            $sync_item['errmsg'] = '';
            $sync_item['syncstate'] = AS_SYNC_ADDED;

            if (array_key_exists($guidfield, $row)) $sync_item['guid'] = $row[$guidfield];
            if (array_key_exists($syncfield, $row)) $sync_item['syncstate'] = $row[$syncfield];

            $idcnd = array();
            $idcndstr = '';
            foreach ($idcolarr as $idcol)
            {
                if (array_key_exists($idcol, $row))
                {
                    $idcnd[$idcol] = $row[$idcol];
                    if (!empty($idcndstr)) $idcndstr .= ' and ';
                    $idcndstr .= $idcol . "=". $db->quote($row[$idcol]);
                    
                    if (empty($row[$idcol])) $row[$idcol] = null;
                }
            }
            $newrow = array();
            foreach ($row as $colname => $colvalue){
                $newrow[$colname] = ($colvalue==='') ? null : $colvalue;
            }
            $row = $newrow;
            $sync_item['id'] = implode(',', $idcnd);

            LogUtils::log_obj($idcnd);

            switch ($sync_item['syncstate'])
            {
                case AS_SYNC_DELETED:
                    if (count($idcnd) > 0)
                    {
                        if (BaseValidator::runValidateBefore($validators, 'delete', $row))
                        {
                            $sql = "delete from sdb_$table where $idcndstr";
                            LogUtils::log_str($sql);
                            if ($db->exec($sql))
                            {
                                $sync_item['succ'] = true;
                                BaseValidator::runValidateAfter($validators, 'delete', $row);
                            }
                        }
                    }
                    break;
                case AS_SYNC_UNCHANGED:
                case AS_SYNC_MODIFIED:
                    if (count($idcnd) > 0)
                    {
                        $sql = "select * from sdb_$table where $idcndstr";
                        LogUtils::log_str($sql);
                        $count = $db->count($sql);
                        if ($count > 0)
                        {
                            if (BaseValidator::runValidateBefore($validators, 'update', $row))
                            {
                                $rs = $db->query($sql);
                                $sql = $db->getUpdateSql($rs, $row, true);
                                LogUtils::log_str($sql);
                                if ($sql && $db->exec($sql))
                                {
                                    $sync_item['succ'] = true;
                                    BaseValidator::runValidateAfter($validators, 'update', $row);
                                }
                            }
                        }else{
                            if (BaseValidator::runValidateBefore($validators, 'insert', $row))
                            {
                                $rs = $db->query($sql);
                                $sql = $db->getInsertSQL($rs, $row);
                                LogUtils::log_str($sql);
                                if ($sql && $db->exec($sql))
                                {
                                    if (count($idcnd) == 1) $sync_item['id'] = $db->lastInsertId();
                                    $sync_item['succ'] = true;
                                    BaseValidator::runValidateAfter($validators, 'insert', $row);
                                }
                            }
                        }
                    }
                    break;
                case AS_SYNC_ADDED:
                    $count = 0;
                    if (count($idcnd) > 0)
                    {
                        $sql = "select * from sdb_$table where $idcndstr";
                        LogUtils::log_str($sql);
                        $count = $db->count($sql);
                    }
                    if ($count > 0)
                    {
                        if (BaseValidator::runValidateBefore($validators, 'update', $row))
                        {
                            $rs = $db->query($sql);
                            $sql = $db->getUpdateSql($rs, $row, true);
                            LogUtils::log_str($sql);
                            if ($sql && $db->exec($sql))
                            {
                                $sync_item['succ'] = true;
                                BaseValidator::runValidateAfter($validators, 'update', $row);
                            }
                        }
                    }else{
                        if (BaseValidator::runValidateBefore($validators, 'insert', $row))
                        {
                            $sql = "select * from sdb_$table where 0=1";
                            LogUtils::log_str($sql);
                            $rs = $db->query($sql);
                            $sql = $db->getInsertSQL($rs, $row);
                            LogUtils::log_str($sql);
                            if ($sql && $db->exec($sql))
                            {
                                if (count($idcnd) == 1) $sync_item['id'] = $db->lastInsertId();
                                $sync_item['succ'] = true;
                                BaseValidator::runValidateAfter($validators, 'insert', $row);
                            }
                        }
                    }
                    break;

            }
            LogUtils::log_obj($sync_item);
            $syncitems[] = $sync_item;
        }
    }

    $pack = array('items' => $syncitems);

    LogUtils::log_str('UploadRecord Return');
    return $pack;
}

?>