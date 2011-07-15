<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$mode_dir = ( !defined( "SHOP_DEVELOPER" ) || !constant( "SHOP_DEVELOPER" ) ) && version_compare( PHP_VERSION, "5.0", ">=" ) ? "include_v5" : "include";
if ( !class_exists( "pageFactory" ) )
{
    require( CORE_DIR."/".$mode_dir."/pageFactory.php" );
}
class admin_ctl_taobao_goods extends pageFactory
{

    public function admin_ctl_taobao_goods( )
    {
        parent::pagefactory( );
        $this->system =& $GLOBALS['GLOBALS']['system'];
        $this->db =& $this->system->database( );
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $tb_api =& $appmgr->load( "taobao_goods" );
        $this->tb =& $tb_api;
        require( "mdl.taobao.php" );
        ( );
        $this->model = new mdl_taobao( );
    }

    public function index( )
    {
        if ( $_GET['top_appkey'] )
        {
            include_once( "mdl.center_send.php" );
            ( );
            $obj = new mdl_center_send( );
            $nick = $obj->get_tb_nick( $_GET['top_session'] );
            $nick = $nick['result_msg'];
            if ( empty( $nick ) )
            {
                echo "<script>alert(\"您还没有对发布淘宝商品的应用进行配置，请先进入工具箱-应用中心，对此应用进行配置后再使用该功能。\");window.close();</script>";
                exit( );
            }
            if ( $nick != $_GET['nick'] )
            {
                echo "<script>alert(\"您登录的淘宝帐号和此功能对应的应用配置中的淘宝帐号不一致，请使用此功能相关应用中配置的淘宝帐号进行登录。\");window.close();</script>";
                exit( );
            }
            $this->save_sess( $_GET );
        }
        if ( !$redirect && !$_GET['view'] )
        {
            echo "<script>window.close()</script>";
            exit( );
        }
    }

    public function product_add( )
    {
        $outer_key = $this->model->get_tb_nick( );
        if ( $_POST['goods']['goods_id'] )
        {
            $adapter_taobao = $this->model->get_outer_data( $_POST['goods']['goods_id'], $outer_key );
        }
        if ( $_POST['goods']['goods_id'] && !empty( $adapter_taobao ) )
        {
            $this->pagedata['pub_taobao'] = $adapter_taobao['disabled'] == "false";
            $adapter_taobao['outer_content'];
            $adapter_taobao = unserialize( $adapter_taobao['outer_content'] );
            $adapter_taobao = $adapter_taobao['content'];
            $adapter_taobao['key_props'] = $adapter_taobao['ex_content']['key_props'];
            $adapter_taobao['location_city'] = $adapter_taobao['ex_content']['location_city'];
            $adapter_taobao['location_state'] = $adapter_taobao['ex_content']['location_state'];
            $adapter_taobao['type_id'] = $adapter_taobao['ex_content']['type_id'];
            $adapter_taobao['goods'] = $adapter_taobao;
            $this->pagedata['local_goods_data'] = urlencode( serialize( $adapter_taobao ) );
            $this->pagedata['local_goods_data_source'] = urlencode( serialize( $_POST ) );
        }
        else
        {
            $goods = $_POST;
            if ( $goods['store'] )
            {
                foreach ( $goods['store'] as $k => $v )
                {
                    $adapter_taobao['goods']['num'] += $v;
                }
            }
            else
            {
                $adapter_taobao['goods']['num'] = $_POST['goods']['store'];
            }
            $adapter_taobao['goods']['title'] = $_POST['goods']['name'];
            $adapter_taobao['goods']['price'] = $_POST['goods']['price'];
            $adapter_taobao['goods']['outer_id'] = $_POST['goods']['bn'];
            $this->pagedata['local_goods_data'] = urlencode( serialize( $_POST ) );
        }
        if ( $adapter_taobao['list_time'] )
        {
            $list_time = $adapter_taobao['list_time'];
            unset( $adapter_taobao['list_time'] );
            $adapter_taobao['list_time'][0] = date( "Y-m-d", strtotime( $list_time ) );
            $adapter_taobao['list_time'][1] = date( "H", strtotime( $list_time ) );
            $adapter_taobao['list_time'][2] = date( "i", strtotime( $list_time ) );
        }
        $this->pagedata['taobao'] = $adapter_taobao;
        if ( $adapter_taobao['cid'] )
        {
            $this->pagedata['cat_path'] = $this->model->get_cat_path( $adapter_taobao['cid'] );
        }
        $citys = $this->model->get_areas( 0 );
        $this->pagedata['states'] = $citys;
        $cur_time = time( );
        $v = $cur_time + 300;
        for ( ; $v < $cur_time + 1209900; $v += 86400 )
        {
            $this->pagedata['send_set']['time_year'][date( "Y-m-d", $v )] = date( "Y年m月d日", $v );
        }
        foreach ( range( 0, 23 ) as $v )
        {
            if ( $v < 10 )
            {
                $v = "0".$v;
            }
            $this->pagedata['send_set']['time_hour'][$v] = $v;
        }
        foreach ( range( 0, 55, 5 ) as $v )
        {
            if ( $v < 10 )
            {
                $v = "0".$v;
            }
            $this->pagedata['send_set']['time_minute'][$v] = $v;
        }
        $p = array(
            "nick" => $outer_key
        );
        $shop_info = $this->model->get_shop_info( $p );
        $this->pagedata['is_shop'] = TRUE;
        if ( $shop_info['error_response'] )
        {
            $this->pagedata['is_shop'] = FALSE;
        }
        $this->display( "view/product_add.html" );
    }

