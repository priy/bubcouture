<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !class_exists( "mdl_magicvars" ) )
{
    require( dirname( __FILE__ )."/../system/mdl.magicvars.php" );
}
class mdl_custommessage extends mdl_magicvars
{

    public $defaultCols = "var_name,var_remark";
    public $filter = "and var_type=\"system\" and var_name in(\"{register_message}\",\"{login_message}\",\"{lost_password}\",\"{buy_product}\",\"{nologin_buy}\",\"{pay_message}\",\"{pay_offline}\",\"{reg_succ_mess}\",\"{pay_succ}\",\"{pay_wait}\")";

    public function getColumns( $filter )
    {
        $cols = parent::getcolumns( );
        $cols['var_name']['hidden'] = true;
        $cols['var_remark']['label'] = __( "信息位置" );
        $cols['var_type']['hidden'] = true;
        $cols['_cmd'] = array(
            "label" => __( "操作" ),
            "width" => 70,
            "html" => "content/custommessage/finder_command.html"
        );
        return $cols;
    }

}

?>
