<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( dirname( __FILE__ )."/mdl.delivery.php" );
class mdl_shipping extends mdl_delivery
{

    public $idColumn = "delivery_id";
    public $adminCtl = "order/shipping";
    public $textColumn = "delivery_id";
    public $defaultCols = "delivery_id,order_id,t_begin,member_id,money,is_protect,ship_name,delivery,logi_name,logi_no";
    public $defaultOrder = array
    (
        0 => "t_begin",
        1 => "DESC"
    );
    public $tableName = "sdb_delivery";

    public function getFilter( $p )
    {
        return $return;
    }

    public function getColumns( )
    {
        $data = parent::getcolumns( );
        unset( $data['_cmd'] );
        return $data;
    }

    public function getOrdersByLogino( $logino )
    {
        $logino = addslashes( $logino );
        $aRet = $this->db->select( "SELECT order_id FROM sdb_delivery WHERE logi_no = '{$logino}'" );
        $aOrder = array( 0 );
        foreach ( $aRet as $row )
        {
            $aOrder[] = $row['order_id'];
        }
        return $aOrder;
    }

    public function searchOptions( )
    {
        $arr = parent::searchoptions( );
        return array_merge( $arr, array(
            "uname" => __( "会员用户名" )
        ) );
    }

    public function _filter( $filter )
    {
        $filter['type'] = "delivery";
        $where = array( 1 );
        if ( isset( $filter['delivery_id'] ) )
        {
            if ( is_array( $filter['delivery_id'] ) )
            {
                if ( $filter['delivery_id'][0] != "_ALL_" )
                {
                    if ( !isset( $filter['delivery_id'][1] ) )
                    {
                        $where[] = "delivery_id = ".$this->db->quote( $filter['delivery_id'][0] )."";
                    }
                    else
                    {
                        $aOrder = array( );
                        foreach ( $filter['delivery_id'] as $delivery_id )
                        {
                            $aOrder[] = "delivery_id=".$this->db->quote( $delivery_id )."";
                        }
                        $where[] = "(".implode( " OR ", $aOrder ).")";
                        unset( $aOrder );
                    }
                }
            }
            else
            {
                $where[] = "delivery_id = ".$this->db->quote( $filter['delivery_id'] )."";
            }
            unset( $filter['delivery_id'] );
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
        return parent::_filter( $filter )." and ".implode( " AND ", $where );
    }

    public function getPlugins( )
    {
        $dir = PLUGIN_DIR."/shipping/";
        if ( $handle = opendir( $dir ) )
        {
            while ( false !== ( $file = readdir( $handle ) ) )
            {
                if ( is_file( $dir."/".$file ) && substr( $file, 0, 5 ) == "ship." )
                {
                    include_once( $dir."/".$file );
                    $payName = substr( $file, 5, -4 );
                    $class_name = "ship_".$payName;
                    $o = new $class_name( );
                    $return[$payName] = get_object_vars( $o );
                }
            }
            closedir( $handle );
        }
        return $return;
    }

    public function toRemove( $id )
    {
        $sqlString = "DELETE FROM sdb_delivery WHERE delivery_id='".$id."'";
        $this->db->exec( $sqlString );
        $sqlString = "DELETE FROM sdb_delivery_item WHERE delivery_id='".$id."'";
        $this->db->exec( $sqlString );
    }

}

?>
