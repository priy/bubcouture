<?php
class goods_1Validator extends BaseValidator
{
    function goods_1Validator($sys)
    {
        parent::BaseValidator($sys);
    }

    function genGoodsBn(&$row)
    {
        $str = isset($row['goods_guid']) ? $row['goods_guid'] : '';
        $str .= isset($row['goods_id']) ? $row['goods_id'] : '';
        $str .= time().rand(1,10000);
        return 'BN-'.strtoupper(substr(md5($str),0,8));
    }

    function validateInsertBefore(&$row)
    {
        $must_fields = array('name');
        foreach ($must_fields as $field)
        {
            if (!isset($row[$field]) || empty($row[$field])) return false;
        }
        if (empty($row['goods_type'])) $row['goods_type'] = 'normal';
        if (empty($row['cat_id'])) $row['cat_id'] = 0;
        $row['cat_id'] = intval($row['cat_id']);
        if (empty($row['type_id'])) $row['type_id'] = 1;
        
        unset($row['goods_id']);
        
        $row['disabled'] = (isset($row['disabled']) && $row['disabled']) ? 'true' : 'false';
        $row['marketable'] = (isset($row['marketable']) && $row['marketable']) ? 'true' : 'false';
        $row['udfimg'] = (isset($row['udfimg']) && $row['udfimg']) ? 'true' : 'false';
        $row['price'] = isset($row['price']) ? $row['price'] : 0;
        $row['cost'] = (isset($row['cost']) && !empty($row['cost'])) ? $row['cost'] : 0;
        $row['p_order'] = isset($row['p_order']) ? intval($row['p_order']) : 0;
        $row['d_order'] = isset($row['d_order']) ? intval($row['d_order']) : 50;
        $row['buy_count'] = isset($row['buy_count']) ? intval($row['buy_count']) : 0;
        $row['notify_num'] = isset($row['notify_num']) ? intval($row['notify_num']) : 0;
        $row['comments_count'] = isset($row['comments_count']) ? intval($row['comments_count']) : 0;
        $row['view_w_count'] = isset($row['view_w_count']) ? intval($row['view_w_count']) : 0;
        $row['view_count'] = isset($row['view_count']) ? intval($row['view_count']) : 0;
        $row['buy_w_count'] = isset($row['buy_w_count']) ? intval($row['buy_w_count']) : 0;        
        $row['score_setting'] = (isset($row['score_setting']) && !empty($row['score_setting'])) ? $row['score_setting'] : 'number';
        $row['goods_type'] = (isset($row['goods_type']) && !empty($row['goods_type'])) ? $row['goods_type'] : 'normal';
        $row['rank'] = isset($row['rank']) ? intval($row['rank']) : 0;                                
        $row['last_modify'] = (isset($row['last_modify']) && !empty($row['last_modify'])) ? intval($row['last_modify']) : time();
                
        if (!isset($row['bn'])) {
            $row['bn'] = $this->genGoodsBn($row);
        }
        $r = $this->_db->selectrow("select count(*) recordcount from ".$this->_tbpre."goods where bn=".$this->_db->quote($row['bn']));
        if ($r && $r['recordcount'] > 0) {
            $row['bn'] = $this->genGoodsBn($row);
        }
        LogUtils::log_str($row['bn']);
        return true;
    }

    function validateInsertAfter(&$row)
    {
        return true;
    }

    function validateUpdateBefore(&$row)
    {
        if (isset($row['disabled']))  $row['disabled'] = $row['disabled'] ? 'true' : 'false';
        if (isset($row['marketable'])) $row['marketable'] = $row['marketable'] ? 'true' : 'false';
        if (isset($row['udfimg']))       $row['udfimg'] = $row['udfimg'] ? 'true' : 'false';
        if (isset($row['score_setting']) && empty($row['score_setting']))  $row['score_setting'] = 'number';
        if (isset($row['goods_type']) && empty($row['goods_type']))  $row['goods_type'] = 'normal';        
        if (isset($row['last_modify']) && empty($row['last_modify']))  $row['last_modify'] = time();        
                
        unset($row['buy_count']);
        unset($row['buy_w_count']);
        unset($row['notify_num']);
        unset($row['comments_count']);
        unset($row['view_w_count']);
        unset($row['view_count']);
        unset($row['buy_w_count']);
        unset($row['rank']);
        unset($row['rank_count']);
        unset($row['goods_info_update_status']);
        unset($row['stock_update_status']);
        unset($row['marketable_update_status']);
        unset($row['img_update_status']);
        
        if (isset($row['bn']) && isset($row['goods_id'])) {                                            
            $r = $this->_db->selectrow("select count(*) recordcount from ".$this->_tbpre."goods where goods_id!=".$this->_db->quote($row['goods_id'])." and bn=".$this->_db->quote($row['bn']));                    
            if ($r && $r['recordcount'] > 0) {                
                $row['bn'] = $this->genGoodsBn($row);
            }
            LogUtils::log_str($row['bn']);
        }

        LogUtils::log_obj($row);

        return true;
    }

    function validateUpdateAfter(&$row)
    {
        return true;
    }

    function validateDeleteBefore(&$row)
    {
        return true;
    }

    function validateDeleteAfter(&$row)
    {
        if (isset($row['goods_id']) && is_numeric($row['goods_id']))
        {
            $goods_id = $this->_db->quote($row['goods_id']);
            LogUtils::log_str('delete goods data:'.$goods_id);
            //products
            $this->_db->exec('delete from sdb_products where goods_id=' . $goods_id);
            //lv price
            $this->_db->exec('delete from sdb_goods_lv_price where goods_id=' . $goods_id);
            //memo (seoinfo and adjunct)
            $this->_db->exec('delete from sdb_goods_memo where goods_id=' . $goods_id);
            //rel goods
            $this->_db->exec('delete from sdb_goods_rate where goods_1=' . $goods_id . ' or goods_2=' . $goods_id);
            //tag rel
            $this->_db->exec('delete from sdb_tag_rel where rel_id=' . $goods_id);

            //images
            $this->_db->exec('delete from sdb_gimages where goods_id='. $goods_id);
        }

        return true;
    }
}

?>