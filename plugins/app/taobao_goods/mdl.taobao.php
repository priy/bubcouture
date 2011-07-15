<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$mode_dir = ( !defined( "SHOP_DEVELOPER" ) || !constant( "SHOP_DEVELOPER" ) ) && version_compare( PHP_VERSION, "5.0", ">=" ) ? "include_v5" : "include";
if ( !class_exists( "app" ) )
{
    require( CORE_DIR."/".$mode_dir."/app.php" );
}
if ( !class_exists( "app_taobao_goods" ) )
{
    require( "app.taobao_goods.php" );
}
class mdl_taobao extends app_taobao_goods
{

    public $api_method = array( );

    public function mdl_taobao( )
    {
        $this->__construct( );
    }

    public function __construct( )
    {
        parent::app_taobao_goods( );
        $this->api = $this->system->call( "tb_method_map" );
        if ( !defined( "SAAS_NATIVE_KEY" ) )
        {
            define( "SAAS_NATIVE_KEY", "371e6dceb2c34cdfb489b8537477ee1c" );
        }
        if ( !defined( "SAAS_API_URL" ) )
        {
            define( "SAAS_API_URL", "http://api.dianzhanggui.net/api.php" );
        }
        $this->saas = $this->system->loadModel( "service/saasdata" );
        $this->db =& $this->system->database( );
    }

    public function get_tb_nick( )
    {
        require_once( "mdl.center_send.php" );
        ( );
        $obj = new mdl_center_send( );
        $status = $this->system->loadModel( "system/status" );
        $sess = $status->get( "TB_SESS" );
        if ( empty( $sess ) )
        {
            return FALSE;
        }
        else
        {
            $status = $this->system->loadModel( "system/status" );
            $sess = $status->get( "TB_SESS" );
            $nick = $obj->get_tb_nick( $sess );
            if ( $nick['result'] == "succ" )
            {
                return $nick['result_msg'];
            }
            else
            {
                return FALSE;
            }
        }
    }

    public function get_cats( $cid, $pid, $type = "c" )
    {
        $citys = $this->saas->getCatesById( $cid, $pid, $type );
        $arrData = unserialize( $citys );
        return $arrData;
    }

    public function get_shop_cats( $args )
    {
        $p = $this->api['get_shop_cats'];
        $p = array_merge( $p, $args );
        $arrData = $this->getContents( $p, TRUE, "get" );
        return $arrData;
    }

    public function get_seller_cats( $args )
    {
        $args['start_modified'] = "1970-01-01 18:23:23";
        $p = $this->api['get_seller_cats'];
        $p = array_merge( $p, $args );
        $rsp = $this->getContents( $p, TRUE, "get" );
        if ( !isset( $rsp['sellercats_list_get_response']['seller_cats'] ) )
        {
            return array( );
        }
        if ( $rsp['sellercats_list_get_response']['seller_cats']['seller_cat'] )
        {
            foreach ( $rsp['sellercats_list_get_response']['seller_cats']['seller_cat'] as $k => $row )
            {
                if ( $row['parent_cid'] )
                {
                    $aCats[$row['parent_cid']]['options'][] = $row;
                }
                else if ( $aCats[$row['cid']] )
                {
                    $a = $aCats[$row['cid']]['options'];
                    $aCats[$row['cid']] = $row;
                    $aCats[$row['cid']]['options'] = $a;
                }
                else
                {
                    $aCats[$row['cid']] = $row;
                }
            }
        }
        return $aCats;
    }

    public function get_postages( $args )
    {
        $p = $this->api['get_postages'];
        $p = array_merge( $p, $args );
        $rsp = $this->getContents( $p );
        return $rsp['postages_get_response']['postages']['postage'];
    }

    public function get_areas( $area_id )
    {
        $citys = $this->saas->getGoodsCity( $area_id );
        $arrData = unserialize( $citys );
        return $arrData;
    }

    public function get_area_name( $province_id = 0, $area_id = 1 )
    {
        $arrData = $this->get_areas( $province_id );
        return $arrData[$area_id];
    }

