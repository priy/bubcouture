<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class shopexapi_validate_parser
{

    public $ns = NULL;

    public function shopexapi_validate_parser( &$api_server )
    {
        $this->api_server =& $api_server;
    }

    public function parse( $xml_data )
    {
        $this->path = array( );
        $xml_parser = xml_parser_create( "UTF-8" );
        xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, TRUE );
        xml_set_element_handler( $xml_parser, array(
            $this,
            "start_element"
        ), array(
            $this,
            "end_element"
        ) );
        xml_set_character_data_handler( $xml_parser, array(
            $this,
            "character_data"
        ) );
        if ( !xml_parse( $xml_parser, $xml_data ) )
        {
            $this->fault( "Client", "Request not of type text/xml" );
        }
        xml_parser_free( $xml_parser );
        preg_match( "#\\<".str_replace( "-", "\\-", $this->ns )."\\:Body[^>]*\\>(.*)\\</".str_replace( "-", "\\-", $this->ns )."\\:Body\\>#i", $xml_data, $matchs );
        $this->result['SOAP-BODY'] = $matchs[1];
        return $this->result;
    }

    public function start_element( $parser, $name, $attrs )
    {
        array_push( $this->path, $name );
        if ( !$this->ns )
        {
            if ( preg_match( "#(.+)\\:ENVELOPE#i", $name, $matchs ) )
            {
                $this->ns = $matchs[1];
            }
        }
        else if ( $name == "DIGESTMETHOD" && in_array( "SHOPEX-SEC", $this->path ) )
        {
            $this->result['DIGEST_ALGORITHM'] = $attrs['ALGORITHM'];
            unset( $attrs['ALGORITHM'] );
            $this->result['DIGEST_OPTIONS'] = $attrs;
        }
        else if ( strtolower( $name ) == strtolower( $this->ns.":body" ) )
        {
            xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, FALSE );
        }
        else if ( strtolower( $this->path[count( $this->path ) - 2] ) == strtolower( $this->ns.":body" ) && !isset( $this->result['METHOD'] ) )
        {
            $this->result['METHOD'] = ( $pos = strpos( $name, ":" ) ) ? substr( $name, $pos + 1 ) : $name;
            if ( isset( $attrs['VERSION'] ) && $attrs['VERSION'] )
            {
                $this->api_server->cmd_version = $attrs['VERSION'];
            }
        }
    }

    public function character_data( $parser, $data )
    {
        if ( in_array( "SHOPEX-SEC", $this->path ) )
        {
            if ( in_array( "DIGESTVALUE", $this->path ) )
            {
                $this->result['DIGESTVALUE'] = $data;
            }
            else if ( in_array( "CLIENTID", $this->path ) )
            {
                $this->result['CLIENTID'] = $data;
            }
        }
    }

    public function end_element( $parser, $name )
    {
        array_pop( $this->path );
    }

}

require_once( CORE_DIR."/lib/nusoap.php" );
require_once( "DIME.php" );
class nusoap_server_dime extends nusoap_server
{

    public $requestAttachments = array( );
    public $responseAttachments = array( );
    public $raw_post_data = NULL;
    public $dimeContentType = "application/dime";
    public $soap_defencoding = "UTF-8";
    public $decode_utf8 = FALSE;
    public $validate_factory = NULL;

    public function addAttachment( $data, $contenttype = "application/octet-stream", $type = NET_DIME_TYPE_UNKNOWN, $cid = FALSE )
    {
        if ( !$cid )
        {
            $cid = md5( uniqid( time( ) ) );
        }
        $info = array( );
        $info['data'] = $data;
        $info['filename'] = $cid;
        $info['contenttype'] = $contenttype;
        $info['cid'] = $cid;
        $info['type'] = $type;
        $this->responseAttachments[] = $info;
        return $cid;
    }

    public function clearAttachments( )
    {
        $this->responseAttachments = array( );
    }

    public function getAttachments( )
    {
        return $this->requestAttachments;
    }

    public function getHTTPBody( $soapmsg )
    {
        if ( 0 < count( $this->responseAttachments ) )
        {
            $soapmsg =& $this->_makeDIMEMessage( $soapmsg );
        }
        return parent::gethttpbody( $soapmsg );
    }

