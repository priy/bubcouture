<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function goods_export( $v, $proto, $t_name, $props, $params, &$object )
{
    $goods = $object->system->loadmodel( "trading/goods" );
    $objCat = $object->system->loadmodel( "goods/productCat" );
    $row[0] = $proto;
    $v['t_name'] = $t_name;
    $v['intro'] = str_replace( "src=\"images", "src=\"".$object->system->base_url( )."images", $v['intro'] );
    $pic_shopex = "http://pic.shopex.cn";
    $v['thumbnail_pic'] = ( strpos( $v['thumbnail_pic'], $pic_shopex ) === false ? $object->system->base_url( ) : "" ).$v['thumbnail_pic'];
    $params = $v['params'] ? unserialize( $v['params'] ) : array( );
    if ( $v['pdt_desc'] && count( unserialize( $v['pdt_desc'] ) ) )
    {
        if ( $v['spec_desc'] = unserialize( $v['spec_desc'] ) )
        {
            foreach ( $v['spec_desc'] as $spec_id => $spec_value )
            {
                $aTmp[] = $spec_id;
            }
            $aSpec = $v['spec_desc'];
            if ( $aTmp )
            {
                $objSpec = $object->system->loadmodel( "goods/specification" );
                $v['spec'] = implode( "|", $objSpec->getarraybyid( $aTmp ) );
            }
            else
            {
                $v['spec'] = "";
            }
        }
        else
        {
            $v['spec'] = implode( "|", unserialize( $v['spec'] ) );
        }
        $member_price = false;
    }
    else
    {
        $aTmp = $goods->getmemberprice( $v['goods_id'] );
        $member_price = $aTmp['mprice'];
        $v['spec'] = "-";
    }
    foreach ( $proto as $k1 => $v1 )
    {
        $tag = substr( $k1, 0, 2 );
        switch ( $tag )
        {
        case "m_" :
            if ( $member_price )
            {
                $temp = explode( "_", $k1 );
                $row[0][$k1] = $member_price[$temp[1]];
            }
            else
            {
                $row[0][$k1] = "-";
            }
            continue;
        case "p_" :
            $temp = explode( "_", $k1 );
            $t_prop = $props[$temp[1]];
            if ( $t_prop['type'] == "select" )
            {
                $row[0][$k1] = $t_prop['options'][$v[$k1]];
            }
            else
            {
                $row[0][$k1] = $v[$k1];
            }
            continue;
        case "a_" :
            $temp = explode( "_", $k1, 2 );
            $p_keys = explode( "->", $temp[1] );
            $row[0][$k1] = $params[$p_keys[0]][$p_keys[1]];
            continue;
        default :
            if ( $k1 == "cat_id" )
            {
                $row[0][$k1] = $objCat->getnamepathbyid( $v[$k1] );
                if ( $k1 == "cat_id" )
                {
                    break;
                }
            }
            if ( $k1 == "marketable" )
            {
                if ( $v[$k1] == "true" )
                {
                    $row[0][$k1] = "Y";
                    if ( $v[$k1] == "true" )
                    {
                        break;
                    }
                }
                $row[0][$k1] = "N";
                if ( $v[$k1] == "true" )
                {
                    break;
                }
            }
            $row[0][$k1] = $v[$k1];
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
    $gimage = $object->system->loadmodel( "goods/gimage" );
    $aImg = $gimage->get_by_goods_id( $v['goods_id'] );
    foreach ( $aImg as $k => $item )
    {
        if ( $item['is_remote'] == "true" )
        {
            $item['source'] = $item['small'];
        }
        $aImgFile[$item['gimage_id']] = $item['gimage_id']."@".( strpos( $item['source'], $pic_shopex ) === false ? $object->system->base_url( ) : "" ).$item['source'];
        if ( $item['thumbnail'] == $v['thumbnail_pic'] )
        {
            $v['thumbnail_pic'] = $item['gimage_id']."@".$item['source'];
        }
    }
    $row[0]['image_file'] = implode( "#", $aImgFile );
    if ( $v['pdt_desc'] = unserialize( $v['pdt_desc'] ) )
    {
        $s = array_keys( $s['pdt_desc'] );
        foreach ( $goods->getproducts( $v['goods_id'] ) as $product )
        {
            if ( !( trim( $product['bn'] ) == "" ) )
            {
                $pdt_line = $proto;
                $pdt_line['t_name'] = $v['t_name'];
                $pdt_line['bn'] = $v['bn'];
                $pdt_line['i_bn'] = $product['bn'];
                $product['props'] = unserialize( $product['props'] );
                if ( $product['props']['spec_private_value_id'] )
                {
                    foreach ( $product['props']['spec_private_value_id'] as $k => $valid )
                    {
                        if ( $aSpec[$k][$valid]['spec_value_id'] )
                        {
                            $aTmp = $objSpec->getvaluebyid( $aSpec[$k][$valid]['spec_value_id'], array( "spec_value" ) );
                            if ( $aSpec[$k][$valid]['spec_value'] !== $aTmp['spec_value'] )
                            {
                                $product['props']['spec_private_value_id'][$k] = trim( $aTmp['spec_value'] ).":".$aSpec[$k][$valid]['spec_value'];
                            }
                            else
                            {
                                $product['props']['spec_private_value_id'][$k] = trim( $aTmp['spec_value'] );
                            }
                        }
                    }
                    $pdt_line['spec'] = implode( "|", $product['props']['spec_private_value_id'] );
                }
                else if ( $product['props']['spec'] && count( $product['props']['spec'] ) )
                {
                    foreach ( $product['props']['spec'] as $k => $specValue )
                    {
                        $product['props']['spec'][$k] = trim( $specValue );
                    }
                    $pdt_line['spec'] = implode( "|", $product['props']['spec'] );
                }
                else
                {
                    $pdt_line['spec'] = "-";
                }
                $aTmp = $goods->getmemberprice( $v['goods_id'], $product['product_id'] );
                $product['mprice'] = $aTmp['mprice'];
                foreach ( $product['mprice'] as $level => $price )
                {
                    $pdt_line["m_".$level] = $price;
                }
                $pdt_line['price'] = $product['price'];
                $pdt_line['cost'] = $product['cost'];
                $pdt_line['mktprice'] = $product['mktprice'];
                $pdt_line['marketable'] = $product['marketable'] == "true" ? "Y" : "N";
                $pdt_line['store'] = $product['store'];
                $pdt_line['store_place'] = $product['store_place'];
                $pdt_line['weight'] = $product['weight'];
                if ( $levelid && isset( $product['mprice'][$levelid] ) )
                {
                    $product['price'] = $product['mprice'][$levelid];
                }
                $row[] = $pdt_line;
            }
        }
    }
    return $row;
}

?>
