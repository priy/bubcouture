<?php
include_once(CORE_DIR.'/api/shop_api_object.php');
class api_b2b_1_0_productline extends shop_api_object {
    var $app_error=array(
            'member has not sell permission'=>array('no'=>'b_productline_001','debug'=>'','level'=>'warning','info'=>'会员没分销权限','desc'=>'','debug'=>''),
            'member has not product line'=>array('no'=>'b_productline_002','debug'=>'','level'=>'warning','info'=>'会员没有指定产品线','desc'=>'','debug'=>''),
            'serivce data error'=>array('no'=>'b_productline_003','debug'=>'','level'=>'warning','info'=>'内部数据异常','desc'=>'','debug'=>''),
            'member has not set sell permission'=>array('no'=>'b_productline_004','debug'=>'','level'=>'warning','info'=>'会员没有设置代销权限','desc'=>'','debug'=>'')

    );

    function getColumns(){
        $columns=array(
            'pline_id'=>array('type'=>'int'),
            'pline_name'=>array('type'=>'string'),
            'custom_name'=>array('type'=>'string'),
            'disabled'=>array('type'=>'string'),
            'cat_id'=>array('type'=>'int'),
            'brand_id'=>array('type'=>'int'),
            'last_modify'=>array('type'=>'int')     
        );
        return $columns;
    }
    
