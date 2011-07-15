<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
class friendcontrol extends base
{

    public function friendcontrol( )
    {
        $this->base( );
        $this->load( "friend" );
    }

    public function ondelete( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $id = $_ENV['friend']->delete( $uid, $friendids );
        return $id;
    }

    public function onadd( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $id = $_ENV['friend']->add( $uid, $friendid, $comment );
        return $id;
    }

    public function ontotalnum( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $totalnum = $_ENV['friend']->get_totalnum_by_uid( $uid, $direction );
        return $totalnum;
    }

    public function onls( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $totalnum = $totalnum ? $totalnum : $_ENV['friend']->get_totalnum_by_uid( $uid );
        $data = $_ENV['friend']->get_list( $uid, $page, $pagesize, $totalnum, $direction );
        return $data;
    }

}

?>
