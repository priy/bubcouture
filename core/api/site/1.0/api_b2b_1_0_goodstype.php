<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_goodstype extends shop_api_object
{

    public function getColumns( )
    {
        $columns = array(
            "type_id" => array( "type" => "int" ),
            "name" => array( "type" => "string" ),
            "alias" => array( "type" => "string" ),
            "is_physical" => array( "type" => "string" ),
            "schema_id" => array( "type" => "string" ),
            "props" => array( "type" => "string" ),
            "spec" => array( "type" => "string" ),
            "setting" => array( "type" => "string" ),
            "minfo" => array( "type" => "string" ),
            "params" => array( "type" => "string" ),
            "dly_func" => array( "type" => "int" ),
            "ret_func" => array( "type" => "string" ),
            "reship" => array( "type" => "string" ),
            "disabled" => array( "type" => "string" ),
            "is_def" => array( "type" => "string" ),
            "last_modify" => array( "type" => "int" )
        );
        return $columns;
    }

    public function search_goodstype_list( $data )
    {
        $data['disabled'] = "false";
        $data['orderby'] = "type_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_goods_type where ".implode( " and ", $where ) );
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where = $this->_filter( $data );
        $data_info = $this->db->select( "select ".implode( ",", $data['columns'] )." from sdb_goods_type".$where );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function search_goodstype_brand( $data )
    {
        $data['orderby'] = "type_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_type_brand where ".implode( " and ", $where ) );
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where = $this->_filter( $data );
        $data_info = $this->db->select( "select type_id,brand_id,brand_order from sdb_type_brand".$where );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function search_goodstype_spec( $data )
    {
        $data['orderby'] = "spec_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_goods_type_spec where ".implode( " and ", $where ) );
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where = $this->_filter( $data );
        $data_info = $this->db->select( "select spec_id,type_id,spec_style from sdb_goods_type_spec".$where );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function before_filter( $filter )
    {
        $where = array( 1 );
        if ( isset( $filter['last_modify_st_time'] ) )
        {
            $where[] = "last_modify >=".intval( $filter['last_modify_st_time'] );
        }
        if ( isset( $filter['last_modify_en_time'] ) )
        {
            $where[] = "last_modify <".intval( $filter['last_modify_en_time'] );
        }
        if ( isset( $filter['disabled'] ) )
        {
            $where[] = "disabled=\"".$filter['disabled']."\"";
        }
        return $where;
    }

    public function _filter( $filter )
    {
        $where = $this->before_filter( $filter );
        return parent::_filter( $where, $filter );
    }

}

?>
