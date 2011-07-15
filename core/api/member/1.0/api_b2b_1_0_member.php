<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_1_0_member extends shop_api_object
{

    public $max_number = 100;
    public $app_error = array
    (
        "dealer member not exists" => array
        (
            "no" => "b_verify_member_valid_001",
            "debug" => "",
            "level" => "warning",
            "info" => "经销商所对应的会员记录无效",
            "desc" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array( );
        return $columns;
    }

    public function verify_member_valid( $dealer_id, &$member, $colums = "*" )
    {
        $_member = $this->db->selectrow( "select ".$colums." from sdb_members where certificate_id=".$dealer_id );
        if ( !$_member )
        {
            $this->api_response( "fail", "data fail", $result, "经销商所对应的会员记录无效" );
        }
        else
        {
            $member = $_member;
        }
    }

}

?>