    public function getHTTPContentType( )
    {
        if ( 0 < count( $this->responseAttachments ) )
        {
            return $this->dimeContentType;
        }
        return parent::gethttpcontenttype( );
    }

    public function getHTTPContentTypeCharset( )
    {
        if ( 0 < count( $this->responseAttachments ) )
        {
            return FALSE;
        }
        return parent::gethttpcontenttypecharset( );
    }

    public function service( &$data )
    {
        $this->raw_post_data = $data;
        parent::service( $data );
    }

    public function parseRequest( $headers, $data )
    {
        $this->debug( "Entering parseRequest() for payload of length ".strlen( $data )." and type of ".$headers['content-type'] );
        $this->requestAttachments = array( );
        if ( strstr( $headers['content-type'], $this->dimeContentType ) )
        {
            $this->_decodeDIMEMessage( $headers, $data );
        }
        else
        {
            $this->debug( "Not dime content" );
        }
        if ( !$this->validate_signatrue( $data ) )
        {
            $errmsg = "SOAP Signatrue ERROR";
            if ( isset( $GLOBALS['validate_signatrue_errmsg'] ) )
            {
                $errmsg = $GLOBALS['validate_signatrue_errmsg'];
            }
            $this->fault( "TokenError", $errmsg );
        }
        return parent::parserequest( $headers, $data );
    }

    public function &_makeDIMEMessage( &$xml )
    {
        ( );
        $dime =& new Net_DIME_Message( );
        $msg =& $dime->encodeData( $xml, $this->namespaces['SOAP-ENV'], NULL, NET_DIME_TYPE_URI );
        $c = count( $this->responseAttachments );
        $this->debug( "Found ".$c." attachments" );
        $i = 0;
        for ( ; $i < $c; ++$i )
        {
            $att =& $this->responseAttachments[$i];
            $msg .= $dime->encodeData( $att['data'], $att['contenttype'], $att['cid'], NET_DIME_TYPE_MEDIA );
        }
        $msg .= $dime->endMessage( );
        return $msg;
    }

    public function _decodeDIMEMessage( &$headers, &$data )
    {
        ( );
        $dime =& new Net_DIME_Message( );
        $err = $dime->decodeData( $data );
        if ( PEAR::iserror( $err ) )
        {
            $this->_raiseSoapFault( "Failed to decode the DIME message!", "", "", "Server" );
            $this->debug( "Failed to decode the DIME message!" );
            $this->setError( "Failed to decode the DIME message!" );
            return;
        }
        if ( strcasecmp( $dime->parts[0]['type'], $this->namespaces['SOAP-ENV'] ) != 0 )
        {
            $this->_raiseSoapFault( "DIME record 1 is not a SOAP envelop!", "", "", "Server" );
            $this->debug( "DIME record 1 is not a SOAP envelop!" );
            $this->setError( "DIME record 1 is not a SOAP envelop!" );
            return;
        }
        $data = $dime->parts[0]['data'];
        $headers['content-type'] = "text/xml;charset=utf-8";
        $headers['content-length'] = strlen( $data );
        $c = count( $dime->parts );
        $i = 1;
        for ( ; $i < $c; ++$i )
        {
            $part =& $dime->parts[$i];
            $info = array(
                "cid" => $part['id'],
                "filename" => $part['id'],
                "contenttype" => $part['type'],
                "data" => $part['data']
            );
            $this->requestAttachments[$part['id']] = $info;
            $this->debug( "Attachment Type:".$part['type']."id/uri: ".$id );
        }
    }

    public function validate_signatrue( $xml_data )
    {
        ( $this );
        $parser = new shopexapi_validate_parser( );
        $xdata = $parser->parse( $xml_data );
        if ( $this->validate_factory )
        {
            return call_user_func_array( $this->validate_factory, array(
                $xdata['CLIENTID'],
                $xdata['SOAP-BODY'],
                $xdata['DIGESTVALUE'],
                $xdata['DIGEST_ALGORITHM'],
                $xdata['METHOD'],
                $xdata['DIGEST_OPTIONS']
            ) );
        }
        return TRUE;
    }

}

class nusoapserverdime extends nusoap_server_dime
{

}

?>
