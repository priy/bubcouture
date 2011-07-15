<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( dirname( __FILE__ )."/mdl.message.php" );
class mdl_shopbbs extends mdl_message
{

    public $finder_action_tpl = "member/message/finder_action.html";
    public $idColumn = "msg_id";
    public $textColumn = "msg_id";
    public $appendCols = "unread";
    public $adminCtl = "member/shopbbs";
    public $defaultCols = "msg_from,subject,message,date_line,is_sec,unread";
    public $defaultOrder = array
    (
        0 => "msg_id",
        1 => "desc"
    );
    public $tableName = "sdb_message";

    public function getColumns( )
    {
        $ret = parent::getcolumns( );
        $ret['_cmd'] = array(
            "label" => __( "操作" ),
            "width" => 70,
            "html" => "payment/finder_command.html"
        );
        $ret['msg_from']['default'] = "";
        return $ret;
    }

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

    public function searchOptions( )
    {
        return array_merge( parent::searchoptions( ), array(
            "msg_from" => __( "留言者" ),
            "keyword" => __( "留言标题" )
        ) );
    }

    public function _filter( $filter )
    {
        $filter['to_type'] = 1;
        $where[] = "folder = 'inbox'";
        $where[] = "for_id = 0";
        $where[] = "rel_order = 1";
        if ( $filter['msg_from'] )
        {
            $where[] = "msg_from ='".addslashes( $filter['msg_from'] )."'";
        }
        if ( !empty( $filter['keyword'] ) )
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
        if ( isset( $filter['unread'] ) )
        {
            $where[] = "unread ='".$filter['unread']."'";
        }
        unset( $filter['keyword'] );
        return parent::_filter( $filter )." AND ".implode( $where, " AND " );
    }

    public function getMsgReply( $msg_id )
    {
        return $this->db->select( "SELECT * FROM sdb_message WHERE for_id = ".intval( $msg_id )." ORDER BY date_line DESC" );
    }

