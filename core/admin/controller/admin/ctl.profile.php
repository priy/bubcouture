<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_profile extends adminPage
{

    public function operator( )
    {
        $this->path[] = array(
            "text" => __( "帐号设置" )
        );
        $oOpt =& $this->system->loadModel( "admin/operator" );
        $data = $oOpt->instance( $this->system->op_id );
        $this->pagedata['op_id'] = $this->system->op_id;
        $this->pagedata['data'] = $data;
        $data['config'] = unserialize( $data['config'] );
        $this->pagedata['timezone_value'] = $GLOBALS['user_timezone'];
        $zones = array( );
        $realtime = time( ) - SERVER_TIMEZONE * 3600;
        $tzs = timezone_list( );
        foreach ( $tzs as $i => $tz )
        {
            $zones[$i] = mydate( "H:i", $realtime + $i * 3600 )." - ".$tz;
        }
        $this->pagedata['timezones'] = $zones;
        $this->pagedata['server_tz'] = $tzs[SERVER_TIMEZONE];
        $this->pagedata['tzlist'] = $tzs;
        $this->display( "admin/self.html" );
    }

    public function saveSelf( )
    {
        $this->begin( "index.php?ctl=admin/profile&act=operator" );
        $oOpt =& $this->system->loadModel( "admin/operator" );
        if ( $_POST['changepwd'] )
        {
            $row = $oOpt->instance( $this->system->op_id, "userpass" );
            if ( md5( $_POST['oldpass'] ) != $row['userpass'] )
            {
                $this->end( FALSE, __( "请输入正确的当前密码" ) );
            }
            if ( $_POST['userpass'] != $_POST['passowrd_again'] )
            {
                $this->end( FALSE, __( "两次密码输入不一致" ) );
            }
        }
        else
        {
            unset( $_POST['userpass'] );
        }
        array_key_filter( $_POST, "userpass,timezone" );
        $oOpt->update( $_POST, array(
            "op_id" => $this->system->op_id
        ) );
        $GLOBALS['_POST']['op_id'] = $this->system->op_id;
        $oProfile =& $this->system->loadModel( "adminprofile" );
        $oProfile->load( $this->system->op_id );
        $this->end( $oOpt->toUpdateSelf( $_POST, $oProfile->setting( ) ), __( "信息保存成功" ) );
    }

}

?>