    /**
     * 获取供应商产品线
     *
     * @param array $data 
     *
     * @return 供应商产品线
     */
    function search_product_line($data){
        $data['disabled'] = 'false'; 
        $data['orderby'] = 'pline_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_product_line where '.implode(' and ',$where));
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];        
        $where =$this->_filter($data);
        $data_info = $this->db->select('select '.implode(',',$data['columns']).' from sdb_product_line'.$where);
        $result['counts'] = count($data_info);  
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    /**
     * 获取经销商与产品线关系
     *
     * @param array $data 
     *
     * @return 经销商与产品线关系
     */
    function search_product_line_dealer($data){
        $data['orderby'] = 'member_id'; 
        $where = $this->before_filter($data);
        $result = $this->db->selectrow('select count(*) as all_counts from sdb_pline_to_dealer where '.implode(' and ',$where));
        $where =$this->_filter($data);
        $pline_to_dealer_list = $this->db->select('select member_id,pline_id,last_modify from sdb_pline_to_dealer'.$where);
     
        if($pline_to_dealer_list){
            $arr_certificate_id = array();
            
            foreach($pline_to_dealer_list as $k=>$pline_to_dealer){
                if(!isset($arr_certificate_id[$pline_to_dealer['member_id']])){
                    $member = $this->db->selectrow('select certificate_id from sdb_members where member_id='.$pline_to_dealer['member_id']); 
                    $arr_certificate_id[$pline_to_dealer['member_id']] = $member['certificate_id'];
                    $dealer_id = $member['certificate_id'];
                }else{
                    $dealer_id = $arr_certificate_id[$pline_to_dealer['member_id']];
                }
                $pline_to_dealer_list[$k]['dealer_id'] = $dealer_id;
                unset($pline_to_dealer_list[$k]['member_id']);
            }
        }
        
        $data_info = $pline_to_dealer_list;
        
        $result['counts'] = count($data_info);
        $result['data_info'] = $data_info;
        $this->api_response('true',false,$result);
    }
    
    function before_filter($filter){
        $where = array(1);
        if(isset($filter['last_modify_st_time'])){
            $where[]='last_modify >='.intval($filter['last_modify_st_time']);
        }
        if(isset($filter['last_modify_en_time'])){
            $where[]='last_modify <'.intval($filter['last_modify_en_time']);
        }
        if(isset($filter['disabled'])){
            $where[]='disabled="'.$filter['disabled'].'"';
        }
        
        return $where;
    }
     
    function _filter($filter){
        $where = $this->before_filter($filter);
        
        return parent::_filter($where,$filter);
    }
    
    function getPlineListByMember($nMId){
        return $this->db->select('SELECT member_id,pline_id,last_modify FROM sdb_pline_to_dealer WHERE member_id = '.$nMId);
    }
    
    function getInfo($pline_id){
       return $this->db->selectRow("SELECT * FROM sdb_product_line WHERE pline_id=".intval($pline_id));
    }
    
    /**
     * 检查会员的代销权限
     *
     * @param array $member 
     * @param array $arr_goods 
     *
     * @return 检查会员的代销权限
     */
    function checkDealerPurview($member='',$arr_goods){
        if(!is_array($arr_goods) || count($arr_goods)<0)return false;
        
        $dealer_purview = $member['dealer_purview'];
        $arr_dealer_purview = array(0,1,2,3);
        if(!empty($member) && in_array($dealer_purview,$arr_dealer_purview)){
           $member_id = $member['member_id'];
           $member_lv_id =  $member['member_lv_id'];
           switch($dealer_purview){
                 case 0://不在1,2,3范围里，会员没有设置代销权限
                      $this->api_response('fail','data fail',$result,'会员没有设置代销权限');             
                 break;
                 case 1://无分销权限 
                      $this->api_response('fail','data fail',$result,'会员没分销权限');          
                 break;
                 case 2://所有商品 
                    
                 break;
                 case 3://指定产品线
                     $objPline = &$this->system->loadModel('trading/pline');
                     $objMemberPline = &$this->system->loadModel('member/memberpline');
                     $objGoods = &$this->system->loadModel('trading/goods');
                     $objProductCat = &$this->system->loadModel('goods/productCat');
                     $member_line_list = $objMemberPline->getPlineListByMember($member_id);
                     if($member_line_list){
                         $is_dealer_cat = array();
                         foreach($member_line_list as $pline){
                             $pline_info = $objPline->getInfo($pline['pline_id']);
                             
                             //如果此产品线分类不是任意，就获取它的分类ID以及子分类ID作比较
                             if($pline_info['cat_id'] != -1 && !in_array($pline_info['cat_id'],$is_dealer_cat)){
                                $arr_sub_cat_id = $objProductCat->getSubCatId($pline_info['cat_id']);
                                if(!empty($arr_sub_cat_id)){
                                   $arr_sub_cat_id[] = $pline_info['cat_id'];
                                   $is_dealer_cat = array_merge($is_dealer_cat,$arr_sub_cat_id);
                                }else{
                                   $is_dealer_cat[] = $pline_info['cat_id'];
                                }
                             }
                             
                             foreach($arr_goods as $k=>$goods){
                                 if(isset($goods['is_dealer']) && $goods['is_dealer']){continue;}//商品已经是可代销,就不作处理
                                 //保证商品数组里存在分类ID与品牌ID
                                
                                 if(!isset($goods['cat_id']) || (!isset($goods['brand_id']) && !is_null($goods['brand_id'])) ){
                                     $goods = $objGoods->getFieldById($goods['goods_id']);
                                 }
                                
                                 if($pline_info['cat_id'] == -1 && $pline_info['brand_id'] == -1){
                                   //当产品线配置为分类是任意并且品牌也是任意,可代销
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else if($pline_info['cat_id'] == -1 && $pline_info['brand_id'] == $goods['brand_id']){
                                   //当产品线配置为分类是任意,品牌与商品的品牌ID也能对上,可代销(如果商品品牌ID为NULL,不纳入可代销范围)
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else if($pline_info['brand_id'] == -1 && in_array($goods['cat_id'],$is_dealer_cat)){
                                   //当产品线配置为品牌是任意,分类与商品的分类ID也能对上,可代销(如果商品分类ID为NULL,不纳入可代销范围)
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else if(in_array($goods['cat_id'],$is_dealer_cat) && $pline_info['brand_id'] == $goods['brand_id']){ 
                                   //当产品线配置为分类与商品的分类ID能对上,品牌与商品的品牌ID也能对上,可代销
                                    $arr_goods[$k]['is_dealer'] = true;
                                 }else{
                                   //否则就是不可代销
                                    $arr_goods[$k]['is_dealer'] = false;
                                 }                  
                             }                                     
                         }
                         
                         foreach($arr_goods as $k=>$goods){
                              if(!$arr_goods[$k]['is_dealer']){
                                  $this->api_response('fail','data fail',$result,'会员没分销权限');          
                              }
                         }         
                     }else{//如果选择指定产品线选项,却没有指定产品线,默认为不可代销
                        $this->api_response('fail','data fail',$result,'会员没有指定产品线');          
                     }
                 break;
                 default:
                       $this->api_response('fail','data fail',$result,'会员没分销权限');          
           }
           
        }else{
            $this->api_response('fail','data fail',$result,'内部数据异常');          
        }
    }



}