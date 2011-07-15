<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_3_1_csvfile extends shop_api_object
{

    public $tableName = "sdb_csv_file";

    public function getColumns( )
    {
        $columns = array(
            "id" => array( "type" => "int" ),
            "filename" => array( "type" => "string" ),
            "createtime" => array( "type" => "string" ),
            "uptime" => array( "type" => "string" ),
            "filesize" => array( "type" => "string" ),
            "goods_ids" => array( "type" => "string" )
        );
        return $columns;
    }

    public function get_csvfile_info( $data )
    {
        $data['counts'] = $data['counts'] ? $data['counts'] : 10;
        $data['pages'] = $data['pages'] ? $data['pages'] : 1;
        $start = ( $data['pages'] - 1 ) * $data['counts'];
        $end = $data['counts'];
        $columns = "id,filename,createtime,uptime,filesize,goods_ids";
        $result = $this->db->selectrow( "SELECT count(*) AS all_counts FROM ".$this->tableName );
        $data_info = $this->db->select( "SELECT ".$columns." FROM ".$this->tableName." ORDER BY id LIMIT ".$start.",".$end );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

}

?>
