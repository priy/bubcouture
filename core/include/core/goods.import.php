<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_import( &$proto, $gtype, &$object )
{
    if ( empty( $proto['name'] ) )
    {
        trigger_error( __( "编号为“" ).$proto['bn'].__( "”的商品没有名称！" ), E_USER_ERROR );
        exit( );
    }
    if ( in_array( $proto['bn'], $object->globalTmp['g'] ) )
    {
        trigger_error( __( "商品“" ).$proto['name'].__( "”的编号在文件中重复！" ), E_USER_ERROR );
        exit( );
    }
    $member_price = array( );
    $t_params = array( );
    $brand = $object->system->loadmodel( "goods/brand" );
    $cat = $object->system->loadmodel( "goods/productCat" );
    $params = $gtype['params'];
    $props = $gtype['props'];
    foreach ( $proto as $k => $v )
    {
        $tag = substr( $k, 0, 2 );
        switch ( $tag )
        {
        case "p_" :
            $temp = explode( "_", $k );
            if ( !( $props[$temp[1]]['type'] == "select" ) )
            {
                continue;
            }
            $interp = array_flip( $props[$temp[1]]['options'] );
            $alias = $props[$temp[1]]['optionAlias'];
            foreach ( $alias as $k1 => $v1 )
            {
                if ( !empty( $v1 ) )
                {
                    $the_alias = explode( "|", $v1 );
                    foreach ( $the_alias as $v2 )
                    {
                        $interp[$v2] = $k1;
                    }
                }
            }
            if ( array_key_exists( $v, $interp ) )
            {
                $proto[$k] = $interp[$v];
            }
            else
            {
                if ( !$v )
                {
                    continue;
                }
                trigger_error( __( "商品“" ).$proto['name'].__( "”中的属性值“" ).$v.__( "”并不存在！" ), E_USER_ERROR );
            }
            exit( );
        case "a_" :
            $temp = explode( "_", $k );
            $t_params = explode( "->", $temp[1] );
            $params[$t_params[0]][$t_params[1]] = $v;
            unset( $proto->$k );
            continue;
        default :
            do
            {
                if ( $k == "brand" )
                {
                    if ( !$v )
                    {
                        $proto['brand_id'] = 0;
                        $proto['brand'] = "";
                    }
                    else if ( $b = $brand->getbrandbyalias( "brand_id", trim( $v ) ) )
                    {
                        $proto['brand_id'] = $b['brand_id'];
                        $proto['brand'] = $v;
                    }
                    else
                    {
                        $proto['brand_id'] = 0;
                        $object->csvlog( "warning", __( "品牌错误" ) );
                    }
                }
                if ( $k == "cat_id" )
                {
                    if ( $catid = $cat->getcatidbyalias( $v ) )
                    {
                        $proto['cat_id'] = $catid;
                    }
                    else
                    {
                        $proto['cat_id'] = 0;
                        $object->csvlog( "warning", __( "商品“" ).$proto['name'].__( "”的分类不存在，导入后的商品分类为空" ) );
                    }
                }
                if ( $v == "Y" || $v == "y" || $v == "TRUE" || $v == "true" )
                {
                    $proto['marketable'] = "true";
                    $proto['downtime'] = time( );
                    if ( $Tmp_2 )
                    {
                        break;
                    }
                }
                $proto['marketable'] = "false";
                $proto['downtime'] = time( );
            } while ( $Tmp_2 );
            if ( $proto )
            {
                continue;
            }
            else
            {
                break;
            }
        }
    }
    $proto['params'] = $params;
    $proto['goods_name'] = $proto['name'];
    $object->checkgoodsspec( $proto );
    $object->globalTmp['g'][] = $proto['bn'];
    $object->csvlog( "tmp", $proto );
    return $proto['bn'];
}

?>