    public function taobao_item_add( $goods_id )
    {
        $gid = $goods_id;
        $outer_key = $this->model->get_tb_nick( );
        header( "Cache-Control:no-cache,must-revalidate" );
        $row = $this->model->get_outer_data( $gid, $outer_key );
        if ( $row['disabled'] == "false" )
        {
            $data = unserialize( $row['outer_content'] );
            $images = $data['images'];
            $rsp = array( );
            foreach ( $images['current'] as $k => $v )
            {
                if ( $v == -1 )
                {
                    $rsp['images'][$k] = -1;
                }
            }
            foreach ( $images['del'] as $k => $v )
            {
                $rsp['images'][$k] = $v;
            }
            if ( $row['outer_id'] )
            {
                $data['content']['iid'] = $row['outer_id'];
            }
            unset( $this->content['ex_content'] );
            $iid = $this->model->taobao_item_add( $data['content'], $errMsg );
            if ( $errMsg )
            {
                $this->errmsg_modify( $errMsg );
                $rsp['error_rsp'] = $errMsg;
            }
            else
            {
                $return = $this->model->bindGoodsOutId( $gid, $outer_key, $iid );
                $tag = "淘宝";
                $this->model->setTaobaoTag( $goods_id, $tag );
                if ( $return == TRUE )
                {
                    $rsp['iid'] = $iid;
                    $rsp['goods_id'] = $gid;
                }
                else
                {
                    $rsp['error_rsp'] = "更新本地iid失败";
                }
            }
            echo json_encode( $rsp );
        }
        else
        {
            $return = $this->model->cancelBindGoodsOutId( $gid, $outer_key, $iid );
        }
    }

    public function errmsg_modify( &$errMsg )
    {
        if ( strpos( $errMsg, "Invalid signature" ) !== FALSE )
        {
            $errMsg = "请检查填写项";
        }
        if ( strpos( $errMsg, "Item service unavailable:" ) !== FALSE )
        {
            $errMsg = str_replace( "Item service unavailable:", "", $errMsg );
        }
    }

    public function get_cats( $p0 = 0, $p1 = 0, $p2 = "c" )
    {
        $tb_cats = $this->model->get_cats( $p0, $p1, $p2 );
        echo json_encode( $tb_cats );
    }

