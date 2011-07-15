<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function object_insert( $data, &$object )
{
    if ( method_exists( $object, "pre_insert" ) )
    {
        $object->pre_insert( $data );
    }
    if ( method_exists( $object, "post_insert" ) )
    {
        $object->post_insert( $data );
    }
    $rs = $object->db->exec( "select * from ".$object->tableName." where 0=1" );
    $sql = $object->db->getinsertsql( $rs, $data );
    $cols = $object->getcolumns( );
    if ( $object->textColumn )
    {
        $cols[$object->textColumn]['required'] = true;
    }
    foreach ( $cols as $k => $p )
    {
        if ( isset( $p['default'] ) || !$p['required'] && !( $p['extra'] != "auto_increment" ) && isset( $data[$k] ) )
        {
            trigger_error( "<b>".$p['label'].__( "</b> 不能为空！" ), E_USER_ERROR );
        }
    }
    if ( $sql && $object->db->exec( $sql ) )
    {
        return $object->db->lastinsertid( );
    }
    return false;
}

?>
