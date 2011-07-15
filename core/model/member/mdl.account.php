<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_account extends shopobject
{

    var $name = "会员";

    function check_uname( $uname, &$message )
    {
        $uname = trim( $uname );
        $len = strlen( $uname );
        if ( $len < 3 )
        {
            $message = __( "用户名过短!" );
            return false;
        }
        if ( 20 < $len )
        {
            $message = __( "用户名过长!" );
            return false;
        }
        if ( !preg_match( "/^([@\\.]|[^\\x00-\\x2f^\\x3a-\\x40]){2,20}\$/i", $uname ) )
        {
            $message = __( "用户名包含非法字符!" );
            return false;
        }
        $row = $this->db->selectrow( "select uname from sdb_members where uname='".$uname."'" );
        if ( $row['uname'] )
        {
            $message = __( "重复的用户名!" );
            return false;
        }
        if ( $this->check_name_inuc( $uname ) == 1 )
        {
            return true;
        }
        return false;
    }

    function check_email( $email, &$message )
    {
        if ( !eregi( "^.+@.+\$", $email ) )
        {
            $message = __( "邮箱输入有误！" );
            return false;
        }
        return true;
    }

    function events( )
    {
        $ret = array(
            "register" => array(
                "label" => __( "注册" ),
                "globals" => 0,
                "params" => array(
                    "has_sup" => array(
                        "label" => __( "有推荐人" ),
                        "type" => "bool"
                    ),
                    "reg_node" => array(
                        "label" => __( "注册序号/编码" ),
                        "type" => "number"
                    )
                )
            ),
            "login" => array(
                "label" => __( "登录" ),
                "globals" => 0,
                "params" => array(
                    "login_times" => array(
                        "label" => __( "登录次数" ),
                        "type" => "number"
                    ),
                    "login_birthay" => array(
                        "label" => __( "生日登录" ),
                        "type" => "bool"
                    )
                )
            ),
            "changelevel" => array(
                "label" => __( "会员等级变化" ),
                "globals" => 0,
                "params" => array(
                    "level_change" => array(
                        "label" => __( "会员等级变化" ),
                        "type" => "bool"
                    )
                )
            ),
            "changepoint" => array(
                "label" => __( "消费积分变化" ),
                "globals" => 0,
                "params" => array(
                    "point_change" => array(
                        "label" => __( "消费积分变化" ),
                        "type" => "bool"
                    )
                )
            ),
            "changeadvance" => array(
                "label" => __( "预存款变化" ),
                "globals" => 0,
                "params" => array(
                    "advance_relay" => array(
                        "label" => __( "余额" ),
                        "type" => "number"
                    ),
                    "advance_onetime" => array(
                        "label" => __( "单次金额" ),
                        "type" => "number"
                    )
                )
            ),
            "advisory_new" => array(
                "label" => __( "新咨询" ),
                "globals" => 0,
                "params" => array(
                    "advisory_new" => array(
                        "label" => __( "新咨询" ),
                        "type" => "bool"
                    )
                )
            ),
            "advisory_replay" => array(
                "label" => __( "咨询被回复" ),
                "globals" => 0,
                "params" => array(
                    "advisory_replay" => array(
                        "label" => __( "咨询被回复" ),
                        "type" => "bool"
                    )
                )
            ),
            "advisory_del" => array(
                "label" => __( "咨询被删除" ),
                "globals" => 0,
                "params" => array(
                    "advisory_del" => array(
                        "label" => __( "咨询被删除" ),
                        "type" => "bool"
                    )
                )
            ),
            "discuzz_new" => array(
                "label" => __( "新评论" ),
                "globals" => 0,
                "params" => array(
                    "discuzz_new" => array(
                        "label" => __( "新评论" ),
                        "type" => "bool"
                    )
                )
            ),
            "discuzz_check" => array(
                "label" => __( "评论被审核" ),
                "globals" => 0,
                "params" => array(
                    "discuzz_check" => array(
                        "label" => __( "评论被审核" ),
                        "type" => "bool"
                    )
                )
            ),
            "discuzz_del" => array(
                "label" => __( "评论被删除" ),
                "globals" => 0,
                "params" => array(
                    "discuzz_del" => array(
                        "label" => __( "评论被删除" ),
                        "type" => "bool"
                    )
                )
            ),
            "shortmessage_new" => array(
                "label" => __( "新消息" ),
                "globals" => 0,
                "params" => array(
                    "shortmessage_new" => array(
                        "label" => __( "新消息" ),
                        "type" => "bool"
                    )
                )
            ),
            "shortmessage_reply" => array(
                "label" => __( "消息被回复" ),
                "globals" => 0,
                "params" => array(
                    "shortmessage_reply" => array(
                        "label" => __( "消息被回复" ),
                        "type" => "bool"
                    )
                )
            ),
            "shortmessage_del" => array(
                "label" => __( "消息被删除" ),
                "globals" => 0,
                "params" => array(
                    "shortmessage_del" => array(
                        "label" => __( "消息被删除" ),
                        "type" => "bool"
                    )
                )
            ),
            "saleservice" => array(
                "label" => __( "售后服务" ),
                "globals" => 0,
                "params" => array(
                    "service_apply" => array(
                        "label" => __( "申请" ),
                        "type" => "bool"
                    ),
                    "service_manage" => array(
                        "label" => __( "处理" ),
                        "type" => "bool"
                    )
                )
            ),
            "shopmessage_new" => array(
                "label" => __( "新留言" ),
                "globals" => 0,
                "params" => array(
                    "shopmessage_new" => array(
                        "label" => __( "新留言" ),
                        "type" => "bool"
                    )
                )
            ),
            "shopmessage_reply" => array(
                "label" => __( "留言被回复" ),
                "globals" => 0,
                "params" => array(
                    "shopmessage_reply" => array(
                        "label" => __( "留言被回复" ),
                        "type" => "bool"
                    )
                )
            ),
            "shopmessage_del" => array(
                "label" => __( "留言被删除" ),
                "globals" => 0,
                "params" => array(
                    "shopmessage_del" => array(
                        "label" => __( "留言被删除" ),
                        "type" => "bool"
                    )
                )
            )
        );
        $global_params = array(
            "ip" => array(
                "label" => __( "ip地址" ),
                "type" => "ip"
            ),
            "reg_days" => array(
                "label" => __( "注册后总天数" ),
                "type" => "number"
            ),
            "is_birthday" => array(
                "label" => __( "生日当天" ),
                "type" => "bool"
            ),
            155 => array(
                "label" => __( "购买总量" ),
                "type" => "money"
            ),
            156 => array(
                "label" => __( "推荐用户数" ),
                "type" => "number"
            )
        );
        foreach ( $ret as $k => $v )
        {
            if ( $ret[$k]['params'] )
            {
                if ( $v['globals'] )
                {
                    $ret[$k]['params'] = array_merge( $ret[$k]['params'], $global_params );
                }
            }
            else
            {
                $ret[$k]['params'] =& $global_params;
            }
        }
        return $ret;
    }

    function create( $data, &$message )
    {
        $data['uname'] = trim( strtolower( $data['uname'] ) );
        $data['email'] = trim( strtolower( $data['email'] ) );
        $data['reg_ip'] = remote_addr( );
        $data['regtime'] = time( );
        if ( !$this->check_uname( $data['uname'], $message ) )
        {
            return false;
        }
        if ( !$this->check_email( $data['email'], $message ) )
        {
            return false;
        }
        if ( $data['passwd'] != $data['passwd_r'] )
        {
            $message = __( "两次密码输入不一致！" );
            return false;
        }
        $row = $this->db->selectrow( "select * from sdb_member_lv where default_lv=\"1\"" );
        $data['member_lv_id'] = $row['member_lv_id'] ? $row['member_lv_id'] : 0;
        $defcur = $this->db->selectrow( "select cur_code from sdb_currency where def_cur=\"true\"" );
        $data['cur'] = $defcur['cur_code'];
        $rs = $this->db->exec( "select * from sdb_members where uname=".$this->db->quote( $data['uname'] ) );
        if ( !$rs && $this->db->getrows( $rs ) )
        {
            trigger_error( __( "存在重复的用户id" ), E_USER_ERROR );
            return false;
        }
        $data['password'] = md5( $data['passwd'] );
        $data['login_count'] = 1;
        getrefer( $data );
        $sql = $this->db->getinsertsql( $rs, $data );
        if ( $this->db->exec( $sql ) )
        {
            $userId = $this->db->lastinsertid( );
            $status =& $this->system->loadmodel( "system/status" );
            $status->add( "MEMBER_REG" );
            $this->init( $userId );
            $sql = "select member_id,member_lv_id,email,uname,password,unreadmsg,cur,lang,point from sdb_members where member_id=".$userId;
            $row = $this->db->selectrow( $sql );
            $row['secstr'] = $this->cookievalue( $userId );
            $this->idColumn = "member_id";
            $data['member_id'] = $userId;
            $this->fireevent( "register", $data, $userId );
            return $row;
        }
        return false;
    }

    function cookievalue( $memberID )
    {
        $row = $this->db->selectrow( "select uname,password from sdb_members where member_id=".$memberID );
        $row['uname'] = md5( $row['uname'] );
        return $memberID."-".utf8_encode( $row['uname'] )."-".md5( $row['password'].STORE_KEY )."-".time( );
    }

    function checkmember( $data )
    {
        $row = $this->db->selectrow( "select member_id,uname,email from sdb_members where uname=\"".$data['uname']."\"" );
        if ( $row['member_id'] && $row['uname'] == $data['uname'] )
        {
            return true;
        }
        return false;
    }

    function verify( $memberId, $code )
    {
        $row = $this->db->selectrow( "select member_id,member_lv_id,email,uname,b_year,b_month,b_day,password,unreadmsg,cur,lang,point,experience from sdb_members where member_id=".intval( $memberId ) );
        if ( $row && md5( $row['password'].STORE_KEY ) == $code )
        {
            $oMsg =& $this->system->loadmodel( "resources/msgbox" );
            $row['unreadmsg'] = $oMsg->getnewmessagenum( $memberId );
            unset( $row->'password' );
            return $row;
        }
        return false;
    }

    function init( $memberId )
    {
        if ( $member = $this->db->selectrow( "select * from sdb_members where member_id=".intval( $memberId ) ) )
        {
            foreach ( $this->listfilters( $member ) as $filter )
            {
                $this->applyfilter( $member, $filter );
            }
        }
        else
        {
        }
        return false;
    }

    function verifylogin( $login, $passwd, &$message, $passport = null )
    {
        $login = trim( strtolower( $login ) );
        if ( !$passport )
        {
            if ( strlen( $login ) == 0 )
            {
                $message = __( "请填写登录信息。" );
                return false;
            }
            $sql = "select member_id,member_lv_id,email,uname,b_year,b_month,b_day,password,unreadmsg,cur,lang,point,login_count,addon from sdb_members where uname=".$this->db->quote( $login )." and password=".$this->db->quote( md5( $passwd ) )." and disabled='false'";
            if ( $row = $this->db->selectrow( $sql ) )
            {
                $row['login_count'] = $data['login_count'] = $row['login_count'] + 1;
                $rs = $this->db->exec( "select login_count from sdb_members where member_id=".intval( $row['member_id'] ) );
                $sSql = $this->db->getupdatesql( $rs, $data );
                $this->db->exec( $sSql );
                $row['secstr'] = $this->cookievalue( $row['member_id'] );
                $oMsg =& $this->system->loadmodel( "resources/msgbox" );
                $row['unreadmsg'] = $oMsg->getnewmessagenum( $row['member_id'] );
                $this->idColumn = "member_id";
                $this->fireevent( "login", $row, $row['member_id'] );
                return $row;
            }
            return false;
        }
        $objPasspt =& $this->system->loadmodel( "member/passport" );
        $objPasspt->verifylogin( $passport, $login, $passwd );
    }

    function verifypassportlogin( $member )
    {
        $sql = "select member_id,member_lv_id,email,uname,password,unreadmsg,cur,lang,point from sdb_members where uname=".$this->db->quote( $member['username'] );
        $row = $this->db->selectrow( $sql );
        if ( $row )
        {
            $sql = "update sdb_members set password=".$this->db->quote( $member['password'] )." where uname=".$this->db->quote( $member['username'] );
            $this->db->exec( $sql );
            return $row;
        }
        return false;
    }

    function tologin( $member )
    {
        if ( empty( $member['username'] ) )
        {
            return false;
        }
        $sql = "select member_id,member_lv_id,email,uname,password,unreadmsg,cur,lang,point from sdb_members where uname=".$this->db->quote( $member['username'] );
        $row = $this->db->selectrow( $sql );
        $row['secstr'] = $this->cookievalue( $row['member_id'] );
        return $row;
    }

    function createpassport( $member )
    {
        $row = $this->db->selectrow( "select * from sdb_member_lv where default_lv=\"1\"" );
        $member['member_lv_id'] = $row['member_lv_id'] ? $row['member_lv_id'] : 0;
        $sql = "insert into sdb_members (member_lv_id,uname,password,email,reg_ip,regtime) values ('".$member['member_lv_id']."','".$member['username']."','".$member['password']."','".$member['email']."','".$member['regip']."','".$member['regdate']."')";
        if ( !$this->db->exec( $sql ) )
        {
            return false;
        }
        return $member['username'];
    }

    function passportcallback( $passport )
    {
        $objPasspt =& $this->system->loadmodel( "member/passport" );
        $memberInfo = $objPasspt->decode( $passort, array_merge( $_GET, $_POST ) );
        $sql = "select member_id,uname from sdb_members where user=".$this->db->quote( $memberId['login'] )." and passport=".$this->db->quote( $memberId['login'] );
        if ( $row = $this->db->selectrow( $sql ) )
        {
            return $this->cookievalue( $row['member_id'] );
        }
        $memberInfo['password_r'] = $memberInfo['password'] = substr( md5( rand( time( ) ) ), 0, 6 );
        return $this->create( $memberInfo );
    }

    function savesecurity( $nMemberId, $aData, &$msg )
    {
        if ( !( $aTemp = $this->db->selectrow( "SELECT password,pw_question,pw_answer,uname,name,email FROM sdb_members WHERE  member_id=".intval( $nMemberId ) ) ) )
        {
            $msg = "无效的用户Id";
            return false;
        }
        if ( empty( $aData['passwd'] ) )
        {
            if ( $aData['pw_answer'] )
            {
            }
            if ( !$aData['pw_question'] )
            {
                $msg = "安全问题修改失败！";
                return false;
            }
            return $this->db->exec( "UPDATE sdb_members SET pw_answer = ".$this->db->quote( $aData['pw_answer'] )." ,pw_question = ".$this->db->quote( $aData['pw_question'] )." WHERE member_id = ".intval( $nMemberId ) );
        }
        $pObj =& $this->system->loadmodel( "member/passport" );
        if ( $obj = $pObj->function_judge( "edituser" ) )
        {
            $res = $obj->edituser( $aTemp['uname'], $aData['old_passwd'], $aData['passwd'], $aTemp['email'] );
            if ( 0 < $res )
            {
                $aSet['password'] = md5( $aData['passwd'] );
                $aRs = $this->db->query( "SELECT password FROM sdb_members WHERE  member_id=".intval( $nMemberId ) );
                $sSql = $this->db->getupdatesql( $aRs, $aSet );
                if ( $this->db->query( $sSql ) )
                {
                    $this->system->setcookie( "MEMBER", $this->cookievalue( $nMemberId ) );
                    return true;
                }
                return false;
            }
            $msg = "输入的旧密码与原密码不符！";
            return false;
        }
        if ( md5( $aData['old_passwd'] ) == $aTemp['password'] )
        {
            if ( $aData['passwd'] == $aData['passwd_re'] )
            {
                if ( $aData['passwd'] == $aData['passwd_re'] )
                {
                    if ( !isset( $aData['passwd'][3], $aData['passwd'][20] ) )
                    {
                        $aSet['password'] = md5( $aData['passwd'] );
                        $aRs = $this->db->query( "SELECT password FROM sdb_members WHERE  member_id=".intval( $nMemberId ) );
                        $sSql = $this->db->getupdatesql( $aRs, $aSet );
                        if ( !$sSql && $this->db->exec( $sSql ) )
                        {
                            $aData = array_merge( $aTemp, $aData );
                            $this->fireevent( "chgpass", $aData, $nMemberId );
                            $this->system->setcookie( "MEMBER", $this->cookievalue( $nMemberId ) );
                            return true;
                        }
                        $msg = "密码修改失败！";
                        return false;
                    }
                    $msg = "密码长度不能大于20";
                    return false;
                }
                $msg = "密码长度不能小于4";
                return false;
            }
            $msg = "两次输入的密码不一致！";
            return false;
        }
        $msg = "输入的旧密码与原密码不符！";
        return false;
    }

    function remove( $memberId )
    {
        return $this->db->exec( "delete from sdb_members where member_id=".intval( $memberId ) );
    }

    function applyfilter( &$who, &$filter )
    {
    }

    function listfilters( $who = null )
    {
        return array( );
    }

    function getfilter( $filterId )
    {
    }

    function load( $memberId )
    {
        $member =& $this->system->loadmodel( "member/member" );
        if ( $member->load( $memberId ) )
        {
            return $member;
        }
        return false;
    }

    function getmemberbyid( $member_id )
    {
    }

    function getmemberbyuser( $user )
    {
    }

    function addmemberprice( $data )
    {
    }

    function getlevelbypoint( $point )
    {
    }

    function getnextlevel( $levelid = 0 )
    {
        $aRet = $this->db->selectrow( "SELECT * FROM sdb_member_lv WHERE pre_id=".intval( $levelid ) );
        return $aRet['member_lv_id'];
    }

    function getprelevel( $levelid = 0 )
    {
        $aRet = $this->db->selectrow( "SELECT * FROM sdb_member_lv WHERE levelid=".intval( $levelid ) );
        return $aRet['member_lv_id'];
    }

    function check_name_inuc( $uname )
    {
        $passport =& $this->system->loadmodel( "member/passport" );
        if ( $obj = $passport->function_judge( "checkuser" ) )
        {
            return $obj->checkuser( $uname );
        }
        return true;
    }

    function getmemberpluginuser( $username )
    {
        $row = $this->db->selectrow( "SELECT * FROM sdb_members WHERE uname = ".$this->db->quote( $username ) );
        if ( $row )
        {
            $row['secstr'] = $this->cookievalue( $row['member_id'] );
            return $row;
        }
        return false;
    }

    function createuserfrompluin( $data, &$message, $uid, $email = "" )
    {
        if ( $data['passwd_r'] && $data['passwd'] != $data['passwd_r'] )
        {
            $message = __( "两次密码输入不一致！" );
            return false;
        }
        $data['uname'] = trim( strtolower( $data['uname'] ) );
        $data['email'] = trim( strtolower( $data['email'] ) );
        $data['reg_ip'] = remote_addr( );
        $data['regtime'] = time( );
        $data['foreign_id'] = $uid;
        $row = $this->db->selectrow( "select * from sdb_member_lv where default_lv=\"1\"" );
        $data['member_lv_id'] = $row['member_lv_id'] ? $row['member_lv_id'] : 0;
        $defcur = $this->db->selectrow( "select cur_code from sdb_currency where def_cur=\"true\"" );
        $data['cur'] = $defcur['cur_code'];
        $rs = $this->db->exec( "select * from sdb_members where uname=".$this->db->quote( $data['uname'] )." or email=".$this->db->quote( $data['email'] ) );
        $data['password'] = md5( $data['passwd'] );
        getrefer( $data );
        $sql = $this->db->getinsertsql( $rs, $data );
        if ( $this->db->exec( $sql ) )
        {
            $userId = $this->db->lastinsertid( );
            $status =& $this->system->loadmodel( "system/status" );
            $status->add( "MEMBER_REG" );
            $this->init( $userId );
            $data['member_id'] = $userId;
            $this->fireevent( "register", $data, $userId );
            $sql = "select member_id,member_lv_id,email,uname,password,unreadmsg,cur,lang,point,foreign_id from sdb_members where member_id=".intval( $userId );
            $row = $this->db->selectrow( $sql );
            $row['secstr'] = $this->cookievalue( $userId );
            return $row;
        }
        return false;
    }

    function pluguserexit( )
    {
        $this->system->setcookie( "MEMBER", "", time( ) - 1000 );
        $this->system->setcookie( "MLV", "", time( ) - 1000 );
        $this->system->setcookie( "CART", "", time( ) - 1000 );
        $this->system->setcookie( "UNAME", "", time( ) - 1000 );
    }

    function plugusersetcookie( $row )
    {
        $this->system->setcookie( "MEMBER", $row['secstr'], null );
        $this->system->setcookie( "UNAME", $row['uname'], null );
        $this->system->setcookie( "MLV", $row['member_lv_id'], null );
        $this->system->setcookie( "CUR", $row['cur'], null );
        $this->system->setcookie( "LANG", $row['lang'], null );
    }

    function pluguserregist( $userdb = "", $memberid = "", $username = "", $password = "", $email = "" )
    {
        if ( is_array( $userdb ) )
        {
            $res = $this->db->selectrow( "SELECT * FROM sdb_members where uname=".$this->db->quote( $userdb['username'] ) );
            if ( !$res )
            {
                $data['uname'] = trim( $userdb['username'] );
                $data['reg_ip'] = remote_addr( );
                $data['regtime'] = $userdb['time'];
                $data['password'] = $userdb['password'];
                $data['email'] = $userdb['email'];
                $defcur = $this->db->selectrow( "select cur_code from sdb_currency where def_cur=\"true\"" );
                $data['cur'] = $defcur['cur_code'];
                getrefer( $data );
                $row = $this->db->selectrow( "select * from sdb_member_lv where default_lv=\"1\"" );
                $data['member_lv_id'] = $row['member_lv_id'] ? $row['member_lv_id'] : 0;
                $rs = $this->db->exec( "select * from sdb_members where 0=1" );
                $sql = $this->db->getinsertsql( $rs, $data );
                if ( !$sql && $this->db->exec( $sql ) )
                {
                    $userId = $this->db->lastinsertid( );
                    $status =& $this->system->loadmodel( "system/status" );
                    $status->add( "MEMBER_REG" );
                    $this->init( $userId );
                    $data['member_id'] = $userId;
                    $this->fireevent( "register", $data, $userId );
                }
            }
            else
            {
                $this->pluguserupdate( $userdb );
            }
            $username = $userdb['username'];
        }
        else
        {
            $res = $this->db->selectrow( "SELECT * FROM sdb_members where foreign_id=".$memberid );
            if ( !$res )
            {
                $data['foreign_id'] = $memberid;
                $data['uname'] = trim( strtolower( $username ) );
                $data['reg_ip'] = remote_addr( );
                $data['regtime'] = trim( time( ) );
                $data['password'] = md5( "123456" );
                $data['email'] = $email;
                $defcur = $this->db->selectrow( "select cur_code from sdb_currency where def_cur=\"true\"" );
                $data['cur'] = $defcur['cur_code'];
                getrefer( $data );
                $row = $this->db->selectrow( "select * from sdb_member_lv where default_lv=\"1\"" );
                $data['member_lv_id'] = $row['member_lv_id'] ? $row['member_lv_id'] : 0;
                $rs = $this->db->exec( "select * from sdb_members where 0=1" );
                $sql = $this->db->getinsertsql( $rs, $data );
                if ( !$sql && $this->db->exec( $sql ) )
                {
                    $userId = $this->db->lastinsertid( );
                    $this->init( $userId );
                    $data['member_id'] = $userId;
                    $this->fireevent( "register", $data, $userId );
                }
                $plugsql = "select member_id,member_lv_id,email,uname,b_year,b_month,b_day,password,unreadmsg,cur,lang,point from sdb_members where member_id=".$userId;
            }
            else
            {
                $plugsql = "select member_id,member_lv_id,email,uname,b_year,b_month,b_day,password,unreadmsg,cur,lang,point from sdb_members where foreign_id=".$memberid;
            }
        }
        !$plugsql ? ( $plugsql = "select member_id,member_lv_id,email,uname,b_year,b_month,b_day,password,unreadmsg,cur,lang,point from sdb_members where uname=".$this->db->quote( $username ) ) : "";
        if ( $row = $this->db->selectrow( $plugsql ) )
        {
            $row['secstr'] = $this->cookievalue( $row['member_id'] );
            $oMsg =& $this->system->loadmodel( "resources/msgbox" );
            $row['unreadmsg'] = $oMsg->getnewmessagenum( $row['member_id'] );
            $this->plugusersetcookie( $row );
        }
        return false;
    }

    function createotherlogin( $row )
    {
        if ( !$row['open_id'] )
        {
            echo "由于网络传输原因，该功能暂不可用，请稍后再试";
            exit( );
        }
        $random = rand( 0, 99 );
        $random = strlen( $random ) == 2 ? $random : "0".$random;
        $rand = time( ).$random;
        $user['uname'] = $rand;
        $member_get = $this->system->loadmodel( "system/appmgr" );
        $username = $member_get->login_refer( $row );
        if ( $username )
        {
            if ( $data = $this->db->selectrow( "SELECT mm.member_id,mm.member_lv_id,mm.cur,mm.lang,mm.disabled,ol.uname FROM sdb_members mm LEFT JOIN sdb_trust_login ol ON mm.member_id = ol.member_id WHERE ol.member_id =".$username['member_id'] ) )
            {
                if ( $data['disabled'] == "false" )
                {
                    unset( $data->'disabled' );
                    $user = $data;
                }
                else
                {
                    echo "账号登录失败，请联系网店管理员";
                    exit( );
                }
            }
        }
        else
        {
            $defcur = $this->db->selectrow( "select cur_code from sdb_currency where def_cur=\"true\"" );
            $user['cur'] = $defcur['cur_code'];
            $mem_level = $this->system->loadmodel( "member/level" );
            $user['member_lv_id'] = $mem_level->getdefaulelv( );
            $user['member_refer'] = $row['open_type'];
            $user['password'] = md5( time( ) );
            $user['lang'] = "123";
            $user['email'] = $row['email'] ? $row['email'] : "*@*.com";
            $rs = $this->db->exec( "select * from sdb_members where uname=\"".$user['uname']."\"" );
            $sql = $this->db->getinsertsql( $rs, $user );
            $this->db->exec( $sql );
            $user['member_id'] = $this->db->lastinsertid( );
            $ol_data['member_id'] = $user['member_id'];
            $ol_data['show_uname'] = $row['open_type']."_".$row['open_id'];
            $ol_data['uname'] = $row['open_id'];
            $ol_data['member_refer'] = $row['open_type'];
            $ol = $this->db->exec( "select * from sdb_trust_login where 1=1" );
            $sql_ol = $this->db->getinsertsql( $ol, $ol_data );
            $this->db->exec( $sql_ol );
        }
        $user['uname'] = $row['open_type']."_".$row['open_id'];
        $user = array_merge( $user, $row );
        $user['secstr'] = $this->cookievalue( $user['member_id'] );
        $this->plugusersetcookie( $user );
        $appmgr = $this->system->loadmodel( "system/appmgr" );
        $app_model = $appmgr->load( "openid_".$row['open_type'] );
        if ( method_exists( $app_model, "openid_login" ) )
        {
            $r_array = $app_model->openid_login( $user );
        }
        $oCart =& $this->system->loadmodel( "trading/cart" );
        $oCart->memberLogin = false;
        $cartCookie = $oCart->getcart( );
        $oCart->checkmember( $user );
        $oCart->memberLogin = true;
        $oCart->save( "all", $cartCookie );
        $this->system->setcookie( "LOGIN_TYPE", $row['open_type'], null );
        return $r_array;
    }

    function pluguserupdate( $userdb )
    {
        $data['password'] = $userdb['password'];
        $data['email'] = $userdb['email'];
        $data['reg_ip'] = remote_addr( );
        $data['regtime'] = $userdb['time'];
        $rs = $this->db->exec( "SELECT * FROM sdb_members where uname=".$this->db->quote( $userdb['username'] ) );
        $sql = $this->db->getupdatesql( $rs, $data );
        if ( $sql && !$this->db->exec( $sql ) )
        {
            return false;
        }
    }

    function pluguserdelete( $param )
    {
        if ( $param )
        {
            $sql = "delete from sdb_members where member_id in (".$param.")";
            $this->db->exec( $sql );
        }
    }

    function setplugcookie( $k, $v )
    {
        $this->system->setcookie( $k, $v );
    }

    function getplugcookie( $k )
    {
        return $_COOKIE[$k];
    }

    function adminupdatememberpassword( $nMId, $aData, $sendemail )
    {
        $pObj = $this->system->loadmodel( "member/passport" );
        if ( $obj = $pObj->function_judge( "edituser" ) )
        {
            $res = $obj->edituser( $aData['uname'], $aData['old_passwd'], $aData['passwd'], $aTemp['email'], 1 );
            if ( $res <= 0 )
            {
                return $res;
            }
        }
        $rs = $this->db->exec( "select password from sdb_members where member_id='".$nMId."'" );
        $sql = $this->db->getupdatesql( $rs, $aData );
        if ( !$sql && $this->db->exec( $sql ) )
        {
            if ( $sendemail )
            {
                $this->fireevent( "chgpass", $aData, $nMId );
            }
            return true;
        }
        return false;
    }

    function updateforeignid( $data )
    {
        foreach ( $data as $key => $val )
        {
            $this->db->exec( "Update sdb_members set foreign_id=".$val." where member_id=".$key );
        }
    }

    function _get_level_change( )
    {
        return true;
    }

    function _get_has_sup( )
    {
        return false;
    }

    function _get_reg_node( $member_id )
    {
        return $member_id;
    }

    function _get_advisory_new( )
    {
        return true;
    }

    function _get_advisory_replay( )
    {
        return true;
    }

    function _get_advisory_del( )
    {
        return true;
    }

    function _get_shortmessage_new( )
    {
        return true;
    }

    function _get_shortmessage_reply( )
    {
        return true;
    }

    function _get_shortmessage_del( )
    {
        return true;
    }

    function _get_shopmessage_new( )
    {
        return true;
    }

    function _get_shopmessage_reply( )
    {
        return true;
    }

    function _get_shopmessage_del( )
    {
        return true;
    }

    function _get_discuzz_check( )
    {
        return true;
    }

    function _get_discuzz_del( )
    {
        return true;
    }

    function _get_discuzz_to_reply( )
    {
        return true;
    }

    function _get_discuzz_new( )
    {
        return true;
    }

    function _get_login_birthay( $member_id )
    {
        $mem = $this->system->loadmodel( "member/member" );
        $row = $mem->instance( $member_id, "b_month,b_day" );
        if ( $row['b_month'] == date( "m" ) && $row['b_day'] == date( "d" ) )
        {
            return true;
        }
        return false;
    }

    function _get_login_times( $member_id )
    {
        $mem = $this->system->loadmodel( "member/member" );
        $row = $mem->instance( $member_id, "login_count" );
        return $row['login_count'];
    }

    function _get_service_apply( )
    {
        return true;
    }

    function _get_service_manage( )
    {
        return true;
    }

    function _get_point_change( )
    {
        return true;
    }

    function _get_advance_relay( $log_id )
    {
        $adv = $this->system->loadmodel( "member/advance" );
        $row = $adv->instance( $log_id, "member_id" );
        $advance = $adv->get( $row['member_id'] );
        return $advance;
    }

    function _get_advance_onetime( $log_id )
    {
        $adv = $this->system->loadmodel( "member/advance" );
        $row = $adv->instance( $log_id, "money" );
        return $row['money'];
    }

}

?>
