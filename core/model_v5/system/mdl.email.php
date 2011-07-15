<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_email
{

    public $hasTitle = true;
    public $maxtime = 300;
    public $maxbodylength = 300;
    public $allowMultiTarget = false;
    public $targetSplit = ",";
    public $Sendmail = "/usr/sbin/sendmail";

    public function ready( $config )
    {
        $system =& $system;
        $this->smtp =& $system->loadModel( "utility/smtp" );
        if ( $config['sendway'] == "smtp" && !$this->SmtpConnect( $config ) )
        {
            return false;
        }
        return true;
    }

    public function finish( $config )
    {
        if ( $config['sendway'] == "smtp" )
        {
            $this->SmtpClose( );
        }
    }

    public function send( $to, $subject, $body, $config )
    {
        $system =& $system;
        $this->Sender = $config['usermail'];
        $this->Subject = $this->inlineCode( $subject );
        $header = array(
            "Return-path" => "<".$config['usermail'].">",
            "Date" => date( "r" ),
            "From" => $this->inlineCode( $system->getConf( "system.shopname" ) )."<".$config['usermail'].">",
            "MIME-Version" => "1.0",
            "Subject" => $this->Subject,
            "To" => $to,
            "Content-Type" => "text/html; charset=UTF-8; format=flowed",
            "Content-Transfer-Encoding" => "base64"
        );
        $body = chunk_split( base64_encode( $body ) );
        $header = $this->buildHeader( $header );
        $config['sendway'] = $config['sendway'] ? $config['sendway'] : "smtp";
        switch ( $config['sendway'] )
        {
        case "sendmail" :
            $result = $this->SendmailSend( $to, $header, $body );
            break;
        case "mail" :
            $result = $this->MailSend( $to, $header, $body );
            break;
        case "smtp" :
            $result = $this->SmtpSend( $to, $header, $body, $config );
            break;
        default :
            $result = false;
            break;
        }
        return $result;
    }

    public function inlineCode( $str )
    {
        $str = trim( $str );
        return $str ? "=?UTF-8?B?".base64_encode( $str )."?= " : "";
    }

    public function buildHeader( $headers )
    {
        $ret = "";
        foreach ( $headers as $k => $v )
        {
            $ret .= $k.": ".$v."\n";
        }
        return $ret;
    }

    public function SendmailSend( $header, $body )
    {
        if ( $this->Sender != "" )
        {
            $sendmail = sprintf( "%s -oi -f %s -t", $this->Sendmail, $this->Sender );
        }
        else
        {
            $sendmail = sprintf( "%s -oi -t", $this->Sendmail );
        }
        if ( !( $mail = @popen( $sendmail, "w" ) ) )
        {
            $this->__maillog( );
            return false;
        }
        fputs( $mail, $header );
        fputs( $mail, $body );
        $result = pclose( $mail ) >> 8 & 255;
        if ( $result != 0 )
        {
            $this->__maillog( );
            return false;
        }
        return true;
    }

    public function MailSend( $to, $header, $body )
    {
        if ( strlen( ini_get( "safe_mode" ) ) < 1 )
        {
            $old_from = ini_get( "sendmail_from" );
            ini_set( "sendmail_from", $this->Sender );
            $params = sprintf( "-oi -f %s", $this->Sender );
            $rt = mail( $to, $this->Subject, $body, $header );
        }
        else
        {
            $rt = mail( $to, $this->Subject, $body, $header );
        }
        if ( isset( $old_from ) )
        {
            ini_set( "sendmail_from", $old_from );
        }
        if ( !$rt )
        {
            return false;
        }
        return true;
    }

    public function __maillog( )
    {
        $this->errorinfo = $this->smtp->error;
        if ( MAIL_LOG )
        {
            error_log( var_export( $this->smtp->error, true )."\n", 3, HOME_DIR."/log/mail.log" );
        }
    }

    public function SmtpSend( $to, $header, $body, $config )
    {
        $system =& $system;
        $smtp_from = $this->Sender;
        if ( !$this->smtp->Mail( $smtp_from ) )
        {
            $this->__maillog( );
            $this->smtp->Reset( );
            return false;
        }
        if ( !$this->smtp->Recipient( $to ) )
        {
            $this->__maillog( );
            $this->smtp->Reset( );
            return false;
        }
        if ( !$this->smtp->Data( $header."\n".$body ) )
        {
            $this->__maillog( );
            $this->smtp->Reset( );
            return false;
        }
        $this->smtp->Reset( );
        return true;
    }

    public function SmtpConnect( $config )
    {
        $this->smtp->do_debug = $this->debug;
        $index = 0;
        $connection = $this->smtp->Connected( );
        if ( $this->smtp->Connect( $config['smtpserver'], $config['smtpport'], 20 ) )
        {
            $this->smtp->Hello( $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : "localhost.localdomain" );
            if ( $config['smtpuname'] && !$this->smtp->Authenticate( $config['smtpuname'], $config['smtppasswd'] ) )
            {
                $this->smtp->Reset( );
                $connection = false;
            }
            $connection = true;
        }
        return $connection;
    }

    public function SmtpClose( )
    {
        if ( $this->smtp != NULL && $this->smtp->Connected( ) )
        {
            $this->smtp->Quit( );
            $this->smtp->Close( );
        }
    }

}

?>
