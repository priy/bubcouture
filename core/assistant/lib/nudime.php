<?php
require_once(CORE_DIR.'/lib/nusoap.php');
require_once('DIME.php');

class nusoap_server_dime extends nusoap_server {

    var $requestAttachments  = array();
    var $responseAttachments = array();
    var $raw_post_data;
    var $dimeContentType = 'application/dime';
    var $soap_defencoding = 'UTF-8';
    var $decode_utf8 = false;
    var $validate_factory = null;

    function addAttachment($data, $contenttype = 'application/octet-stream', $type=NET_DIME_TYPE_UNKNOWN, $cid = false) {
        if (! $cid) {
            $cid = md5(uniqid(time()));
        }

        $info = array();
        $info['data'] = $data;
        $info['filename'] = $cid;
        $info['contenttype'] = $contenttype;
        $info['cid'] = $cid;
        $info['type'] = $type;

        $this->responseAttachments[] = $info;

        return $cid;
    }

    function clearAttachments() {
        $this->responseAttachments = array();
    }

    function getAttachments() {
        return $this->requestAttachments;
    }

    function getHTTPBody($soapmsg) {
        //file_put_contents(dirname(__FILE__).'/logout.txt', $soapmsg);
        if (count($this->responseAttachments) > 0) {
            $soapmsg = &$this->_makeDIMEMessage($soapmsg);
        }

        return parent::getHTTPBody($soapmsg);
    }

    function getHTTPContentType() {
        if (count($this->responseAttachments) > 0) {
            return $this->dimeContentType;
        }
        return parent::getHTTPContentType();
    }

    function getHTTPContentTypeCharset() {
        if (count($this->responseAttachments) > 0) {
            return false;
        }
        return parent::getHTTPContentTypeCharset();
    }

    function service(&$data){
        $this->raw_post_data = $data;
        parent::service($data);
    }

    function parseRequest($headers, $data) {
        $this->debug('Entering parseRequest() for payload of length ' . strlen($data) . ' and type of ' . $headers['content-type']);
        $this->requestAttachments = array();

        if (strstr($headers['content-type'], $this->dimeContentType))
        {
            $this->_decodeDIMEMessage($headers, $data);
        }else{
            $this->debug('Not dime content');
        }

        if(!$this->validate_signatrue($data)){
            $errmsg = 'SOAP Signatrue ERROR';
            if (isset($GLOBALS['validate_signatrue_errmsg'])) $errmsg = $GLOBALS['validate_signatrue_errmsg'];
            $this->fault('TokenError', $errmsg);
        }

        
        return parent::parseRequest($headers, $data);
    }

    function &_makeDIMEMessage(&$xml)
    {
        // encode any attachments
        // see http://msdn.microsoft.com/library/en-us/dnglobspec/html/draft-nielsen-dime-02.txt
        // now we have to DIME encode the message
        $dime = &new Net_DIME_Message();
        $msg  = &$dime->encodeData($xml, $this->namespaces['SOAP-ENV'], NULL, NET_DIME_TYPE_URI);

        // add the attachements
        $c = count($this->responseAttachments);
        $this->debug('Found '. $c .' attachments');
        for ($i=0; $i < $c; $i++) {
            $att =& $this->responseAttachments[$i];
            $msg .= $dime->encodeData($att['data'], $att['contenttype'], $att['cid'], NET_DIME_TYPE_MEDIA);
        }
        $msg .= $dime->endMessage();
        return $msg;
    }

