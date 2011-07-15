<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_cur extends shop_api_object
{

    public $max_number = 100;

    public function getColumns( )
    {
        $columns = array(
            "cur_name" => array( "type" => "string" ),
            "cur_code" => array( "type" => "string" ),
            "cur_sign" => array( "type" => "string" ),
            "cur_rate" => array( "type" => "decimal" ),
            "def_cur" => array( "type" => "string" ),
            "disabled" => array( "type" => "string" )
        );
        return $columns;
    }

    public function get_currency_list( )
    {
        $data_info = $this->db->select( "select * from sdb_currency" );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function search_cur_list( $data )
    {
        $data['orderby'] = "cur_name";
        $where = $this->_filter( array( 1 ), $data );
        $data_info = $this->db->select( "select * from sdb_currency".$where );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

}

?>
