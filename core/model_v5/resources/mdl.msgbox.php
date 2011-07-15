<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( dirname( __FILE__ )."/mdl.message.php" );
class mdl_msgbox extends mdl_message
{

    public $finder_action_tpl = "member/message/finder_action.html";
    public $idColumn = "msg_id";
    public $textColumn = "msg_id";
    public $appendCols = "unread";
    public $adminCtl = "member/msgbox";
    public $defaultCols = "msg_from,from_type,subject,message,date_line,is_sec,unread";
    public $defaultOrder = array
    (
        0 => "msg_id",
        1 => "desc"
    );
    public $tableName = "sdb_message";

    public function is_highlight( $row )
    {
        if ( $row['unread'] == "1" )
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }

    public function modifier_is_sec( &$rows )
    {
        $status = array(
            "true" => __( "否" ),
            "false" => __( "是" )
        );
        foreach ( $rows as $k => $v )
        {
            $rows[$k] = $status[$v];
        }
    }

    public function searchOptions( )
    {
        return array_merge( parent::searchoptions( ), array(
            "msg_from" => __( "留言者" ),
            "keyword" => __( "留言标题" )
        ) );
    }

    public function getColumns( )
    {
        $data = parent::getcolumns( );
        $data['is_sec']['label'] = __( "作为留言" );
        return $data;
    }

    public function _filter( $filter )
    {
        $filter['to_type'] = 1;
        $where[] = "folder = 'inbox'";
        $where[] = "for_id = 0";
        $where[] = "rel_order = 0";
        if ( $filter['msg_from'] )
        {
            $where[] = "msg_from ='".addslashes( $filter['msg_from'] )."'";
        }
        if ( $filter['keyword'] )
        {
            $where[] = "subject like '%".addslashes( $filter['keyword'] )."%'";
        }
        if ( $filter['del_status'] )
        {
            $where[] = "del_status ='".intval( $filter['del_status'] )."'";
        }
        if ( $filter['is_sec'] )
        {
            $where[] = "is_sec ='".$filter['is_sec']."'";
        }
        if ( $filter['to_id'] )
        {
            $where[] = "(to_id ='".$filter['to_id']."' or to_id = 0)";
        }
        if ( $filter['to_type'] )
        {
            $where[] = "to_type ='".$filter['to_type']."'";
        }
        if ( $filter['unread'] )
        {
            $where[] = "unread ='".$filter['unread']."'";
        }
        unset( $filter['keyword'] );
        return parent::_filter( $filter )." AND ".implode( $where, " AND " );
    }

