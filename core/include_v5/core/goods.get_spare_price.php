<?php
function goods_get_spare_price(&$list,$memberLevel,$onMarketable = true , &$object){
    if(count($list)>0){
        $level='';
        if($memberLevel){
            $level='and B.level_id='.$memberLevel;

            $oLv = $object->system->loadModel('member/level');
            $aLevel = $oLv->getFieldById($memberLevel, array('dis_count'));
            if(floatval($aLevel['dis_count']) <= 0) $aLevel['dis_count'] = 1;
        }
        $id=array();
        foreach($list as $p=>$q){
            $id[]=intval($q['goods_id']);
        }
        $all=implode(",",$id);
        $sql='SELECT A.product_id,A.goods_id,A.pdt_desc,A.price,B.price as m_price,A.store,A.freez,A.marketable FROM sdb_products A';
        $sql.=' LEFT JOIN sdb_goods_lv_price B ON A.product_id=B.product_id '.$level;
        $sql.=' WHERE A.goods_id IN ('.$all.') order by A.goods_id';
        $price_gid=array();
        $store_gid=array();
        $freez_gid=array();
        $marketable_gid=array();
        $oMath = $object->system->loadModel('system/math');
        foreach($object->db->select($sql) as $q1=>$v1){
            $price_gid[$v1['product_id']]=$v1['m_price']?$v1['m_price']:($v1['price']*$aLevel['dis_count']);
            $price_gid[$v1['product_id']]= $oMath->getOperationNumber($price_gid[$v1['product_id']]);
            $price_goodsid[$v1['goods_id']]= $oMath->getOperationNumber($price_gid[$v1['product_id']]);
            $store_gid[$v1['product_id']]=$v1['store'];
            $freez_gid[$v1['product_id']]=$v1['freez'];
            $marketable_gid[$v1['product_id']]=$v1['marketable'];
        }
        foreach($list as $k => $aRow){
            $list[$k]['pdt_desc'] = unserialize($list[$k]['pdt_desc']);
            if(is_array($list[$k]['pdt_desc'])){
                foreach($list[$k]['pdt_desc'] as $q=>$v){
                    if( $onMarketable && $marketable_gid[$q] == 'false'){
                        unset($list[$k]['pdt_desc'][$q]);
                        continue;
                    }
                    $list[$k]['pdt_desc'][$q]=stripslashes($list[$k]['pdt_desc'][$q]);
                    $list[$k]['pdt_desc']['marketable'][$q]=$marketable_gid[$q];
                    $list[$k]['pdt_desc']['price'][$q]=$price_gid[$q];
                    $list[$k]['pdt_desc']['store'][$q]=$store_gid[$q];
                    $list[$k]['pdt_desc']['freez'][$q]=$freez_gid[$q];
                }
            }else{
                $list[$k]['price'] = $price_goodsid[$aRow['goods_id']];
            }
        }
    }
    return $list;
}
?>
