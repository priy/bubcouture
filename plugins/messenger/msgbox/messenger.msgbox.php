<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class messenger_msgbox
{

    public $name = "站内消息";
    public $iconclass = "sysiconBtn msgbox";
    public $name_show = "发消息";
    public $version = "\$ver\$";
    public $updateUrl = FALSE;
    public $isHtml = FALSE;
    public $hasTitle = TRUE;
    public $maxtime = 300;
    public $maxbodylength = 300;
    public $allowMultiTarget = FALSE;
    public $dataname = "member_id";
    public $withoutQueue = TRUE;

    public function messenger_msgbox( )
    {
        $this->system =& $GLOBALS['GLOBALS']['system'];
    }

    public function send( $to, $subject, $message, $config )
    {
        $oMessage = $this->system->loadModel( "resources/msgbox" );
        return $oMessage->sendMsg( 0, $to, $message, array(
            "from_type" => 1,
            "subject" => $subject
        ) );
    }

    public function ready( $config )
    {
    }

    public function finish( $config )
    {
    }

}

?>
