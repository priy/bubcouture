<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_status extends modelfactory
{

    function getlist( )
    {
        $return = array( );
        foreach ( $this->db->select( "select * from sdb_status where date_affect=\"0000-00-00\"" ) as $row )
        {
            $return[$row['status_key']] = $row['status_value'];
        }
        return $return;
    }

    function add( $key, $value = 1, $skip_history = false )
    {
        $key = strtoupper( $key );
        if ( !$skip_history )
        {
            $this->_add_value( $key, date( "Y-m-d" ), $value );
        }
        $this->_add_value( $key, "0000-00-00", $value );
        return true;
    }

    function _add_value( $key, $date, $value )
    {
        if ( false !== $this->get( $key, $date ) )
        {
            $this->db->exec( "update sdb_status set status_value=status_value+".floatval( $value )." where status_key=".$this->db->quote( $key )." and date_affect=".$this->db->quote( $date ) );
        }
        else
        {
            $this->set( $key, $this->get( $key, $date ) + $value, $date );
        }
    }

    function set( $key, $value, $date = "0000-00-00" )
    {
        $key = strtoupper( $key );
        $rs = $this->db->exec( "select * from sdb_status where status_key='".$key."' and date_affect=\"".$date."\"" );
        $sql = $this->db->getupdatesql( $rs, array(
            "status_key" => $key,
            "status_value" => $value,
            "last_update" => time( ),
            "date_affect" => $date
        ), true );
        return $this->db->exec( $sql );
    }

    function get( $key, $date = "0000-00-00" )
    {
        $key = strtoupper( $key );
        if ( $row = $this->db->selectrow( "select status_value from sdb_status where status_key='".$key."' and date_affect=\"".$date."\"" ) )
        {
            return $row['status_value'];
        }
        return false;
    }

    function update( $force_count = false )
    {
        $in_lib = $this->getlist( );
        foreach ( get_class_methods( $this ) as $func )
        {
            if ( !( substr( $func, 0, 6 ) == "count_" ) && !$force_count || isset( $in_lib[strtoupper( substr( $func, 6 ) )] ) )
            {
                $this->$func( );
            }
        }
    }

    function _update_count( $func, $count )
    {
        return $this->set( substr( $func, 6 ), $count );
    }

    function count_gnotify( )
    {
        $oNotify =& $this->system->loadmodel( "goods/goodsNotify" );
        $filter['status'] = "ready";
        $filter['disabled'] = "false";
        return $this->_update_count( "count_gnotify", $oNotify->count( $filter ) );
    }

    function count_galert( )
    {
        $r = $this->db->selectrow( "SELECT count(distinct(goods_id)) as c FROM sdb_products where store<=".intval( $this->system->getconf( "system.product.alert.num" ) ) );
        return $this->_update_count( "count_galert", $r['c'] );
    }

    function count_gdiscuss( )
    {
        $oDiscuss =& $this->system->loadmodel( "comment/discuss" );
        return $this->_update_count( "count_gdiscuss", $oDiscuss->count( array( "adm_read_status" => "false" ) ) );
    }

    function count_gask( )
    {
        $oGask =& $this->system->loadmodel( "comment/gask" );
        return $this->_update_count( "count_gask", $oGask->count( array( "adm_read_status" => "false", "disabled" => "false" ) ) );
    }

    function count_messages( )
    {
        $oBBS =& $this->system->loadmodel( "resources/shopbbs" );
        return $this->_update_count( "count_messages", $oBBS->count( array( "unread" => 0 ) ) );
    }

    function count_order_new( )
    {
        $oOrder =& $this->system->loadmodel( "trading/order" );
        $filter['status'] = "active";
        $filter['pay_status'] = array( "0" );
        $filter['ship_status'] = array( "0" );
        $filter['disabled'] = "false";
        $filter['confirm'] = "N";
        return $this->_update_count( "count_order_new", $oOrder->count( $filter ) );
    }

    function count_order_to_pay( )
    {
        $sales =& $this->system->loadmodel( "utility/salescount" );
        $count = $sales->orderwithoutpay( );
        return $this->_update_count( "count_order_to_pay", $count );
    }

    function count_order_to_dly( )
    {
        $sales =& $this->system->loadmodel( "utility/salescount" );
        $count = $sales->playwithoutdeliever( );
        return $this->_update_count( "count_order_to_dly", $count );
    }

    function count_goods_online( )
    {
        $oGoods =& $this->system->loadmodel( "goods/products" );
        $count = $oGoods->getmarketgoods( "true" );
        return $this->_update_count( "count_goods_online", $count );
    }

    function count_goods_hidden( )
    {
        $oGoods =& $this->system->loadmodel( "goods/products" );
        $count = $oGoods->getmarketgoods( "false" );
        return $this->_update_count( "count_goods_hidden", $count );
    }

}

?>
