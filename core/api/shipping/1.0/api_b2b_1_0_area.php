<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_area extends shop_api_object
{

    public function getColumns( )
    {
    }

    public function search_dly_area( $data )
    {
        $data['disabled'] = "false";
        $data['orderby'] = "area_id";
        $where = $this->_filter( $data );
        $result['data_info'] = $this->db->select( "select * from sdb_dly_area".$where );
        $this->api_response( "true", FALSE, $result );
    }

    public function before_filter( $filter )
    {
        $where = array( 1 );
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
