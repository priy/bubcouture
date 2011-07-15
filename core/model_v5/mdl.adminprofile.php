<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_adminProfile extends modelFactory
{

    public function load( $opid = null )
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

    public function setting( )
    {
        return $this->_setting;
    }

    public function getMenu( $part = null, $is_supper = false )
    {
        include( "adminSchema.php" );
        $role =& $this->system->loadModel( "admin/adminroles" );
        $opt = $role->rolemap( );
        $op =& $this->system->loadModel( "admin/operator" );
        if ( $part )
        {
            if ( !$is_supper )
            {
                foreach ( $menu[$part]['items'] as $k => $v )
                {
                    if ( $v['super_only'] )
                    {
                        unset( $this->items[$k] );
                    }
                }
            }
            return $menu[$part]['items'];
        }
        else
        {
            if ( !$this->is_super )
            {
                $allow_wground = $op->getActions( $this->opid );
                foreach ( $menu as $k => $v )
                {
                    if ( !isset( $allow_wground[$opt[$k]] ) )
                    {
                        unset( $menu[$k] );
                    }
                }
            }
            return $menu;
        }
    }

    public function get( $string )
    {
        if ( !isset( $this->_setting ) )
        {
            $row = $this->db->selectrow( "SELECT config FROM sdb_operators WHERE op_id=".intval( $this->opid ) );
            $this->_setting = unserialize( $row['config'] );
        }
        return $this->_setting[$string];
    }

    public function set( $key, $value = null )
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

    public function save( )
    {
        global $system;
        $this->system =& $system;
        $this->db =& $system->database( );
        $rs = $this->db->exec( "SELECT config FROM sdb_operators WHERE op_id=".intval( $this->opid ) );
        $sql = $this->db->GetUpdateSql( $rs, array(
            "config" => $this->_setting
        ) );
        return !$sql || $this->db->exec( $sql );
    }

    public function __sleep( )
    {
        return array_keys( get_object_vars( $this ) );
    }

    public function __wakeup( )
    {
        global $system;
        $this->system =& $system;
        $this->db =& $system->database( );
    }

}

?>
