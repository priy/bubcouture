<?php
/**
 *  类型转换
 */
if(!class_exists('app')){
    require(CORE_DIR.'/include/app.php');
}
if(!class_exists('app_taobao_goods')){
    require('app.taobao_goods.php');
}

class mdl_gtypetransform extends app_taobao_goods{
    
    function typeTransformCheck($sourceTypeId=0,$destTypeId=0, &$goods, $sourceTypeData = null, $destTypeData = null, $transInShopex = true ){
        $oGtype = $this->system->loadModel('goods/gtype');
        $this->sourceType = $sourceTypeData?$sourceTypeData:$oGtype->getTypeDetail($sourceTypeId);
        $this->destType = $destTypeData?$destTypeData:$oGtype->getTypeDetail($destTypeId);

        return $this->specTransformCheck( $goods['spec_desc'] );
    }
    /**   类型转换
       *   sourceTypeId    source typeid
       *   destTypeId        dest typeid
       *   goods                 goods data
    */
    function typeTransform($sourceTypeId , $destTypeId, &$goods, $sourceTypeData = null, $destTypeData = null, $transInShopex = true ){
        //类型id相同 不做操作 跳出
        if( $transInShopex && ( $sourceTypeId == $destTypeId ) )
            return false;

        $this->transInShopex = $transInShopex;
        $oGtype = $this->system->loadModel('goods/gtype');
        $this->sourceType = $sourctTypeData?$sourceTypeData:$oGtype->getTypeDetail( $sourceTypeId );         //得到source类型
        $this->destType = $destTypeData?$destTypeData:$oGtype->getTypeDetail( $destTypeId );                  //得到dest类型

        //生成转换规则
        //捕获转换规则异常

        $specTransfromFlag = $this->specTransformCheck( $goods['spec_desc'] );
        $brandTransformFlag = $this->brandTransformCheck();
        $propsTransformFlag = $this->propsTransformCheck();
        $paramsTransformFlag = $this->paramsTransformCheck();
        
        if( 
            $transInShopex && 
            (
                !$specTransformFlag || 
                !$brandTransformFlag || 
                !$propsTransformFlag || 
                !$paramsTransformFlag 
            ) 
        ){
            return false;
        }
        //将数据进行转换
        $obj = $this->specTransform($goods);
        $obj = $obj->propsTransform($goods);
        $obj = $obj->brandTransform($goods);
        $obj = $obj->paramsTransform($goods);
        return true;
    }

