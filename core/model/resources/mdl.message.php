<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_message extends shopobject
{

    function getcolumns( )
    {
        $data = shopobject::getcolumns( );
        return $data;
    }

    function getfieldbyid( $id, $aFeild = array
    (
        0 => "*"
    ) )
    {
        $sqlString = "SELECT ".implode( ",", $aFeild )." FROM sdb_message WHERE msg_id = ".intval( $id );
        return $this->db->selectrow( $sqlString );
    }

    function getmsgreply( $msg_id )
    {
        return $this->db->select( "SELECT * FROM sdb_message WHERE for_id = ".intval( $msg_id )." ORDER BY date_line DESC" );
    }

    function setreaded( $msg_id )
    {
        $rs = $this->db->query( "SELECT * FROM sdb_message WHERE msg_id=".intval( $msg_id ) );
        $aUpdate['unread'] = "1";
        $sql = $this->db->getupdatesql( $rs, $aUpdate );
        return !$sql || $this->db->exec( $sql );
    }

    function revert( $aData )
    {
        if ( !$aData['for_id'] )
        {
            trigger_error( __( "保存失败：留言ID丢失" ), E_USER_ERROR );
            return false;
        }
        $aData['date_line'] = time( );
        $aData['msg_from'] = $this->getopnamebyid( $aData['from_id'] );
        $aData['from_type'] = 1;
        $aData['unread'] = "1";
        $aData['folder'] = "inbox";
        $aData['is_sec'] = "false";
        $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            $aMsg = $this->getfieldbyid( $aData['for_id'], array( "is_sec", "from_type" ) );
            if ( $aMsg['from_type'] == 2 && $aMsg['is_sec'] == "true" )
            {
                $aMsg['is_sec'] = "false";
                $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE msg_id=".$aData['for_id'] );
                $sSql = $this->db->getupdatesql( $aRs, $aMsg );
                $this->db->exec( $sSql );
            }
            return true;
        }
        trigger_error( __( "保存失败：" ).$sSql, E_USER_ERROR );
        return false;
    }

    function todisplay( $msg_id, $status )
    {
        $this->db->exec( "UPDATE sdb_message SET is_sec = '".$this->db->quote( $status )."' WHERE msg_id = ".intval( $msg_id ) );
        return true;
    }

    function toremove( $msg_id )
    {
        return $this->db->exec( "DELETE FROM sdb_message WHERE msg_id = ".intval( $msg_id )." OR for_id = ".intval( $msg_id ) );
    }

    function removesendbox( $sd_id )
    {
        return $this->db->exec( "DELETE FROM sdb_sendbox WHERE out_id = ".intval( $sd_id ) );
    }

    function listfilter( $filter )
    {
        $where = array( 1 );
        if ( isset( $filter['from_id'] ) )
        {
            $where[] = "from_id = ".intval( $filter['from_id'] );
        }
        if ( isset( $filter['from_type'] ) )
        {
            $where[] = "from_type = ".intval( $filter['from_type'] );
        }
        if ( isset( $filter['to_id'] ) )
        {
            $where[] = "to_id = ".intval( $filter['to_id'] );
        }
        if ( isset( $filter['to_type'] ) )
        {
            $where[] = "to_type = ".intval( $filter['to_type'] );
        }
        if ( isset( $filter['folder'] ) )
        {
            $where[] = "folder = '".$filter['folder']."'";
        }
        if ( isset( $filter['is_sec'] ) )
        {
            $where[] = "is_sec = '".$filter['is_sec']."'";
        }
        if ( $filter['del_status'] )
        {
            $where[] = "del_status != '".intval( $filter['del_status'] )."'";
        }
        return "WHERE ".implode( " AND ", $where );
    }

    function getmemidbyuname( $sName )
    {
        $aRs = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname='".$sName."'" );
        return $aRs['member_id'];
    }

    function getmemunamebyid( $nMid )
    {
        $aRs = $this->db->selectrow( "SELECT uname FROM sdb_members WHERE member_id=".$nMid );
        return $aRs['uname'];
    }

    function getopnamebyid( $nOpId )
    {
        if ( !$this->opName )
        {
            $aRs = $this->db->selectrow( "SELECT op_id, username FROM sdb_operators WHERE op_id=".$nOpId );
            $this->opName = $aRs['username'];
        }
        return $this->opName;
    }

    function getopid( )
    {
        $aRs = $this->db->selectrow( "SELECT op_id FROM sdb_operators WHERE super=1" );
        return $aRs['op_id'];
    }

    function sendmsg( $from, $to, $meessage, $options = false )
    {
        $aData['from_id'] = $from;
        $aData['to_id'] = $to;
        $aData['from_type'] = isset( $options['from_type'] ) ? $options['from_type'] : 0;
        $aData['msg_from'] = $aData['from_type'] ? isset( $options['msg_from'] ) ? $options['msg_from'] : $this->getopnamebyid( $from ) : isset( $options['msg_from'] ) ? $options['msg_from'] : $this->getmemunamebyid( $from );
        $aData['to_type'] = isset( $options['to_type'] ) ? $options['to_type'] : 0;
        $aData['subject'] = isset( $options['subject'] ) ? $options['subject'] : __( "无标题" );
        $aData['message'] = $meessage;
        $aData['unread'] = "0";
        $aData['is_sec'] = isset( $options['is_sec'] ) && $options['is_sec'] != "" ? $options['is_sec'] : "true";
        $aData['folder'] = isset( $options['folder'] ) ? $options['folder'] : "inbox";
        $aData['date_line'] = time( );
        foreach ( $aData as $ke => $ve )
        {
            $aData[$ke] = htmlspecialchars( $ve );
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( !$sSql && $this->db->exec( $sSql ) )
        {
            if ( $options['folder'] == "inbox" )
            {
                $msgNun = $this->db->selectrow( "SELECT unreadmsg FROM sdb_members WHERE member_id=".$to );
                $aRs = $this->db->query( "SELECT unreadmsg FROM sdb_members WHERE member_id=".$to );
                $sSql = $this->db->getupdatesql( $aRs, array(
                    "unreadmsg" => $msgNun['unreadmsg'] + 1
                ) );
                if ( $sSql )
                {
                    $this->db->exec( $sSql );
                }
            }
            return true;
        }
        return false;
    }

    function getmsgbyid( $nMsgId )
    {
        $aTemp = $this->db->selectrow( "SELECT to_id,to_type, subject, message, unread, is_sec, folder\n                                            FROM sdb_message\n                                            WHERE msg_id=".$nMsgId );
        if ( $aTemp && $aTemp['unread'] == "0" )
        {
            $aRs = $this->db->query( "SELECT unread FROM sdb_message WHERE msg_id=".$nMsgId );
            $sSql = $this->db->getupdatesql( $aRs, array( "unread" => "1" ) );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            $msgNum = $this->db->selectrow( "SELECT count(msg_id) as num FROM sdb_message WHERE unread=\"0\" and folder=\"inbox\" and to_type=".$aTemp['to_type']." and to_id=".$aTemp['to_id'] );
            $aRs = $this->db->query( "SELECT unreadmsg FROM sdb_members WHERE member_id=".$aTemp['to_id'] );
            $sSql = $this->db->getupdatesql( $aRs, array(
                "unreadmsg" => $msgNum['num']
            ) );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
        }
        return $aTemp;
    }

    function getmsginfo( $nMsgId, $status = "send" )
    {
        $aRs = $this->db->selectrow( "SELECT * FROM sdb_message WHERE msg_id=".$nMsgId );
        if ( $aRs )
        {
            if ( $status == "send" )
            {
                $aRs['msg_to'] = $aRs['to_type'] ? __( "管理员" ) : $this->getmemunamebyid( $aRs['to_id'] );
                return $aRs;
            }
            $aRs['msg_to'] = $aRs['from_type'] ? __( "管理员" ) : $this->getmemunamebyid( $aRs['from_id'] );
        }
        return $aRs;
    }

    function delinboxmsg( $aMsgId )
    {
        foreach ( $aMsgId as $val )
        {
            $val = intval( $val );
            if ( $val )
            {
                $aTmp[] = $val;
            }
        }
        if ( $aTmp )
        {
            $this->db->exec( "DELETE FROM sdb_message WHERE msg_id IN (".implode( ",", $aTmp ).") AND del_status='2'" );
            $this->db->exec( "UPDATE sdb_message SET del_status='1' WHERE msg_id IN (".implode( ",", $aTmp ).")" );
        }
        return true;
    }

    function deltrackmsg( $aMsgId )
    {
        foreach ( $aMsgId as $val )
        {
            $val = intval( $val );
            if ( $val )
            {
                $aTmp[] = $val;
            }
        }
        if ( $aTmp )
        {
            $this->db->exec( "DELETE FROM sdb_message WHERE msg_id IN (".implode( ",", $aTmp ).") AND del_status='1'" );
            $this->db->exec( "UPDATE sdb_message SET del_status='2' WHERE msg_id IN (".implode( ",", $aTmp ).")" );
        }
        return true;
    }

    function deloutboxmsg( $aMsgId )
    {
        foreach ( $aMsgId as $val )
        {
            $val = intval( $val );
            if ( $val )
            {
                $aTmp[] = $val;
            }
        }
        if ( $aTmp )
        {
            $this->db->exec( "DELETE FROM sdb_message WHERE msg_id IN (".implode( ",", $aTmp ).")" );
        }
        return true;
    }

    function gettotalmsg( $nMId )
    {
        $aRow = $this->db->selectrow( "SELECT COUNT(msg_id) AS num FROM sdb_message WHERE from_id=".$nMId." OR to_id=".$nMId );
        return $aRow['num'];
    }

    function getmsglistbymemid( $nMId )
    {
        $aRs = $this->db->select( "SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.date_line, s.is_sec, s.unread,m.uname, o.username\n                                                    FROM sdb_message s\n                                                    LEFT JOIN sdb_members m ON s.to_id = m.member_id\n                                                    LEFT JOIN sdb_operators o ON s.to_id = o.op_id\n                                                    WHERE (s.from_id=".$nMId." AND from_type=0) OR (s.to_id=".$nMId." AND to_type=0)\n                                                    ORDER BY s.msg_id" );
        if ( $aRs )
        {
            foreach ( $aRs as $key => $val )
            {
                $aRs[$key]['msg_to'] = $val['to_type'] == 0 ? $val['uname'] : $val['username'];
            }
        }
        return $aRs;
    }

    function getnewmessagenum( $memberid )
    {
        $aMsg = $this->db->selectrow( "SELECT count(*) AS unreadmsg FROM sdb_message WHERE to_type = 0 AND del_status != '1' AND folder = 'inbox' AND unread = '0' AND to_id =".intval( $memberid ) );
        return $aMsg['unreadmsg'];
    }

    function getordermessage( $orderid )
    {
        $row = $this->db->select( "SELECT * FROM sdb_message WHERE rel_order = '".$orderid."' ORDER BY msg_id DESC" );
        return $row;
    }

    function sethasreaded( $orderid )
    {
        $this->db->exec( "UPDATE sdb_message SET unread = '1' WHERE rel_order =".$orderid );
    }

}

?>
