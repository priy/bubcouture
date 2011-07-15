<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_3_0_order extends shop_api_object
{

    public $app_error = array( );

    public function get_order_setting( )
    {
        $obj_payments = $this->load_api_instance( "search_payments_by_order", "2.0" );
        $cursign = $obj_payments->getcur( "CNY" );
        $return = array(
            "cur_sign" => $cursign['cur_sign'],
            "decimals" => $this->system->getConf( "system.money.operation.decimals" ),
            "carryset" => $this->system->getConf( "system.money.operation.carryset" ),
            "dec_point" => $this->system->getConf( "system.money.dec_point" ),
            "thousands_sep" => $this->system->getConf( "system.money.thousands_sep" ),
            "decimal_digit" => $this->system->getConf( "site.decimal_digit" ),
            "decimal_type" => $this->system->getConf( "site.decimal_type" ),
            "trigger_tax" => $this->system->getConf( "site.trigger_tax" ),
            "tax_ratio" => $this->system->getConf( "site.tax_ratio" )
        );
        $this->api_response( "true", FALSE, $return );
    }

}

?>