    //规格转换验证
    /**
       *  $toOutTypeSign 外部系统表示 本系统为false 外部系统 如淘宝传入true
       */
    function specTransformCheck($goodsSpecDesc = null, $transInShopex = true){

        /* 比对规格
          * 类型绑定规格数须完全相等
          * 类型绑定规格数不一致时 销毁商品绑定规格
          * 影响： 根据规格转换规则 复制给$this->specTransformState
          * $this->specTransformState= array(
          *         'different' => 完全不匹配 执行操作：不复制规格,
          *         'same' => 完全相同 执行操作：复制规格,
          *         'contain' => 规格数量相等 source规格值包含于dest规格值 执行操作：转换规格
          *  )
          *  when $this->specTransformState == 'contain'
          *  specTransformHash            规格id对应hash map
                array(
                    source_spec_id => array(
                        'spec_id' => dest_spec_id
                        'spec_value' => array(
                            source_spec_value_id => dest_spec_value_id
                        )
                    )
                 )
         */
        $this->specTransformError = array();
        if( count( $goodsSpecDesc ) != count( $this->destType['spec'] ) ){
            $this->specTransformState = 'different';
            return true;
        }else{
            if(
                $transInShopex && 
                (
                    array_diff( array_keys( $goodsSpecDesc ), array_keys( $this->destType['spec'] ) ) ==
                    array_diff( array_keys( $this->destType['spec'] ), array_keys( $goodsSpecDesc ) )
                )
            ){
                //类型绑定规格完全相同（id） 执行复制
                $this->specTransformState = 'same';
                return true;
            }else{
                //类型绑定规格不完全相同 规格名须完全相等 source规格值包含于dest规格值

                //------整理goods数据spec_desc字段----整理spec_desc所管理的规格作为源规格模板-----
                $goodsSpecDescHash = $this->_prepareGoodsSpecDesc($goodsSpecDesc); 
   
                //-------整理目标规格--------                 
                $this->destSpecAliasHash = array();       //规格别名->规格名
                $this->destSpecHash = array();            //规格名->规格id
                $this->destSpecValueAliasHash = array();  //规格值别名->规格值名
                $this->destSpecValueHash = array();       //规格值名->规格值id
                $this->_prepareDestSpecHashTable();
                //---整理源规格模板与目标规格建立对应关系---- 
                if( !($this->specTransformHash = $this->_prepareSpecSrc2DestHash( $goodsSpecDescHash )) ){
                    $this->specTransformState = 'different';
                    return true;
                }
                //todo----
                $specTransformError = array();
                foreach( $goodsSpecDescHash as $sourceSpecId => $srcv ){
                    $this->specTransformHash[$sourceSpecId]['spec_value'] = $this->_prepareSpecValueSrc2DestHash( $srcv['spec_value'],$this->specTransformHash[$sourceSpecId]['spec_id'],$specTransformError );
                    if( !empty( $specTransformError ) )
                        $this->specTransformError[$sourceSpecId] = array( 
                            'spec_name' => $srcv['name'],
                            'spec_value' => $specTransformError
                        );
                }
                $this->destSpecAliasHash = null;
                $this->destSpecHash = null;
                $this->destSpecValueAliasHash = null;
                $this->destSpecValueHash = null;
                //-----------------------
           }
        }
        if(!empty( $this->specTransformError )){
            $this->specTransformState = 'spec_value different';
            return false;
        }
        $this->specTransformState = 'contain';
        return true;
    }

    function &_prepareDestSpecHashTable(){
        foreach( $this->destType['spec'] as $destSpecId => $destv ){
            if( !$transInShopex ){
                $destv['name'] = $destv['spec_name']?$destv['spec_name']:$destv['name'];
                $this->destType['spec'][$destSpecId]['name'] = $destv['name'];
                $destv['spec_value'] = empty($destv['options'])?$destv['spec_value']:$destv['options'];
                $this->destType['spec'][$destSpecId]['spec_value'] = $destv['spec_value'];
            }
            if( $destv['alias'] || $destSpecv['alias'] === 0 ){
                foreach( explode('|',$destv['alias']) as $tmpDestvAlias ){
                    $this->destSpecAliasHash[$tmpDestvAlias] = $destv['name'];
                }
            }
            $this->destSpecHash[$destv['name']] = $destSpecId;
            foreach( $destv['spec_value'] as $destSpecValueId => $destSpecv ){
                if( $destSpecv['alias'] || $destSpecv['alias'] === 0 ){
                    foreach( explode( '|', $destSpecv['alias'] ) as $destSpecVAlias ){
                        $this->destSpecValueAliasHash[$destSpecId][$destSpecVAlias] = $destSpecv['spec_value'];
                    }
                }
                $this->destSpecValueHash[$destSpecId][$destSpecv['spec_value']] = $destSpecValueId;
            }
        }
    }

    function &_prepareGoodsSpecDesc($goodsSpecDesc){
        if( !empty($goodsSpecDesc) ){
            foreach( $goodsSpecDesc as $goodsSpecId => $goodsSpecV ){
                foreach( $goodsSpecV as $goodsPSpecId => $goodsPSpecV ){
                    $specDescHash[$goodsSpecId][$goodsPSpecV['spec_value_id']] = $goodsPSpecV['spec_value'];
                }
            }
        }
        $oSpec = $this->system->loadModel('goods/specification');
        $goodsSpecDescHash = array();
        foreach( $specDescHash as $goodsSpecId => $goodsSpecValueList ){
            $goodsSpecDescHash[$goodsSpecId] = $oSpec->getFieldById($goodsSpecId, array('spec_name as name','alias') );
            $tmpSpecValue = $oSpec->getValueList($goodsSpecId);
            foreach( $goodsSpecValueList as $goodsSpecValueId => $goodsSpecValue )
                $goodsSpecDescHash[$goodsSpecId]['spec_value'][$goodsSpecValueId] = $tmpSpecValue[$goodsSpecValueId];
            unset($tmpSpecValue);
        }
        return $goodsSpecDescHash;
    }

