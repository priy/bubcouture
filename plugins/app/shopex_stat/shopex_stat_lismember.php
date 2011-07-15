<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !class_exists( "ctl_member" ) )
{
    require( CORE_DIR."/admin/controller/member/ctl.member.php" );
}
class shopex_stat_lismember extends ctl_member
{

    public function shopex_stat_lismember( )
    {
        parent::ctl_member( );
        $this->system =& $GLOBALS['GLOBALS']['system'];
    }

    public function get_adminaddmen( )
    {
        $oMem =& $this->system->loadModel( "member/member" );
        $id = $oMem->addMemberByAdmin( $_POST, $message );
        $name = $_POST['uname'];
        $stuats = "back";
        $info_mem = array(
            "aid" => $id,
            "aname" => $name,
            "style" => $stuats
        );
        $this->system->setConf( "site.addmenbyadmin", serialize( $info_mem ) );
    }

}

?>
