<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class miscmodel
{

    public $db = NULL;
    public $base = NULL;

    public function miscmodel( &$base )
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    public function get_apps( $col = "*", $where = "" )
    {
        $arr = $this->db->fetch_all( "SELECT {$col} FROM ".UC_DBTABLEPRE."applications".( $where ? " WHERE ".$where : "" ) );
        return $arr;
    }

    public function delete_apps( $appids )
    {
    }

    public function update_app( $appid, $name, $url, $authkey, $charset, $dbcharset )
    {
    }

    public function alter_app_table( $appid, $operation = "ADD" )
    {
    }

    public function get_host_by_url( $url )
    {
    }

    public function check_url( $url )
    {
    }

    public function check_ip( $url )
    {
    }

    public function test_api( $url, $ip = "" )
    {
    }

    public function dfopen( $url, $limit = 0, $post = "", $cookie = "", $bysocket = FALSE, $ip = "", $timeout = 15, $block = TRUE )
    {
    }

    public function array2string( $arr )
    {
        $s = $sep = "";
        if ( $arr && is_array( $arr ) )
        {
            foreach ( $arr as $k => $v )
            {
                $s .= $sep.$k.UC_ARRAY_SEP_1.$v;
                $sep = UC_ARRAY_SEP_2;
            }
        }
        return $s;
    }

    public function string2array( $s )
    {
        $arr = explode( UC_ARRAY_SEP_2, $s );
        $arr2 = array( );
        foreach ( $arr as $k => $v )
        {
            list( $key, $val ) = explode( UC_ARRAY_SEP_1, $v );
            $arr2[$key] = $val;
        }
        return $arr2;
    }

}

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
define( "UC_ARRAY_SEP_1", "UC_ARRAY_SEP_1" );
define( "UC_ARRAY_SEP_2", "UC_ARRAY_SEP_2" );
?>
