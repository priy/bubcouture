<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class app_openid_taobao extends app
{

    public $ver = 1;
    public $name = "淘宝信任登录";
    public $website = "http://www.shopex.cn";
    public $author = "shopex";
    public $reqver = "";
    public $app_id = "taobao";
    public $help_tip = "<a href='http://api.renren.com' target='_blank'>淘宝免登申请方法</a>";

    public function ctl_mapper( )
    {
        return array( );
    }

    public function install( )
    {
        if ( $this->system->getConf( "certificate.id" ) )
        {
            $obj = $this->system->loadModel( "plugins/openid_taobao/openid_taobao_center_send" );
            $obj->save_api_key( );
            $return = $obj->edit_app_status( "open" );
            if ( $return['result'] == "succ" )
            {
                return parent::install( );
            }
            else
            {
                echo "启用失败";
                return FALSE;
            }
        }
    }

    public function openid_login( $row )
    {
        $this->save_userinfo( $row );
    }

    public function save_userinfo( $row )
    {
        $member =& $this->system->loadModel( "member/member" );
        $defalut_cols = array( "address" => "addr", "truename" => "name" );
        $row_array = array( "nick_name" => "昵称", "credid" => "身份证", "alipay_account" => "支付宝帐号", "company" => "公司名", "qq" => "QQ", "truename" => "姓名", "sex" => "性别", "mobile" => "移动电话", "address" => "联系地址", "birthday" => "出生日期", "member_id" => "", "member_lv_id" => "" );
        $user_insert = $this->array_key_filter( $row, $row_array, $defalut_cols );
        $member->thirdLoginInfo( $user_insert );
    }

    public function array_key_filter( &$array, $keys, $defalut_cols )
    {
        $return = array( );
        foreach ( $keys as $k => $v )
        {
            foreach ( $array as $k1 => $v1 )
            {
                if ( $k == $k1 )
                {
                    if ( $v == "" )
                    {
                        $return[$k] =& $array[$k1];
                    }
                    else
                    {
                        foreach ( $defalut_cols as $k2 => $v2 )
                        {
                            if ( $k1 == $k2 )
                            {
                                $return[$defalut_cols[$k2]] = array(
                                    $keys[$k] => $array[$k1]
                                );
                                unset( $k );
                                unset( $k1 );
                            }
                            else
                            {
                                $return[$k] = array(
                                    $keys[$k] => $array[$k1]
                                );
                            }
                        }
                    }
                }
            }
        }
        return $array = $return;
    }

    public function uninstall( )
    {
        $obj = $this->system->loadModel( "plugins/openid_taobao/openid_taobao_center_send" );
        $return = $obj->edit_app_status( "close" );
        if ( $return['result'] == "succ" )
        {
            return parent::uninstall( );
        }
        else
        {
            echo "卸载失败";
            return FALSE;
        }
    }

    public function enable( )
    {
        $obj = $this->system->loadModel( "plugins/openid_taobao/openid_taobao_center_send" );
        $return = $obj->edit_app_status( "open" );
        if ( $return['result'] == "succ" )
        {
            return TRUE;
        }
        else
        {
            echo "启用失败";
            exit( );
        }
    }

    public function disable( )
    {
        $obj = $this->system->loadModel( "plugins/openid_taobao/openid_taobao_center_send" );
        $return = $obj->edit_app_status( "close" );
        if ( $return['result'] == "succ" )
        {
            return TRUE;
        }
        else
        {
            echo "禁用失败";
            exit( );
        }
    }

}

?>