    public function get_props( $args )
    {
        $p = $this->api['get_props'];
        $p = array_merge( $p, $args );
        $arrData = $this->getContents( $p, TRUE, "get" );
        return $arrData;
    }

    public function get_props_value( $type_id )
    {
        $arrData = $this->saas->getPropValuesById( $type_id );
        $arrData = unserialize( $arrData );
        return $arrData;
    }

    public function get_shop_info( $args )
    {
        $p = $this->api['get_shop_info'];
        $p = array_merge( $p, $args );
        $arrData = $this->getContents( $p, FALSE, "get" );
        return $arrData;
    }

    public function get_cat_path( $cid )
    {
        return $this->saas->getTypePathApi( $cid );
    }

    public function get_product_propsvalue( $catid, $props, $productid = 0 )
    {
        $args['cid'] = $catid;
        $args['props'] = $props;
        $p = $this->api['get_product_propsvalue'];
        $p = array_merge( $p, $args );
        $rsp = $this->getContents( $p );
        return $rsp['product_get_response']['product'];
    }

    public function taobao_item_img_upload( $args, $pre_img_id = 0 )
    {
        $p = $this->api['taobao_item_img_upload'];
        $params = array_merge( $p, $args );
        if ( $pre_img_id != -1 )
        {
            $tmp = $params;
            unset( $tmp['image'] );
            $tmp['is_major'] = "false";
            $tmp['itemimg_id'] = $pre_img_id;
            $rsp = $this->getContents( $tmp, FALSE, "upload" );
        }
        $params['is_major'] = "true";
        $rsp = $this->getContents( $params, FALSE, "upload" );
        if ( $rsp['error_response'] )
        {
            $errCode = $rsp['error_response']['code'];
            if ( $errCode == "551" && !preg_match( "/Item service unavailable/", $rsp['error_response']['sub_msg'] ) )
            {
                unset( $params['iid'] );
                $rsp = $this->getContents( $params, FALSE, "upload" );
            }
            $errMsg = $rsp['error_response']['sub_msg'];
            return FALSE;
        }
        else
        {
            return $rsp['item_img_upload_response']['item_img'];
        }
    }

    public function taobao_item_img_delete( $args )
    {
        $p = $this->api['taobao_item_img_delete'];
        $params = array_merge( $p, $args );
        $rsp = $this->getContents( $params, TRUE, "get" );
        if ( $rsp['error_response'] )
        {
            $errCode = $rsp['error_response']['code'];
            if ( $errCode == "551" && !preg_match( "/Item service unavailable/", $rsp['error_response']['sub_msg'] ) )
            {
                unset( $params['iid'] );
                $rsp = $this->getContents( $params, TRUE, "get" );
            }
            $errMsg = $rsp['error_response']['sub_msg'];
            return $errMsg;
        }
        else
        {
            return $rsp['item_img_delete_response']['item_img']['id'];
        }
    }

    public function get_img_path( $imgage_id )
    {
        $sql = "select * from sdb_gimages where gimage_id={$imgage_id}";
        return $this->db->selectrow( $sql );
    }

    public function taobao_user_get( $nick )
    {
        $params = $this->api['taobao_user_get'];
        $params['nick'] = $nick;
        return $this->getContents( $params, FALSE, "get" );
    }

    public function taobao_item_add( $data, &$errMsg )
    {
        $params = $this->api['taobao_item_add'];
        if ( $data['iid'] )
        {
            $params = $this->api['taobao_item_update'];
        }
        $params = array_merge( $data, $params );
        $rsp = $this->getContents( $params, FALSE, "post" );
        if ( $rsp['error_response'] )
        {
            $errCode = $rsp['error_response']['code'];
            if ( $errCode == "40" )
            {
                $errMsg = "未填写详细介绍或商品名称，请返回填写";
                return FALSE;
            }
            if ( $errCode == "551" && !preg_match( "/Item service unavailable/", $rsp['error_response']['sub_msg'] ) )
            {
                unset( $params['iid'] );
                $rsp = $this->getContents( $params, FALSE, "post" );
            }
            $errMsg = $rsp['error_response']['sub_msg'];
            return FALSE;
        }
        else
        {
            if ( $data['iid'] )
            {
                return $rsp['item_update_response'][0]['iid'];
            }
            return $rsp['item_add_response'][0]['iid'];
        }
    }

