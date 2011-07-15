<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_refund extends shopObject
{

    public $adminCtl = "order/refund";
    public $idColumn = "refund_id";
    public $textColumn = "refund_id";
    public $defaultCols = "refund_id,money,currency,order_id,paymethod,account,bank,pay_account,status,t_sent";
    public $defaultOrder = array
    (
        0 => "refund_id",
        1 => "desc"
    );
    public $tableName = "sdb_refunds";

    public function getColumns( )
    {
        $ret = parent::getcolumns( );
        $ret['money']['default'] = "";
        return $ret;
    }

    public function getFilter( $p )
    {
        $oPayment =& $this->system->loadModel( "trading/payment" );
        $return['payment'] = $oPayment->getMethods( );
        return $return;
    }

    public function detail( $nRefundID )
    {
        return $this->db->selectrow( "select * from sdb_refunds where refund_id=".$nRefundID );
    }

    public function edit( $aDetail )
    {
        $rRefund = $this->db->query( "select * from sdb_refunds where refund_id=".$aDetail['refund_id'] );
        unset( $aDetail['refund_id'] );
        $sSQL = $this->db->GetUpdateSQL( $rRefund, $aDetail );
        if ( !$sSQL || $this->db->exec( $sSQL ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getOrderBillList( $orderid )
    {
        return $this->db->select( "SELECT * FROM sdb_refunds WHERE order_id = ".$orderid );
    }

    public function searchOptions( )
    {
        $arr = parent::searchoptions( );
        return array_merge( $arr, array(
            "uname" => __( "会员用户名" ),
            "username" => __( "操作员" )
        ) );
    }

    public function _filter( $filter )
    {
        $where = array( 1 );
        if ( isset( $filter['refund_id'] ) )
        {
            if ( is_array( $filter['refund_id'] ) )
            {
                if ( $filter['refund_id'][0] != "_ALL_" )
                {
                    if ( !isset( $filter['refund_id'][1] ) )
                    {
                        $where[] = "refund_id = ".$this->db->quote( $filter['refund_id'][0] )."";
                    }
                    else
                    {
                        $aOrder = array( );
                        foreach ( $filter['refund_id'] as $refund_id )
                        {
                            $aOrder[] = "refund_id=".$this->db->quote( $refund_id )."";
                        }
                        $where[] = "(".implode( " OR ", $aOrder ).")";
                        unset( $aOrder );
                    }
                }
            }
            else
            {
                $where[] = "refund_id = ".$this->db->quote( $filter['refund_id'] )."";
            }
            unset( $filter['refund_id'] );
        }
        if ( array_key_exists( "uname", $filter ) && trim( $filter['uname'] ) != "" )
        {
            $user_data = $this->db->select( "select member_id from sdb_members where uname = '".addslashes( $filter['uname'] )."'" );
            foreach ( $user_data as $tmp_user )
            {
                $now_user[] = $tmp_user['member_id'];
            }
            $where[] = "member_id IN ('".implode( "','", $now_user )."')";
            unset( $filter['uname'] );
        }
        else if ( isset( $filter['uname'] ) )
        {
            unset( $filter['uname'] );
        }
        if ( isset( $filter['username'] ) && trim( $filter['username'] ) )
        {
            $op_data = $this->db->select( "select op_id from sdb_operators where username = '".addslashes( $filter['username'] )."'" );
            foreach ( $op_data as $tmp_op )
            {
                $now_op[] = $tmp_op['op_id'];
            }
            $where[] = "send_op_id IN ('".implode( "','", $now_op )."')";
            unset( $filter['username'] );
        }
        else if ( isset( $filter['username'] ) )
        {
            unset( $filter['username'] );
        }
        return parent::_filter( $filter )." and ".implode( " AND ", $where );
    }

    public function gen_id( )
    {
        $i = rand( 0, 9999 );
        do
        {
            if ( 9999 == $i )
            {
                $i = 0;
            }
            ++$i;
            $refund_id = time( ).str_pad( $i, 4, "0", STR_PAD_LEFT );
            $row = $this->db->selectrow( "select refund_id from sdb_refunds where refund_id ='".$refund_id."'" );
        } while ( $row );
        return $refund_id;
    }

    public function create( $data )
    {
        $data['refund_id'] = $this->gen_id( );
        $data['t_ready'] = time( );
        $data['t_sent'] = time( );
        $data['ip'] = remote_addr( );
        if ( $payCfg = $this->db->selectrow( "SELECT pay_type,fee,custom_name FROM sdb_payment_cfg WHERE id=".intval( $data['payment'] ) ) )
        {
            $data['paycost'] = $payCfg['fee'] * $data['money'];
            $data['paymethod'] = $payCfg['custom_name'];
        }
        $rs = $this->db->query( "select * from sdb_refunds where 0=1" );
        $sql = $this->db->getInsertSQL( $rs, $data );
        if ( $this->db->exec( $sql ) )
        {
            return $data['refund_id'];
        }
        else
        {
            return false;
        }
    }

}

?>
