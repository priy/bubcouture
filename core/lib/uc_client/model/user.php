<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class usermodel
{

    public $db = NULL;
    public $base = NULL;

    public function usermodel( &$base )
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    public function get_user_by_uid( $uid )
    {
        $arr = $this->db->fetch_first( "SELECT * FROM ".UC_DBTABLEPRE."members WHERE uid='{$uid}'" );
        return $arr;
    }

    public function get_user_by_username( $username )
    {
        $arr = $this->db->fetch_first( "SELECT * FROM ".UC_DBTABLEPRE."members WHERE username='{$username}'" );
        return $arr;
    }

    public function check_username( $username )
    {
        $guestexp = "\\xA1\\xA1|\\xAC\\xA3|^Guest|^\\xD3\\xCE\\xBF\\xCD|\\xB9\\x43\\xAB\\xC8";
        $len = strlen( $username );
        if ( 15 < $len || $len < 3 || preg_match( "/\\s+|^c:\\con\\con|[%,\\*\"\\s\\<\\>\\&]|{$guestexp}/is", $username ) )
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    public function check_mergeuser( $username )
    {
        $data = $this->db->result_first( "SELECT count(*) FROM ".UC_DBTABLEPRE."mergemembers WHERE appid='".UC_APPID."' AND username='{$username}'" );
        return $data;
    }

    public function check_usernamecensor( $username )
    {
        $_CACHE = $this->base->cache( "badwords" );
        $censorusername = $this->base->get_setting( "censorusername" );
        $censorusername = $censorusername['censorusername'];
        $censorexp = "/^(".str_replace( array( "\\*", "\r\n", " " ), array( ".*", "|", "" ), preg_quote( $censorusername = trim( $censorusername ), "/" ) ).")\$/i";
        $usernamereplaced = $_CACHE['badwords']['findpattern'] ? preg_replace( $_CACHE['badwords']['findpattern'], $_CACHE['badwords']['replace'], $username ) : $username;
        if ( $usernamereplaced != $username || $censorusername && preg_match( $censorexp, $username ) )
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    public function check_usernameexists( $username )
    {
        $data = $this->db->result_first( "SELECT username FROM ".UC_DBTABLEPRE."members WHERE username='{$username}'" );
        return $data;
    }

    public function check_emailformat( $email )
    {
        return 6 < strlen( $email ) && preg_match( "/^[\\w\\-\\.]+@[\\w\\-\\.]+(\\.\\w+)+\$/", $email );
    }

    public function check_emailaccess( $email )
    {
        $setting = $this->base->get_setting( array( "accessemail", "censoremail" ) );
        $accessemail = $setting['accessemail'];
        $censoremail = $setting['censoremail'];
        $accessexp = "/(".str_replace( "\r\n", "|", preg_quote( trim( $accessemail ), "/" ) ).")\$/i";
        $censorexp = "/(".str_replace( "\r\n", "|", preg_quote( trim( $censoremail ), "/" ) ).")\$/i";
        if ( $accessemail || $censoremail )
        {
            if ( $accessemail && !preg_match( $accessexp, $email ) || $censoremail && preg_match( $censorexp, $email ) )
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        else
        {
            return TRUE;
        }
    }

    public function check_emailexists( $email )
    {
        $email = $this->db->result_first( "SELECT email FROM  ".UC_DBTABLEPRE."members WHERE email='{$email}'" );
        return $email;
    }

    public function check_login( $username, $password, &$user )
    {
        $user = $this->get_user_by_username( $username );
        if ( empty( $user['username'] ) )
        {
            return -1;
        }
        else if ( $user['password'] != md5( md5( $password ).$user['salt'] ) )
        {
            return -2;
        }
        return $user['uid'];
    }

    public function add_user( $username, $password, $email, $uid = 0 )
    {
        $salt = substr( uniqid( rand( ) ), -6 );
        $password = md5( md5( $password ).$salt );
        $sqladd = $uid ? "uid='".intval( $uid )."'," : "";
        $this->db->query( "INSERT INTO ".UC_DBTABLEPRE."members SET {$sqladd} username='{$username}', password='{$password}', email='{$email}', regip='".$this->base->onlineip."', regdate='".$this->base->time."', salt='{$salt}'" );
        $uid = $this->db->insert_id( );
        $this->db->query( "INSERT INTO ".UC_DBTABLEPRE."memberfields SET uid='{$uid}'" );
        return $uid;
    }

    public function edit_user( $username, $oldpw, $newpw, $email, $ignoreoldpw = 0 )
    {
        $data = $this->db->fetch_first( "SELECT username, uid, password, salt FROM ".UC_DBTABLEPRE."members WHERE username='{$username}'" );
        if ( $ignoreoldpw )
        {
            $isprotected = $this->db->result_first( "SELECT COUNT(*) FROM ".UC_DBTABLEPRE."protectedmembers WHERE uid = '{$data['uid']}'" );
            if ( $isprotected )
            {
                return -8;
            }
        }
        if ( !$ignoreoldpw && $data['password'] != md5( md5( $oldpw ).$data['salt'] ) )
        {
            return -1;
        }
        $sqladd = $newpw ? "password='".md5( md5( $newpw ).$data['salt'] )."'" : "";
        $sqladd .= $email ? ( $sqladd ? "," : "" )." email='{$email}'" : "";
        if ( $sqladd || $emailadd )
        {
            $this->db->query( "UPDATE ".UC_DBTABLEPRE."members SET {$sqladd} WHERE username='{$username}'" );
            return $this->db->affected_rows( );
        }
        else
        {
            return -7;
        }
    }

    public function delete_user( $uidsarr )
    {
        $uidsarr = ( array )$uidsarr;
        $uids = $this->base->implode( $uidsarr );
        $arr = $this->db->fetch_all( "SELECT uid FROM ".UC_DBTABLEPRE."protectedmembers WHERE uid IN ({$uids})" );
        $puids = array( );
        foreach ( ( array )$arr as $member )
        {
            $puids[] = $member['uid'];
        }
        $uids = $this->base->implode( array_diff( $uidsarr, $puids ) );
        if ( $uids )
        {
            $this->db->query( "DELETE FROM ".UC_DBTABLEPRE."members WHERE uid IN({$uids})" );
            $this->db->query( "DELETE FROM ".UC_DBTABLEPRE."memberfields WHERE uid IN({$uids})" );
            $this->base->load( "note" );
            $_ENV['note']->add( "deleteuser", "ids={$uids}" );
            return $this->db->affected_rows( );
        }
        else
        {
            return 0;
        }
    }

    public function get_total_num( $sqladd = "" )
    {
        $data = $this->db->result_first( "SELECT COUNT(*) FROM ".UC_DBTABLEPRE."members {$sqladd}" );
        return $data;
    }

    public function get_list( $page, $ppp, $totalnum, $sqladd )
    {
        $start = $this->base->page_get_start( $page, $ppp, $totalnum );
        $data = $this->db->fetch_all( "SELECT * FROM ".UC_DBTABLEPRE."members {$sqladd} LIMIT {$start}, {$ppp}" );
        return $data;
    }

    public function name2id( $usernamesarr )
    {
        $usernamesarr = uc_addslashes( $usernamesarr, 1, TRUE );
        $usernames = $this->base->implode( $usernamesarr );
        $query = $this->db->query( "SELECT uid FROM ".UC_DBTABLEPRE."members WHERE username IN({$usernames})" );
        $arr = array( );
        while ( $user = $this->db->fetch_array( $query ) )
        {
            $arr[] = $user['uid'];
        }
        return $arr;
    }

}

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
?>