    function &_prepareSpecSrc2DestHash(&$goodsSpecDescHash){
        $toDestSpecId = array();    //源规格id->目标规格id
        foreach( $goodsSpecDescHash as $sourceSpecId => $srcv ){
            $toDestSpecId[$sourceSpecId]['spec_id'] = 0;
            if( $srcv['alias'] || $srcv['alias'] === 0 ){
                foreach( explode('|',$srcv['alias']) as $tmpSrcSpecAlias ){
                    if( isset( $this->destSpecAliasHash[$tmpSrcSpecAlias] ) ){
                        $toDestSpecId[$sourceSpecId]['spec_id'] = $this->destSpecHash[$this->destSpecAliasHash[$tmpSrcSpecAlias]];
                    }
                }
               if( !$toDestSpecId[$sourceSpecId]['spec_id'] ){
                    foreach( explode('|',$srcv['alias']) as $tmpSrcSpecAlias ){
                        if( isset( $this->destSpecHash[$tmpSrcSpecAlias] ) ){
                            $toDestSpecId[$sourceSpecId]['spec_id'] = $this->destSpecHash[$tmpSrcSpecAlias];
                        }
                    }
                }
            }
            if( !$toDestSpecId[$sourceSpecId]['spec_id'] && isset( $this->destSpecAliasHash[$srcv['name']] ) ){
                $toDestSpecId[$sourceSpecId]['spec_id'] = $this->destSpecHash[$this->destSpecAliasHash[$srcv['name']]];
            }
            if( !$toDestSpecId[$sourceSpecId]['spec_id'] && isset( $this->destSpecHash[$srcv['name']] ) ){
                $toDestSpecId[$sourceSpecId]['spec_id'] = $this->destSpecHash[$srcv['name']];
            }
            if( !$toDestSpecId[$sourceSpecId]['spec_id'] ){
                //$this->specTransformState = 'different';
                return false;
            }
        }
        return $toDestSpecId;
    }
    
    function &_prepareSpecValueSrc2DestHash( $specValue,$destSpecId, &$specTransformError ){
        $tSpecValueHash = array();
        foreach( $specValue as $sourceSpecValueId => $srcSpecv ){
            if( $srcSpecv['alias'] || $srcSpecv === 0 ){
                foreach( explode('|',$srcSpecv['alias']) as $srcSpecvAlias )
                    if( isset( $this->destSpecValueAliasHash[$destSpecId][$toDestSpecId[$sourceSpecValueId]][$srcSpecvAlias] ) )
                        $tSpecValueHash[$sourceSpecValueId] = $this->destSpecValueHash[$destSpecId][$this->destSpecValueAliasHash[$destSpecId][$toDestSpecId[$sourceSpecValueId]][$srcSpecvAlias]];
                foreach( explode('|',$srcSpecv['alias']) as $srcSpecvAlias ){
                    if( !isset($tSpecValueHash[$sourceSpecValueId]) && isset( $this->destSpecValueHash[$destSpecId][$srcSpecvAlias] ) ){
                        $tSpecValueHash[$sourceSpecValueId] = $this->destSpecValueHash[$destSpecId][$srcSpecvAlias];
                    }
                }
            }

            if( !isset( $tSpecValueHash[$sourceSpecValueId] ) && $this->destSpecValueAliasHash[$destSpecId][$srcSpecv['spec_value']] )
                $tSpecValueHash[$sourceSpecValueId] = $this->destSpecValueHash[$destSpecId][$this->destSpecValueAliasHash[$destSpecId][$srcSpecv['spec_value']]];
            if( !isset( $tSpecValueHash[$sourceSpecValueId] ) && $this->destSpecValueHash[$destSpecId][$srcSpecv['spec_value']] )
                $tSpecValueHash[$sourceSpecValueId] = $this->destSpecValueHash[$destSpecId][$srcSpecv['spec_value']];

            if( !isset( $tSpecValueHash[$sourceSpecValueId] ) )
                $specTransformError[] = $srcSpecv['spec_value'];
        }
        return $tSpecValueHash;
    }

