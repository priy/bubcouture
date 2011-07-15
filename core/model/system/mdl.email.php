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

    var $hasTitle = true;
    var $maxtime = 300;
    var $maxbodylength = 300;
    var $allowMultiTarget = false;
    var $targetSplit = ",";
    var $Sendmail = "/usr/sbin/sendmail";

    function ready( $config )
    {
        $system =& $system;
        $this->smtp =& $system->loadmodel( "utility/smtp" );
        if ( $config['sendway'] == "smtp" && !$this->smtpconnect( $config ) )
        {
            return false;
        }
        return true;
    }

    function finish( $config )
    {
        if ( $config['sendway'] == "smtp" )
        {
            $this->smtpclose( );
        }
    }

    function send( $to, $subject, $body, $config )
    {
        $system =& $system;
        $this->Sender = $config['usermail'];
        $this->Subject = $this->inlinecode( $subject );
        $header = array(
            "Return-path" => "<".$config['usermail'].">",
            "Date" => date( "r" ),
            "From" => $this->inlinecode( $system->getconf( "system.shopname" ) )."<".$config['usermail'].">",
            "MIME-Version" => "1.0",
            "Subject" => $this->Subject,
            "To" => $to,
            "Content-Type" => "text/html; charset=UTF-8; format=flowed",
            "Content-Transfer-Encoding" => "base64"
        );
        $body = chunk_split( base64_encode( $body ) );
        $header = $this->buildheader( $header );
        $config['sendway'] = $config['sendway'] ? $config['sendway'] : "smtp";
        switch ( $config['sendway'] )
        {
        case "sendmail" :
            $result = $this->sendmailsend( $to, $header, $body );
            break;
        case "mail" :
            $result = $this->mailsend( $to, $header, $body );
            break;
        case "smtp" :
            $result = $this->smtpsend( $to, $header, $body, $config );
            break;
        default :
            $result = false;
        }
        return $result;
    }

    function inlinecode( $str )
    {
        $str = trim( $str );
        if ( $str )
        {
            return "=?UTF-8?B?".base64_encode( $str )."?= ";
        }
        return "";
    }

    function buildheader( $headers )
    {
        $ret = "";
        foreach ( $headers as $k => $v )
        {
            $ret .= $k.": ".$v."\n";
        }
        return $ret;
    }

    function sendmailsend( $header, $body )
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

    function mailsend( $to, $header, $body )
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

    function __maillog( )
    {
        $this->errorinfo = $this->smtp->error;
        if ( MAIL_LOG )
        {
            error_log( var_export( $this->smtp->error, true )."\n", 3, HOME_DIR."/log/mail.log" );
        }
    }

    function smtpsend( $to, $header, $body, $config )
    {
        $system =& $system;
        $smtp_from = $this->Sender;
        if ( !$this->smtp->mail( $smtp_from ) )
        {
            $this->__maillog( );
            $this->smtp->reset( );
            return false;
        }
        if ( !$this->smtp->recipient( $to ) )
        {
            $this->__maillog( );
            $this->smtp->reset( );
            return false;
        }
        if ( !$this->smtp->data( $header."\n".$body ) )
        {
            $this->__maillog( );
            $this->smtp->reset( );
            return false;
        }
        $this->smtp->reset( );
        return true;
    }

    function smtpconnect( $config )
    {
        $this->smtp->do_debug = $this->debug;
        $index = 0;
        $connection = $this->smtp->connected( );
        if ( $this->smtp->connect( $config['smtpserver'], $config['smtpport'], 20 ) )
        {
            $this->smtp->hello( $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : "localhost.localdomain" );
            if ( $config['smtpuname'] && !$this->smtp->authenticate( $config['smtpuname'], $config['smtppasswd'] ) )
            {
                $this->smtp->reset( );
                $connection = false;
            }
            $connection = true;
        }
        return $connection;
    }

    function smtpclose( )
    {
        if ( $this->smtp != NULL && $this->smtp->connected( ) )
        {
            $this->smtp->quit( );
            $this->smtp->close( );
        }
    }

}

?>
