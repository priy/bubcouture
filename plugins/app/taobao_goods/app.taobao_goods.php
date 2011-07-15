<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class app_taobao_goods extends app
{

    public $ver = 1.2;
    public $name = "同步管理淘宝商品";
    public $website = "http://www.shopex.cn";
    public $author = "shopex";
    public $reqver = "";
    public $help = "http://www.shopex.cn/help/ShopEx48/help_shopex48-1264414583-12129.html";

    public function ctl_mapper( )
    {
        return array( );
    }

    public function install( )
    {
        parent::install( );
        return TRUE;
    }

    public function uninstall( )
    {
        foreach ( $this->dbtables( ) as $k => $v )
        {
            $this->db->exec( $a = "drop table if exists sdb_".$this->ident."_".$k );
        }
        $this->db->exec( "delete from sdb_status where status_key='TB_SESS' LIMIT 1" );
        return TRUE;
    }

    public function output_modifiers( )
    {
        return array( "admin:goods/product:update" => "product_modifiers:product_update", "admin:goods/product:edit" => "product_modifiers:product_edit", "admin:goods/product:addNew" => "product_modifiers:product_add", "admin:goods/product:index" => "product_modifiers:product_index", "admin:goods/product:detail" => "product_modifiers:detail" );
    }

    public function listener( )
    {
        return array( "trading/goods:save" => "product_listener:toAdd" );
    }

    public function setting_save( )
    {
        require( "mdl.taobao.php" );
        require( "mdl.center_send.php" );
        ( );
        $objTaobao = new mdl_taobao( );
        $setting = $_POST['setting'];
        if ( $setting['app.taobao_goods.nick'] )
        {
            $nick = $setting['app.taobao_goods.nick'];
            unset( $setting['app.taobao_goods.nick'] );
            ( );
            $obj = new mdl_center_send( );
            if ( !$obj->open_servies( ) )
            {
                echo "服务开通失败";
                exit( );
            }
            $return = $obj->set_tb_nick( $nick );
            if ( $return['result'] == "succ" )
            {
                $objTaobao->setConf( "app.taobao_goods.nick", $nick );
            }
            else
            {
                echo "淘宝昵称不允许更改";
            }
        }
        foreach ( $setting as $key => $val )
        {
            $objTaobao->setConf( $key, $val );
        }
    }

    public function setting_load( )
    {
        require_once( "mdl.center_send.php" );
        ( );
        $center = new mdl_center_send( );
        $mess = $center->get_tb_nick( );
        if ( $mess )
        {
            $nick = $mess['result_msg'];
            $this->system->setConf( "app.taobao_goods.nick", $nick, TRUE );
        }
    }

    public function getContents( $params, $session = FALSE, $method = "get", $no_red = FALSE )
    {
        require_once( "mdl.center_send.php" );
        ( );
        $center = new mdl_center_send( );
        $tb_api_mess = $center->getTbAppInfo( );
        $params = array_merge( $tb_api_mess['result_msg'], $params );
        return $this->system->call( "tb_mess_send", $params, $session, $method, $no_red );
    }

    public function timeout( )
    {
        echo "fail";
        exit( );
    }

}

?>
