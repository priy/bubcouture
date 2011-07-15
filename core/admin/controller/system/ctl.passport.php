<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_passport extends adminPage
{

    public $workground = "setting";

    public function getPassportList( )
    {
        $oPassport =& $this->system->loadModel( "member/passport" );
        $this->pagedata['items'] = $oPassport->getList( );
        $this->path[] = array(
            "text" => __( "登录整合" )
        );
        $this->page( "passport/passport_list.html" );
    }

    public function savePassport( )
    {
        $this->begin( "index.php?ctl=system/passport&act=getPassportList" );
        $oPassport =& $this->system->loadModel( "member/passport" );
        $this->end( $oPassport->savePassport( $_POST ), __( "保存成功！" ) );
    }

    public function detailPassport( $type )
    {
        $oPassport =& $this->system->loadModel( "member/passport" );
        $this->pagedata['options'] = $oPassport->getOptions( $type );
        $this->pagedata['passport_type'] = $type;
        $this->pagedata['params'] = $oPassport->getParams( $type, FALSE );
        $this->pagedata['passport_ifvalid'] = $oPassport->getCurrentPlugin( ) == $type ? "true" : "false";
        if ( $this->pagedata['params']['tmpl'] )
        {
            $this->page( "passport/".$this->pagedata['params']['tmpl'] );
        }
        else
        {
            $this->page( "passport/passport_edit.html" );
        }
    }

}

?>
