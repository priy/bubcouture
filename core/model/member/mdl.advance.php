<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_advance extends shopobject
{

    var $idColumn = "log_id";
    var $adminCtl = "member/advance";
    var $textColumn = "memo";
    var $defaultCols = "member_id,mtime,memo,import_money,explode_money,member_advance,paymethod,message";
    var $defaultOrder = array
    (
        0 => "log_id",
        1 => "DESC"
    );
    var $orderAble = false;
    var $tableName = "sdb_advance_logs";

    function _filter( $filter )
    {
        $sdtime = "";
        if ( $filter['sdtime'] )
        {
            $sdtime = explode( "/", $filter['sdtime'] );
        }
        else
        {
            $sdtime = explode( "/", $filter['sdtimecommon'] );
        }
        if ( count( $sdtime ) == 1 )
        {
            $sdtime = explode( "%2F", $sdtime[0] );
        }
        $where = array( 1 );
        $filter['start_date'] = strtotime( $sdtime[0] );
        $filter['end_date'] = strtotime( $sdtime[1] );
        if ( $filter['start_date'] )
        {
            $where[] = " mtime >= ".$filter['start_date'];
        }
        if ( $filter['end_date'] )
        {
            $where[] = " mtime <= ".( $filter['end_date'] + 86400 );
        }
        unset( $filter->'sdtime' );
        unset( $filter->'sdtimecommon' );
        unset( $sdtime );
        return shopobject::_filter( $filter )." AND ".implode( $where, " AND " );
    }

    function tolog( $data )
    {
        if ( $rs = $this->db->query( "SELECT advance,member_id FROM sdb_members WHERE member_id=".intval( $data['member_id'] ) ) )
        {
            $sqlString = $this->db->getupdatesql( $rs, $data );
            if ( !$sqlString && $this->db->exec( $sqlString ) )
            {
                return true;
            }
            return false;
        }
        return false;
    }

    function checkaccount( $member_id, $money = 0, &$errMsg, &$rows )
    {
        if ( $rs = $this->db->exec( "SELECT advance,member_id FROM sdb_members WHERE member_id=".intval( $member_id ) ) )
        {
            $rows = $this->db->getrows( $rs, 1 );
            if ( 0 < count( $rows ) )
            {
                if ( $rows[0]['advance'] < $money )
                {
                    $errMsg .= __( "预存款帐户余额不足" );
                    return 0;
                }
                return $rows;
            }
            $errMsg .= __( "预存款帐户不存在" );
            return false;
        }
        $errMsg .= __( "查询预存款帐户失败" );
        return false;
    }

    function add( $member_id, $money, $message, &$errMsg, $payment_id = "", $order_id = "", $paymethod = "", $memo = "", $type = 0 )
    {
        if ( $rows = $this->checkaccount( $member_id, 0, $errMsg ) )
        {
            $data = array(
                "advance" => $rows[0]['advance'] + $money
            );
            if ( $data['advance'] < 0 )
            {
                $errMsg .= __( "更新预存款账户失败" );
                return false;
            }
            $member_advance = $data['advance'];
            $rs = $this->db->exec( "SELECT * FROM sdb_members WHERE member_id=".intval( $member_id ) );
            $sql = $this->db->getupdatesql( $rs, $data );
            if ( $this->db->exec( $sql ) )
            {
                $this->log( $member_id, $money, $message, $payment_id, $order_id, $paymethod, $memo, $member_advance );
                if ( !$type )
                {
                    $data['member_id'] = $member_id;
                    $this->fireevent( "member/account:changeadvance", $data, $member_id );
                }
                return true;
            }
            $errMsg .= __( "更新预存款帐户失败" );
            return false;
        }
        return false;
    }

    function deduct( $member_id, $money, $message, &$errMsg, $payment_id = "", $order_id = "", $paymethod = "", $memo = "" )
    {
        if ( $rows = $this->checkaccount( $member_id, $money, $errMsg ) )
        {
            $data = array(
                "advance" => $rows[0]['advance'] - $money
            );
            $member_advance = $data['advance'];
            $rs = $this->db->exec( "SELECT * FROM sdb_members WHERE member_id=".intval( $member_id ) );
            $sql = $this->db->getupdatesql( $rs, $data );
            if ( !$sql && $this->db->exec( $sql ) )
            {
                $this->log( $member_id, 0 - $money, $message, $payment_id, $order_id, $paymethod, $memo, $member_advance );
                return true;
            }
            $errMsg .= __( "更新预存款帐户失败" );
            return false;
        }
        return false;
    }

    function log( $member_id, $money, $message, $payment_id = "", $order_id = "", $paymethod = "", $memo = "", $member_advance = "" )
    {
        $shop_advance = $this->getshopadvance( );
        $rs = $this->db->exec( "select * from sdb_advance_logs where 0=1" );
        $sql = $this->db->getinsertsql( $rs, array(
            "member_id" => $member_id,
            "money" => $money,
            "mtime" => time( ),
            "message" => $message,
            "payment_id" => $payment_id,
            "order_id" => $order_id ? $order_id : null,
            "paymethod" => $paymethod,
            "memo" => $memo,
            "import_money" => 0 < $money ? $money : 0,
            "explode_money" => $money < 0 ? 0 - $money : 0,
            "member_advance" => $member_advance,
            "shop_advance" => $shop_advance
        ) );
        return $this->db->exec( $sql );
    }

    function getlistbymemid( $member_id )
    {
        return $this->db->select( "SELECT * FROM sdb_advance_logs WHERE member_id=".$member_id );
    }

    function getfrontadvlist( $memberId, $nPage, $perpage = PERPAGE )
    {
        return $this->db->selectpager( "SELECT * FROM sdb_advance_logs WHERE member_id=".$memberId." ORDER BY mtime DESC", $nPage, $perpage );
    }

    function getshopadvance( )
    {
        $row = $this->db->selectrow( "SELECT SUM(advance) as sum_advance FROM sdb_members" );
        return $row['sum_advance'];
    }

    function get( $member_id )
    {
        $row = $this->db->selectrow( "SELECT advance FROM sdb_members WHERE member_id=".intval( $member_id ) );
        return $row['advance'];
    }

    function getadvancestatistics( $sdate = null, $edate = null )
    {
        $sql = "SELECT COUNT(*) AS count, SUM(import_money) AS import_money, SUM(explode_money) AS explode_money FROM sdb_advance_logs ";
        $where = array( );
        if ( $sdate )
        {
            $where[] = " mtime >= ".strtotime( $sdate );
        }
        if ( $edate )
        {
            $where[] = " mtime <= ".( strtotime( $edate ) + 3600000 );
        }
        if ( !empty( $where ) )
        {
            $sql .= " WHERE ".implode( " AND ", $where );
        }
        return $this->db->selectrow( $sql );
    }

    function getmemberadvancestatistics( $mId )
    {
        $sql = "SELECT COUNT(*) AS count, SUM(import_money) AS import_money, SUM(explode_money) AS explode_money FROM sdb_advance_logs WHERE member_id = ".$mId;
        return $this->db->selectrow( $sql );
    }

    function getadvancelogbylogid( $logid )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_advance_logs WHERE log_id = ".$logid );
    }

}

?>
