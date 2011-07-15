<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_region( $params, $ctl )
{
    $SYSTEM =& $system;
    $loc =& $SYSTEM->loadmodel( "system/local" );
    if ( $params['required'] == "true" )
    {
        $req = " vtype=\"area\"";
    }
    else
    {
        $req = " vtype=".$params['vtype'];
    }
    if ( !$params['value'] )
    {
        $package = $params['package'] ? $params['package'] : $SYSTEM->getconf( "system.location" );
        return "<span package=\"".$package."\" class=\"span _x_ipt\"".$req."><input ".( $params['id'] ? " id=\"".$params['id']."\"  " : "" )." type=\"hidden\" name=\"".$params['name']."\" />".$loc->get_area_select( null, $params )."</span>";
    }
    list( $package, $regions, $region_id ) = explode( ":", $params['value'] );
    if ( !is_numeric( $region_id ) )
    {
        if ( !$package )
        {
            $package = $SYSTEM->getconf( "system.location" );
        }
        return "<span package=\"".$package."\" class=\"span _x_ipt\"".$req."><input type=\"hidden\" name=\"".$params['name']."\" />".$loc->get_area_select( null, $params )."</span>";
    }
    $arr_regions = array( );
    $ret = "";
    while ( $region_id && ( $region = $loc->instance( $region_id, "region_id,local_name,p_region_id" ) ) )
    {
        array_unshift( $arr_regions, $region );
        if ( $region_id = $region['p_region_id'] )
        {
            $notice = "-";
            $data = $loc->get_area_select( $region['p_region_id'], $params, $region['region_id'] );
            if ( !$data )
            {
                $notice = "";
            }
            $ret = "<span class=\"x-region-child\">&nbsp;".$notice."&nbsp".$loc->get_area_select( $region['p_region_id'], $params, $region['region_id'] ).$ret."</span>";
        }
        else
        {
            $ret = "<span package=\"".$package."\" class=\"span _x_ipt\"".$req."><input type=\"hidden\" value=\"".$params['value']."\" name=\"".$params['name']."\" />".$loc->get_area_select( null, $params, $region['region_id'] ).$ret."</span>";
        }
    }
    if ( !$ret )
    {
        $ret = "<span package=\"".$package."\" class=\"span _x_ipt\"".$req."><input type=\"hidden\" value=\"\" name=\"".$params['name']."\" />".$loc->get_area_select( null, $params, $region['region_id'] )."</span>";
    }
    return $ret;
}

?>