    public function get_product_propsvalue( $p0 = 0, $p1 = 0 )
    {
        $tmp_var = $this->model->get_product_propsvalue( $p0, $p1 );
        echo json_encode( $tmp_var );
    }

    public function show_cats( )
    {
        $this->pagedata['history_list'] = $this->model->getHistory( );
        $this->display( "view/select_category.html" );
    }

    public function get_shop_cats( )
    {
        $tb_cats = $this->model->get_shop_cats( array( ) );
        $this->pagedata['tb_cats'] = $tb_cats;
        $this->display( "view/tb_cats.html" );
    }

    public function get_seller_cats( )
    {
        $local_goods_data = unserialize( urldecode( $_POST['local_goods_data'] ) );
        $args = array(
            "nick" => $this->model->get_tb_nick( )
        );
        $tb_cats = $this->model->get_seller_cats( $args );
        $this->pagedata['cats'] = $tb_cats;
        $this->pagedata['seller_cids'] = $local_goods_data['ex_content']['seller_cids'];
        $this->display( "view/taobao_cat.html" );
    }

    public function get_postages( )
    {
        $args = array(
            "nick" => $this->model->get_tb_nick( )
        );
        $this->pagedata['postages'] = $this->model->get_postages( $args );
        if ( $this->pagedata['postages'] == "fail" )
        {
            $this->sess_timeout( );
            exit( );
        }
        $this->display( "view/taobao_postage.html" );
    }

    public function get_props( $p0 )
    {
        $args['pid'] = $p0;
        $prototype = $this->model->get_props_value( $args['pid'] );
        $nick = $this->model->get_tb_nick( );
        $user = $this->model->taobao_user_get( $nick );
        if ( $user['user_get_response']['user']['type'] == "B" )
        {
            $brand = $this->model->getbuserbrand( );
            $b_brand = $brand['itemcats_authorize_get_response']['seller_authorize']['brands']['brand'];
            foreach ( $prototype['props'] as $key => $value )
            {
                if ( $value['name'] == "品牌" )
                {
                    unset( $Var_888['options'] );
                    foreach ( $b_brand as $k => $v )
                    {
                        $prototype['props'][$key]['options'][$v['name']] = $v['name'];
                    }
                    $this->pagedata['is_b_seller'] = TRUE;
                }
            }
        }
        $local_goods_data = unserialize( urldecode( $_POST['local_goods_data'] ) );
        $local_type_id = $local_goods_data['goods']['type_id'];
        if ( $_POST['mode'] == "edit" )
        {
            $local_goods_data_source = unserialize( urldecode( $_POST['local_goods_data_source'] ) );
            $this->_type_transform( $local_type_id, $p0, $local_goods_data_source, NULL, $prototype, $relation );
            unset( $_POST['local_goods_data_source'] );
            unset( $local_goods_data_source );
        }
        else
        {
            $this->_type_transform( $local_type_id, $p0, $local_goods_data, NULL, $prototype, $relation );
        }
        if ( $prototype['spec'] )
        {
            foreach ( $prototype['spec'] as $pid => $row )
            {
                $this->pagedata['spec']['vars'][$pid] = $row;
            }
            $prototype['setting']['use_spec'] = 1;
            $this->pagedata['spec_desc_str'] = serialize( $prototype['spec'] );
        }
        $this->pagedata['spec_json'] = json_encode( $prototype['spec'] );
        $this->pagedata['spec_array'] = $prototype['spec'];
        $this->pagedata['prototype'] = $prototype;
        $this->pagedata['tmp_type'] = serialize( $prototype );
        $this->pagedata['taobao'] = $local_goods_data;
        $this->pagedata['relation'] = serialize( $relation );
        if ( $local_goods_data['goods']['products'] )
        {
            $this->pagedata['taobao']['goods'] = $local_goods_data;
            foreach ( $local_goods_data['goods']['products'] as $k => $v )
            {
                $specs[] = $v['props']['spec_value_id'];
            }
        }
        if ( $local_goods_data['ex_content'] )
        {
            $this->pagedata['taobao']['goods'] = $local_goods_data['ex_content'];
            foreach ( $local_goods_data['ex_content']['pSpecId'] as $spec_id => $vs )
            {
                $index = 0;
                foreach ( $vs as $k => $spec_value_id )
                {
                    $specs[$index][$spec_id] = $spec_value_id;
                    ++$index;
                }
            }
        }
        foreach ( $prototype['spec'] as $spec_id => $noneed )
        {
            $spec_sort[] = $spec_id;
        }
        foreach ( $specs as $k => $v )
        {
            foreach ( $spec_sort as $spec_id )
            {
                $tmp[$k][$spec_id] = $v[$spec_id];
            }
        }
        $specs = $tmp;
        unset( $tmp );
        $this->pagedata['specs'] = $specs;
        $this->display( "view/goods_props.html" );
        exit( );
    }

