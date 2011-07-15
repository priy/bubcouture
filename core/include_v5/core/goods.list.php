<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_list( $cols, $filter = "", $start = 0, $limit = 20, $orderType = null, &$object )
{
    $ident = md5( $cols.var_export( $filter, true ).$start.$limit.$orderType );
    if ( !$object->_dbstorage[$ident] )
    {
        if ( !$cols )
        {
            $cols = $object->defaultCols;
        }
        if ( $object->appendCols )
        {
            $cols .= ",".$object->appendCols;
        }
        $sql = "SELECT ".$cols." FROM ".$object->tableName." WHERE ".$object->_filter( $filter );
        if ( is_array( $orderType ) )
        {
            $orderType = trim( implode( " ", $orderType ) ) ? $orderType : $object->defaultOrder;
            if ( $orderType )
            {
                $sql .= " ORDER BY ".implode( " ", $orderType );
            }
        }
        else if ( $orderType )
        {
            $sql .= " ORDER BY ".$orderType;
        }
        else
        {
            $sql .= " ORDER BY ".implode( " ", $object->defaultOrder );
        }
        $count = $object->db->count( $sql );
        $rows = $object->db->selectLimit( $sql, $limit, $start );
        if ( isset( $filter['mlevel'] ) && $filter['mlevel'] )
        {
            $oLv = $object->system->loadModel( "member/level" );
            if ( $level = $oLv->getFieldById( $filter['mlevel'] ) )
            {
                foreach ( $rows as $k => $r )
                {
                    $arrMp[$r['goods_id']] =& $rows[$k]['price'];
                    if ( 0 < $level['dis_count'] )
                    {
                        $rows[$k]['price'] *= $level['dis_count'];
                    }
                }
                if ( 0 < count( $arrMp ) )
                {
                    $sql = "SELECT goods_id,MIN(price) AS mprice FROM sdb_goods_lv_price WHERE goods_id IN (".implode( ",", array_keys( $arrMp ) ).") AND level_id=".intval( $filter['mlevel'] )." GROUP BY goods_id";
                    foreach ( $object->db->select( $sql ) as $k => $r )
                    {
                        $arrMp[$r['goods_id']] = $r['mprice'];
                    }
                }
            }
        }
        $object->_dbstorage[$ident] = $rows;
    }
    return $object->_dbstorage[$ident];
}

?>
