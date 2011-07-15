<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_msgbox extends objectPage
{

    public $workground = "member";
    public $object = "resources/msgbox";

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "站内信息" ),
                "tpl" => "member/msgbox/msg_items.html"
            )
        );
    }

    public function show_detail( $msg_id )
    {
        $objMsgbox =& $this->system->loadModel( "resources/msgbox" );
        $aMsg = $objMsgbox->getFieldById( $msg_id );
        $this->pagedata['message'] = $aMsg;
        $this->pagedata['revert'] = $objMsgbox->getMsgReply( $msg_id );
        $objMsgbox->setReaded( $msg_id );
        $member =& $this->system->loadModel( "member/member" );
        $this->pagedata['member'] = $member->getFieldById( $aMsg['from_id'] );
    }

    public function revert( )
    {
        $this->begin( "index.php?ctl=member/msgbox&act=detail&p[0]=".$_POST['for_id'] );
        $oMsg =& $this->system->loadModel( "resources/msgbox" );
        $GLOBALS['_POST']['from_id'] = $this->system->op_id;
        $this->end( $oMsg->revert( $_POST ), __( "回复成功" ) );
    }

    public function toDisplay( $msg_id, $status = "false", $f_id = 0 )
    {
        $this->begin( "index.php?ctl=member/msgbox&act=detail&p[0]=".( $f_id ? $f_id : $msg_id ) );
        $oMsg =& $this->system->loadModel( "resources/msgbox" );
        if ( 0 < intval( $msg_id ) )
        {
            if ( $status == "true" )
            {
                $status = "false";
            }
            else
            {
                $status = "true";
            }
            $this->end( $oMsg->toDisplay( intval( $msg_id ), $status ), __( "操作成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "操作失败: 传入参数丢失!" ) );
        }
    }

    public function delete( )
    {
        if ( $_REQUEST['f_id'] )
        {
            $this->begin( "index.php?ctl=member/msgbox&act=detail&p[0]=".$_REQUEST['f_id'] );
        }
        else
        {
            $this->begin( "index.php?ctl=member/msgbox&act=index" );
        }
        $oMsg =& $this->system->loadModel( "resources/msgbox" );
        if ( is_array( $_REQUEST['msg_id'] ) )
        {
            foreach ( $_REQUEST['msg_id'] as $id )
            {
                $oMsg->toRemove( $id );
            }
        }
        $this->end( TRUE );
    }

}

?>
