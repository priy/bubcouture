<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_cat extends shop_api_object
{

    public function getColumns( )
    {
        $columns = array(
            "cat_id" => array( "type" => "int" ),
            "parent_id" => array( "type" => "int" ),
            "cat_path" => array( "type" => "string" ),
            "is_leaf" => array( "type" => "string" ),
            "type_id" => array( "type" => "int" ),
            "cat_name" => array( "type" => "string" ),
            "disabled" => array( "type" => "string" ),
            "p_order" => array( "type" => "int" ),
            "goods_count" => array( "type" => "int" ),
            "tabs" => array( "type" => "string" ),
            "finder" => array( "type" => "string" ),
            "addon" => array( "type" => "string" ),
            "child_count" => array( "type" => "int" ),
            "last_modify" => array( "type" => "int" )
        );
        return $columns;
    }

    public function search_cat_list( $data )
    {
        $data['disabled'] = "false";
        $data['orderby'] = "cat_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_goods_cat where ".implode( " and ", $where ) );
        $result['last_modify_st_time'] = $data['last_modify_st_time'];
        $result['last_modify_en_time'] = $data['last_modify_en_time'];
        $where = $this->_filter( $data );
        $data_info = $this->db->select( "select ".implode( ",", $data['columns'] )." from sdb_goods_cat".$where );
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