    public function setReaded( $msg_id )
    {
        $rs = $this->db->query( "SELECT * FROM sdb_message WHERE msg_id=".intval( $msg_id ) );
        $aUpdate['unread'] = "1";
        $sql = $this->db->getUpdateSQL( $rs, $aUpdate );
        if ( $sql )
        {
            if ( $this->db->exec( $sql ) )
            {
                $status =& $this->system->loadModel( "system/status" );
                return $status->count_messages( );
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
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
        $aData['unread'] = "1";
        $aData['folder'] = "inbox";
        $aData['is_sec'] = "false";
        $aData['rel_order'] = "1";
        $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE 0" );
        $sSql = $this->db->getInsertSql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            $aMsg = $this->getFieldById( $aData['for_id'], array( "is_sec" ) );
            if ( $aMsg['is_sec'] == "true" )
            {
                $aMsg['is_sec'] = "false";
                $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE msg_id=".$aData['for_id'] );
                $sSql = $this->db->getUpdateSql( $aRs, $aMsg );
                $this->db->exec( $sSql );
            }
            $status =& $this->system->loadModel( "system/status" );
            $status->count_messages( );
            $aData['member_id'] = $aData['to_id'];
            $this->modelName = "member/account";
            $this->fireEvent( "shopmessage_reply", $aData, $aData['member_id'] );
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
        $this->db->exec( "UPDATE sdb_message SET is_sec = '".$this->db->quote( $status )."' WHERE msg_id = ".intval( $msg_id ) );
        return true;
    }

    public function toRemove( $msg_id )
    {
        $row = $this->db->selectrow( "select from_id from sdb_message where msg_id=".intval( $msg_id ) );
        if ( $this->db->exec( "DELETE FROM sdb_message WHERE msg_id = ".intval( $msg_id )." OR for_id = ".intval( $msg_id ) ) )
        {
            $this->modelName = "member/account";
            $data['member_id'] = $row['from_id'];
            $this->fireEvent( "shopmessage_del", $data, $row['from_id'] );
        }
    }

    public function removeSendBox( $sd_id )
    {
        return $this->db->exec( "DELETE FROM sdb_sendbox WHERE out_id = ".intval( $sd_id ) );
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

    public function getFrontMsg( $nPage )
    {
        return $this->db->selectPager( "SELECT * FROM sdb_message\n                WHERE  to_type=1 and is_sec=\"false\" and for_id=0 and del_status='0' and disabled='false'\n                ORDER BY date_line DESC", $nPage, PAGELIMIT );
    }

    public function getFrontMsgReply( $aID )
    {
        return $this->db->select( "select * from sdb_message where for_id in('".implode( "','", $aID )."') AND is_sec='false' AND del_status='0' ORDER BY date_line DESC" );
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

    public function sendMsg( $from, $to, $meessage, $options = false )
    {
        $aData['from_id'] = $from;
        $aData['to_id'] = $to;
        $aData['from_type'] = isset( $options['from_type'] ) ? $options['from_type'] : 0;
        $aData['msg_from'] = $aData['from_type'] ? isset( $options['msg_from'] ) ? $options['msg_from'] : $this->getOpNameById( $from ) : isset( $options['msg_from'] ) ? $options['msg_from'] : $this->getMemUNameById( $from );
        $aData['to_type'] = 1;
        $aData['rel_order'] = 1;
        $aData['subject'] = isset( $options['subject'] ) ? $options['subject'] : __( "无标题" );
        $aData['message'] = $meessage;
        $aData['unread'] = "0";
        $aData['is_sec'] = isset( $options['is_sec'] ) && $options['is_sec'] != "" ? $options['is_sec'] : "true";
        $aData['folder'] = isset( $options['folder'] ) ? $options['folder'] : "inbox";
        $aData['date_line'] = time( );
        $aData['email'] = $options['email'] ? $options['email'] : "";
        $aData['msg_ip'] = $options['msg_ip'] ? $options['msg_ip'] : "";
        $aRs = $this->db->query( "SELECT * FROM sdb_message WHERE 0" );
        $sSql = $this->db->GetInsertSql( $aRs, $aData );
        if ( !$sSql || $this->db->exec( $sSql ) )
        {
            $aData['member_id'] = $aData['from_id'];
            $this->modelName = "member/account";
            $this->fireEvent( "shopmessage_new", $aData, $aData['member_id'] );
            if ( $options['folder'] == "inbox" )
            {
                $msgNun = $this->db->selectrow( "SELECT unreadmsg FROM sdb_members WHERE member_id=".$to );
                $aRs = $this->db->query( "SELECT unreadmsg FROM sdb_members WHERE member_id=".$to );
                $sSql = $this->db->getUpdateSql( $aRs, array(
                    "unreadmsg" => $msgNun['unreadmsg'] + 1
                ) );
                if ( $sSql )
                {
                    $this->db->exec( $sSql );
                }
            }
            $status =& $this->system->loadModel( "system/status" );
            $status->count_messages( );
            return true;
        }
        return false;
    }

    public function getMsgById( $nMsgId )
    {
        $aTemp = $this->db->selectrow( "SELECT to_id,to_type, subject, message, unread, is_sec, folder\n                                            FROM sdb_message\n                                            WHERE msg_id=".$nMsgId );
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
        $aRow = $this->db->selectrow( "SELECT COUNT(msg_id) AS num FROM sdb_message WHERE from_id=".$nMId." OR to_id=".$nMId );
        return $aRow['num'];
    }

    public function getMsgListByMemId( $nMId )
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

    public function getNewOrderMessage( $get_result = false )
    {
        if ( $get_result )
        {
            $aRs = $this->db->select( "select rel_order from sdb_message as msg,sdb_orders as d_order where d_order.order_id=msg.rel_order and d_order.disabled=\"false\" and msg.unread=\"0\" and msg.rel_order!=\"0\" and msg.disabled=\"false\"" );
            $result = array( );
            foreach ( $aRs as $key => $value )
            {
                $result[] = $aRs[$key]['rel_order'];
            }
            return $result;
        }
        else
        {
            $aRs = $this->db->selectrow( "select count(*) as msg_num from sdb_message as msg,sdb_orders as d_order where d_order.order_id=msg.rel_order and d_order.disabled=\"false\" and msg.unread=\"0\" and msg.rel_order!=\"0\" and msg.disabled=\"false\"" );
            return $aRs['msg_num'];
        }
    }

    public function modifier_is_sec( &$rows, $options = null )
    {
        $aBool = array(
            "false" => __( "是" ),
            "true" => __( "否" )
        );
        foreach ( $rows as $i => $v )
        {
            $rows[$i] = $aBool[$v];
        }
    }

    public function getNewMessageNum( $memberid )
    {
        $aMsg = $this->db->selectrow( "SELECT count(*) AS unreadmsg FROM sdb_message WHERE to_type = 0 AND del_status != '1' AND folder = 'inbox' AND unread = '0' AND to_id =".intval( $memberid ) );
        return $aMsg['unreadmsg'];
    }

    public function getOrderMessage( $orderid )
    {
        $row = $this->db->select( "SELECT * FROM sdb_message WHERE rel_order = '".$orderid."'" );
        return $row;
    }

}

?>