    public function taobao_item_img_upload( $gid, $iid, $image_id, $pre_img_id )
    {
        $outer_key = $this->model->get_tb_nick( );
        $row = $this->model->get_outer_data( $gid, $outer_key );
        if ( $row['disable'] == "true" )
        {
            exit( );
        }
        $outer_content = unserialize( $row['outer_content'] );
        $images = $outer_content['images'];
        $args['iid'] = $iid;
        if ( $images['current'][$image_id] == -1 )
        {
            $file = $this->model->get_img_path( $image_id );
            $args['image'] = HOME_DIR."/upload/".$file['source'];
            $tmp = $this->model->taobao_item_img_upload( $args, $pre_img_id );
            $images['current'][$image_id] = $tmp['itemimg_id'];
            $outer_content['images'] = $images;
            $row['outer_content'] = $outer_content;
            $this->model->save_outer_data( $row );
            echo json_encode( $tmp );
        }
    }

    public function taobao_item_img_delete( $gid, $iid, $image_id )
    {
        $outer_key = $this->model->get_tb_nick( );
        $row = $this->model->get_outer_data( $gid, $outer_key );
        $outer_content = unserialize( $row['outer_content'] );
        $images = $outer_content['images'];
        $args['iid'] = $iid;
        if ( 0 <= $images['del'][$image_id] )
        {
            $args['itemimg_id'] = $images['del'][$image_id];
            $tmp = $this->model->taobao_item_img_delete( $args );
            unset( $this->images[$image_id] );
            $row['outer_content'] = unserialize( $outer_content );
            $this->model->save_outer_data( $row );
            echo json_encode( $tmp );
        }
    }

    public function _type_transform( $local_type_id, $tb_cat_id, &$gdata, $arr_type, $arr_tbcat, &$relation )
    {
        $goods = $gdata['goods'];
        $goods['name'] = trim( $gdata['goods']['name'] );
        $goods['image_default'] = $gdata['image_default'];
        if ( $gdata['bn'] && is_array( $gdata['bn'] ) )
        {
            foreach ( $gdata['bn'] as $gk => $gbn )
            {
                $goods['products'][$gk] = array(
                    "bn" => $gbn,
                    "cost" => $gdata['cost'][$gk],
                    "weight" => $gdata['weight'][$gk],
                    "store" => $gdata['store'][$gk],
                    "price" => $gdata['price'][$gk]
                );
                foreach ( $gdata['mprice'] as $memlvid => $mprice )
                {
                    $goods['products'][$gk]['mprice'][$memlvid] = $mprice[$gk];
                }
                $goodsProps = array( );
                foreach ( $gdata['val'] as $valSpecId => $valSpec )
                {
                    $goodsProps['spec'][$valSpecId] = urldecode( $valSpec[$gk] );
                    $goodsProps['spec_private_value_id'][$valSpecId] = $gdata['pSpecId'][$valSpecId][$gk];
                    $goodsProps['spec_value_id'][$valSpecId] = $gdata['specVId'][$valSpecId][$gk];
                }
                $goods['products'][$gk]['props'] = $goodsProps;
            }
        }
        if ( $goods['spec_desc'] )
        {
            $goods['spec_desc'] = unserialize( urldecode( $goods['spec_desc'] ) );
        }
        require( "mdl.gtypetransform.php" );
        $oGtype = $this->system->loadModel( "goods/gtype" );
        $arr_type = $arr_type ? $arr_type : $oGtype->getTypeDetail( $local_type_id );
        ( );
        $objtran = new mdl_gtypetransform( );
        $objtran->typeTransform( $local_type_id, $tb_cat_id, $goods, $arr_type, $arr_tbcat, FALSE );
        $relation = $this->_get_type_alias( $arr_type, $gdata['goods'], $objtran->typePropsHash, NULL );
        $gdata['goods'] = $goods;
    }

