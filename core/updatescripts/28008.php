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

    public $noticeMsg = array( );

    public function upgrade_checkdb( )
    {
        $sql = "SELECT extend FROM sdb_orders";
        if ( !$this->db->selectrow( $sql ) )
        {
            $this->db->exec( "ALTER TABLE `sdb_orders` ADD COLUMN `extend` varchar(255) default NULL" );
        }
        return "finish";
    }

}

?>