    //规格转换
    /**

    **/
    function &specTransform(&$goods){
        if( $goods['spec_desc'] && $this->specTransformState == 'contain' ){
            $goodsSpecDesc = array();
            foreach( $goods['spec_desc'] as $t_specId => $pSpec ){
                foreach( $pSpec as $pSpecId => $pSpecValue ){
                    $pSpecValue['spec_value_id'] = $this->specTransformHash[$t_specId]['spec_value'][$pSpecValue['spec_value_id']];
                    $goodsSpecDesc[$this->specTransformHash[$t_specId]['spec_id']][$pSpecId] = $pSpecValue;
                }
            }
            $goods['spec_desc'] = $goodsSpecDesc;
            foreach( $goods['products'] as $pid => $pro ){
                $aProProps = array();
                foreach( $pro['props'] as $pkey => $pvalue ){
                    foreach( $pvalue as $pSpecId => $pSpecv ){
                        if( $pkey == 'spec_value_id' ){
                            $aProProps[$pkey][$this->specTransformHash[$pSpecId]['spec_id']] = $this->specTransformHash[$pSpecId]['spec_value'][$pSpecv];
                        }else{
                            $aProProps[$pkey][$this->specTransformHash[$pSpecId]['spec_id']] = $pSpecv;
                        }
                    }
                }
                $goods['products'][$pid]['props'] = $aProProps;
            }
        }
        return $this;
    }

    //属性转换
    /**
     * this->typePropsHash = array(
     *    source props key => array(
     *          'key' => dest props key,
     *          'dest_type' => dest props type,
     *          'source_type' => source props type,
     * =========================================
     * if 源与目标类型都为输入类型 转换时执行copy
     * =========================================
     * else if源类型与目标类型都为单选类型
     *          'propsSelectHash' => array(
     *              source props k => dest props k
     *              ......
     *          ),
     * =========================================
     * else if源类型与目标类型为不同类型 需要记录单选类型的属性值的key name alia
     *          'options' => array(
     *              'source' => array(
     *                  source props key => array(
     *                      'name' => source props name,
     *                      'alias' => '|'.source props alias.'|'
     *                  )
     *              ),
     *              'dest' => array(
     *                  dest props key => array(
     *                      'name' => source props name,
     *                      'alias' => '|'.source props alias.'|'
     *                  )
     *              )
     *          )
     * =========================================
     *    )
     * )
    */
    function propsTransformCheck(){
        //属性比对
        //目标类型属性整理
        $propsAlias = array();                   //属性别名数组
        $propsOptions = array();             //属性数组
        $propsValueAlias = array();         //属性值别名数组
// ===================================================
        foreach( $this->destType['props'] as $destPropsk => $destPropsv ){
            foreach( explode('|',$destPropsv['alias']) as $aliasName ){
                if( $aliasName == '' )
                    continue;
                $propsAlias[$aliasName] = $destPropsv['name'];
            }
            $propsOptions[$destPropsv['name']]['key'] = $destPropsk;
            $propsOptions[$destPropsv['name']]['type'] = $destPropsv['type'];

        }
        //source属性对应
        //通过别名比对dest属性
        foreach( $this->sourceType['props'] as $sourcePropsk => $sourcePropsv ){
            $destPropsName = '';  //对应的dest属性名
            foreach( explode('|',$sourcePropsv['alias'] ) as $sourceAliasName ){
                if( !$sourceAliasName || !isset( $propsAlias[$sourceAliasName] ) )
                    continue;
                $destPropsName = $propsAlias[$sourceAliasName];
                break;
            }

            if( !$destPropsName ){
                foreach( explode('|',$sourcePropsv['alias']) as $sourceAliasName ){
                    if( !$sourceAliasName || !isset( $propsOptions[$sourceAliasName] ) )
                        continue;
                    $destPropsName = $sourceAliasName;
                    break;
                }
            }

            //通过属性名比对dest属性
            if( !$destPropsName ){
                if( !isset($this->typePropsHash[$sourcePropsk]) && isset( $propsAlias[$sourcePropsv['name']] ) ){
                    $destPropsName = $propsAlias[$sourcePropsv['name']];
                }
            }

            if( !$destPropsName ){
                if( !isset($this->typePropsHash[$sourcePropsk]) && isset( $propsOptions[$sourcePropsv['name']] ) ){
                    $destPropsName = $sourcePropsv['name'];
                }
            }

            if( $destPropsName ){
                $this->typePropsHash[$sourcePropsk]['key'] = $propsOptions[$destPropsName]['key'];
                $this->typePropsHash[$sourcePropsk]['dest_type'] = $propsOptions[$destPropsName]['type'];
                $this->typePropsHash[$sourcePropsk]['source_type'] = $sourcePropsv['type'];
            }
            unset($destPropsName);

            //属性比对成功 比对属性值
            if( isset($this->typePropsHash[$sourcePropsk]) ){

                //目标type属性整理
                $destTypeOpAlias = $this->destType['props'][$this->typePropsHash[$sourcePropsk]['key']]['optionAlias'];
                $destTypeOps = $this->destType['props'][$this->typePropsHash[$sourcePropsk]['key']]['options'];

                if( $this->typePropsHash[$sourcePropsk]['dest_type'] == $this->typePropsHash[$sourcePropsk]['source_type'] ){
                    if( $this->typePropsHash[$sourcePropsk]['dest_type'] == 'select' ){
                        $this->_correspondSelectProps($destTypeOpAlias, $destTypeOps,$sourcePropsk ,$sourcePropsv);
                    }
                }else{
                    $this->_correspondDiffProps($destTypeOpAlias, $destTypeOps,$sourcePropsk ,$sourcePropsv);
                }
            }
        }
        return true;
    }