    public function _get_type_alias( $sourceType, $goods, $propsHash, $specHash )
    {
        $relation = array( );
        foreach ( $propsHash as $skey => $val )
        {
            $relation[$skey] = array(
                "key" => $val['key'],
                "val" => $sourceType['props'][$skey]['options'][$goods["p_".$skey]]
            );
        }
        return $relation;
    }

    public function get_areas( $p0 )
    {
        $aData = $this->model->get_areas( $p0 );
        echo json_encode( $aData );
    }

    public function getTbloginurl( $url )
    {
        require_once( "mdl.center_send.php" );
        ( );
        $center = new mdl_center_send( );
        $return = $center->getTbAppInfo( );
        if ( $return )
        {
            $tbs_params['api_key'] = $return['result_msg']['app_key'];
        }
        $tbs_params['ext_shop_title'] = "shopex网店";
        $tbs_params['ext_shop_domain'] = $this->system->base_url( );
        $tbs_params['action'] = "logon";
        $tbs_params['callback_url'] = $url ? $url : $this->system->base_url( )."shopadmin/index.php?ctl=plugins/ctl_taobao_goods&act=save_sess";
        $login_url = "http://container.api.taobao.com/container/exShop";
        foreach ( $tbs_params as $key => $value )
        {
            $ps_s[] = $key."=".$value;
        }
        $tb_url = $login_url."?".implode( "&", $ps_s );
        return $tb_url;
    }

    public function sess_timeout( )
    {
        $this->pagedata['tblogin_url'] = $this->getTbloginurl( );
        $this->display( "view/sess_timeout.html" );
    }

    public function save_sess( $params )
    {
        require_once( "mdl.center_send.php" );
        ( );
        $center = new mdl_center_send( );
        $return = $center->getTbAppInfo( );
        if ( $return )
        {
            $app_secret = $return['result_msg']['app_secret'];
        }
        $sign = base64_encode( $this->md5bin( md5( $params['top_appkey'].$params['top_parameters'].$params['top_session'].$app_secret ) ) );
        if ( $params['top_sign'] == $sign )
        {
            $status = $this->system->loadModel( "system/status" );
            $status->set( "tb_sess", $params['top_session'] );
            $mess = $center->save_sess( $params['top_session'] );
        }
    }

    public function md5bin( $md5str )
    {
        $ret = "";
        $i = 0;
        for ( ; $i < 32; $i += 2 )
        {
            $ret .= chr( hexdec( $md5str[$i].$md5str[$i + 1] ) );
        }
        return $ret;
    }

    public function addRow( )
    {
        $spec_desc = unserialize( stripslashes( $_POST['spec_desc'] ) );
        if ( !$spec_desc )
        {
            $spec_desc = array( );
        }
        $this->pagedata['goods']['spec_desc'] = $spec_desc;
        $this->display( "view/spec_row.html" );
    }

    public function save_data( )
    {
        echo "failed";
        echo "success";
    }

    public function save_pic( )
    {
    }

    public function product_index( )
    {
        $ids = $_POST['goods_id'];
        $outer_data = $this->model->get_goodslist_by_ids( $ids );
        $tmp = array( );
        foreach ( $outer_data as $key => $value )
        {
            $tmp[$value['goods_id']] .= $value['outer_type']."&nbsp";
        }
        echo json_encode( $tmp );
    }

}

?>
