<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_3_0_member extends shop_api_object
{

    public $app_error = array
    (
        "dealer_member_not_exists" => array
        (
            "no" => "b_member_001",
            "debug" => "",
            "level" => "error",
            "desc" => "经销商所对应的会员记录无效",
            "info" => ""
        ),
        "member invalid" => array
        (
            "no" => "b_member_002",
            "debug" => "",
            "level" => "error",
            "desc" => "会员用户名密码错误或者不存在",
            "info" => ""
        ),
        "license invalid" => array
        (
            "no" => "b_member_003",
            "debug" => "",
            "level" => "error",
            "desc" => "错误格式的license"
        ),
        "license exist" => array
        (
            "no" => "b_member_004",
            "debug" => "",
            "level" => "error",
            "desc" => "license已经存在"
        )
    );

    public function search_member_list( $data )
    {
        $data['disabled'] = "false";
        $data['orderby'] = "member_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "SELECT count(*) AS all_counts FROM sdb_members AS m WHERE ".implode( " and ", $where ) );
        $where = $this->_filter( $data );
        $member_list = $this->db->select( "SELECT m.*,l.dis_count FROM sdb_members AS m \n                                        LEFT JOIN sdb_member_lv AS l ON l.member_lv_id = m.member_lv_id".$where );
        if ( !is_array( $member_list ) )
        {
            $member_list = array( );
        }
        else
        {
            $arr_member_id = array( );
            foreach ( $member_list as $k => $member )
            {
                $arr_member_id[] = $member['member_id'];
            }
            $tmp_member_dealer_list = $this->db->select( "SELECT * from sdb_member_dealer where member_id IN(".implode( ",", $arr_member_id ).")" );
            $member_dealer_list = array( );
            if ( $tmp_member_dealer_list )
            {
                foreach ( $tmp_member_dealer_list as $k => $member_dealer )
                {
                    $member_dealer_list[$member_dealer['member_id']] = $member_dealer;
                }
            }
            foreach ( $member_list as $k => $member )
            {
                $new_member = array( );
                $new_member['member_lv'] = $member['member_lv_id'];
                $new_member['certificate_id'] = $member['certificate_id'];
                $new_member['dis_count'] = $member['dis_count'];
                $new_member['bind_time'] = $member['bind_time'];
                unset( $member['member_lv_id'] );
                unset( $member['certificate_id'] );
                unset( $member['dis_count'] );
                unset( $member['bind_time'] );
                if ( $member['role_type'] == "dealer" && isset( $member_dealer_list[$member['member_id']] ) )
                {
                    $member['member_dealer'] = $member_dealer_list[$member['member_id']];
                }
                else
                {
                    $member['member_dealer'] = array( );
                }
                $new_member['ext_info'] = $member;
                $member_list[$k] = $new_member;
            }
        }
        $result['counts'] = count( $member_list );
        $result['data_info'] = $this->_filter_member_list( $member_list );
        $this->api_response( "true", FALSE, $result );
    }

    public function _filter_member_list( $data )
    {
        foreach ( $data as $key => $value )
        {
            if ( is_null( $value['dis_count'] ) )
            {
                $data[$key]['dis_count'] = 1;
            }
        }
        return $data;
    }

    public function before_filter( $filter )
    {
        $where[] = "m.certificate_id != 0";
        if ( isset( $filter['disabled'] ) )
        {
            $where[] = "m.disabled = \"".$filter['disabled']."\"";
        }
        return $where;
    }

    public function _filter( $filter )
    {
        $where = $this->before_filter( $filter );
        return parent::_filter( $where, $filter );
    }

    public function check_member( $data )
    {
        $user_name = $data['user_name'];
        $user_pwd = $data['user_pwd'];
        $member_info = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname='".$user_name."' AND password='".md5( $user_pwd )."'" );
        if ( $member_info )
        {
            $this->api_response( "true", FALSE, array( "data_info" => "success" ) );
        }
        else
        {
            $this->api_response( "fail", "Member Error!" );
        }
    }

    public function bind_license( $data )
    {
        $user_name = $data['user_name'];
        $user_pwd = $data['user_pwd'];
        $license_id = $data['license_id'];
        if ( empty( $license_id ) )
        {
            $this->add_application_error( "license invalid" );
        }
        $pObj = $this->system->loadModel( "member/passport" );
        if ( $obj = $pObj->function_judge( "checkusername" ) )
        {
            $uinfo = $obj->checkusername( $user_name, $user_pwd, "" );
            if ( is_array( $uinfo ) && 0 < intval( $uinfo[0] ) )
            {
                $member_info = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname='".$user_name."'" );
            }
            else
            {
                $member_info = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname='".$user_name."' AND password='".md5( $user_pwd )."'" );
            }
        }
        else
        {
            $member_info = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname='".$user_name."' AND password='".md5( $user_pwd )."'" );
        }
        if ( empty( $member_info ) )
        {
            $this->add_application_error( "member invalid" );
        }
        else
        {
            $member_id = $member_info['member_id'];
            if ( $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE certificate_id=".floatval( $license_id ) ) || $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE member_id=".$member_id." AND certificate_id > 0" ) )
            {
                $this->add_application_error( "license exist" );
            }
            else
            {
                $rs = $this->db->query( "SELECT * FROM sdb_members WHERE member_id=".$member_id );
                $sql = $this->db->GetUpdateSQL( $rs, array(
                    "certificate_id" => $license_id,
                    "bind_time" => time( )
                ) );
                if ( $sql && !$this->db->exec( $sql ) )
                {
                    $this->api_response( "fail", "db error" );
                }
                else
                {
                    $this->api_response( "true", FALSE, array( "data_info" => "success" ) );
                }
            }
        }
    }

}

?>