    function &propsTransform(&$goods){
        $_typeProps = array();
        foreach( $this->typePropsHash as $sourcePropsk => $sourcePropsv ){
            if( $sourcePropsv['dest_type'] == $sourcePropsv['source_type'] ){
                switch( $sourcePropsv['dest_type'] ){
                    case 'input':
                        $_typeProps['p_'.$sourcePropsv['key']] = $goods['p_'.$sourcePropsk];
                        break;

                    case 'select':
                        $_typeProps['p_'.$sourcePropsv['key']] = $sourcePropsv['propsSelectHash'][$goods['p_'.$sourcePropsk]];
                        break;
                }
            }else{
                switch( $sourcePropsv['dest_type'] ){
                    case 'input':
                        $_typeProps['p_'.$sourcePropsv['key']] = $sourcePropsv['options']['source'][$goods['p_'.$sourcePropsk]]['name'];
                        break;

                    case 'select':
                        foreach( $sourcePropsv['options']['dest'] as $s_typePropsk => $s_typePropsv ){
                            if( $s_typePropsv['alias'] ){
                                foreach( explode('|',$s_typePropsv['alias']) as $s_typePropsVName ){
                                    if(
                                        strpos($goods['p_'.$sourcePropsk],$s_typePropsVName ) === false &&
                                        strpos($s_typePropsVName,$goods['p_'.$sourcePropsk]) === false
                                    )
                                        continue;
                                    $_typeProps['p_'.$sourcePropsv['key']] = $s_typePropsk;
                                    break;
                                }
                            }
                        }
                        if( !isset( $_typeProps['p_'.$sourcePropsv['key']] ) ){
                            foreach( $sourcePropsv['options']['dest'] as $s_typePropsk => $s_typePropsv ){
                                if(
                                    strpos($goods['p_'.$sourcePropsk],$s_typePropsv['name'] ) === false &&
                                    strpos($s_typePropsv['name'],$goods['p_'.$sourcePropsk]) === false
                                )
                                    continue;
                                $_typeProps['p_'.$sourcePropsv['key']] = $s_typePropsk;
                                break;
                            }
                        }
                        break;
                }
            }
        }
        foreach( $goods as $goodsk => $goodsv ){
            if(substr($goodsk,0,2) == 'p_'){
                unset($goods[$goodsk]);
            }
        }
        foreach( $_typeProps as $typePropsk => $typePropsv ){
            $goods[$typePropsk] = $typePropsv;
        }
        return $this;
    }

