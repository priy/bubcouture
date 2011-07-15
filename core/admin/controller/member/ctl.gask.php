<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_gask extends objectPage
{

    public $workground = "member";
    public $object = "comment/gask";
    public $finder_action_tpl = "member/gask/finder_action.html";
    public $finder_filter_tpl = "member/gask/finder_filter.html";

    public function setting( )
    {
        $this->path[] = array(
            "text" => __( "咨询设置" )
        );
        $comment =& $this->system->loadModel( "comment/comment" );
        $aOut = $comment->getSetting( "ask" );
        if ( !$aOut['verifyCode']['ask'] )
        {
            $aOut['verifyCode']['ask'] = "off";
        }
        $aOut['aSwitch']['ask'] = array(
            "on" => __( "开启" ),
            "off" => __( "关闭" )
        );
        $aOut['aPower']['ask'] = array(
            "null" => __( "所有顾客都可咨询" ),
            "member" => __( "只有注册会员才能咨询" )
        );
        $aOut['verifyLCode']['ask'] = array(
            "on" => __( "开启" ),
            "off" => __( "关闭" )
        );
        $this->pagedata['setting'] = $aOut;
        $this->page( "member/gask/setting.html" );
    }

    public function toSetting( )
    {
        $comment =& $this->system->loadModel( "comment/comment" );
        $comment->setSetting( "ask", $_POST );
        $this->splash( "success", "index.php?ctl=member/gask&act=setting", __( "保存成功!" ) );
    }

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "咨询信息" ),
                "tpl" => "member/gask/detail.html"
            )
        );
    }

    public function show_detail( $comment_id )
    {
        $Mem =& $this->system->loadModel( "member/member" );
        $objComment =& $this->system->loadModel( "comment/comment" );
        $aComment = $objComment->getCommentById( $comment_id );
        $aComment['url'] = $this->system->realUrl( "product", "index", array(
            $aComment['goods_id']
        ), NULL, $this->system->base_url( ) );
        $this->pagedata['comment'] = $aComment;
        $this->pagedata['reply'] = $objComment->getCommentReply( $comment_id );
        $data = $Mem->getMemIdByName( $aComment['author'] );
        $mem_id = $data[0]['member_id'];
        $this->pagedata['reply'] = $objComment->getCommentReply( $comment_id );
        $comment_ids = array(
            $comment_id
        );
        foreach ( $this->pagedata['reply'] as $key => $replyItem )
        {
            $comment_ids[] = $replyItem['comment_id'];
        }
        $tree = $Mem->getContactObject( $mem_id );
        $this->pagedata['tree'] = $tree;
        $objComment->setReaded( $comment_ids );
    }

    public function delete( )
    {
        if ( $_REQUEST['f_id'] )
        {
            $this->begin( "index.php?ctl=member/gask&act=detail&p[0]=".$_REQUEST['f_id'] );
        }
        else
        {
            $this->begin( "index.php?ctl=member/gask&act=index" );
        }
        $objComment =& $this->system->loadModel( "comment/comment" );
        if ( is_array( $_REQUEST['comment_id'] ) )
        {
            foreach ( $_REQUEST['comment_id'] as $id )
            {
                $objComment->toRemove( $id );
            }
        }
        $this->end( TRUE, __( "操作成功!" ) );
    }

    public function toDisplay( $comment_id, $status = "false", $f_id = 0 )
    {
        $this->begin( "index.php?ctl=member/gask&act=detail&p[0]=".( $f_id ? $f_id : $comment_id ) );
        $objComment =& $this->system->loadModel( "comment/comment" );
        if ( 0 < intval( $comment_id ) )
        {
            if ( $status == "true" )
            {
                $status = "false";
            }
            else
            {
                $status = "true";
            }
            $this->end( $objComment->toDisplay( intval( $comment_id ), $status ), __( "操作成功!" ) );
        }
        else
        {
            $this->end( FALSE, __( "操作失败: 传入参数丢失!" ) );
        }
    }

    public function toReply( $comment_id )
    {
        $this->begin( "index.php?ctl=member/gask&act=detail&p[0]=".$comment_id );
        $objComment =& $this->system->loadModel( "comment/comment" );
        $aComment = $objComment->getFieldById( $comment_id, array( "*" ) );
        $aData['comment'] = $_POST['reply_content'];
        $aData['for_comment_id'] = $comment_id;
        $aData['goods_id'] = $aComment['goods_id'];
        $aData['object_type'] = $aComment['object_type'];
        $aData['author_id'] = $this->system->op_id;
        $aData['author'] = __( "管理员" )."[".$this->system->op_name."]";
        $aData['time'] = time( );
        $aData['lastreply'] = time( );
        $aData['display'] = "true";
        $aData['ip'] = remote_addr( );
        $this->end( $objComment->toReply( $aData ), __( "回复成功!" ) );
    }

    public function setIndexOrder( )
    {
        $objComment =& $this->system->loadModel( "comment/comment" );
        if ( 0 < count( $_POST['comment_id'] ) )
        {
            foreach ( $_POST['comment_id'] as $id )
            {
                $objComment->setIndexOrder( $id );
            }
        }
        unset( $GLOBALS['$_POST'] );
        echo __( "操作成功!" );
    }

}

?>
