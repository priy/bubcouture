<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_adminprofile extends modelfactory
{

    function load( $opid = null )
    {
        if ( !is_null( $opid ) )
        {
            $this->opid = $opid;
        }
        else
        {
            $opid = $this->opid;
        }
        $aCfg = $this->db->selectrow( "SELECT username,name,super FROM sdb_operators WHERE op_id=".intval( $this->opid ) );
        $this->loginName = $aCfg['username'];
        $this->name = $aCfg['name'];
        $this->is_super = $aCfg['super'];
    }

    function setting( )
    {
        return $this->_setting;
    }

    function getmenu( $part = null, $is_supper = false )
    {
        include( "adminSchema.php" );
        $role =& $this->system->loadmodel( "admin/adminroles" );
        $opt = $role->rolemap( );
        $op =& $this->system->loadmodel( "admin/operator" );
        if ( $part )
        {
            if ( !$is_supper )
            {
                foreach ( $menu[$part]['items'] as $k => $v )
                {
                    if ( $v['super_only'] )
                    {
                        unset( $this->items->$k );
                    }
                }
            }
            return $menu[$part]['items'];
        }
        if ( !$this->is_super )
        {
            $allow_wground = $op->getactions( $this->opid );
            foreach ( $menu as $k => $v )
            {
                if ( !isset( $allow_wground[$opt[$k]] ) )
                {
                    unset( $menu->$k );
                }
            }
        }
        return $menu;
    }

    function get( $string )
    {
        if ( !isset( $this->_setting ) )
        {
            $row = $this->db->selectrow( "SELECT config FROM sdb_operators WHERE op_id=".intval( $this->opid ) );
            $this->_setting = unserialize( $row['config'] );
        }
        return $this->_setting[$string];
    }

    function set( $key, $value = null )
    {
        define( "Setting_Modified", true );
        if ( is_array( $key ) && !$value )
        {
            $this->_setting = array_merge( $this->_setting, $key );
        }
        else
        {
            $this->_setting[$key] = $value;
        }
        register_shutdown_function( array(
            $this,
            "save"
        ) );
    }

    function save( )
    {
        global $system;
        $this->system =& $system;
        $this->db =& $system->database( );
        $rs = $this->db->exec( "SELECT config FROM sdb_operators WHERE op_id=".intval( $this->opid ) );
        $sql = $this->db->getupdatesql( $rs, array(
            "config" => $this->_setting
        ) );
        return !$sql || $this->db->exec( $sql );
    }

    function __sleep( )
    {
        unset( $this->'db' );
        unset( $this->'system' );
        return array_keys( get_object_vars( $this ) );
    }

    function __wakeup( )
    {
        global $system;
        $this->system =& $system;
        $this->db =& $system->database( );
    }

}

?>
