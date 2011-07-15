<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_delivery extends shop_api_object
{

    public function getDlTypeByArea( $areaid, $weight = 0, $method_id = NULL )
    {
        if ( $method_id )
        {
            $where = " and t.dt_id = ".intval( $method_id );
        }
        if ( substr( $areaid, 0, 8 ) == "mainland" )
        {
            $aTmp = explode( ":", $areaid );
            $areaid = $aTmp[2];
        }
        $rsall = array( );
        $rs1 = $this->db->select( "SELECT t.dt_id,t.dt_name, t.protect, t.detail ,a.config AS dt_config, t.minprice,t.protect_rate,a.expressions, a.has_cod AS pad, t.ordernum\n        FROM sdb_dly_type t INNER JOIN sdb_dly_h_area a ON t.dt_id = a.dt_id \n        WHERE t.disabled = 'false' AND t.dt_status = 1 AND a.areaid_group like '%,".intval( $areaid ).",%' ".$where." ORDER BY t.ordernum ASC , a.dha_id ASC" );
        foreach ( $rs1 as $val1 )
        {
            if ( !$rsall[$val1['dt_id']] )
            {
                $rsall[$val1['dt_id']] = $val1;
            }
        }
        $rs2 = $this->db->select( "SELECT t.dt_id,t.dt_name, t.has_cod AS pad, t.protect, t.dt_config,\n                            t.dt_expressions AS expressions ,t.detail,t.minprice,t.protect_rate, t.ordernum \n                            FROM sdb_dly_type t  WHERE t.disabled = 'false' AND t.dt_status = 1  \n                            AND ( dt_config LIKE '%\"setting\";s:11:\"setting_hda\"%' \n                            OR ( dt_config LIKE '%\"defAreaFee\";i:1%'  AND dt_config LIKE '%\"setting\";s:11:\"setting_sda\"%') ) ".( $rsall ? " AND t.dt_id NOT IN ( ".implode( ",", array_keys( $rsall ) )." ) " : "" ).$where." ORDER BY t.ordernum" );
        foreach ( $rs2 as $val2 )
        {
            $rsall[$val2['dt_id']] = $val2;
        }
        $rsall1 = array( );
        foreach ( $rsall as $rsv )
        {
            $rsall1[$rsv['ordernum']][] = $rsv;
        }
        ksort( $rsall1 );
        $rs = array( );
        foreach ( $rsall1 as $rsorderv )
        {
            foreach ( $rsorderv as $rsallv )
            {
                $rs[] = $rsallv;
            }
        }
        return $rs;
    }

    public function cal_fee( $exp, $weight, $totalmoney, $defPrice = 0 )
    {
        if ( trim( $exp ) )
        {
            $dprice = 0;
            $weight += 0;
            $totalmoney += 0;
            $str = str_replace( "[", "getceil(", $exp );
            $str = str_replace( "]", ")", $str );
            $str = str_replace( "{", "getval(", $str );
            $str = str_replace( "}", ")", $str );
            $str = str_replace( "w", $weight, $str );
            $str = str_replace( "W", $weight, $str );
            $str = str_replace( "p", $totalmoney, $str );
            $str = str_replace( "P", $totalmoney, $str );
            eval( "\$dprice = {$str};" );
            if ( $dprice === "failed" )
            {
                return $defPrice;
            }
            else
            {
                return $dprice;
            }
        }
        else
        {
            return $defPrice;
        }
    }

}

?>