    public function revert( $aData )
    {
        if ( !$aData['for_id'] )
        {
            trigger_error( __( "保存失败：留言ID丢失" ), E_USER_ERROR );
            return false;
        }
        $aData['date_line'] = time( );
        $aData['msg_from'] = $this->getOpNameById( $aData['from_id'] );
        $aData['from_type'] = 1;
        $aData['unread'] = "0";
        $aData['folder'] = "inbox";
        $aData['is_sec'] = "true";
        $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE 0" );
        $sSql = $this->db->getInsertSql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            $aMsg = $this->getFieldById( $aData['for_id'], array( "is_sec", "from_type" ) );
            if ( $aMsg['from_type'] == 2 && $aMsg['is_sec'] == "false" )
            {
                $aMsg['is_sec'] = "false";
                $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE msg_id=".$aData['for_id'] );
                $sSql = $this->db->getUpdateSql( $aRs, $aMsg );
                $this->db->exec( $sSql );
            }
            $aData['member_id'] = $aData['to_id'];
            $this->modelName = "member/account";
            $this->fireEvent( "shortmessage_reply", $aData, $aData['member_id'] );
            return true;
        }
        else
        {
            trigger_error( __( "保存失败：" ).$sSql, E_USER_ERROR );
            return false;
        }
    }

    public function toDisplay( $msg_id, $status )
    {
        $this->db->exec( "UPDATE sdb_message SET is_sec = ".$this->db->quote( $status )." WHERE msg_id = ".intval( $msg_id ) );
        return true;
    }

    public function getMsgList( $filter, $nPage )
    {
        $aRs = $this->db->selectPager( "SELECT * FROM sdb_message ".$this->listFilter( $filter )." order by date_line desc", $nPage, PERPAGE );
        if ( $filter['from_type'] == 0 )
        {
            foreach ( $aRs['data'] as $key => $val )
            {
                if ( $val['for_id'] != 0 && !$this->db->selectrow( "SELECT msg_id FROM sdb_message WHERE msg_id=".$val['for_id']." and disabled=\"false\"" ) )
                {
                    unset( $this->data[$key] );
                    break;
                }
                if ( $val['to_type'] == 0 )
                {
                    $aTmp[$val['to_id']] = $key;
                    $aUser[$key] = $val['to_id'];
                    $tmp = $this->db->selectrow( "SELECT member_id,uname FROM sdb_members WHERE member_id=".$val['to_id'] );
                    $aRs['data'][$key]['to_name'] = $tmp['uname'];
                }
            }
        }
        return $aRs;
    }

    public function listFilter( $filter )
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

    public function getMemIdByUName( $sName )
    {
        $aRs = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname='".$sName."'" );
        return $aRs['member_id'];
    }

    public function getMemUNameById( $nMid )
    {
        $aRs = $this->db->selectrow( "SELECT uname FROM sdb_members WHERE member_id=".$nMid );
        return $aRs['uname'];
    }

    public function getOpNameById( $nOpId )
    {
        if ( !$this->opName )
        {
            $aRs = $this->db->selectrow( "SELECT op_id, username FROM sdb_operators WHERE op_id=".$nOpId );
            $this->opName = $aRs['username'];
        }
        return $this->opName;
    }

    public function getOpId( )
    {
        $aRs = $this->db->selectrow( "SELECT op_id FROM sdb_operators WHERE super='1'" );
        return $aRs['op_id'];
    }

    public function sendMsg( $from, $to, $meessage, $options = false, $admin = 0 )
    {
        $aData['from_id'] = intval( $from );
        $aData['to_id'] = intval( $to );
        $aData['from_type'] = isset( $options['from_type'] ) ? $options['from_type'] : 0;
        $aData['msg_from'] = $aData['from_type'] ? isset( $options['msg_from'] ) ? $options['msg_from'] : $this->getOpNameById( $from ) : isset( $options['msg_from'] ) ? $options['msg_from'] : $this->getMemUNameById( $from );
        $aData['to_type'] = isset( $options['to_type'] ) ? $options['to_type'] : 0;
        $aData['subject'] = isset( $options['subject'] ) ? $options['subject'] : __( "无标题" );
        $aData['message'] = $meessage;
        $aData['unread'] = "0";
        $aData['is_sec'] = isset( $options['is_sec'] ) && $options['is_sec'] != "" ? $options['is_sec'] : "true";
        $aData['folder'] = isset( $options['folder'] ) ? $options['folder'] : "inbox";
        $aData['date_line'] = time( );
        if ( $options['msg_id'] )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE msg_id=".intval( $options['msg_id'] ) );
            $sSql = $this->db->GetUpdateSql( $aRs, $aData, true );
            $aData['member_id'] = $aData['to_id'];
        }
        else
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE 0=1" );
            $sSql = $this->db->GetInsertSql( $aRs, $aData );
            $aData['member_id'] = $aData['from_id'];
        }
        if ( !$sSql || $this->db->exec( $sSql ) )
        {
            if ( $admin )
            {
                $this->modelName = "member/account";
                $this->fireEvent( "shortmessage_new", $aData, $aData['member_id'] );
            }
            if ( $options['folder'] == "inbox" && $aData['to_type'][0] == 0 )
            {
                $this->db->exec( "update sdb_members set unreadmsg=unreadmsg+1 where member_id=".$to );
            }
            return true;
        }
        return false;
    }

    public function getMsgById( $nMsgId )
    {
        $aTemp = $this->db->selectrow( "SELECT to_id,to_type, subject, message, unread, is_sec, folder\n                                            FROM sdb_message\n                                            WHERE msg_id=".$nMsgId." and disabled=\"false\"" );
        if ( $aTemp && $aTemp['unread'] == "0" )
        {
            $aRs = $this->db->query( "SELECT unread FROM sdb_message WHERE msg_id=".$nMsgId );
            $sSql = $this->db->getUpdateSql( $aRs, array( "unread" => "1" ) );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            $msgNum = $this->db->selectrow( "SELECT count(msg_id) as num FROM sdb_message WHERE unread=\"0\" and folder=\"inbox\" and to_type=".$aTemp['to_type']." and to_id=".$aTemp['to_id'] );
            $aRs = $this->db->query( "SELECT unreadmsg FROM sdb_members WHERE member_id=".$aTemp['to_id'] );
            $sSql = $this->db->getUpdateSql( $aRs, array(
                "unreadmsg" => $msgNum['num']
            ) );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
        }
        return $aTemp;
    }

    public function getMsgInfo( $nMsgId, $status = "send" )
    {
        $aRs = $this->db->selectrow( "SELECT * FROM sdb_message WHERE msg_id=".$nMsgId );
        if ( $aRs )
        {
            if ( $status == "send" )
            {
                $aRs['msg_to'] = $aRs['to_type'] ? __( "管理员" ) : $this->getMemUNameById( $aRs['to_id'] );
            }
            else
            {
                $aRs['msg_to'] = $aRs['from_type'] ? __( "管理员" ) : $this->getMemUNameById( $aRs['from_id'] );
            }
        }
        return $aRs;
    }

    public function delInBoxMsg( $aMsgId )
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

    public function delTrackMsg( $aMsgId )
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

    public function delOutBoxMsg( $aMsgId )
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

    public function getTotalMsg( $nMId )
    {
        $aRs1 = $this->db->select( "SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.date_line, s.is_sec, s.unread,m.uname, o.username\n                                                  FROM sdb_message s\n                                                  LEFT JOIN sdb_members m ON s.to_id = m.member_id\n                                                   LEFT JOIN sdb_operators o ON s.to_id = o.op_id\n                                                   WHERE s.from_id=".$nMId." AND from_type=0 and s.disabled=\"false\" and s.del_status!='2'\n                                                  ORDER BY s.msg_id" );
        $aRs3 = $this->db->select( "SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.date_line, s.is_sec, s.unread,m.uname, o.username\n                                                  FROM sdb_message s\n                                                  LEFT JOIN sdb_members m ON s.to_id = m.member_id\n                                                   LEFT JOIN sdb_operators o ON s.to_id = o.op_id\n                                                   WHERE s.to_id=".$nMId." AND to_type=0 and s.disabled=\"false\" and s.del_status!='1'\n                                                  ORDER BY s.msg_id" );
        $aRs = array_merge( $aRs1, $aRs3 );
        $TotalMsg = count( $aRs );
        return $TotalMsg;
    }

    public function getMsgListByMemId( $nMId )
    {
        $aRs1 = $this->db->select( "SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.date_line, s.is_sec, s.unread,m.uname, o.username\n                                                  FROM sdb_message s\n                                                  LEFT JOIN sdb_members m ON s.to_id = m.member_id\n                                                   LEFT JOIN sdb_operators o ON s.to_id = o.op_id\n                                                   WHERE s.from_id=".$nMId." AND from_type=0 and s.disabled=\"false\" and s.del_status!='2'\n                                                  ORDER BY s.msg_id" );
        $aRs3 = $this->db->select( "SELECT s.msg_id, s.msg_from, s.from_id, s.from_type, s.to_id, s.to_type, s.subject, s.date_line, s.is_sec, s.unread,m.uname, o.username\n                                                  FROM sdb_message s\n                                                  LEFT JOIN sdb_members m ON s.to_id = m.member_id\n                                                   LEFT JOIN sdb_operators o ON s.to_id = o.op_id\n                                                   WHERE s.to_id=".$nMId." AND to_type=0 and s.disabled=\"false\" and s.del_status!='1'\n                                                  ORDER BY s.msg_id" );
        $aRs = array_merge( $aRs1, $aRs3 );
        if ( $aRs )
        {
            foreach ( $aRs as $key => $val )
            {
                $aRs[$key]['msg_to'] = $val['to_type'] == 0 ? $val['uname'] : $val['username'];
            }
        }
        return $aRs;
    }

    public function getNewMessageNum( $memberid )
    {
        $aMsg = $this->db->selectrow( "SELECT count(*) AS unreadmsg FROM sdb_message WHERE to_type = 0 AND del_status != '1' AND folder = 'inbox' AND unread = '0' AND to_id =".intval( $memberid ) );
        return $aMsg['unreadmsg'];
    }

    public function getOrderMessage( $orderid )
    {
        $row = $this->db->select( "SELECT * FROM sdb_message WHERE rel_order = '".$orderid."' and disabled=\"false\"" );
        return $row;
    }

    public function toRemove( $msg_id )
    {
        $row = $this->db->selectrow( "SELECT * FROM sdb_message WHERE msg_id = ".intval( $msg_id ) );
        $data['member_id'] = $row['from_id'];
        parent::toremove( $msg_id );
        $this->modelName = "member/account";
        $this->fireEvent( "shortmessage_del", $data, $data['member_id'] );
    }

}

?>
