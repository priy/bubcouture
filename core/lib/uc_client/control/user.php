<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
class usercontrol extends base
{

    public function usercontrol( )
    {
        $this->base( );
        $this->load( "user" );
    }

    public function onregister( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        if ( ( $status = $this->_check_username( $username ) ) < 0 )
        {
            return $status;
        }
        if ( ( $status = $this->_check_email( $email ) ) < 0 )
        {
            return $status;
        }
        $uid = $_ENV['user']->add_user( $username, $password, $email );
        return $uid;
    }

    public function onedit( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        if ( !$ignoreoldpw && $email && ( $status = $this->_check_email( $email ) ) < 0 )
        {
            return $status;
        }
        $status = $_ENV['user']->edit_user( $username, $oldpw, $newpw, $email, $ignoreoldpw );
        if ( $newpw && 0 < $status )
        {
            $this->load( "note" );
            $_ENV['note']->add( "updatepw", "username=".urlencode( $username )."&password=".urlencode( $newpw ) );
        }
        return $status;
    }

    public function onlogin( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        if ( $isuid )
        {
            $user = $_ENV['user']->get_user_by_uid( $username );
        }
        else
        {
            $user = $_ENV['user']->get_user_by_username( $username );
        }
        if ( empty( $user ) )
        {
            $status = -1;
        }
        else if ( $user['password'] != md5( md5( $password ).$user['salt'] ) )
        {
            $status = -2;
        }
        else
        {
            $status = $user['uid'];
        }
        $merge = $status != -1 && !$isuid && $_ENV['user']->check_mergeuser( $username ) ? 1 : 0;
        return array(
            $status,
            $user['username'],
            $password,
            $user['email'],
            $merge
        );
    }

    public function oncheck_email( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        return $this->_check_email( $email );
    }

    public function oncheck_username( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        if ( ( $status = $this->_check_username( $username ) ) < 0 )
        {
            return $status;
        }
        else
        {
            return 1;
        }
    }

    public function onget_user( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        if ( !$isuid )
        {
            $status = $_ENV['user']->get_user_by_username( $username );
        }
        else
        {
            $status = $_ENV['user']->get_user_by_uid( $username );
        }
        if ( $status )
        {
            return array(
                $status['uid'],
                $status['username'],
                $status['email']
            );
        }
        else
        {
            return 0;
        }
    }

    public function ondelete( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        return $_ENV['user']->delete_user( $uid );
    }

    public function ongetprotected( )
    {
        $protectedmembers = $this->db->fetch_all( "SELECT username FROM ".UC_DBTABLEPRE."protectedmembers GROUP BY username" );
        return $protectedmembers;
    }

    public function onaddprotected( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $appid = UC_APPID;
        $usernames = ( array )$username;
        foreach ( $usernames as $username )
        {
            $user = $_ENV['user']->get_user_by_username( $username );
            $uid = $user['uid'];
            $this->db->query( "REPLACE INTO ".UC_DBTABLEPRE."protectedmembers SET uid='{$uid}', username='{$username}', appid='{$appid}', dateline='{$this->time}', admin='{$admin}'", "SILENT" );
        }
        return $this->db->errno( ) ? -1 : 1;
    }

    public function ondeleteprotected( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $appid = UC_APPID;
        $usernames = ( array )$username;
        foreach ( $usernames as $username )
        {
            $this->db->query( "DELETE FROM ".UC_DBTABLEPRE."protectedmembers WHERE username='{$username}' AND appid='{$appid}'" );
        }
        return $this->db->errno( ) ? -1 : 1;
    }

    public function _check_username( $username )
    {
        $username = addslashes( trim( stripslashes( $username ) ) );
        if ( !$_ENV['user']->check_username( $username ) )
        {
            return -1;
        }
        else if ( $username != $_ENV['user']->check_usernamecensor( $username ) )
        {
            return -2;
        }
        else if ( $_ENV['user']->check_usernameexists( $username ) )
        {
            return -3;
        }
        return 1;
    }

    public function _check_email( $email )
    {
        if ( !$this->settings )
        {
            $this->settings = $this->cache( "settings" );
            $this->settings = $this->settings['settings'];
        }
        if ( !$_ENV['user']->check_emailformat( $email ) )
        {
            return -4;
        }
        else if ( !$_ENV['user']->check_emailaccess( $email ) )
        {
            return -5;
        }
        else if ( !$this->settings['doublee'] && $_ENV['user']->check_emailexists( $email ) )
        {
            return -6;
        }
        else
        {
            return 1;
        }
    }

    public function onmerge( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        if ( ( $status = $this->_check_username( $newusername ) ) < 0 )
        {
            return $status;
        }
        $uid = $_ENV['user']->add_user( $newusername, $password, $email, $uid );
        $this->db->query( "UPDATE ".UC_DBTABLEPRE."pms SET msgfrom='{$newusername}' WHERE msgfromid='{$uid}' AND msgfrom='{$oldusername}'" );
        $this->db->query( "DELETE FROM ".UC_DBTABLEPRE."mergemembers WHERE appid='".UC_APPID."' AND username='{$oldusername}'" );
        return $uid;
    }

    public function getmaxuid( )
    {
        $query = $this->db->query( "SHOW CREATE TABLE ".UC_DBTABLEPRE."members" );
        $data = $this->db->fetch_array( $query );
        $data = $data['Create Table'];
        if ( preg_match( "/AUTO_INCREMENT=(\\d+?)[\\s|\$]/i", $data, $a ) )
        {
            return $a[1] - 1;
        }
        else
        {
            return 0;
        }
    }

    public function onallmerge( $data )
    {
        $maxuid = $this->getmaxuid( );
        if ( is_array( $data ) )
        {
            foreach ( $data[0] as $key => $val )
            {
                $salt = rand( 100000, 999999 );
                $password = md5( $val['password'].$salt );
                $val['uname'] = addslashes( $val['uname'] );
                $lastuid = $val['member_id'] + $maxuid;
                $queryuc = $this->db->query( "SELECT count(*) FROM ".UC_DBTABLEPRE."members WHERE username='{$val['uname']}'" );
                $userexist = $this->db->result( $queryuc, 0 );
                if ( !$userexist )
                {
                    $this->db->query( "INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."members SET username='{$val['uname']}', password='{$password}',\n                        email='{$val['email']}', regip='{$val['reg_ip']}', regdate='{$val['regtime']}', salt='{$salt}'", "SILENT" );
                    $uid = $this->db->insert_id( );
                    $data[1][$val['member_id']] = $uid;
                    $this->db->query( "INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."memberfields SET uid='{$uid}'", "SILENT" );
                    $this->db->query( "ALTER TABLE ".UC_DBTABLEPRE."members AUTO_INCREMENT=".( $uid + 1 ) );
                }
                else
                {
                    $this->db->query( "REPLACE INTO ".UC_DBTABLEPRE."mergemembers SET appid='".UC_APPID."', username='{$val['uname']}'", "SILENT" );
                }
            }
        }
    }

}

?>
