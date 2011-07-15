<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "plugin.php" );
class mdl_local extends plugin
{

    var $plugin_type = "dir";
    var $plugin_name = "location";
    var $prefix = "local.";

    function get_area_select( $path, $params, $selected_id = null )
    {
        $params['depth'] = $params['depth'] ? $params['depth'] : 1;
        $html = ( "<select onchange=\"selectArea(this,this.value,".( $params['depth'] + 1 ) ).")\">";
        $html .= __( "<option value=\"_NULL_\">请选择...</option>" );
        $package = $params['package'] ? $params['package'] : $this->get_default( );
        $sql = "select region_id,p_region_id,local_name,region_grade from sdb_regions where p_region_id".( $path ? "=".intval( $path ) : " is null" )." and package=\"".$package."\" order by ordernum asc,region_id asc";
        if ( $rows = $this->db->select( $sql ) )
        {
            foreach ( $rows as $item )
            {
                if ( $item['region_grade'] <= $this->system->getconf( "system.area_depth" ) )
                {
                    $selected = $selected_id == $item['region_id'] ? "selected=\"selected\"" : "";
                    if ( $params['depth'] < $this->system->getconf( "system.area_depth" ) )
                    {
                        $html .= "<option has_c=\"true\" value=\"".$item['region_id']."\" ".$selected.">".$item['local_name']."</option>";
                    }
                    else
                    {
                        $html .= "<option value=\"".$item['region_id']."\" ".$selected.">".$item['local_name']."</option>";
                    }
                }
                else
                {
                    $no = true;
                }
            }
            $html .= "</select>";
            if ( $no )
            {
                $html = "";
            }
            return $html;
        }
        return false;
    }

    function instance( $region_id, $cols = "*" )
    {
        return $this->db->selectrow( "select ".$cols." from sdb_regions where region_id=".intval( $region_id ) );
    }

    function _pos( $path, &$area_map )
    {
        $path = explode( " ", trim( $path ) );
        $current_area =& $this->area_map;
        foreach ( $path as $p )
        {
            $current_area =& $current_area[$p];
        }
        return $current_area;
    }

    function get_default( )
    {
        $pkg = $this->system->getconf( "system.location" );
        if ( $this->is_installed( $pkg ) )
        {
            return $pkg;
        }
        return "mainland";
    }

    function is_installed( $pkg )
    {
        $row = $this->db->selectrow( "select count(*) as c from sdb_regions where package=\"".$pkg."\"" );
        return 0 < $row['c'];
    }

    function &load( $pkg )
    {
        $pkg =& plugin::load( $pkg );
        $pkg->db =& $this->db;
        return $pkg;
    }

    function use_package( $package )
    {
        $o = $this->load( $package );
        if ( !$this->is_installed( $package ) || !$o->install( ) )
        {
            return false;
        }
        $pkg = $this->system->setconf( "system.location", $package );
        return true;
    }

    function clearolddata( $package )
    {
        $sql = "delete from sdb_regions where package='".$package."'";
        $this->db->exec( $sql );
    }

    function getregiongrad( $pkg )
    {
        $sql = "select max(region_grade) as grade from sdb_regions where package='".$pkg."'";
        return $this->db->selectrow( $sql );
    }

}

?>
