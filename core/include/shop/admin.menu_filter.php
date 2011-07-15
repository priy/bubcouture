<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function admin_menu_filter( &$system, $part = null )
{
    require( CORE_INCLUDE_DIR."/adminSchema.php" );
    $role =& $system->loadmodel( "admin/adminroles" );
    $opt =& $role->rolemap( );
    $operator =& $system->loadmodel( "admin/operator" );
    $addons =& $system->loadmodel( "system/addons" );
    if ( !constant( "SAFE_MODE" ) )
    {
        foreach ( $addons->getlist( "plugin_name,plugin_ident", array( "plugin_type" => "app" ) ) as $r )
        {
            $app_names[$r['plugin_ident']] = $r['plugin_name'];
            if ( $app_c = $addons->load( $r['plugin_ident'], "app" ) )
            {
                $app_c->getmenu( $menu );
            }
        }
    }
    foreach ( $addons->getlist( "plugin_struct,plugin_ident,plugin_package,plugin_id", array( "plugin_type" => "admin" ) ) as $r )
    {
        $info = unserialize( $r['plugin_struct'] );
        $grpname = isset( $app_names[$r['plugin_package']] ) ? $app_names[$r['plugin_package']] : "插件";
    }
    foreach ( $menu_group as $k => $wgs )
    {
        foreach ( $wgs as $name => $group )
        {
            $menu[$k]['items'][] = array(
                "type" => "group",
                "label" => $name,
                "items" => $group
            );
        }
    }
    if ( $part )
    {
        if ( !$system->op_is_super )
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
    if ( !$system->op_is_super )
    {
        $allow_wground = $operator->getactions( $system->op_id );
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

?>
