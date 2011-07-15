<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class UpgradeScript extends Upgrade
{

    public $left_domain = "shopex.cn";
    public $workground = "setting";
    public $max_runtime = 5;
    public $safebytes = 10;
    public $set = NULL;
    public $noticeMsg = array( );

    public function upgrade_checkdb( )
    {
        $this->db->exec( "UPDATE sdb_member_attr SET attr_name = '移动电话' WHERE attr_name = '手机号码'" );
    }

}

?>
