<?php
class taobao_goods_product_listener extends pageFactory{
    function taobao_goods_product_listener(){        
        parent::pageFactory();
        $this->system = &$GLOBALS['system'];
        $this->db = &$this->system->database();
    }
    
    function toAdd($args,$p2)
    {
        /* 属性别名对应 */
        $oGtype = $this->system->loadModel('goods/gtype');
        $local_type = $oGtype->getTypeDetail( $_POST['goods']['type_id'] );
        
        $a_tmp_type = unserialize($_POST['tmp_type']);
        $a_rel = unserialize($_POST['relation']);
        foreach($a_rel as $k => $v){
            //如果选择的值和原POST过来的值不一致
            $p_val = $a_tmp_type['props'][$v['key']]['options'][$_POST['taobao']['p_'.$v['key']]];
            if($v['val'] != $p_val){
                $skey = array_search ($v['val'], $local_type['props'][$k]['options']);
                if($local_type['props'][$k]['optionAlias'][$skey]){
                    $aTmp = explode('|',$local_type['props'][$k]['optionAlias'][$skey]);
                    $aTmp = array_unshift($aTmp, $p_val);
                    $local_type['props'][$k]['optionAlias'][$skey] = implode('|', $aTmp);
                }else{
                    $local_type['props'][$k]['optionAlias'][$skey] = $p_val;
                }
            }
        }
        $t_data['props'] = $local_type['props'];
        $rs = $this->db->exec("select * from sdb_goods_type where type_id = ".$_POST['goods']['type_id']);
        $sql = $this->db->getUpdateSQL($rs, $t_data);
        if($sql) $this->db->exec($sql);
        
        require("mdl.taobao.php");
        $objTaobao = new mdl_taobao();
        $data = $_POST;
        $send_params = $this->system->call('tb_goods_data',$data,$objTaobao);

        foreach($data['taobao'] as $k => $v){
            if(substr($k, 0, 2) == 'p_'){
                $source_data[$k] = $v;
            }
        }
        $source_data['seller_cids'] = $data['taobao']['seller_cids'];
        $source_data['location_state'] = $data['taobao']['state'];//省份
        $source_data['location_city'] = $data['taobao']['city'];//城市

        if($data['taobao']['list_time'][0]){
            $source_data['list_time'] = $data['taobao']['list_time'];
        }


        $source_data['key_props'] = $data['taobao']['key_props'];
        //编辑使用到的规格数组

        $spec_desc = unserialize($data['taobao']['spec_desc']);
        foreach($data['linkImg'] as $key=>$value){
            foreach($value as $k1=>$v1){
                $value[$k1] = $spec_desc['spec_desc'][$key]['options'][$data['taobao']['specVId'][$key][$k1]]['spec_image'];
            }
            $data['linkImg'][$key] =  $value;
        }
        foreach($data['goodsImg'] as $key=>$value){
            foreach($value as $k1=>$v1){
                $value[$k1] = $spec_desc['spec_desc'][$key]['options'][$v1]['spec_goods_images'];
            }
            $data['goodsImg'][$key] =  $value;
        }

        $spec = array(
            'vars'=>$data['taobao']['vars'],
            'bn'=>$data['taobao']['bn'],
            'price'=>$data['taobao']['sku']['price'],
            'store'=>$data['taobao']['sku']['store'],
            'val'=>$data['taobao']['val'],
            'pSpecId'=>$data['taobao']['pSpecId'],
            'linkImg'=>$data['linkImg'],
            'goodsImg'=>$data['goodsImg']
        );

        $save_data['disabled'] = ($data['goods']['pub_taobao']==1)?'false':'true';
        $save_data['goods_id'] = $p2['goods_id'];
        $save_data['outer_key'] = $objTaobao->get_tb_nick();

        $send_params['ex_content'] = array_merge($source_data,$spec);
        $send_params['ex_content']['type_id'] = $data['goods']['type_id'];
        //检查是否首次保存
        $row = $objTaobao->get_outer_data($save_data['goods_id'], $save_data['outer_key']);
        if($row){//编辑时
            $tmp = unserialize($row['outer_content']);
            $images = $tmp['images'];
            //把新添加的加进来
            foreach($data['goods']['image_file'] as $key=>$value){
                if(!array_key_exists($value,$images['current'])){
                    $images['current'][$value] = -1;
                }
            }
            //把需要删除的加进来
            foreach($images['current'] as $key=>$value){
                if(!in_array($key,$data['goods']['image_file'])){
                    $images['del'][$key] = $value;
                    unset($images['current'][$key]);
                }
            }
    
        }else{//新增时
            foreach($data['goods']['image_file'] as $key=>$value){
                $images['current'][$value] = -1;
            }
        }

        $path_info = $objTaobao->get_cat_path($send_params['cid']);
        $objTaobao->logHistory($path_info['id'], $path_info['name']);

        //setting
        $setting = $data['taobao']['setting'];
        if($_POST['taobao']['auction_point']) $send_params['auction_point'] = $_POST['taobao']['auction_point'];
        $outer_content = array('content'=>$send_params,'images'=>$images,'setting'=>$setting);
        $save_data['outer_content'] = serialize($outer_content);
        if(isset($_POST['goods']['pub_taobao'])){
            $objTaobao->save_outer_data($save_data);
        }
    }
}