    /**
     * 源类型与目标类型都为select型时 建立对应关系
     */
    function _correspondSelectProps($destTypeOpAlias, $destTypeOps,$sourcePropsk ,$sourcePropsv){
        $_destTypeOpNameHash = array();
        $_destTypeOpAliasHash = array();
        /*
        var_dump($destTypeOpAlias);
        var_dump($destTypeOps);
        foreach( $destTypeOpAlias as $d_typeOpAliask => $d_typeOpAliasv ){
            $_destTypeOpNameHash[$destTypeOps[$d_typeOpAliask]] = $d_typeOpAliask;
            if( $d_typeOpAliasv ){
                foreach( explode('|',$d_typeOpAliasv) as $d_typeOpAliasVName )
                    $_destTypeOpAliasHash[$d_typeOpAliasVName] = $destTypeOps[$d_typeOpAliask];
            }
        }
        */

        foreach( $destTypeOps as $d_typeOpAliask => $d_typeOpAliasv ){
            $_destTypeOpNameHash[$destTypeOps[$d_typeOpAliask]] = $d_typeOpAliask;
            if( $d_typeOpAliasv ){
                foreach( explode('|',$d_typeOpAliasv) as $d_typeOpAliasVName )
                    $_destTypeOpAliasHash[$d_typeOpAliasVName] = $destTypeOps[$d_typeOpAliask];
            }
        }
        
        foreach( $sourcePropsv['optionAlias'] as $s_typeOpAliask => $s_typeOpAliasv ){
            // -------------------------------
            if( $s_typeOpAliasv ){
                
                foreach( explode('|',$s_typeOpAliasv) as $s_typeOpAliasVName ){
                    if( isset( $_destTypeOpAliasHash[$s_typeOpAliasVName] ) ){
                        $this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAliask] = $_destTypeOpNameHash[$_destTypeOpAliasHash[$s_typeOpAliasVName]];
                        break;
                    }
                }
            }

            // ---------------------------------
            if( $s_typeOpAliasv && !isset($this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAlias]) ){
                foreach( explode('|', $s_typeOpAliasv) as $s_typeOpAliasVName ){
                    if( isset( $_destTypeOpNameHash[$s_typeOpAliasVName] ) ){
                        $this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAliask] = $_destTypeOpNameHash[$s_typeOpAliasVName];
                        break;
                    }
                }
            }

            // ---------------------------------
            if( !isset($this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAlias]) ){
                if( isset( $_destTypeOpAliasHash[$sourcePropskv['options'][$s_typeOpAliask]] ) ){
                    $this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAliask] = $_destTypeOpNameHash[$_destTypeOpAliasHash[$sourcePropskv['options'][$s_typeOpAliask]]];
                }
            }

            // --------------------------------
            if( !isset($this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAlias]) ){
                if( isset( $_destTypeOpNameHash[$sourcePropsv['options'][$s_typeOpAliask]] ) ){
                    $this->typePropsHash[$sourcePropsk]['propsSelectHash'][$s_typeOpAliask] = $_destTypeOpNameHash[$sourcePropsv['options'][$s_typeOpAliask]];
                }
            }

        }
    }

    function _correspondDiffProps($destTypeOpAlias, $destTypeOps,$sourcePropsk ,$sourcePropsv){
        if( $this->typePropsHash[$sourcePropsk]['source_type'] == 'select' ){
            foreach( $sourcePropsv['optionAlias'] as $s_typeOpAliask => $s_typeOpAliasv ){
                $this->typePropsHash[$sourcePropsk]['options']['source'][$s_typeOpAliask] = array(
                    'name' => $sourcePropsv['options'][$s_typeOpAliask] ,
                    'alias' => $s_typeOpAliasv
                );
            }
        }
        if( $this->typePropsHash[$sourcePropsk]['dest_type'] == 'select' ){
            foreach( $destTypeOpAlias as $d_typeOpAliask => $d_typeOpAliasv ){
                $this->typePropsHash[$sourcePropsk]['options']['dest'][$d_typeOpAliask] = array(
                    'name' => $destTypeOps[$d_typeOpAliask] ,
                    'alias' => $d_typeOpAliasv
                );
            }
        }
    }

    /**
     *
     * return array(
     *     source brand id => dest brand id
     * )
     */
    function brandTransformCheck(){
        $oBrand = $this->system->loadModel('goods/brand');
        $sourceBrand = $oBrand->getTypeBrands($this->sourceType['type_id']);
        $destBrand = $oBrand->getTypeBrands($this->destType['type_id']);

        $sourceBrandHash = $this->_createAliasNameIdHash( $sourceBrand, 'brand_keywords', 'brand_name', 'brand_id' );
        $destBrandHash = $this->_createAliasNameIdHash( $destBrand, 'brand_keywords', 'brand_name', 'brand_id' );

        foreach( $destBrandHash['name2id'] as $bk => $bv ){
            if( isset( $sourceBrandHash['name2id'][$bk] ) )
                $this->brandTransformHash[$sourceBrandHash['name2id'][$bk]] = $bv;
            if( isset( $sourceBrandHash['alias2name'][$bk] ) )
                $this->brandTransformHash[$sourceBrandHash['name2id'][$sourceBrandHash['alias2name'][$bk]]] = $bv;
        }

        foreach( $destBrandHash['alias2name'] as $bk => $bv ){
            if( isset( $sourceBrandHash['name2id'][$bk] ) )
                $this->brandTransformHash[$sourceBrandHash['name2id'][$bk]] = $sourceBrandHash['name2id'][$bv];
            if( isset( $sourceBrandHash['alias2name'][$bk] ) )
                $this->brandTransformHash[$sourceBrandHash['name2id'][$sourceBrandHash['alias2name'][$bk]]] = $sourcebrandHash['name2id'][$sourceBrandHash['alias2name'][$bk]];
        }

        return true;
    }

    function &brandTransform(&$goods){
        $oBrand = $this->system->loadModel('goods/brand');
        $brandName = $oBrand->getFieldById($goods['brand_id'],array('brand_name'));
        $brandName = $brandName['brand_name'];
        foreach( $this->destType['props'] as $goodsp => $propsv ){
            if(
                (
                    strpos( '|'.$propsv['name'].'|'.$propsv['alias'].'|', '|品牌|') !== false ||
                    strpos( '|'.$propsv['name'].'|'.$propsv['alias'].'|', '|brand|') !== false
                ) && !$goods['p_'.$goodsp]
            ){
                if( $propsv['type'] == 'input' ){
                    $goods['p_'.$goodsp] = $brandName;
                }else{
                    foreach( $propsv['options'] as $propsOpk => $propsOpv ){
                        if( strpos( '|'.$propsOpv.'|'.$propsv['optionAlias'].'|',$brandName ) !== false ){
                            $goods['p_'.$goodsp] = $propsOpk;
                        }
                    }
                }
            }
        }

        if( isset( $this->brandTransformHash[$goods['brand_id']] ) ){
            $goods['brand_id'] = $this->brandTransformHash[$goods['brand_id']];
        }else{
            foreach( $this->sourceType['props'] as $pk => $pv ){
                if(
                    strpos( '|'.$pv['name'].'|'.$pv['alias'].'|','品牌' ) !== false ||
                    strpos( '|'.$pv['name'].'|'.$pv['alias'].'|','brand' ) !== false
                ){
                    if( $pv['type'] == 'input' ){
                        $propsBrandName = $goods['p_'.$pk];
                    }else if( $pv['type'] == 'select' ){
                        $propsBrandName = $pv['options'][$goods['p_'.$pk]];
                    }
                    break;
                }
            }
            if( $propsBrandName ){
                $sql = 'SELECT a.brand_id FROM sdb_brand a LEFT JOIN sdb_type_brand b ON a.brand_id = b.brand_id WHERE a.brand_name = "'.$propsBrandName.'" AND b.type_id = '.$this->destType['type_id'];
                $bId = $this->db->selectrow($sql);
                if( $bId )
                    $goods['brand_id'] = $bId['brand_id'];
            }
        }
        return $this;
    }

    function paramsTransformCheck(){
        $sParamsHash = array(
            'alias2name' => array(),
            'name2id' => array()
        );
        $sParams = &$this->sourceType['params'];
        $dParams = &$this->destType['params'];
        foreach( $dParams as $dParamGroup => $dParamv ){
            foreach( $dParamv as $dParamName => $dParamAlias ){
                foreach( explode('|',$dParamAlias) as $dAlias ){
                    if( !$dAlias )
                        continue;
                    $dParamsHash['alias2name'][$dAlias] = $dParamName;
                }
                $dParamsHash['name2id'][$dParamName] = $dParamGroup;
            }
        }
        foreach( $sParams as $sParamGroup => $sParamv ){
            foreach( $sParamv as $sParamName => $sParamAlias ){
                foreach( explode('|',$sParamAlias) as $sAlias ){
                    if( !$sAlias )
                        continue;
                    if( isset( $dParamsHash['alias2name'][$sAlias] ) ){
                        $this->paramTransformHash[$sParamName] = array( 'group' => $dParamsHash['name2id'][$dParamsHash['alias2name'][$sAlias]] , 'name' => $dParamsHash['alias2name'][$sAlias] );
                        break;
                    }
                    if( !isset( $this->paramTransformHash[$sParamName] ) ){
                        if( isset( $dParamsHash['name2id'][$sAlias] ) ){
                            $this->paramTransformHash[$sParamName] = array( 'group' => $dParamsHash['name2id'][$sAlias] , 'name' => $sAlias);
                            break;
                        }
                    }
               }
                if( !isset( $this->paramTransformHash[$sParamName] ) && isset( $dParamsHash['alias2name'][$sParamName] ) )
                    $this->paramTransformHash[$sParamName] = array(
                        'group' => $dParamsHash['name2id'][$dParamsHash['alias2name'][$sParamName]] ,
                        'name' => $dParamsHash['alias2name'][$sParamName]
                    );
                if( !isset( $this->paramTransformHash[$sParamName] ) && isset( $dParamsHash['name2id'][$sParamName] ) )
                    $this->paramTransformHash[$sParamName] = array(
                        'group' => $dParamsHash['name2id'][$sParamName],
                        'name' => $sParamName
                    );
            }
        }
        return true;
    }

    function &paramsTransform(&$goods){
        $gParams = $goods['params'];
        unset( $goods['params'] );
        foreach( $gParams as $sParamv ){
            foreach( $sParamv as $spk => $spv ){
                if( isset( $this->paramTransformHash[$spk] ) ){
                    $goods['params'][$this->paramTransformHash[$spk]['group']][$this->paramTransformHash[$spk]['name']] = $spv;
                }
            }
        }
        return $this;
    }

    /**
     * 分析数据 迭代别名与名称和id
     * $data            需要分析的array数据
     * $alias           别名key名称
     * $name            name key名称
     * $id              id key 名称
     * $tag             别名分隔符 默认'|'
     * return array(
     *     'alias2name' => array(
     *         alias => name
     *     ),
     *     'name2id' => array(
     *         name => id
     *     )
     *)
     */
    function _createAliasNameIdHash( $data, $alias, $name, $id, $tag = '|' ){
        $rs = array();
        foreach( $data as $aData ){
            foreach( $aData[$alias] as $dataAlias ){
                if( !$dataAlias )
                    continue;
                $rs['alias2name'][$dataAlias] = $aData[$name];
            }
            $rs['name2id'][$aData[$name]] = $aData[$id];
        }
        return $rs;
    }

}
?>
