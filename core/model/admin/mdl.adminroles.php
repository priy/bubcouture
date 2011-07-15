<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "shopObject.php" );
class mdl_adminroles extends shopobject
{

    var $idColumn = "role_id";
    var $textColumn = "role_name";
    var $defaultCols = "role_name,role_memo";
    var $adminCtl = "trading/delivery_centers";
    var $defaultOrder = array
    (
        0 => "role_id",
        1 => "desc"
    );
    var $tableName = "sdb_admin_roles";

    function getallactions( )
    {
        $actions = array(
            "1" => __( "商品" ),
            "2" => __( "订单" ),
            "3" => __( "会员" ),
            "4" => __( "营销推广" ),
            "5" => __( "页面管理" ),
            "6" => __( "统计报表" ),
            "7" => __( "商店配置" ),
            "8" => __( "工具箱" )
        );
        if ( $this->system->getconf( "certificate.distribute" ) )
        {
            $actions['29'] = __( "采购中心" );
        }
        return $actions;
    }

    function rolemap( )
    {
        $map = array( "goods" => 1, "order" => 2, "member" => 3, "sale" => 4, "site" => 5, "analytics" => 6, "setting" => 7, "tools" => 8 );
        if ( $this->system->getconf( "certificate.distribute" ) )
        {
            $map['distribution'] = 29;
        }
        return $map;
    }

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 75,
                "html" => "admin/roles_cmd.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function instance( $role_id )
    {
        $role = shopobject::instance( $role_id );
        if ( $role )
        {
            $rows = $this->db->select( "select * from sdb_lnk_acts where role_id=".intval( $role_id ) );
            foreach ( $rows as $r )
            {
                $role['actions'][] = $r['action_id'];
            }
        }
        return $role;
    }

    function update( $data, $filter )
    {
        $c = shopobject::update( $data, $filter );
        if ( $filter['role_id'] )
        {
            $role_id = array( );
            foreach ( $this->getlist( "role_id", $filter ) as $r )
            {
                $role_id[] = $r['role_id'];
            }
        }
        else
        {
            $role_id = $filter['role_id'];
        }
        if ( count( $role_id ) == 1 )
        {
            $rows = $this->db->select( "select action_id from sdb_lnk_acts where role_id in (".implode( ",", $role_id ).")" );
            $in_db = array( );
            foreach ( $rows as $r )
            {
                $in_db[] = $r['action_id'];
            }
            $data['actions'] = $data['actions'] ? $data['actions'] : array( );
            $to_add = array_diff( $data['actions'], $in_db );
            $to_del = array_diff( $in_db, $data['actions'] );
            if ( 0 < count( $to_add ) )
            {
                $sql = "INSERT INTO `sdb_lnk_acts` (`role_id`,`action_id`) VALUES ";
                foreach ( $to_add as $action_id )
                {
                    $actions[] = "(".$role_id[0].",{$action_id})";
                }
                $sql .= implode( $actions, "," ).";";
                $a = $this->db->exec( $sql );
            }
            if ( 0 < count( $to_del ) )
            {
                $this->db->exec( "delete from sdb_lnk_acts where action_id in (".implode( ",", $to_del ).") and role_id=".intval( $role_id[0] ) );
            }
        }
        return $c;
    }

    function insert( $data )
    {
        $role_id = shopobject::insert( $data );
        if ( $role_id && is_array( $data['actions'] ) )
        {
            $sql = "INSERT INTO `sdb_lnk_acts` (`role_id`,`action_id`) VALUES ";
            foreach ( $data['actions'] as $action_id )
            {
                $actions[] = "(".$role_id.",{$action_id})";
            }
            $sql .= implode( $actions, "," ).";";
            $a = $this->db->exec( $sql );
        }
        return $role_id;
    }

}

?>
