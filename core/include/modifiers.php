<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function modifier_tag( &$rows )
{
    foreach ( $rows as $r )
    {
        $rows[$r] = null;
        if ( is_array( $this->tags[$r] ) )
        {
            foreach ( $this->tags[$r] as $t )
            {
                $rows[$r] .= "<b class=\"tag\">".$t."</b>";
            }
        }
    }
    unset( $this->'tags' );
}

function type_modifier_gender( &$rows )
{
    $gender = array(
        "0" => __( "女" ),
        "1" => __( "男" )
    );
    foreach ( $rows as $i => $v )
    {
        $rows[$i] = $gender[$v];
    }
}

function type_modifier_region( &$rows )
{
    foreach ( $rows as $i => $r )
    {
        list( $pkg, $regions, $region_id ) = explode( ":", $r );
        if ( is_numeric( $region_id ) )
        {
            $rows[$i] = str_replace( "/", "-", $regions );
        }
    }
}

function type_modifier_date( &$rows, $options = null )
{
    foreach ( $rows as $i => $date )
    {
        if ( $date )
        {
            $date += ( $user_timezone - SERVER_TIMEZONE ) * 3600;
            $rows[$i] = $date ? date( "Y-m-d", $date ) : "";
        }
    }
}

function type_modifier_time( &$rows, $options = null )
{
    foreach ( $rows as $i => $date )
    {
        if ( $date )
        {
            $date += ( $user_timezone - SERVER_TIMEZONE ) * 3600;
            $rows[$i] = $date ? date( "Y-m-d H:i:s", $date ) : "";
        }
    }
}

function type_modifier_money( &$rows, $options = null )
{
    $system =& $system;
    $oCur =& $system->loadmodel( "system/cur" );
    $aCur = $oCur->getformat( );
    $oMath =& $system->loadmodel( "system/math" );
    foreach ( $rows as $i => $money )
    {
        $aTmp = explode( "-", $money );
        if ( 1 < count( $aTmp ) )
        {
            $aCur = $oCur->getformat( $aTmp[1] );
        }
        $rows[$i] = $aCur['sign'].number_format( $oMath->getoperationnumber( $money ), $aCur['decimals'], $aCur['dec_point'], $aCur['thousands_sep'] );
    }
}

function type_modifier_intbool( &$rows, $options = null )
{
    $aBool = array(
        "0" => __( "否" ),
        "1" => __( "是" )
    );
    foreach ( $rows as $i => $v )
    {
        $rows[$i] = $aBool[$v];
    }
}

function type_modifier_tinybool( &$rows, $options = null )
{
    $aBool = array(
        "N" => __( "否" ),
        "Y" => __( "是" )
    );
    foreach ( $rows as $i => $v )
    {
        $rows[$i] = $aBool[$v];
    }
}

function type_modifier_bool( &$rows, $options = null )
{
    $aBool = array(
        "false" => __( "否" ),
        "true" => __( "是" )
    );
    foreach ( $rows as $i => $v )
    {
        $rows[$i] = $aBool[$v];
    }
}

function type_modifier_enum( &$rows, $options = null )
{
    $options = $options['options'];
    foreach ( $rows as $i => $v )
    {
        $rows[$i] = $options[$v];
    }
}

function type_modifier_object( &$rows, $options = null )
{
    if ( !isset( $options[0] ) )
    {
        trigger_error( "Undefined object params", E_USER_WARNING );
    }
    else if ( $options[0] )
    {
        $o =& $this->system->loadmodel( $options[0] );
        $col = isset( $options[2] ) ? $options[2] : $o->textColumn;
        $aList = $o->getlist( $o->idColumn.",".$col, array(
            $o->idColumn => array_keys( array_flip( $rows ) )
        ), 0, -1 );
        foreach ( $rows as $k => $v )
        {
            $rows[$k] = "-";
        }
        foreach ( $aList as $r )
        {
            if ( $r[$col] )
            {
                $rows[$r[$o->idColumn]] = $r[$col];
            }
        }
        $aList = null;
    }
}

?>
