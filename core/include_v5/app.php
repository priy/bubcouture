<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class app
{

    public function app( )
    {
        $this->ident = substr( get_class( $this ), 4 );
        $this->system =& $system;
        $this->db =& $this->system->database( );
    }

    public function install( )
    {
        $schemaObj = $this->system->loadModel( "utility/schemas" );
        foreach ( $this->dbtables( ) as $k => $v )
        {
            if ( !preg_match( "/^[a-z0-9\\_]+\$/", $k ) )
            {
                trigger_error( E_ERROR, "Error Table name" );
            }
            $this->db->exec( "drop table if exists ".DB_PREFIX.$this->ident."_".$k );
            $arr =& $schemaObj->load_array( $v );
            $sql = $schemaObj->get_sql( $this->ident."_".$k, $arr );
            $this->db->exec( $sql );
        }
        return true;
    }

    public function uninstall( )
    {
        foreach ( $this->dbtables( ) as $k => $v )
        {
            $this->db->exec( $a = "drop table if exists sdb_".$this->ident."_".$k );
        }
        return true;
    }

    public function getConf( $key )
    {
        return $this->system->getConf( $key );
    }

    public function setConf( $key, $value )
    {
        return $this->system->setConf( $key, $value );
    }

    public function dbtables( )
    {
        $app_path = PLUGIN_DIR."/app/".$this->ident."/dbschema";
        if ( is_dir( $app_path ) )
        {
            $db = array( );
            $path = @opendir( $app_path );
            while ( $file = readdir( $path ) )
            {
                $schema_name = substr( $file, 0, -4 );
                if ( substr( $file, -4 ) == ".php" )
                {
                    require_once( $app_path."/".$file );
                }
            }
            return $db;
        }
        else
        {
            return array( );
        }
        return array( );
    }

    public function update( )
    {
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $diff_tables = $appmgr->get_app_diff( $this->ident );
        foreach ( $diff_tables as $key => $value )
        {
            foreach ( $value as $g => $d )
            {
                $this->db->exec( $d );
            }
        }
        return true;
    }

    public function setting( )
    {
        $setting_file = PLUGIN_DIR."/app/".$this->ident."/setting.php";
        if ( file_exists( $setting_file ) )
        {
            include( $setting_file );
        }
        return is_array( $setting ) ? $setting : array( );
    }

    public function listener( )
    {
        return array( );
    }

    public function ctl_mapper( )
    {
        return array( );
    }

    public function output_modifiers( )
    {
        return array( );
    }

    public function getMenu( &$menu )
    {
    }

    public function crontab_queue( )
    {
        return array( );
    }

    public function passport_verify( $mem )
    {
        $mem['member_refer'] = $this->member_refer;
        if ( $mem['uname'] && $mem['password'] && $mem['email'] )
        {
            $mem_data = $this->db->selectrow( "SELECT * FROM sdb_members  WHERE uname = '".$mem['uname']."'" );
            $GLOBALS['_POST']['login'] = $mem['uname'];
            $GLOBALS['_POST']['passwd'] = $mem['password'];
            $mem['password'] = md5( $mem['password'] );
            if ( $mem_data )
            {
                $mem_temp = $this->db->exec( "SELECT * FROM  sdb_members WHERE uname='".$mem['uname']."'" );
                $sql = $this->db->getUpdateSql( $mem_temp, $mem );
                if ( $sql && !$this->db->exec( $sql ) )
                {
                    echo "请联系开发者";
                    exit( );
                }
                return $mem_data;
            }
            else
            {
                $mem_level = $this->system->loadModel( "member/level" );
                if ( $mem_le_id = $mem_level->getDefauleLv( ) )
                {
                    $mem['member_lv_id'] = $mem_le_id;
                }
                $aRs = $this->db->exec( "SELECT * FROM sdb_members WHERE 1=0" );
                $sql = $this->db->getInsertSql( $aRs, $mem );
                if ( $this->db->exec( $sql ) )
                {
                    $mem['member_id'] = $this->db->lastinsertid( );
                    return $mem;
                }
                else
                {
                    echo $sql."插入错误";
                    echo "请联系开发者";
                }
            }
        }
        else
        {
            echo "请联系开发者";
        }
        exit( );
    }

}

?>
