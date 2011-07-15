<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_2_0_region extends shop_api_object
{

    public $app_error = array
    (
        "region has not info" => array
        (
            "no" => "b_region_001",
            "debug" => "",
            "level" => "warning",
            "info" => "没有相对应的配送信息",
            "desc" => ""
        )
    );

    public function getColumns( )
    {
    }

    public function search_sub_regions( $data )
    {
        $p_region_id = $data['p_region_id'];
        if ( $p_region_id == 0 )
        {
            $region_list = $this->db->select( "select region_id,local_name,region_grade from sdb_regions where disabled=\"false\" and p_region_id IS NULL" );
        }
        else
        {
            $region_list = $this->db->select( "select region_id,local_name,region_grade from sdb_regions where disabled=\"false\" and p_region_id =".$p_region_id );
        }
        foreach ( $region_list as $k => $region )
        {
            if ( !$this->db->selectrow( "SELECT region_id FROM sdb_regions WHERE disabled=\"false\" AND p_region_id=".$region['region_id'] ) )
            {
                $region['is_node'] = 0;
            }
            else
            {
                $region['is_node'] = 1;
            }
            unset( $region['region_grade'] );
            $region_list[$k] = $region;
        }
        $result['data_info'] = $region_list;
        $this->api_response( "true", FALSE, $result );
    }

    public function search_dly_type_byid( $data )
    {
        $dly_type_list = $this->_dltype_byarea( $data['area_id'] );
        if ( !$dly_type_list )
        {
            $this->add_application_error( $this->app_error['region has not info'] );
        }
        $dly_type = FALSE;
        foreach ( $dly_type_list as $dly_type_row )
        {
            if ( $dly_type_row['dt_id'] == $data['delivery_id'] )
            {
                $dly_type = $dly_type_row;
                break;
            }
        }
        if ( !$dly_type )
        {
            $this->add_application_error( "region has not info" );
        }
        $result['data_info'] = $dly_type;
        $this->api_response( "true", FALSE, $result );
    }

    public function search_dltype_byarea( $data )
    {
        $rs = $this->_dltype_byarea( $data['area_id'] );
        $result['data_info'] = $rs;
        $this->api_response( "true", FALSE, $result );
    }

    public function _dltype_byarea( $areaid )
    {
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

}

?>
