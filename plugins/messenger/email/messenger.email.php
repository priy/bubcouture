<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class messenger_email
{

    public $name = "电子邮件";
    public $iconclass = "sysiconBtn email";
    public $name_show = "发邮件";
    public $version = "\$ver\$";
    public $updateUrl = "";
    public $isHtml = TRUE;
    public $hasTitle = TRUE;
    public $maxtime = 300;
    public $maxbodylength = 300;
    public $allowMultiTarget = FALSE;
    public $targetSplit = ",";
    public $dataname = "email";
    public $debug = FALSE;

    public function ready( $config )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        $this->email =& $system->loadModel( "system/email" );
        if ( $config['sendway'] == "smtp" )
        {
            $this->email->smtp =& $system->loadModel( "utility/smtp" );
            if ( !$this->email->SmtpConnect( $config ) )
            {
                return FALSE;
            }
        }
    }

    public function finish( $config )
    {
        if ( $config['sendway'] == "smtp" )
        {
            $this->email->SmtpClose( );
        }
    }

    public function send( $to, $subject, $body, $config )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        if ( $config['sendway'] == "mail" )
        {
            $this->email =& $system->loadModel( "system/email" );
        }
        $this->Sender = $config['usermail'];
        $this->Subject = $this->email->inlineCode( $subject );
        $this->email->Sender = $this->Sender;
        $this->email->Subject = $this->Subject;
        $header = array(
            "Return-path" => "<".$config['usermail'].">",
            "Date" => date( "r" ),
            "From" => $this->email->inlineCode( $system->getConf( "system.shopname" ) )."<".$config['usermail'].">",
            "MIME-Version" => "1.0",
            "Subject" => $this->Subject,
            "To" => $to,
            "Content-Type" => "text/html; charset=UTF-8; format=flowed",
            "Content-Transfer-Encoding" => "base64"
        );
        $body = chunk_split( base64_encode( $body ) );
        $config['sendway'] = $config['sendway'] ? $config['sendway'] : "smtp";
        if ( $config['sendway'] == "mail" )
        {
            unset( $header['To'] );
            unset( $header['Subject'] );
        }
        $header = $this->email->buildHeader( $header );
        switch ( $config['sendway'] )
        {
        case "sendmail" :
            $result = $this->email->SendmailSend( $to, $header, $body );
            break;
        case "mail" :
            $result = $this->email->MailSend( $to, $header, $body );
            break;
        case "smtp" :
            $result = $this->email->SmtpSend( $to, $header, $body, $config );
            break;
        default :
            trigger_error( "mailer_not_supported", E_ERROR );
            $result = FALSE;
            break;
        }
        return $result;
    }

    public function getOptions( )
    {
        return array(
            "sendway" => array(
                "label" => "发送方式",
                "type" => "radio",
                "options" => array( "mail" => "使用本服务器发送", "smtp" => "使用外部SMTP发送" ),
                "value" => "mail"
            ),
            "usermail" => array( "label" => "发信人邮箱", "type" => "input", "value" => "yourname@domain.com" ),
            "smtpserver" => array( "label" => "smtp服务器地址", "type" => "input", "value" => "mail.domain.com" ),
            "smtpport" => array( "label" => "smtp服务器端口", "type" => "input", "value" => "25" ),
            "smtpuname" => array( "label" => "smtp用户名", "type" => "input", "value" => "" ),
            "smtppasswd" => array( "label" => "smtp密码", "type" => "password", "value" => "" )
        );
    }

}

?>
