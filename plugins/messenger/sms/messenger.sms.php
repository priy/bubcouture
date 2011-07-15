<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class messenger_sms
{

    public $name = "手机短信";
    public $iconclass = "sysiconBtn sms";
    public $name_show = "发短信";
    public $version = "\$ver\$";
    public $updateUrl = FALSE;
    public $isHtml = FALSE;
    public $hasTitle = FALSE;
    public $maxtime = 300;
    public $maxbodylength = 300;
    public $allowMultiTarget = FALSE;
    public $withoutQueue = FALSE;
    public $dataname = "mobile";
    public $sms_service_ip = "124.74.193.222";
    public $sms_service = "http://idx.sms.shopex.cn/service.php";

    public function messenger_sms( )
    {
        $this->system =& $GLOBALS['GLOBALS']['system'];
        $this->net =& $this->system->loadModel( "utility/http_client" );
    }

    public function send( $to, $message, $config, $sms_type )
    {
        if ( !$this->checkL( ) )
        {
            return "license error";
        }
        $result = $this->apply( $this->sms_service, $this->version );
        if ( !$result )
        {
            return "you must to active the code";
        }
        $this->content = $message;
        $this->mobile = $to;
        $result = $this->send_info( $result, $this->ex_type, $this->version, $sms_type );
        $msg = $this->msg( $result );
        return $msg;
    }

    public function checkL( )
    {
        if ( !$this->getCerti( ) || !$this->getToken( ) )
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    public function apply( )
    {
        $submit_str['certi_id'] = $this->getCerti( );
        $submit_str['ac'] = md5( $this->getCerti( ).$this->getToken( ) );
        $submit_str['version'] = $this->version;
        $results = $this->net->post( $this->sms_service, $submit_str );
        $result = explode( "|", $results );
        if ( $result[0] == "0" )
        {
            return $result[1];
        }
        if ( $result[0] == "1" )
        {
            return FALSE;
        }
        if ( $result[0] == "2" )
        {
            return FALSE;
        }
    }

    public function send_info( $url, $ex_type, $version, $sms_type = FALSE )
    {
        if ( $sms_type == TRUE || $sms_type == 1 )
        {
            $sms_type = 1;
        }
        else
        {
            $sms_type = "";
        }
        $send_arr = array(
            0 => array(
                0 => $this->mobile,
                1 => $this->content,
                2 => "Now"
            )
        );
        $send_str['certi_id'] = $this->getCerti( );
        $send_str['ex_type'] = $ex_type;
        $send_str['content'] = json_encode( $send_arr );
        $send_str['sms_type'] = $sms_type;
        $send_str['encoding'] = "utf8";
        $send_str['version'] = $version;
        $send_str['ac'] = md5( $send_str['certi_id'].$send_str['ex_type'].$send_str['content'].$send_str['encoding'].$this->getToken( ) );
        $results = $this->net->post( $url, $send_str );
        $result = explode( "|", $results );
        if ( $result[0] == "true" )
        {
            return 200;
        }
        else if ( $result[0] == "false" )
        {
            return $result[1];
        }
    }

    public function getCerti( )
    {
        if ( $this->system->getConf( "certificate.id" ) )
        {
            return $this->system->getConf( "certificate.id" );
        }
        else
        {
            return FALSE;
        }
    }

    public function getToken( )
    {
        if ( $this->system->getConf( "certificate.token" ) )
        {
            return $this->system->getConf( "certificate.token" );
        }
        else
        {
            return FALSE;
        }
    }

    public function msg( $index )
    {
        $aMsg = array( "200" => "true", "1" => "Security check can not pass!", "2" => "Phone number format is not correct.", "3" => "Lack of content or content coding error.", "4" => "Lack of balance.", "5" => "Information packets over limited.", "6" => "You must recharge before write message!", "901" => "Write sms_log error!", "902" => "Write sms_API error!" );
        return $aMsg[$index];
    }

    public function ready( $config )
    {
        $this->url = $this->apply( $this->sms_service, $this->version );
        return $this->url;
    }

    public function finish( $config )
    {
    }

    public function extraVars( )
    {
        if ( !$this->getCerti( ) )
        {
            $certi_id = "error";
        }
        else
        {
            $certi_id = $this->getCerti( );
        }
        $sess_id = $this->system->session->sess_id;
        $auth = mktime( );
        $ac = md5( $certi_id."SHOPEX_SMS".$auth );
        $url = "http://service.shopex.cn/sms/index.php?certificate_id=".$certi_id."&sess_id=".$sess_id."&auth=".$auth."&ac=".$ac;
        return array(
            "outgoingOptions" => $url
        );
    }

}

?>