    function _decodeDIMEMessage(&$headers, &$data)
    {
        // XXX this SHOULD be moved to the transport layer, e.g. PHP  itself
        $dime =& new Net_DIME_Message();
        $err = $dime->decodeData($data);
        if ( PEAR::isError($err) )
        {
            $this->_raiseSoapFault('Failed to decode the DIME message!','','','Server');
            $this->debug('Failed to decode the DIME message!');
            $this->setError('Failed to decode the DIME message!');
            return;
        }
        if (strcasecmp($dime->parts[0]['type'], $this->namespaces['SOAP-ENV']) != 0)
        {
            $this->_raiseSoapFault('DIME record 1 is not a SOAP envelop!','','','Server');
            $this->debug('DIME record 1 is not a SOAP envelop!');
            $this->setError('DIME record 1 is not a SOAP envelop!');
            return;
        }

        // return by reference
        $data = $dime->parts[0]['data'];
        $headers['content-type'] = 'text/xml;charset=utf-8';
        $headers["content-length"] = strlen($data);
        $c = count($dime->parts);
        for ($i = 1; $i < $c; $i++)
        {
            $part =& $dime->parts[$i];
            // XXX we need to handle URI's better
            //$id = strncmp( $part['id'], 'cid:', 4 ) ? 'cid:'.$part['id'] : $part['id'];
            $info = array('cid'         => $part['id'],
                           'filename'     => $part['id'],
                           'contenttype' => $part['type'],
                           'data'         => $part['data']
                                   );
            $this->requestAttachments[$part['id']] = $info;
            $this->debug('Attachment Type:' . $part['type'] . 'id/uri: '. $id );
        }
    }

    function validate_signatrue($xml_data){
        $parser = new shopexapi_validate_parser($this);
        $xdata = $parser->parse($xml_data);

        if ($this->validate_factory)
        {
            return call_user_func_array($this->validate_factory,array($xdata['CLIENTID'],$xdata['SOAP-BODY'],$xdata['DIGESTVALUE'],$xdata['DIGEST_ALGORITHM'],$xdata['METHOD'],$xdata['DIGEST_OPTIONS']));
        }

        return true;
    }
}

/*
 *    For backwards compatiblity
 */
class nusoapserverdime extends nusoap_server_dime {
}


class shopexapi_validate_parser{

    var $ns=null;

    function shopexapi_validate_parser(&$api_server){
        $this->api_server = &$api_server;
    }

    function parse($xml_data){
        $this->path=array();
        $xml_parser = xml_parser_create('UTF-8');
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($xml_parser, array(&$this,"start_element"), array(&$this,"end_element"));
        xml_set_character_data_handler($xml_parser, array(&$this,"character_data"));

        if (!xml_parse($xml_parser, $xml_data)) {
            $this->fault('Client', 'Request not of type text/xml');
        }
        xml_parser_free($xml_parser);
        preg_match('#\<'.str_replace('-','\-',$this->ns).'\:Body[^>]*\>(.*)\</'.str_replace('-','\-',$this->ns).'\:Body\>#i',$xml_data,$matchs);
        $this->result['SOAP-BODY'] = $matchs[1];
        return $this->result;
    }

    function start_element($parser, $name, $attrs){
        array_push($this->path,$name);
        if(!$this->ns){
            if(preg_match('#(.+)\:ENVELOPE#i',$name,$matchs)){
                $this->ns = $matchs[1];
            }
        }else{
            if($name == 'DIGESTMETHOD' && in_array('SHOPEX-SEC',$this->path)){
                $this->result['DIGEST_ALGORITHM'] = $attrs['ALGORITHM'];
                unset($attrs['ALGORITHM']);
                $this->result['DIGEST_OPTIONS'] = $attrs;
            }elseif(strtolower($name) == strtolower($this->ns.':body')){
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
            }elseif(strtolower($this->path[count($this->path)-2]) == strtolower($this->ns.':body')){                
                if (!isset($this->result['METHOD'])){
                    $this->result['METHOD'] = ($pos = strpos($name,':'))?substr($name,$pos+1):$name;                                            
                    if(isset($attrs['VERSION']) && $attrs['VERSION'])$this->api_server->cmd_version = $attrs['VERSION'];
                }
            }
        }
    }

    function character_data($parser,$data){
        if(in_array('SHOPEX-SEC',$this->path)){
            if(in_array('DIGESTVALUE',$this->path)){
                $this->result['DIGESTVALUE'] = $data;
            }elseif(in_array('CLIENTID',$this->path)){
                $this->result['CLIENTID'] = $data;
            }
        }
    }

    function end_element($parser, $name){
        array_pop($this->path);
    }
}

?>
