<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class app_pay_paypal extends app
{

    public $ver = 1.1;
    public $name = "PayPal";
    public $website = "http://www.shopex.cn";
    public $author = "shopex";
    public $help = "";
    public $type = "paypal";

    public function install( )
    {
        parent::install( );
        return TRUE;
    }

    public function uninstall( )
    {
        $this->db->exec( "delete from sdb_payment_cfg where pay_type =\"".$this->type."\"" );
        return parent::uninstall( );
    }

    public function ctl_mapper( )
    {
        return array( );
    }

}

?>
