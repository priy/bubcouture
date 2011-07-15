<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_certificate extends modelfactory
{

    function getcerti( )
    {
        if ( $this->system->getconf( "certificate.id" ) )
        {
            return $this->system->getconf( "certificate.id" );
        }
        return false;
    }

    function gettoken( )
    {
        if ( $this->system->getconf( "certificate.token" ) )
        {
            return $this->system->getconf( "certificate.token" );
        }
        return false;
    }

    function get_app_instance_id( $app_id )
    {
        if ( empty( $this->app_instance_id ) )
        {
            $return = "";
            $post = array(
                "certi_app" => "app.get_instance_list",
                "app_id" => VERIFY_APP_ID,
                "version" => "1.0",
                "certi_url" => $this->system->base_url( ),
                "certi_session" => $this->get_sess( ),
                "certi_validate_url" => $this->system->base_url( )."shopadmin/index.php?ctl=passport&act=certi_validate",
                "format" => "json"
            );
            $post['certi_ac'] = $this->make_shopex_ac( $post, $this->gettoken( ) );
            $instance_list = $this->read_shopex_server( $post );
            if ( $instance_list['res'] == "succ" )
            {
                $return = implode( "|", $instance_list['info'] );
            }
            else
            {
                $return = "";
            }
            $this->app_instance_id = $return;
            return $return;
        }
        $return = $this->app_instance_id;
        return $return;
    }

    function to_shopex_certificate( $certi_app, $format = "json" )
    {
        $admPth = substr( dirname( $_SERVER['PHP_SELF'] ), strrpos( dirname( $_SERVER['PHP_SELF'] ), "/" ) + 1 );
        $post = array(
            "certi_app" => $certi_app,
            "certificate_id" => $this->getcerti( ),
            "app_id" => VERIFY_APP_ID,
            "app_instance_id" => $this->get_app_instance_id( VERIFY_APP_ID ),
            "version" => "1.0",
            "certi_url" => $this->system->base_url( ),
            "certi_session" => $this->get_sess( ),
            "certi_validate_url" => $this->system->base_url( ).$admPth."/index.php?ctl=passport&act=certi_validate",
            "format" => $format,
            "shop_version" => $this->getversion( )
        );
        $post['certi_ac'] = $this->make_shopex_ac( $post, $this->gettoken( ) );
        return $this->read_shopex_server( $post );
    }

    function make_shopex_ac( $temp_arr, $token )
    {
        ksort( $temp_arr );
        $str = "";
        foreach ( $temp_arr as $key => $value )
        {
            if ( $key != "certi_ac" )
            {
                $str .= $value;
            }
        }
        return md5( $str.$token );
    }

    function get_sess( )
    {
        $sql = "select sess_id from sdb_op_sessions WHERE status=1 ORDER BY last_time DESC";
        $data = $this->db->selectrow( $sql );
        return $data['sess_id'];
    }

    function read_shopex_server( $post )
    {
        $url = "http://service.shopex.cn/openapi/api.php";
        $net = $this->system->loadmodel( "utility/http_client" );
        $results = $net->post( $url, $post );
        if ( $results )
        {
            if ( $results[0] == "{" )
            {
                return json_decode( $results, true );
            }
            $results = strstr( $results, "{" );
            if ( $results )
            {
                return json_decode( $results, true );
            }
        }
    }

    function checkvalid( $sStr )
    {
        if ( $sStr == "valid" )
        {
            return __( "激活" );
        }
        return __( "未激活" );
    }

    function dellicense( )
    {
        $this->system->setconf( "certificate.id", "" );
        $this->system->setconf( "certificate.token", "" );
    }

    function explodestr( $sStr )
    {
        $aTmp = explode( "|||", $sStr );
        return $aTmp;
    }

    function setcerti( $certi_id )
    {
        return $this->system->setconf( "certificate.id", $certi_id, true );
    }

    function settoken( $token )
    {
        return $this->system->setconf( "certificate.token", $token, true );
    }

    function setstr( $str )
    {
        $this->system->setconf( "certificate.str", $str );
    }

    function setformal( $state )
    {
        $this->system->setconf( "certificate.formal", $state );
    }

    function set_channel_url( $url )
    {
        $this->system->setconf( "certificate.channel.url", $url );
    }

    function set_channel_name( $name )
    {
        $this->system->setconf( "certificate.channel.name", $name );
    }

    function set_channel_is( $status )
    {
        $this->system->setconf( "certificate.channel.status", $status );
    }

    function set_channel_service( $service )
    {
        $this->system->setconf( "certificate.channel.service", $service );
    }

    function get_channel_url( )
    {
        return $this->system->getconf( "certificate.channel.url" );
    }

    function get_channel_name( $name )
    {
        return $this->system->getconf( "certificate.channel.name" );
    }

    function getname( )
    {
        if ( $this->system->getconf( "system.shopname" ) )
        {
            return $this->system->getconf( "system.shopname" );
        }
    }

    function getsess( $sess_id )
    {
        $sSql = "select * from sdb_op_sessions where sess_id='".$sess_id."'";
        if ( $this->db->selectrow( $sSql ) )
        {
            return true;
        }
        return false;
    }

    function setencode( $sess_id, $certi_id )
    {
        $ENCODEKEY = "ShopEx@License";
        $confirmkey = md5( $sess_id.$ENCODEKEY.$certi_id );
        return $confirmkey;
    }

    function checkfile( $files )
    {
        if ( empty( $files ) )
        {
            return false;
        }
        return true;
    }

    function checkpass( $aIn )
    {
        $sSql = "select * from sdb_operators where username = '".$aIn['username']."' and userpass = '".md5( $aIn['userpass'] )."' and super='1' and status='1'";
        if ( $this->db->selectrow( $sSql ) )
        {
            return true;
        }
        return false;
    }

    function upload( $tmp )
    {
        if ( !$this->checkfile( $tmp ) )
        {
            return false;
        }
        $certInfo = @file( $tmp );
        $line = $certInfo[0];
        $result = $this->checkcerti( $line, "check" );
        if ( !$result )
        {
            return false;
        }
        $expTmp = explode( "|||", $line );
        if ( !$this->checkcerti( $expTmp[0], "id" ) )
        {
            return false;
        }
        if ( !$this->checkcerti( $expTmp[1], "token" ) )
        {
            return false;
        }
        $this->dellicense( );
        $_r1 = $this->setcerti( $expTmp[0] );
        $_r2 = $this->settoken( $expTmp[1] );
        if ( $_r1 && $_r2 )
        {
            return true;
        }
        return false;
    }

    function checkcerti( $certi, $action )
    {
        switch ( $action )
        {
        case "check" :
            if ( strtok( $certi, "|||" ) )
            {
                return true;
            }
            return false;
        case "id" :
            if ( strlen( $certi ) < 12 )
            {
                return true;
            }
            return false;
        case "token" :
            if ( strlen( $certi ) == 64 )
            {
                return true;
            }
            return false;
        }
    }

    function msg_pack( )
    {
        $data['ip'] = remote_addr( );
        $data['url'] = $this->system->base_url( );
        $data['login_time'] = mktime( );
        $data['certificate_id'] = $this->getcerti( );
        $data['shopname'] = $this->system->getconf( "system.shopname" );
        $data['ac'] = $this->make_shopex_ac( $data, "ShopEx_LOG" );
        return $data;
    }

    function post_data( $data )
    {
        $url = "http://service.shopex.cn/class.license_log.php";
        $httpd = $this->system->loadmodel( "utility/http_client" );
        $results = $httpd->post( $url, $data );
        return $results;
    }

    function show_pack_data( )
    {
        $data = array( );
        $tmp = $this->db->selectrow( "select count(product_id) as product_num from sdb_products as aProducts\r\n            left join sdb_goods as aGoods on aProducts.goods_id=aGoods.goods_id where aProducts.goods_id=aGoods.goods_id and aGoods.disabled='false' and aProducts.disabled='false'" );
        $data['goodnum'] = $tmp['product_num'];
        $sql = "select count(*) as categorynum from sdb_goods_type";
        $tmp = $this->db->selectrow( $sql );
        $data['categorynum'] = $tmp['categorynum'];
        $sql = "select count(*) as membernum from sdb_members where disabled='false'";
        $tmp = $this->db->selectrow( $sql );
        $data['membernum'] = $tmp['membernum'];
        $tmp = $this->db->selectrow( "select count(order_id) as order_num,sum(total_amount) as order_total_count from sdb_orders where ship_status='1' and (pay_status='1' or pay_status='2') and disabled='false'" );
        $data['ordernum'] = $tmp['order_num'];
        $data['orderprice'] = $tmp['order_total_count'];
        return $data['goodnum']."###".$data['categorynum']."###".$data['ordernum']."###".$data['membernum']."###".$data['orderprice'];
    }

    function check_api( )
    {
        ksort( $_POST );
        $str = "";
        foreach ( $GLOBALS['_POST'] as $key => $value )
        {
            if ( $key != "ac" )
            {
                $str .= $value;
            }
        }
        if ( md5( $str ) == $_POST['ac'] )
        {
            return true;
        }
        return false;
    }

    function show_channel( )
    {
        $net = $this->system->loadmodel( "utility/http_client" );
        $url = "http://service.shopex.cn/class.channel.php";
        $certificate_id = $this->getcerti( );
        $ac = md5( $certificate_id."ShopEx_CHANNEL" );
        $data = array(
            "certificate_id" => $certificate_id,
            "ac" => $ac
        );
        $msg = $net->post( $url, $data );
        if ( $msg && !( strpos( $msg, "true" ) === false ) )
        {
            $tmp = $this->explodestr( $msg );
            $this->set_channel_name( $tmp[1] );
            $this->set_channel_url( $tmp[2] );
            $this->set_channel_service( $tmp[3] );
            $this->set_channel_is( true );
        }
        else
        {
            $this->set_channel_is( false );
        }
    }

    function getinfo( )
    {
        set_time_limit( 0 );
        $this->Certi = $this->getcerti( );
        $this->Token = $this->gettoken( );
        $this->setformal( $state );
        if ( $this->Certi && $this->Token )
        {
            $this->sendmsg( );
            $this->show_channel( );
            $this->setstr( $str );
            $_r = $this->tologin( );
        }
        else
        {
            $this->dellicense( );
            $_r = $this->toreg( );
        }
        $str = $this->geturl( $_r );
        $deskauth = $this->geturl( "授权" );
        $str .= "<script>\$('authinfo').set('html','".$deskauth."');</script>";
        $this->system->output( $str );
    }

    function tologin( )
    {
        $loginToShopEx = $this->to_shopex_certificate( "certi.login" );
        if ( $loginToShopEx['res'] == "succ" )
        {
            $str = $loginToShopEx['info']['service'][VERIFY_APP_ID]['cert_auth']['auth_strname']."[".$loginToShopEx['info']['service'][VERIFY_APP_ID]['cert_auth']['auth_typename']."]";
            $auth = $loginToShopEx['info']['service'][VERIFY_APP_ID]['cert_auth']['auth_strname'];
            $autype = $loginToShopEx['info']['service'][VERIFY_APP_ID]['cert_auth']['auth_str'];
            $this->system->setconf( "certificate.auth_type", $autype );
            $this->system->setconf( "certificate.auth_strname", $auth );
            if ( !$this->system->getconf( "certificate.distribute" ) || $loginToShopEx['info']['service'][VERIFY_APP_ID]['distribute']['status'] == "open" )
            {
                $this->system->setconf( "certificate.distribute", true );
                return $str;
            }
        }
        else if ( is_array( $loginToShopEx['info']['service'] ) )
        {
            $str = $loginToShopEx['info']['service'][VERIFY_APP_ID]['cert_auth']['auth_strname']."[".$loginToShopEx['info']['service'][VERIFY_APP_ID]['cert_auth']['auth_typename']."]";
        }
        return $str;
    }

    function toreg( )
    {
        $regToShopEx = $this->to_shopex_certificate( "certi.reg" );
        if ( $regToShopEx['res'] == "succ" )
        {
            $this->setcerti( $regToShopEx['info']['certificate_id'] );
            $this->settoken( $regToShopEx['info']['token'] );
            $_r = $this->tologin( );
            return $_r;
        }
        return $regToShopEx['info']['auth_strname']."[".$regToShopEx['info']['auth_typename']."]";
    }

    function geturl( $str, $unlogin = 0 )
    {
        $this->certi_id = $this->getcerti( );
        $sess_id = $this->get_sess( );
        if ( empty( $this->certi_id ) )
        {
            $this->certi_id = "error";
        }
        if ( empty( $sess_id ) )
        {
            $this->system->session->sess_id = "error";
        }
        $confirmkey = $this->setencode( $sess_id, $this->certi_id );
        $url = "?sess_id=".urlencode( $sess_id )."&certi_id=".urlencode( $this->certi_id )."&version=".urlencode( $this->getversion( ) )."&confirmkey=".$confirmkey;
        $url = "http://service.shopex.cn/info.php".$url."&_key_=do";
        if ( $unlogin )
        {
            $url .= "&state=no";
        }
        if ( $this->certi_id == "error" )
        {
            unset( $this->'certi_id' );
        }
        $prefix = "<a href=\"".$url."\" target=\"_blank\" title=\"".$this->certi_id."\">".$str."</a>";
        return $prefix;
    }

    function getversion( )
    {
        $version = $this->system->version( );
        return $version['app']."#".$version['rev'];
    }

    function sendmsg( )
    {
        $data = $this->msg_pack( );
        $date = $this->post_data( $data );
        return true;
    }

    function session_vaild( $session )
    {
        $vaild_params = array(
            "certi_app" => "sess.valid_session",
            "certificate_id" => $this->getcerti( ),
            "app_id" => VERIFY_APP_ID,
            "app_instance_id" => $this->get_app_instance_id( VERIFY_APP_ID ),
            "version" => "1.1",
            "certi_session" => $session,
            "format" => "json"
        );
        $vaild_params['certi_ac'] = $this->make_shopex_ac( $vaild_params, $this->gettoken( ) );
        $net =& $this->system->loadmodel( "utility/http_client" );
        $data = $net->post( APP_WLTX_URL, $vaild_params );
        $return_msg = json_decode( $data, true );
        if ( $return_msg['res'] == "succ" )
        {
            return true;
        }
        return $return_msg;
    }

    function center_send( $function, $params )
    {
        $params['certi_app'] = $function;
        $cer =& $this->system->loadmodel( "service/certificate" );
        $params['certificate_id'] = $cer->getcerti( );
        $token = $cer->gettoken( );
        if ( ( !$token && !$params['certificate_id'] ) || $function != "co.show_se" && $function != "category.get_category_info" )
        {
            return array( );
        }
        $params['certi_url'] = $this->system->base_url( );
        $params['certi_session'] = $this->get_sess( );
        $params['certi_validate_url'] = $this->system->realurl( "tools", "checkSession", array(
            $params['certi_session']
        ) );
        $params['app_id'] = APP_WLTX_ID;
        $params['version'] = APP_WLTX_VERSION;
        $params['certi_ac'] = $this->make_shopex_ac( $params, $token );
        $net =& $this->system->loadmodel( "utility/http_client" );
        $data = $net->post( APP_WLTX_URL, $params, array( "User-Agent" => "ShopEx_Cert_Client" ) );
        $data = json_decode( $data, true );
        return $data;
    }

    function update_info( $app_id, $setting_info )
    {
        $params = array(
            "certi_app" => "certi.update_info",
            "certificate_id" => $this->getcerti( ),
            "app_id" => VERIFY_APP_ID,
            "app_instance_id" => $this->get_app_instance_id( $app_id ),
            "version" => "1.0",
            "certi_session" => $this->get_sess( ),
            "format" => "json",
            "shop_name" => $setting_info['system.shopname'],
            "shop_url" => $setting_info['store.shop_url'],
            "shop_type" => $setting_info['typeid'],
            "tel" => $setting_info['store.telephone'],
            "email" => $setting_info['store.email'],
            "address" => $setting_info['store.address']
        );
        $params['certi_ac'] = $this->make_shopex_ac( $params, $this->gettoken( ) );
        return $params;
    }

    function get_category_info( $app_id )
    {
        $cat_params = array(
            "certi_app" => "category.get_category_info",
            "certificate_id" => $this->getcerti( ),
            "app_id" => VERIFY_APP_ID,
            "version" => "1.0",
            "format" => "json"
        );
        $cat_params['certi_ac'] = $this->make_shopex_ac( $cat_params, $this->gettoken( ) );
        return $cat_params;
    }

}

?>
