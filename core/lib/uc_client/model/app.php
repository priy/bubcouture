<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class appmodel
{

    public $db = NULL;
    public $base = NULL;

    public function appmodel( &$base )
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    public function get_apps( $col = "*", $where = "" )
    {
        $arr = $this->db->fetch_all( "SELECT {$col} FROM ".UC_DBTABLEPRE."applications".( $where ? " WHERE ".$where : "" ) );
        return $arr;
    }

}

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
?>