    public function bindGoodsOutId( $gid, $outer_key, $iid )
    {
        $sql = "UPDATE sdb_taobao_goods_goods set outer_id=\"".$iid."\" where outer_key=\"".$outer_key."\" and goods_id=".$gid;
        if ( $this->db->exec( $sql ) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function cancelBindGoodsOutId( $gid, $outer_key, $iid )
    {
        $sql = "UPDATE sdb_taobao_goods_goods set disabled=\"true\" where outer_key=\"".$outer_key."\" and goods_id=".$gid;
        if ( $this->db->exec( $sql ) )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function save_outer_data( $data )
    {
        $outer_id = $this->get_outer_id( $data['goods_id'], $data['outer_key'] );
        $rs = $this->db->query( "select * from sdb_taobao_goods_goods where outer_key=\"".$data['outer_key']."\" and goods_id=".$data['goods_id'] );
        $sql = $this->db->getUpdateSQL( $rs, $data, TRUE );
        if ( $sql )
        {
            $this->db->exec( $sql );
        }
    }

    public function get_outer_data( $gid, $outer_key )
    {
        $data = $this->db->selectrow( "SELECT * FROM sdb_taobao_goods_goods WHERE goods_id =".intval( $gid )." AND outer_key ='".$outer_key."'" );
        return $data;
    }

    public function get_outer_id( $gid, $outer_key )
    {
        $data = $this->db->selectrow( "SELECT outer_id FROM sdb_taobao_goods_goods WHERE goods_id =".intval( $gid )." AND outer_key ='".$key."'" );
        return $data['outer_id'];
    }

    public function remove_outer_id( $gid, $key )
    {
        $sql = "DELETE FROM sdb_goods_outer_id WHERE goods_id=".$gid." AND outer_key ='".$key."'";
        $this->db->exec( $sql );
        $data = $this->getGoodsMemo( $gid, $key );
        $data = unserialize( $data );
        $data['update_status'] = "delete";
        $this->setGoodsMemo( $gid, $key, $data );
        return TRUE;
    }

    public function get_goodslist_by_id( $id )
    {
        $data = $this->db->selectrow( "SELECT goods_id,outer_id,outer_key FROM sdb_taobao_goods_goods WHERE goods_id=".$id." and disabled='false'" );
        return $data;
    }

    public function setTaobaoTag( $goods_id, $tag )
    {
        $objTag =& $this->system->loadModel( "system/tag" );
        $tagName = trim( $tag );
        if ( $tagName )
        {
            if ( !( $tagid = $objTag->getTagByName( "goods", $tag ) ) )
            {
                $tagid = $objTag->newTag( $tagName, "goods" );
            }
            if ( $objTag->getTagRel( $tagid, $goods_id ) == FALSE )
            {
                $objTag->addTag( $tagid, $goods_id );
            }
        }
    }

    public function logHistory( $id, $name )
    {
        $config = $this->getConf( "app.taobao_cat_history" );
        $taobao_cat_history = unserialize( $config );
        $iLoop = 1;
        $data[$id] = $name;
        foreach ( $taobao_cat_history as $k => $name )
        {
            if ( $iLoop < 10 )
            {
                $data[$k] = $name;
            }
            ++$iLoop;
        }
        return $this->setConf( "app.taobao_cat_history", serialize( $data ) );
    }

    public function getHistory( )
    {
        return unserialize( $this->getConf( "app.taobao_cat_history" ) );
    }

    public function getbuserbrand( )
    {
        $params['method'] = "taobao.itemcats.authorize.get";
        $params['fields'] = "brand.vid,brand.name";
        return $this->getContents( $params, FALSE, "get" );
    }

}

?>
